<?php
// +----------------------------------------------------------------------
// | TwoThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.twothink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 艺品网络  82550565@qq.com <www.twothink.cn>
// +----------------------------------------------------------------------
namespace think\addons;

use think\Hook;
use think\Request;
use think\Config;
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
    public function execute()
    {
        $request = Request::instance();
        // 是否自动转换控制器和操作名
        $convert = Config::get('url_convert');
        $filter = $convert ? 'strtolower' : '';
        // 处理路由参数
        $addon = $request->param('addon', '', $filter);
        $controller = $request->param('controller', 'index', $filter);
        $action = $request->param('action', 'index', $filter);

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
                // 监听addons_init
                Hook::listen('addons_init', $this);
                return call_user_func_array([$model, $action], [$request]);
            } else {
                abort(500, lang('Controller Class Not Exists'));
            }
        }
        abort(500, lang('addon cannot name or action'));
    }
}
