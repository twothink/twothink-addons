<?php
// +----------------------------------------------------------------------
// | TwoThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.twothink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 苹果  593657688@qq.com <www.twothink.cn>
// +----------------------------------------------------------------------

namespace think\addons\model;

use think\facade\Log;
use think\Exception;
use think\addons\facade\HooksTags;
use think\addons\validate\Addons as VadeAddons;


class Addons extends Base {
    protected $name = 'addons';

    protected $auto = [
        'create_time'
    ];

    protected function setCreateTimeAttr($value) {
        return time ();
    }

    public function hooksTags(){
        return $this->belongsTo('think\addons\model\HooksTags','name','addons')->setEagerlyType(0);
    }

    /**
     * 获取插件列表
     *
     * @param string $addon_dir
     * @Author 苹果  593657688@qq.com
     */
    public function getList($addon_dir = '') {
        if (! $addon_dir)
            $addon_dir = TWOTHINK_ADDON_PATH;
        $dirs = array_map ( 'basename', glob ( $addon_dir . '*', GLOB_ONLYDIR ) );
        if ($dirs === FALSE || ! file_exists ( $addon_dir )) {
            throw new Exception('插件目录不可读或者不存在');
        }
        $addons = [];
        $list = $this->where ('name','in',$dirs)->field ( true )->select ();
        foreach ( $list as $key => $value ) {
            $list [$key] = $value->toArray ();
        }
        foreach ( $list as $addon ) {
            $addon ['uninstall'] = 0;
            $addons [$addon ['name']] = $addon;
        }
        foreach ( $dirs as $value ) {
            if (! isset ( $addons [$value] )) {
                $class = get_addon_class ( $value );
                if (! class_exists ( $class )) { // 实例化插件失败忽略执行
                    trace($class);
                    Log::record ( '插件' . $value . '的入口文件不存在！' );
                    continue;
                }
                $obj = new $class ();
                $addons [$value] = $obj->info;
                if ($addons [$value]) {
                    $addons [$value] ['uninstall'] = 1;
                    unset ( $addons [$value] ['status'] );
                }
            }
        }

        int_to_string ( $addons, [
                                    'status' =>[
                                        - 1 => '损坏',
                                        0 => '禁用',
                                        1 => '启用',
                                        null => '未安装'
                                    ]
                                ]
        );
        $addons = list_sort_by ( $addons, 'uninstall', 'desc' );
        return $addons;
    }
    /*
     * 插件安装
     *
     * @Author 苹果  593657688@qq.com
     */
    public function install($addon_name)
    {
        $class = get_addon_class ( $addon_name );
        $addons = new $class ();
        $info = $addons->info; //插件配置信息
        if (isset($addons->admin_list) && is_array ( $addons->admin_list ) && $addons->admin_list !== array ()) {
            $info ['has_adminlist'] = 1;
        } else {
            $info ['has_adminlist'] = 0;
        }
        $info ['config'] = json_encode (get_addon_config($addon_name));

        if ($this->save ( $info )) {
            //删除hooks_tags 旧数据
            HooksTags::setDelete([['addons','=',$addon_name]]);
            $addons_tags = isset($addons->addons_tags)?$addons->addons_tags:'';
            if($addons_tags){
                if(HooksTags::setSaveAll($addons_tags)){
                    cache ( 'hooks', null );
                }else{
                    $this->where ('name','=',$addon_name)->delete ();
                    throw new Exception('新增钩子行为失败');
                }
            }
            //新增钩子
            $addons_hooks = isset($addons->addons_hooks)?$addons->addons_hooks:'';
            if($addons_hooks){
                foreach ($addons_hooks as $value){
                    if(!db('hooks')->where('name','=',$value['name'])->find()){
                        $value['update_time'] = time();
                        db('hooks')->insert($value);
                    }
                }
            }
        } else {
            throw new Exception('写入插件数据失败');
        }
        return true;
    }
    /**
     * 卸载插件
     * @Author 苹果  593657688@qq.com
     */
    public function uninstall($addon_name) {
        session ( 'addons_uninstall_error', null );
        $class = get_addon_class ( $addon_name );
        $addons = new $class ();
        if(!$uninstall_flag = $addons->uninstall ()){
            throw new Exception('删除插件信息失败');
        }
        if(!$this->where('name','=',$addon_name)->delete()){
            throw new Exception('删除插件信息失败');
        }
        if (db( 'hooks' )->where('addons','=',$addon_name)->delete() === false) {
            throw new Exception('卸载插件所挂载的钩子数据失败');
        }
        if (db( 'hooks_tags' )->where('addons','=',$addon_name)->delete() === false) {
            throw new Exception('卸载插件所挂载的行为数据失败');
        }
        cache ( 'hooks', null );
        return true;
    }

    /*
     * 数据验证
     *
     * @paran array $data 数据集
     * @Author: 苹果  593657688@qq.com <www.twothink.cn>
     */
    public function setValidate($data=[]){
        $VadeHooksTags = new VadeAddons();
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