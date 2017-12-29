<?php
// +----------------------------------------------------------------------
// | TwoThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.twothink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 苹果  593657688@qq.com <www.twothink.cn> 
// +----------------------------------------------------------------------

namespace think\addons\model;

use think\model\concern\SoftDelete;
/*
 * @Author: 苹果  <593657688@qq.com>
 */
class Hooks extends Base{

    use SoftDelete;
    protected $deleteTime = 'delete_time'; //软删除字段

    public function hooksTags(){
        return $this->hasMany('think\addons\model\HooksTags','hooks','name')->order('sort asc');
    }

    protected function getTypeAttr($value){
        $hooks_type = config('hooks_type.');
        return isset($hooks_type[$value])?$hooks_type[$value]:$value;
    }
}