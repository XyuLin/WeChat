<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/9/30
 * Time: 10:20
 */

namespace app\common\model;


use think\Model;

class Notify extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];


    public static function addNews($user_id,$receive_id,$event_id)
    {

    }
}