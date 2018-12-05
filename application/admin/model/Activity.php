<?php

namespace app\admin\model;

use think\Model;

class Activity extends Model
{
    // 表名
    protected $table = 'activity';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [

    ];

    public function getActivityStartAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['activity_start']) ? $data['activity_start'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getActivityEndAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['activity_end']) ? $data['activity_end'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setActivityStartAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setActivityEndAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }
    







}
