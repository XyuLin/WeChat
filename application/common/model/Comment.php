<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/9/13
 * Time: 10:38
 */

namespace app\common\model;


use think\Model;

class Comment extends Model
{
    protected $name = 'comments';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    // 追加属性
    protected $append = [

    ];

    public function getCreatetimeAttr($value,$data)
    {
        return time_ago($value);
    }

    public function Article()
    {
        return $this->belongsTo('Article','article_id');
    }

//    public function isLike($id,$user_id)
//    {
//
//        $isExist = Like::where('type','2')->where('like_id',$data['id'])->find();
//        if($isExist != null) {
//            return true;
//        } else {
//            return false;
//        }
//    }

//    public function

}