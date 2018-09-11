<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use Think\Env;
use think\Exception;

/**
 * 首页接口
 */
class Index extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     * 
     */
    public function index()
    {
        $user = \app\admin\model\User::get('1');
        $this->success($user);
    }

    public function findUser()
    {
        Db::startTrans();
        try {
            $isSave = Db::table('User')->where('id','1')->setField('nickname','lilinGeger');
            if($isSave == 0){
                exception('更新用户信息失败,回滚!','10020');
            }
            Db::table('User')->where('id','1')->setField('username','linpang');
            Db::commit();
        } catch (Exception $e) {
            $this->error($e->getMessage());
            Db::rollback();
        }
        $this->success('修改信息成功!');
    }

    public function userInfo()
    {

    }

}
