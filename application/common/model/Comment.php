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
        'create_text'
    ];

    public function getCreateTextAttr($value,$data)
    {
        return time_ago($data['createtime']);
    }

    public function getCreatetimeAttr($value,$data)
    {
        return date('Y-m-d H:i', $value);
    }

    public function Article()
    {
        return $this->belongsTo('Article','article_id');
    }

    public function User()
    {
        return $this->belongsTo('User','user_id');
    }

    // 是否点赞评论  参数如果用户
    public static function isLike($array,$user_id)
    {
        $ids = pickIds($array);
        $existIds = Like::where('user_id',$user_id)->where('type','2')->where('like_id','in',$ids)->column('like_id');
        $intersect = array_intersect($ids,$existIds);

        $url = \think\Config::get('url');
        foreach ($array as $k => &$v) {
            if(in_array($v['id'],$intersect)) {
                $v['isLike'] = true;
            } else {
                $v['isLike'] = false;
            }

            // 用户信息
            $v['nickname'] = $v->User->nickname;
            $v['avatar'] = $url . $v->User->avatar;
            unset($v->User);
        }
        unset($v);

        $arr = [];
        foreach ($array as $value) {
            $arr[$value['id']] = $value;
        }

        foreach($arr as $item => &$value) {
            if($value['parent_id'] != 0) {
                $value['parent'] = [
                    'id'        =>  $arr[$value['parent_id']]['id'],
                    'user_id'   =>  $arr[$value['parent_id']]['user_id'],
                    'avatar'    =>  $arr[$value['parent_id']]['avatar'],
                    'nickname'  =>  $arr[$value['parent_id']]['nickname'],
                ];
            }
        }
        unset($value);

        // 重置键位
        return array_values($arr);
    }


    public function getComment($user_id,$page = '1')
    {
        $list = $this->where('user_id',$user_id)
            ->limit('10')
            ->page($page)
            ->order('createtime','desc')
            ->select();

        $total = $this->where('user_id',$user_id)->count();

        foreach ($list as $item => &$value) {
            $value['article'] = $value->Article;
        }
        unset($value);

        $data['list'] = $list;
        $data['total'] = $total;
        return $data;
    }

}