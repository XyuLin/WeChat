<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/12
 * Time: 16:51
 */

namespace app\common\model\motion;


use think\Config;
use think\Model;

class Task extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    protected $name = 'motion_task';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [

    ];

    public function Module()
    {
        return $this->belongsTo('Module', 'module_id');
    }

    public function getList($param = '',$event = 'ids')
    {
        $url = Config::get('url');
        if($event == 'ids') {
            if(!empty($param)) {
                $list = $this->where('id','in',$param)->select();
            }else {
                $list = $this->select();
            }
        } elseif ($event == 'type') {
            $list = $this->where('module_id',$param)->select();
        }


        if(!empty($list)) {
            foreach ($list as &$value) {
                $value['url'] = $url . $value['url'];
                $value['image'] = $url . $value['image'];
            }
            unset($value);
        }

        return $list;

    }
}