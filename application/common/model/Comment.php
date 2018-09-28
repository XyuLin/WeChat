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

    // 是否点赞评论  参数如果用户
    public static function isLike($array,$user_id)
    {
        $ids = pickIds($array);
        $existIds = Like::where('user_id',$user_id)->where('type','2')->where('like_id','in',$ids)->column('like_id');
        $intersect = array_intersect($ids,$existIds);

        foreach ($array as $k => &$v) {
            if(in_array($v['id'],$intersect)) {
                $v['isLike'] = true;
            } else {
                $v['isLike'] = false;
            }
        }
        unset($v);

        return $array;
    }

}