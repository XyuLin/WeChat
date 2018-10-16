<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/10/12
 * Time: 16:37
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\motion\Module;

class Motion extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';
    protected $model ;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('app\common\model\motion\Task');
    }

    public function taskList()
    {
        $type = $this->request->param('type');
        $module = new Module();
        $modules = $module->getList();
        if(!empty($type)) {
            $isExist = $module->where('id',$type)->find();
            if(empty($isExist)) {
                $this->error('type - 无效参数!');
            }
        } else {
            $type = $modules[0]->id;
        }
        $tasks = $this->model->getList($type,'type');
        $data['modules'] = $modules;
        $data['tasks'] =  $tasks;
        $this->success('请求成功!', $data);
    }

    public function punchTheClock()
    {
        $user = $this->auth->getUser();
        $time = time();
        $param['year'] = date('Y',$time);
        $param['month'] = date('m',$time);
        $param['day']   = date('d',$time);
        $param['user_id'] = $user->id;

        $this->success('OK', $param);
    }
}