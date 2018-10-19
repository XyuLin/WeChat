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
    protected $name = '';
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
        $result = $this->create($param);
        return $result;
    }

    // 检索 搜寻附近 1km(变量) 的用户。
    public function retrieval($user,$unit = 1)
    {
        $core = $this->where('user_id',$user)->find();
        $lat = $core->lat;
        $lng = $core->lng;
        // 计算最大最小经纬度
        $range = 180 / pi() * $unit / 6372.797;
        $lngR = $range / cos($lat * pi() / 180);

        $maxLat = $lat + $range;//最大纬度
        $minLat = $lat - $range;//最小纬度
        $maxLng = $lng + $lngR;//最大经度
        $minLng = $lng - $lngR;//最小经度

        $ids = $this->where('lat','between',"$maxLat,$minLat")
            ->where('lng','between',"$maxLng,$minLng")
            ->column('user_id');
        return $ids;
    }

    // 发送接单请求
    public function pushAid($ids)
    {

    }

    // 接单 导航。计算两者之间的距离

    //
}