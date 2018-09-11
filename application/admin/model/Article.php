<?php

namespace app\admin\model;

use think\Model;

class Article extends Model
{
    // 表名
    protected $table = 'article';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'block_category_title',
        'user_name'
    ];

    public function BlockCategory()
    {
        return  $this->belongsTo('app\admin\model\block\Category','block_category_id');
    }

    public function User()
    {
        return $this->belongsTo('app\admin\model\User','user_id');
    }

    public function getBlockCategoryTitleAttr()
    {
        if($this->BlockCategory() != null) {
            return $this->BlockCategory->title;
        } else {
            return '此类型已删除!';
        }
    }

    public function getUserNameAttr()
    {
        if($this->User() != null) {
            return $this->User->nickname;
        } else {
            return '此会员以注销';
        }
    }






}
