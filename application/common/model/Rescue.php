<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/29
 * Time: 14:17
 */

namespace app\common\model;


use think\Model;

class Rescue extends Model
{
    protected $name = 'rescue';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $append = [];
    private $status = '状态:1=未选择,2=接受,3=接单人数已满,4=拒绝,5=完成';
    // 发送援助请求
    public function pushAid($cry_id,$ids)
    {
        $array = [];
        foreach ($ids as $value) {
            $array[] = [
                'cry_id' => $cry_id,
                'invited_id' => $value,
                'status' => 1,
            ];
        }

        $this->allowField(true)->saveAll($array);
    }
}