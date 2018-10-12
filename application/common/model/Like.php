<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/9/13
 * Time: 14:35
 */

namespace app\common\model;


use think\Model;

class Like extends Model
{
    protected $name = 'likes';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

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
        return $this->belongsTo('Article','like_id');
    }

    /**
     * @param $user_id
     * @param like_id
     * @param $type 1=文章,2=评论
     *
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function isLike($user_id,$like_id,$type = '1')
    {
        $result = self::where('user_id',$user_id)->where('like_id',$like_id)->where('type',$type)->find();
        if($result != null) {
            return true;
        } else {
            return false;
        }
    }

    /** 是否存在
     * @param $data array
     *
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function isExist($data)
    {
        if(!is_array($data)) return false;

        $modelArr = ['1'=>'Article','2'=>'Comment'];

        if(!array_key_exists($data['type'],$modelArr)) return false;
        $model = model($modelArr[$data['type']]);

        $result = $model->where('id',$data['like_id'])->find();
        if(!$result) {
            return false;
        } else {
            return true;
        }
    }

    /** 点赞该文章的用户
     * @param $article_id
     *
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public static function LikeUser($article_id)
    {
        $ids = self::where('type','1')
            ->where('like_id',$article_id)
            ->order('createtime','desc')
            ->column('user_id');

        if(!empty($ids)) {
            $userModel = new User();
            $userList = $userModel::getUserList($ids);
            return $userList;
        } else {
            return [];
        }


    }
}