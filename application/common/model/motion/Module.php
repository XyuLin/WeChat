<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/12
 * Time: 16:51
 */

namespace app\common\model\motion;


use think\Model;

class Module extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    protected $name = 'motion_module';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [

    ];

    public function getList($ids = '')
    {
        if(empty($ids)) {
            $list = $this->select();
        } else {
            $list = $this->where('id', 'in', $ids)->select();
        }

        return $list;
    }
}