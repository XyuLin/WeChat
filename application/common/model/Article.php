<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/9/12
 * Time: 10:34
 */

namespace app\common\model;


use think\Model;

class Article extends Model
{
    protected $name = 'article';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected $append = [
        'create_text',
        'block_category_text'
    ];

    public function getCreateTextAttr($value,$data)
    {
        return time_ago($data['createtime']);
    }

    public function getCreatetimeAttr($value,$data)
    {
        return date('Y-m-d H:i',$value);
    }

    public function getUpdatetimeAttr($value,$data)
    {
        return date('Y-m-d H:i',$value);
    }

    public function getBlockCategoryTextAttr($value,$data)
    {
        $model = new BlockCategory();
        if($name = $model->where('id',$data['block_category_id'])->value('title')) {
            return $name;
        } else {
            return '';
        }
    }

    public function User()
    {
        return $this->belongsTo('User');
    }

    public function Comment()
    {
        return $this->hasMany('Comment');
    }

//    public function Like()
//    {
//        return $this->hasMany('Like','like_id');
//    }
//
//    public function Share()
//    {
//        return $this->hasMany('Share');
//    }

    /**
     * @param string $category_id
     * @param string $page
     * @param string $type  1 = 文章  ， 2 = 视频
     *
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getHotArticle($category_id = '',$page = '1', $type = '1' )
    {
        if($category_id != '') {
            $array = $this->where('block_category_id',$category_id)->where('type',$type)->limit('10')->page($page)->order('id desc')->select();
            $total = $this->where('block_category_id',$category_id)->where('type',$type)->count();
        } else {
            $array = $this->where('type',$type)->limit('10')->page($page)->order('id desc')->select();
            $total = $this->where('type',$type)->count();
        }

        $data['list'] = $this->splicingUrl($array);
        $data['total'] = $total;
        return $data;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function splicingUrl($array = [])
    {
        $url = \think\Config::get('url');
        if(!empty($array)){
            foreach($array as $key => &$value) {
                $images = explode(',',$value['images']);
                foreach($images as &$item) {
                    $item = $url.$item;
                }
                unset($item);
                $value['images'] = $images;
                $value['user'] = [
                    'nickname' => $value->User->nickname,
                    'avatar'    => $url . $value->User->avatar,
                    'id'        => $value->User->id,
                ];
                unset($value['User']);

            }
            unset($value);
        }

        return $array;
    }

    /**
     * @param $category_id
     * @param $user_id
     * @param $page
     * @param $type  1 = 文章 ，2 = 视频
     *
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public function recommendUser($category_id,$user_id,$page = '1',$type = '1')
    {
        $str = '找出在本版块发布过文章的用户，排序(规则) 
            用文章的热度来排序，重复会员去重。' ;
        $ids = $this->where('block_category_id',$category_id)->where('type',$type)->order('comments desc,shares desc,likes desc')->limit('4')->page($page)->column('user_id');
        // 推荐用户id
        $ids = array_unique($ids);

        $follow = new Follow();
        $list = $follow->where('user_id',$user_id)->where('type','1')->column('follow_id');
        // 用户自身关注的列表
        $list = array_merge([$user_id],$list);
        $ids = array_diff($ids,$list);

        if(!empty($ids)) {
            $array = User::getUserList($ids);
        }else{
            $array = [];
        }
        return $array;
    }


    public static function getArticleDetail($article_id,$user_id)
    {
        $detail = self::where('id',$article_id)->find();
        $url = \think\Config::get('url');

        $images = explode(',',$detail['images']);

        foreach($images as &$item) {
            $item = $url . $item;
        }
        unset($item);
        $detail['images'] = $images;
        // 作者信息
        $detail['author'] = [
            'nickname'  =>  $detail->User->nickname,
            'avatar'    =>  $detail->User->avatar,
        ];
        // 评论信息
        $detail['comment'] = Comment::isLike($detail->Comment,$user_id);
        // 点赞用户信息
        $detail['likeUser'] = Like::LikeUser($detail['id']);
        // 判断用户是否收藏，是否点赞，是，是否关注作者
        $detail['isFollow'] = Follow::isFollow($user_id,$detail['user_id'],'1');
        $detail['isCollection'] = Collection::isCollection($user_id,$detail['user_id']);
        $detail['isLike'] = Like::isLike($user_id,$detail['id']);
        unset($detail->User);
        unset($detail->Comment);
        return $detail;
    }

    /**
     * @param $article_id
     * @param $type string [1=>'like','2'=>'share',3=>'comment']
     * @param $option string [1=>'inc',2=>'dec']
     * @param $is_comment false 不是评论 true 是评论
     *
     * @return bool
     * @throws \think\exception\DbException
     */
    public static function plusLessOneType($article_id,$type,$option,$is_comment = false)
    {
        strtolower($type);
        $typeArr = [1=>'like','2'=>'share',3=>'comment'];
        $optionArr = [1=>'inc','2'=>'dec'];
        if(!in_array($type,$typeArr) && in_array($option,$optionArr)) {
            return false;
        } else {
            $type =  array_search($type,$typeArr);
        }

        if($is_comment == true) {
            if($type != '1') return false; // 评论点赞，type 只能是 like  1
            $info = Comment::where('id',$article_id)->find();
        } else {
            $info = self::get($article_id);
        }

        if($type == '1'){
            $info->likes = [$option,1];
        } elseif($type == '2') {
            $info->shares = [$option,1];
        } elseif($type == '3') {
            $info->comments = [$option,1];
        }
        if($info->save()) {
            return true;
        } else {
            return false;
        }
    }

    // 我发表的文章
    public function getPublish($user_id,$page = '1')
    {
        $list = $this->where('user_id',$user_id)
            ->order('createtime','dsec')
            ->limit('10')
            ->page($page)
            ->select();

        $total = $this->where('user_id',$user_id)->count();

        $data['list'] = $list;
        $data['total'] = $total;
        return $data;
    }
}