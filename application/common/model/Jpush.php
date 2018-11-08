<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/29
 * Time: 10:30
 */

namespace app\common\model;


use think\Model;
use Jpush\Client;
use Jpush\Config as JpConfig;

class Jpush extends Model
{
    protected $name = 'jpush';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $append = [];

    // 绑定用户与设备id
    public function bindOnlyId($user,$only)
    {
        $info = $this->where('user_id',$user)->find();
        if($info != null) {
            $info->only_id = $only;
            $result = $info->save();
            if($result !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            $param = [
                'user_id' => $user,
                'only_id' => $only,
            ];
            $info = $param->create($param);
            return true;
        }
    }

    // 推送
    public function push($userArray,$title = '救救我,我快不行了',$alert = '救救我',$extras = [])
    {
        // 获取需要推送的用户设备
        // $registration_ids = $this->where('user_id','in',$userArray)->column('only_id');
        $client = new Client(JpConfig::APP_KEY,JpConfig::SECRET);
        if(!isset($extras['type'])) {
            return $msg = [
                'code'  => '0',
                'msg'   => '参数错误 - jpush.type',
            ];
        } elseif($extras['type'] == '1') {
            // 求救信号
            if($extras['cry_id'] == '') {
                return $msg = [
                  'code' => '0',
                  'msg' => '参数错误 - jpush.cry_id',
                ];
            }
        }
        $userArray = ['10'];
        try {
            $response = $client->push()
                ->setPlatform(array('ios', 'android'))
                // 一般情况下，关于 audience 的设置只需要调用 addAlias、addTag、addTagAnd  或 addRegistrationId
                // 这四个方法中的某一个即可，这里仅作为示例，当然全部调用也可以，多项 audience 调用表示其结果的交集
                // 即是说一般情况下，下面三个方法和没有列出的 addTagAnd 一共四个，只适用一个便可满足大多数的场景需求
                // 推送别用
                ->addAlias($userArray)
                // ->addTag(array('tag1', 'tag2'))
                // ->addRegistrationId($registration_ids)

                ->setNotificationAlert($alert)
                ->iosNotification($alert, array(
                    'sound' => 'sound.caf',
                    // 'badge' => '+1',
                    // 'content-available' => true,
                    // 'mutable-content' => true,
                    'category' => 'jiguang',
                    'extras' => $extras,
                ))
                ->androidNotification($alert, array(
                    'title' => $title,
                    // 'builder_id' => 2,
                    'extras' => $extras,
                ))
                ->message('message content', array(
                    'title' => 'hello jpush',
                    // 'content_type' => 'text',
                    'extras' => array(
                        'key' => 'value',
                        'jiguang'
                    ),
                ))
                ->options(array(
                    // sendno: 表示推送序号，纯粹用来作为 API 调用标识，
                    // API 返回时被原样返回，以方便 API 调用方匹配请求与返回
                    // 这里设置为 100 仅作为示例

                    // 'sendno' => 100,

                    // time_to_live: 表示离线消息保留时长(秒)，
                    // 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
                    // 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
                    // 这里设置为 1 仅作为示例

                    // 'time_to_live' => 1,

                    // apns_production: 表示APNs是否生产环境，
                    // True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境

                    'apns_production' => false,

                    // big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
                    // 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
                    // 这里设置为 1 仅作为示例

                    // 'big_push_duration' => 1
                ))
                ->send();
            return $msg = [
                'code' => '1',
                'msg'   => '推送成功',
                'data'  => $response,
            ];

        } catch (\JPush\Exceptions\APIConnectionException $e) {
            // try something here
            // print $e;
            return $msg = [
                'code' => '0',
                'msg'   => $e->getMessage(),
            ];
        } catch (\JPush\Exceptions\APIRequestException $e) {
            // try something here
            // print $e;
            return $msg = [
                'code' => '0',
                'msg'   => $e->getMessage(),
            ];
        }
    }


}