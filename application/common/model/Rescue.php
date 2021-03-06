<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/29
 * Time: 14:17
 */

namespace app\common\model;


use think\Model;

class Rescue extends Model
{
    protected $name = 'rescue';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $append = [];
    private $statusArr = '状态:1=未选择,2=接受,3=接单人数已满,4=拒绝,5=完成,6=被取消';
    // 发送援助请求
    public function pushAid($cry_id,$ids)
    {
        $cryModel = new CryHelp();
        // 循环呼救，找出该条信号已接收接受到呼救信息的用户。
        $invited_id = $this->where('cry_id',$cry_id)->column('invited_id');
        if(empty($invited_id)) {
            $isEmpty = true;
        } else {
            $isEmpty = false;
        }
        // 找出已经接受其他呼救的用户
        $busy = $this->where('status','in','1,2')->column('invited_id');

        // 找出正在发送呼救的用户
        $cryUser = $cryModel->where('status','not in','3,4')->where('id','neq',$cry_id)->column('user_id');
        $invited_id = array_merge($invited_id,$busy);
        $invited_id = array_merge($invited_id,$cryUser);
        //dump($ids);
        //dump($invited_id);

        if(!empty($invited_id)){
            // 找出两者之间不同的用户 过滤已接收的用户或者忙碌的用户
            $invited_id = array_unique($invited_id);
            foreach ($ids as $key => $value) {
                if(in_array($value,$invited_id)) {
                    unset($ids[$key]);
                }
            }
            // $ids = array_diff($invited_id,$ids);
        }
        //halt($ids);
        if(empty($ids)) {
            if($isEmpty == true) {
                return '附近未检测到空闲用户!';
            } else {
                return [];
            }
        }
        $array = [];
        foreach ($ids as $value) {
            $array[] = [
                'cry_id' => $cry_id,
                'invited_id' => $value,
                'status' => 1,
            ];
        }

        $result = $this->allowField(true)->saveAll($array);
        if($result == false) {
            return false;
        }
        return $ids;
    }

    // 查看当前状态
    public function getStatus($user,$cry_id)
    {
        $info = $this->where('user_id',$user)->where('cry_id',$cry_id)->find();
        if($info == null) {
            return false;
        }
        if($info->status == '1') {

        }
    }

    // 接单
    public function receipt($user,$cry_id)
    {
        $info = $this->where('invited_id',$user)->where('cry_id',$cry_id)->find();
        if($info == null) {
            $this->setCustomError('参数错误 - cry_id');
            return $this;
        }
        if($info->status != '1') {
            $this->setCustomError('不可重复接单!');
            return $this;
        }
        $model = new CryHelp();
        $cryInfo = $model->where('id',$cry_id)->find();
        if($cryInfo == null) {
            $this->setCustomError('参数错误 - cry_id');
            return $this;
        }

        // 等于0 或 等于1 表示 该呼救任务没有结束也没有达标
        if($cryInfo->status == '0' || $cryInfo->status == '1') {
            // 修改状态
            $info->status = '2';
            $info->save();

            if($cryInfo->status == '0') {
                $cryInfo->status = '1';
            }
            // 统计是否已满2名用户接单
            $count = $this->where('cry_id',$cry_id)->where('status','2')->count('id');
            if($count == '2') {
                $cryInfo->status = '2';
            }

            $cryInfo->save();
            return $info;

        } elseif($cryInfo->status == '2') {
            $this->setCustomError('施救者已数量上限!');
        } elseif($cryInfo->status == '3') {
            $this->setCustomError('该任务已完成!');
        } elseif($cryInfo->status == '4') {
            $this->setCustomError('该任务已取消!');
        }
        return $this;
    }

    // 判断是否有人接单
    public function getIsReceipt($cry_id)
    {
        $info = $this->where('cry_id',$cry_id)->where('status','2')->select();

        if($info == null) {
            return false;
        } else {
            return true;
        }
    }

    // 检测用户是否施救
    static public function checkIsRescue($user)
    {
        // 查询是否有求救信号或者接单记录
        $info = self::where('invited_id',$user)->where('status','in','1,2')->find();
        if($info != null) {
            return $info;
        } else {
            return NULL;
        }
    }
}