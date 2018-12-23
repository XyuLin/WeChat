<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/12/4
 * Time: 20:25
 */

namespace app\common\model;


use think\Model;

class Activity extends Model
{
    protected $name = 'activity';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public function getImageAttr($value)
    {
        $url = \think\Config::get('url');
        return empty($value) ? '' : $url.$value;
    }

    public function getActivityStartAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['activity_start']) ? $data['activity_start'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getActivityEndAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['activity_end']) ? $data['activity_end'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }
}