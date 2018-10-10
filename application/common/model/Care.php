<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/8
 * Time: 14:22
 */

namespace app\common\model;


use think\Model;

class Care extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $table = 'cares';
    // 追加属性
    protected $append = [
    ];
}