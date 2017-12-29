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

class Addons extends Validate{
    protected $rule  =[
        'name'=>'require|unique:addons'
        ,'title'=>'require'
        ,'description'=>'require'
    ];
    protected $message   = [
        'name.require' => '标识必须'
        ,'name.unique' => '标识已存在'
        ,'title.require' => '标题必须'
        ,'description.require' => '描述必须'
    ];
}