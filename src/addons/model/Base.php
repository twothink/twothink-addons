<?php
// +----------------------------------------------------------------------
// | TwoThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.twothink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 艺品网络  82550565@qq.com <www.twothink.cn> 
// +----------------------------------------------------------------------
namespace think\addons\model;

use think\Model;

class Base extends Model{

    /*
     * 新增更新
     *
     * @param array  $dataSet 数据集
     * @param array $where    查询条件
     * @Author: 苹果  593657688@qq.com <www.twothink.cn>
     */
    public function setSave($dataSet = [],$where = []){
        if($where){
            $res = $this->save($dataSet,$where);
        }else{
            $res = $this->data($dataSet)->save();
        }
        return $res;
    }
    /*
     * 批量新增更新
     *
     * @param array  $dataSet 数据集
     * @Author: 苹果  593657688@qq.com <www.twothink.cn>
     */
    public function setSaveAll(array $dataSet = []){
        return $res = $this->saveAll($dataSet);
    }
    /*
     * 删除
     *
     * @param array  $where 删除条件
     * @Author: 苹果  593657688@qq.com <www.twothink.cn>
     */
    public function setDelete(array $where = []){
        return $res = $this->where($where)->delete();
    }
    /**
     * 返回模型的错误信息
     * @access public
     * @return string|array
     */
    public function getError()
    {
        return $this->error;
    }
}