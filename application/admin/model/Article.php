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
        'user_name'
    ];

    public function User()
    {
        return $this->belongsTo('app\admin\model\User','user_id');
    }


    public function getUserNameAttr()
    {
        if($this->User != null) {
            return $this->User->nickname;
        } else {
            return '此会员以注销';
        }
    }

    public function getTitleAttr($value,$data)
    {
        return $this->changeStr($value,'15');
    }





}
