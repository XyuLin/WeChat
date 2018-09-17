<?php

namespace app\admin\model;

use think\Config;
use think\Model;

class Banner extends Model
{
    // 表名
    protected $table = 'banner';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [

    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    /**
     *
     */
    public static function getBanner()
    {

        $banner = self::order('weigh','desc')->select();
        // 拼接Url
        $url = Config::get('url');
        foreach($banner as $key => &$value) {
            $value->image = $url . $value->image;
        }
        unset($value);
        return collection($banner)->toArray();
    }

    







}
