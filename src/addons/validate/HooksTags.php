<?php
// +----------------------------------------------------------------------
// | TwoThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.twothink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 苹果 593657688@qq.com <www.twothink.cn>
// +----------------------------------------------------------------------
namespace think\addons\validate;

use think\Validate;

class HooksTags extends Validate{
    protected $rule  =[
        'hooks'=>'require'
        ,'addons'=>'require'
        ,'tags'=>'require'
        ,'description'=>'require'
    ];
    protected $message   = [
        'hooks.require' => 'hooks钩子必须'
        ,'addons.require' => 'addons插件标识必须'
        ,'tags.require' => 'tags插件标识必须'
        ,'description.require' => 'description描述必须'
    ];
}