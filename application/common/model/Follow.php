<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/9/12
 * Time: 10:49
 */

namespace app\common\model;


use think\Model;
use think\Request;

class Follow extends Model
{
    protected $name = 'follows';
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
     * @param        $user_id
     * @param string $type 1=关注的用户,2=关注的版块
     * @param string $default
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserFollows($user_id, $type = '1',$default = '')
    {
        $ids = self::where('user_id',$user_id)->where('type',$type)->column('follow_id');
        if($default != '' && !in_array($default,$ids)) {
            return '错误的参数:default - 参数未在关注列表中!';
        }
        if($ids != null && !empty($ids)) {
            if($type == '1') {
                // 列出我关注的用户
                $userModel = new User();
                $data = $userModel->getUserlist($ids);
            } else {
                // 列出我关注的模块 判断请求是否为社区热门
                $request = Request::instance();
                $action = $request->action();
                if($action == 'communityhot') {
                    $action = '1';
                } else {
                    $action = '2';
                }
                $categoryModel = new BlockCategory;
                $data = $categoryModel->getBlockCategoryList($ids,$default,$action);
            }
            return $data;
        } else {
            return [];
        }
    }

    /**
     * @param $user_id
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserNoFollows($user_id)
    {
        $ids = self::where('user_id',$user_id)->where('type','2')->column('follow_id');
        $blockModel = new Block();
        $categoryModel = new BlockCategory();
        $block = $blockModel->select();
        $block = collection($block)->toArray();
        $category = $categoryModel->where('id','not in', $ids)->select();
        $category = collection($category)->toArray();
        // halt($block);
        foreach($block as &$value) {
            foreach($category as $item) {
                if($item['block_id'] == $value['id']){
                    $value['list'][]  = $item;
                }
            }
        }
        unset($value);
        return $block;
    }

    /**
     * @param $user_id
     * @param $follow_id
     * @param $type
     *
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function isFollow($user_id,$follow_id,$type)
    {
        $info = self::where('user_id',$user_id)->where('follow_id',$follow_id)->where('type',$type)->find();
        if($info) {
            return true;
        } else {
            return false;
        }
    }

}