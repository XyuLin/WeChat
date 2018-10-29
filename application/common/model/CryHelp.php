<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/24
 * Time: 16:26
 */

namespace app\common\model;


use think\Model;

class CryHelp extends Model
{
    protected $name = 'cry_help';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $append = [];

    private $status = '状态:0=信号,1=未完成,2=施救者数量上限,3=已完成,4=已取消';

    // 呼救 给附近的人发出信号
    public function callForHelp($user,$cry_id = '')
    {
        // 如果求救信号为空 则发出求救信号
        if($cry_id == '') {
            $param = [
                'user_id' => $user,
                'status'  => '1',
            ];
            // 发出求救信号
            $info = $this->create($param);
            $cry_id = $info->id;
        } else {
            // 如果有两名施救者，则不再继续发送信号给附近的人。
            $info = $this->where('id',$cry_id)->find();
            if($info->status == 2) {
                return ;
            }
        }
        // 检索附近的用户
        $map = new Map();
        $ids = $map->retrieval($user);
        // 如果当前附近没有用户，则直接返回空
        if(empty($ids)) {
            return [];
        }
        $rescue = new Rescue();
        $result = $rescue->pushAid($user,$ids);
    }
}