<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/9/12
 * Time: 15:12
 */

namespace app\common\model;


use think\Model;

class Block extends Model
{
    protected $name = 'block';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public function BlockCategory()
    {
        return $this->hasMany('BlockCategory');
    }
}