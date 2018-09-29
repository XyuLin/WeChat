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

    public function getCollection($user_id, $page = '1')
    {
        $ids = $this->where('user_id',$user_id)
            ->limit('10')
            ->page($page)
            ->order('createtime','desc')
            ->column('article_id');
        $total = $this->where('user_id',$user_id)->count();

        if(!empty($ids)){
            $model = new Article();
            $list = $model->where('id','in',$ids)->select();
            $list = $model->splicingUrl($list);
        } else {
            $list  = [];
        }

        $data['list'] = $list;
        $data['total'] = $total;
        return $data;
    }

}