<?php
// +----------------------------------------------------------------------
// | TwoThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.twothink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 苹果  593657688@qq.com <www.twothink.cn>
// +----------------------------------------------------------------------

use think\facade\App;
use think\facade\Hook;
use think\Loader;
use think\facade\Cache;
use think\facade\Route;
use think\facade\Env;
use think\Exception;
use think\addons\model\Addons as AddonsModel;

// 插件目录
define('TWOTHINK_ADDON_PATH', dirname(realpath(Env::get('app_path'))) . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR);
define('DS',DIRECTORY_SEPARATOR);
// 定义路由
Route::any('addon/:addon/[:controller]/[:action]', "\\think\\addons\\Route@execute");
//Route::any('/', "\\think\\addons\\Route@execute?addon=forum&controller=index&action=index");

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
        $data = App::isDebug() ? [] : Cache::get('hooks');
        if (count($data) < 1) {
            $hooks =(new AddonsModel())
                ->with("hooksTags")
                ->where('addons.status','=',1)
                ->where('hooksTags.status','=',1)
                ->select();

            $addons = [];
            if($hooks){
                $hooks_tags = array_column($hooks->toArray(),'hooks_tags');

                foreach ($hooks_tags as $value){
                    $addons[$value['hooks']] = $value['tags'];
                    Hook::add($value['hooks'],$value['tags']);
                }
            }

            $addon_autoload_files = [];
            $addons_list  = db('addons')->where('status','=',1)->select();
            foreach ($addons_list as $value){
                $commonPath = TWOTHINK_ADDON_PATH.$value['name'].'/common.php';
                if (file_exists($commonPath)) {
                    $addon_autoload_files[] =  $commonPath;
                }
            }
            Cache::set('hooks', $addons);
            Cache::set('addon_autoload_files',$addon_autoload_files);
            addon_autoload_files($addon_autoload_files);
        } else {
            Hook::import($data, false);
            addon_autoload_files(Cache::get('addon_autoload_files'));
        }
    }
    catch (Exception $exception){
        throw new Exception($exception->getMessage());
    }
});

//加载函数文件
function addon_autoload_files($list){
    foreach ($list as $value){
        include $value;
    }
}

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
 * 检测插件是否安装或者禁用
 */
function is_addons($name){
    $map['name'] = $name;
    $map['status'] = 1;
    $id  =   db('addons')->where($map)->value('id');
    return $id?:false;
}
/**
 * @title 获取插件的配置数组
 * @param string $name 插件名
 * @return array|mixed|null
 */
function get_addon_config($name){
    static $_config = [];
    if (isset($_config[$name])) {
        return $_config[$name];
    }
    $map['name'] = $name;
    $map['status'] = 1;
    $config  =   db('addons')->where($map)->value('config');
    if($config){
        $config   =  json_decode($config, true);
    }else{
        $addon_path = TWOTHINK_ADDON_PATH . $name. DS. 'config.php';
        $config = null;
        if(is_file($addon_path)){
            $addon_config = include $addon_path;

            $config = (new think\modelinfo\Quiet())->info($addon_config)
                ->getFields()
                ->FieldDefaultValue()
                ->getParam('info.field_default_value');
        }
    }
    $_config[$name] = $config;
    return $config;
}

/**
 * 插件显示内容里生成访问插件的url
 * @param $url
 * @param array $param
 * @return bool|string
 * @param bool|string $suffix 生成的URL后缀
 * @param bool|string $domain 域名
 */
function addon_url($url, $param = [], $suffix = true, $domain = false)
{
    return url("@addon/{$url}", $param, $suffix, $domain);
}

/**
 * 自动定位模板文件
 * @access private
 * @param  string $template 模板文件规则
 * @param  string $layered  模版文件夹 不知道文件夹设置 false
 * @return string
 */
function T($template,$layered = 'view')
{
    // 获取视图根目录
    if (strpos($template, '@')) {
        // 跨模块调用
        list($module, $template) = explode('@', $template);
    }
    if($layered === false){
        $layered = '';
    }else{
        $layered = $layered.'/';
    }
    $path = dirname(realpath(Env::get('app_path'))) . '/addons/'.$module.'/'.$layered;
    return $path.$template.'.html';
}