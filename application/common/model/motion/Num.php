<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/16
 * Time: 16:35
 */

namespace app\common\model\motion;


use think\Model;

class Num extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    protected $name = 'motion_num';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [

    ];

    public function punchTheClock($param)
    {
        if(empty($param['task_id'])) {
            $this->setCustomError('task_id - 参数不可为空!');
            return $this;
        }
        $time = time();
        $param['year'] = date('Y',$time);
        $param['month'] = date('m',$time);
        $param['day']   = date('d',$time);

        $isExist = $this->where($param)->find();
        if($isExist){
            $this->setCustomError('不可重复打卡!');
            return $this;
        }
        $isBeyond = $this->where('day',$param['day'])->count('id');
        if($isBeyond == '5') {
            $this->setCustomError('打卡次数以用完!');
            return $this;
        }
        $result = $this->create($param);
        if($result->id > 0 ) {
            return $result;
        } else {
            $this->setCustomError('打卡失败!');
            return $this;
        }
    }


    public function getUserRecord($user_id,$month = '')
    {
        if($month == '') {
            $month = date('m',time());
        }else {
            if(!is_numeric($month) ||  0 > $month || $month < 12) {
                $this->setCustomError('month - 参数错误!');
                return $this;
            }
        }

        $list = $this->where('user_id',$user_id)->where('month',$month)->column('id,day');

        $list = array_count_values($list);

        $days = $this->getMonthLastDay($month);
        $data = [];
        for ($x=0; $x<$days; $x++) {
            $data[$x]['day'] = $x+1;
            $data[$x]['number'] = 0;
        }

        if(!empty($list)) {
            foreach ($data as &$value) {
                if(isset($list[$value['day']])) {
                    $value['number'] = $list[$value['day']];
                }
                unset($value);
            }
        }

        return $data;
    }

    public function getMonthLastDay($month) {
        $year = date('Y',time());
        switch ($month) {
            case 4 :
            case 6 :
            case 9 :
            case 11 :
                $days = 30;
                break;
            case 2 :
                if ($year % 4 == 0) {
                    if ($year % 100 == 0) {
                        $days = $year % 400 == 0 ? 29 : 28;
                    } else {
                        $days = 29;
                    }
                } else {
                    $days = 28;
                }
                break;
            default :
                $days = 31;
                break;
        }
        return $days;
    }


    public static function longTask($user,$time)
    {
        $year = date('Y',$time);
        $month = date('m',$time);
        $day = date('d',$time);
        $ids = self::where('user_id',$user)->where('year',$year)->where('month',$month)->where('day',$day)->column('task_id');
        if($ids != null) {
            $leng = Task::where('id','in',$ids)->column('lenght');
            $leng = timePlus($leng);
        } else {
            $leng = "0:00";
        }
        return $leng;
    }

}