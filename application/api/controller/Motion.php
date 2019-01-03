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
use app\common\model\motion\Num;
use think\Db;

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

    /**
     * 打卡
     */
    public function punchTheClock()
    {
        $user = $this->auth->getUser();
        $param = [
            'task_id'   => 'task_id/s',
        ];
        $param = $this->buildParam($param);
        $param['user_id'] = $user->id;

        $model = new Num();
        $result = $model->punchTheClock($param);
        if($result->getCustomError()) {
            $this->error($result->getCustomError());
        } else {
            $this->success('打卡成功!');
        }
    }

    public function getRecord()
    {
        $user = $this->auth->getUser();
        $model = new Num();
        $month = $this->request->param('month/d');
        $year = $this->request->param('year/d');
        $result = $model->getUserRecord($user->id,$month,$year);
        if($result == false) {
            $this->error($model->getCustomError());
        } else {
            $this->success('请求成功!',$result);
        }
    }

    public function getTaskInfo()
    {
        $id = $this->request->param('id');
        $info = Db::name('motion_task')->where('id',$id)->find();
        $url = \think\Config::get('url');
        $info['url'] = $url.$info['url'];
        $info['image'] = $url.$info['image'];
        $this->success('请求成功',$info);
    }

}