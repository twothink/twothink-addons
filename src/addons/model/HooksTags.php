<?php
// +----------------------------------------------------------------------
// | TwoThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.twothink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 艺品网络  82550565@qq.com <www.twothink.cn> 
// +----------------------------------------------------------------------
namespace think\addons\model;

use think\Validate;
use think\Exception;
use think\addons\validate\HooksTags as VadeHooksTags;
/*
 * 插件行为模型类
 *
 * @Author: 苹果  593657688@qq.com <www.twothink.cn>
 */
class HooksTags extends Base {

    protected $auto = ['update_time'];

    protected function setUpdateTimeAttr($value) {
        return time ();
    }
    /*
     * 数据验证
     *
     * @paran array $data 数据集
     * @Author: 苹果  593657688@qq.com <www.twothink.cn>
     */
    public function setValidate($data=[]){
        $VadeHooksTags = new VadeHooksTags();
        if (!$VadeHooksTags->check($data)) {
            throw new Exception($VadeHooksTags->getError());
        }
        return true;
    }
    public function setSave($dataSet = [], $where = [])
    {
        self::setValidate($dataSet);

        return parent::setSave($dataSet, $where); // TODO: Change the autogenerated stub
    }

    public function setSaveAll(array $dataSet = [])
    {
        foreach ($dataSet as $value){
            self::setValidate($value);
        }
        return parent::setSaveAll($dataSet); // TODO: Change the autogenerated stub
    }
}