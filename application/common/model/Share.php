<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/12
 * Time: 11:05
 */

namespace app\common\model;


use think\Model;

class Share extends Model
{
    protected $name = 'shares';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $append = [
        'create_text'
    ];

    public function getCreateTextAttr($value,$data)
    {
        return time_ago($data['createtime']);
    }

    public function getCreatetimeAttr($value,$data)
    {
        return date('Y-m-d H:i', $value);
    }

    public function Article()
    {
        return $this->belongsTo('Article');
    }

}