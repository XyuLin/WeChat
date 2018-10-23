<?php

namespace app\admin\model;

use think\Model;

class Comments extends Model
{
    // 表名
    protected $table = 'comments';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'user_name',
        'article_title',
        'parent_name',
    ];

    public function User()
    {
        return $this->belongsTo('User');
    }

    public function Article()
    {
        return $this->belongsTo('Article');
    }


    public function getUserNameAttr($value,$data)
    {
        if($this->User == null) {
            return '账户已注销';
        } else {
            return $this->User->nickname;
        }

    }

    public function getArticleTitleAttr($value,$data)
    {
        if($this->Article == null) {
            return '视频已删除!';
        } else {
            return $this->changeStr($this->Article->title,'15');
        }
    }

    public function getParentNameAttr($value,$data)
    {
        if($data['parent_id'] != '0') {
            $info = $this->where('id',$data['parent_id'])->find();

            $user = User::where('id',$info['user_id'])->find();
            // halt($info['user_id']);
            if($user != null) {
                return $user->nickname;
            } else {
                return '账号已注销!';
            }
        } else {
            return '';
        }

    }







}
