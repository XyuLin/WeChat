<?php

namespace app\admin\model\motion;

use think\Config;
use think\Model;

class Task extends Model
{
    // 表名
    protected $table = 'motion_task';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'module_text'
    ];


    public function Module()
    {
        return $this->belongsTo('Module','module_id');
    }

    public function getModuleTextAttr($value, $data)
    {
        if($this->Module == null){
            return '模块不存在!';
        } else {
            return $this->Module->title;
        }
    }

    public function getUrlAttr($value,$data)
    {
        $url = Config::get('url');
        return empty($value) ? '' : $url .$value;
    }

    







}
