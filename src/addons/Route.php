<?php
// +----------------------------------------------------------------------
// | TwoThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.twothink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 艺品网络  82550565@qq.com <www.twothink.cn>
// +----------------------------------------------------------------------
namespace think\addons;

use think\facade\Hook;
use think\facade\Config;
/**
 * 插件执行默认控制器
 * Class AddonsController
 * @Author: 艺品网络  82550565@qq.com <www.twothink.cn>
 */
class Route extends Controller
{
    /**
     * 插件执行
     */
    public function execute($addon = null, $controller = null, $action = null)
    {
        $request = Request();
        // 是否自动转换控制器和操作名
        $convert = Config::get('url_convert');
        $filter = $convert ? 'strtolower' : 'trim';
        // 处理路由参数
        $addon = $addon ? call_user_func($filter, $addon) : '';
        $controller = $controller ? call_user_func($filter, $controller) : 'index';
        $action = $action ? call_user_func($filter, $action) : 'index';

        if (!empty($addon) && !empty($controller) && !empty($action)) {
            // 设置当前请求的控制器、操作
            $request->controller($controller)->action($action);
            // 获取类的命名空间
            $class = get_addon_class($addon, 'controller', $controller);
            if (class_exists($class)) {
                $model = new $class();
                if ($model === false) {
                    abort(500, lang('addon init fail'));
                }
                // 调用操作
                if (!method_exists($model, $action)) {
                    abort(500, lang('Controller Class Method Not Exists'));
                }
                // 监听addon_init
                Hook::listen('addon_init', $this);
                return call_user_func_array([$model, $action], [$request]);
            } else {
                abort(500, lang('Controller Class Not Exists'));
            }
        }
        abort(500, lang('addon cannot name or action'));
    }
}
