<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/9/11
 * Time: 16:48
 */

namespace app\api\controller;


use app\admin\command\Api;

class Cartgory extends Api
{
    protected $noNeedLogin = ['init'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }
}