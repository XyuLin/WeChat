<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/24
 * Time: 13:59
 */

namespace app\common\model;


use think\Model;

class Step extends Model
{
    protected $name = 'step';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $append = [];

    public function afferentStep($param)
    {
        $time = todayTime(time());
        $info = $this->where('user_id',$param['user_id'])
            ->where('createtime','between',[$time['beginTime'],$time['endTime']])
            ->find();

        if($info == null) {
            $info= $this->allowField(true)->create($param);
            $result = $info->id;
        } else {
            $info->step = $param['step'];
            $result = $info->save();
        }

        if($result !== false) {
            return true;
        } else {
            return false;
        }
    }

    public static function getTodayStepNumber($user,$time = '')
    {
        if($time == '') {
            $time = time();
        }
        $time = todayTime($time);

        $info = self::where('user_id',$user)
            ->where('createtime','between',[$time['beginTime'],$time['endTime']])
            ->find();

        if($info == null) {
            return 0;
        } else {
            return $info->step;
        }
    }
}