<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/19
 * Time: 10:51
 */

namespace app\common\model;


use think\Model;

class Map extends Model
{
    protected $name = 'map';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $append = [];

    // 存入用户当前经纬度
    public function depositInLatLng($user,$lat,$lng)
    {
        $param = [
            'user_id'   => $user,
            'lat'       => $lat,
            'lng'       => $lng,
        ];
        $info = $this->where('user_id',$param['user_id'])->find();
        if($info == null) {
            $result = $this->create($param);
        } else {
            $result = $info->save($param);
        }

        return $result;
    }

    // 检索 搜寻附近 1km(变量) 的用户。
    public function retrieval($user,$unit = 1)
    {
        $core = $this->where('user_id',$user)->find();
        if($core == null) {
            return false;
        }
        $lat = $core->lat;
        $lng = $core->lng;
        // 计算最大最小经纬度
        $range = 180 / pi() * $unit / 6372.797;
        $lngR = $range / cos($lat * pi() / 180);

        $maxLat = $lat + $range;//最大纬度
        $minLat = $lat - $range;//最小纬度
        $maxLng = $lng + $lngR;//最大经度
        $minLng = $lng - $lngR;//最小经度

        $array =collection( $this->field('user_id,lat,lng')->where('lat','between',"$maxLat,$minLat")
            ->where('lng','between',"$maxLng,$minLng")
            ->select())->toArray();

        // 如果附近没有用户，返回空数组
        if(empty($array)) {
            return [];
        }

        // 如果附近人数大于10 则排序最近的10名用户
        if(length($array) > 10) {
            $distance = [];
            foreach($array as $key => $value) {
                // 将每一个用户与呼救者的距离列出
                $result = $this->getDistance($lat,$lng,$value['lat'],$value['lng']);
                $distance[$value['user_id']] = $result;
            }

            // 进行升序排序 距离近的取出用户id
            $distance = asort($distance);
            $satisfy = array_slice($distance,0,10);
            $ids = pickIds($satisfy);
        } else {
            // 人数小于等于10 直接返回10名用户的id
            $ids = pickIds($array,'user_id');
        }

        return $ids;
    }

    // 受邀人 标出本人位置与发出求救信号的用户位置
    public function invitedUser($user)
    {
        $model = new CryHelp();
        //

    }



    /*
 * 1.纬度1，经度1，纬度2，经度2
 * 2.返回结果是单位是KM。
 * 3.保留一位小数
 */
    function getDistance($lat1,$lng1,$lat2,$lng2)
    {
        //将角度转为狐度
        $radLat1 = deg2rad($lat1);//deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6371;
        return round($s,1);
    }
}