<?php

namespace app\admin\model\block;

use think\Model;

class Block extends Model
{
    // 表名
    protected $table = 'block';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [

    ];

    public function BlockCategory()
    {
        return $this->hasMany('app\admin\model\block\Category');
    }
    







}
