<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/24
 * Time: 16:26
 */

namespace app\common\model;


use think\Model;

class CryHelp extends Model
{
    protected $name = 'cry_help';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $append = [];
}