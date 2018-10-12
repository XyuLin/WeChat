<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/11
 * Time: 13:46
 */

namespace app\common\model;


use think\Model;

class News extends Model
{
    protected $name = 'news';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected $append = [];

    /** 添加新消息
     *
     * @param        $user_id
     * @param string $type
     * @param        $article_id
     * @param string $comment_id
     *
     * @return \app\common\model\News
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function addNewMessage($user_id,$type = '1', $article_id , $comment_id = '')
    {
        $model = new News();
        $typeArr = ['点赞'=>'1','分享'=>'2','评论'=>'3'];
        if(!in_array($type, $typeArr)) {
            $model->setCustomError('类型参数无效');
            return $model;
        }

        $articleModel = new Article;
        $article = $articleModel->where('id',$article_id)->find();

        if($article == null) {
            $model->setCustomError('文章参数无效');
            return $model;
        }

        if($type == '3' && !is_numeric($comment_id)) {
            $model->setCustomError('评论参数无效');

            return $model;
        }

        $param = [
            'user_id'       => $article->user_id,
            'type'          => $type,
            'article_id'    => $article_id,
            'comment_id'    => $comment_id,
            'status'        => '1',
        ];

        if($param['user_id'] == $user_id) {
            return $model;
        }
        $result = $model->create($param);
        if($result) {
            return $model;
        } else {
            $model->setCustomError('添加新消息失败!');
            return $model;
        }

    }

    /** 查看消息
     * @param $news_id
     * @param $user_id
     *
     * @return bool
     * @throws \think\exception\DbException
     */
    public static function viewMessage($user_id,$news_id)
    {
        $model = new News();
        $message = $model->where('user_id',$user_id)->where('id',$news_id)->find();
        if($message == null) {
            $model->setCustomError('参数无效!');
            return $model;
        }

        if($message->status == '2') {
            return $message;
        }

        $message->status = '2';
        if($message->save() > 0) {
            return $message;
        } else {
            $message->setCustomError('查看信息失败!');
            return $message;
        }
    }

    /**查看我的新消息
     *
     * @param        $user_id
     * @param string $type
     *
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMyMessage($user_id,$type = '1')
    {
        if($type == '1' || $type == '2') {
            $typeWhere['type'] = ['neq','3'];
            $column = 'id,comment_id,type';
        } else {
            $typeWhere['type'] = ['eq','3'];
            $column = 'comment_id';
            $model = new Comment();
        }
        $ids = self::where('user_id',$user_id)->where($typeWhere)->column($column);
        if(empty($ids)) {
            return [];
        } else {
            if($type == '3') {
                $result = $model->where('id','in',$ids)->select();
            } else {
                $shareArr = $likeArr = [];
                foreach ($ids as $item => $value) {
                    if($value['type'] == '2'){
                        $shareArr[] = $value['comment_id'];
                    } else {
                        $likeArr[] = $value['comment_id'];
                    }
                }

                if(!empty($shareArr)) {
                    $share = Share::where('id', 'in', $shareArr)->select();
                } else {
                    $share = [];
                }

                if(!empty($likeArr)) {
                    $like = Like::where('id', 'in', $likeArr)->select();
                } else {
                    $like = [];
                }

                $result = array_merge($share,$like);
            }

            if(!empty($result)){
                foreach($result as &$value) {
                    $value->article;
                }
                unset($value);

                return $result;
            } else {
                return [];
            }
        }
    }
}