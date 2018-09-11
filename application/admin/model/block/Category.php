<?php

namespace app\admin\model\block;

use think\Model;

class Category extends Model
{
    // 表名
    protected $table = 'block_category';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'block_title'
    ];

    public function Block()
    {
        return $this->belongsTo('app\admin\model\block\Block','block_id','id');
    }

    public function getBlockTitleAttr($value,$data)
    {
        if($this->Block != null){
            // halt($this->Block());
            return $this->Block->title;
        }else{
            return '此分类已被删除';
        }
    }


    

    







}
