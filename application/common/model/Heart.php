<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/23
 * Time: 17:31
 */

namespace app\common\model;


use think\Model;

class Heart extends Model
{
    protected $name = 'heart';

    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $append = [];
}