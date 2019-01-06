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

    private $statusArr = '状态:0=信号,1=未完成,2=施救者数量上限,3=已完成,4=已取消';

    // 呼救 给附近的人发出信号
    public function callForHelp($user,$cry_id = '')
    {
        // 判断用户是否接单施救任务
        $rescue = new Rescue();
        $info = $rescue->where('invited_id',$user)->where('status','in','1,2')->find();
        if($info != null) {
            $info->status = '4';
            $info->save();
        }
        // 如果求救信号为空 则发出求救信号
        if($cry_id == '') {
            $param = [
                'user_id' => $user,
                'status'  => '0',
            ];
            // 判断用户是否存在没有完成的呼救
            $info = $this->where('user_id',$user)->where('status','in','0,1,2')->find();
            if($info == null) {
                // 发出求救信号
                $info = $this->create($param);
                // 记录求救次数
                $userInfo = User::get($user);
                $userInfo->rescue_num = ['inc','1'];
                $userInfo->save();
                $cry_id = $info->id;
            } else {
                $cry_id = $info->id;
            }
        } else {
            // 如果有两名施救者，则不再继续发送信号给附近的人。
            $info = $this->where('id',$cry_id)->find();
            if($info->status == 2) {
                return false;
            }
        }
        return $cry_id;
    }

    // 取消或者完成求救
    public function complete($user,$cry_id)
    {
        $info = $this->where('user_id',$user)->where('id',$cry_id)->find();
        // 如果查询结果为NULL 表示 参数错误
        if($info == null) {
            $this->setCustomError('参数错误 - cry_id');
            return $this;
        }

        $rescue = new Rescue();
        $list = $rescue->where('cry_id',$cry_id)->where('status','2')->column('id');

        if(empty($list)) {
            // 如果没有接单的用户，则表示取消
            $info->status = '4';
            $rescue->where('id','in',$list)->update([
                'status' => '6',  // 被取消
            ]);
            $rescue->where('cry_id',$cry_id)->where('status','1')->update([
                'status' => '6',
            ]);
        } else {
            // 完成
            $info->status = '3';
            $list = $rescue->where('cry_id',$cry_id)->column('id');
            $rescue->where('id','in',$list)->update([
                'status' => '5', // 完成
            ]);
        }

        $info->save();
        return $info;
    }

    // 求救详情
    public function getDetail($cry_id,$user)
    {
        // 判断用户是求救者还是施救者
        $array = [];
        $info = $this->where('id',$cry_id)->find();
        $model = new Rescue();
        $rescue = collection($model->where('cry_id',$cry_id)->where('status','2')->select())->toArray();
        $help_ids['0'] = $info['user_id'];

        if(empty($rescue)) {
                $invited_ids = [];
        } else {
            foreach ($rescue as $value) {
                $invited_ids[] = $value['invited_id'];
            }
        }


        $ids = array_merge($help_ids,$invited_ids);
        $userList = User::getUserList($ids,true);
        $userPosition = Map::getUserPosition($ids);

        foreach($userList as $key => $val) {
            foreach($userPosition as $v) {
                if($val['id'] == $v['user_id']) {
                    $array[$key] = $val;
                    $array[$key]['lat'] = $v['lat'];
                    $array[$key]['lng'] = $v['lng'];
                }
            }
        }
        $data = [
            'cry'    => [],
            'rescue' => [],
        ];

        if(in_array($info['status'],['3','4'])) {
            $data['status'] = 'false';
        } else {
            $data['status'] = 'true';
        }
        foreach ($array as $value) {
            if($help_ids[0] == $value['id']) {
                $data['cry'] = $value;
            } else {
                $data['rescue'][] = $value;
            }
        }
        return $data;
    }

    // 检测用户是否求救
    static public function checkIsCry($user)
    {
        $info = self::where('user_id',$user)->where('status','not in','3,4')->find();

        if($info != null) {
            return $info;
        } else {
            return NULL;
        }
    }
}