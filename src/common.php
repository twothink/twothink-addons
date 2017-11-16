<?php
// +----------------------------------------------------------------------
// | TwoThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.twothink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 艺品网络  82550565@qq.com <www.twothink.cn>
// +----------------------------------------------------------------------

use think\App;
use think\Hook;
use think\Loader;
use think\Cache;
use think\Route;
use think\Db;

// 插件目录
define('TWOTHINK_ADDON_PATH', ROOT_PATH . 'addons' . DS);

// 定义路由
Route::any('addons/:addon/[:controller]/[:action]', "\\think\\addons\\Route@execute");

// 如果插件目录不存在则创建
if (!is_dir(TWOTHINK_ADDON_PATH)) {
    @mkdir(TWOTHINK_ADDON_PATH, 0777, true);
}

// 注册类的根命名空间
Loader::addNamespace('addons', TWOTHINK_ADDON_PATH);

// 闭包初始化行为
Hook::add('app_init', function () {
    try {
        // 获取系统配置
        $data = App::$debug ? [] : Cache::get('hooks');
        if (empty($data)) {
            $hooks = Db::name('Hooks')->column('name,addons');
            foreach ($hooks as $key => $value) {
                if ($value) {
                    $map['status'] = 1;
                    $names = explode(',', $value);
                    $map['name'] = array('IN', $names);
                    $data = Db::name('Addons')->where($map)->column('id,name');
                    if ($data) {
                        $addons_arr = array_intersect($names, $data);
                        $addons[$key] = array_map('get_addon_class', $addons_arr);
                        Hook::add($key, $addons[$key]);
                    }
                }
            }
            Cache::set('hooks', $addons);
        } else {
            Hook::import($data, false);
        }
    }
    catch (Exception $e){
//        dump($e);
    }
});

/**
 * 处理插件钩子
 * @param string $hook 钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook, $params = [])
{
    Hook::listen($hook, $params);
}

/**
 * 获取插件类的类名
 * @param $name 插件名
 * @param string $type 返回命名空间类型
 * @param string $class 当前类名
 * @return string
 */
function get_addon_class($name, $type = 'hook', $class = null)
{
    $name = Loader::parseName($name);
    // 处理多级控制器情况
    if (!is_null($class) && strpos($class, '.')) {
        $class = explode('.', $class);
        foreach ($class as $key => $cls) {
            $class[$key] = Loader::parseName($cls, 1);
        }
        $class = implode('\\', $class);
    } else {
        $class = Loader::parseName(is_null($class) ? $name : $class, 1);
    }
    switch ($type) {
        case 'controller':
            $namespace = "\\addons\\" . $name . "\\controller\\" . $class;
            break;
        default:
            $namespace = "\\addons\\" . $name . "\\" . $class;
    }

    return class_exists($namespace) ? $namespace : '';
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 * @return array
 */
function get_addon_config($name)
{
    $class = get_addon_class($name);
    if (class_exists($class)) {
        $addon = new $class();
        return $addon->getConfig();
    } else {
        return [];
    }
}

/**
 * 插件显示内容里生成访问插件的url
 * @param $url
 * @param array $param
 * @return bool|string
 * @param bool|string $suffix 生成的URL后缀
 * @param bool|string $domain 域名
 */
function addons_url($url, $param = [], $suffix = true, $domain = false)
{
    return url("@addons/{$url}", $param, $suffix, $domain);
}