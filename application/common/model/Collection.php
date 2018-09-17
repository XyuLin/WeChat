<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/9/13
 * Time: 14:31
 */

namespace app\common\model;


use think\Model;

class Collection extends Model
{
    protected $name = 'collections';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    public function getCreatetimeAttr($value,$data)
    {
        return time_ago($value);
    }

    /**
     * @param $user_id
     * @param $article_id
     *
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function isCollection($user_id,$article_id)
    {
        $result = self::where('user_id',$user_id)->where('article_id',$article_id)->find();
        if($result != null) {
            return true;
        } else {
            return false;
        }
    }

}