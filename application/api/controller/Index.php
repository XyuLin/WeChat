<?php

namespace app\api\controller;

use app\admin\command\Api\library\Extractor;
use app\admin\model\Admin;
use app\admin\model\apply\Pass;
use app\admin\model\Area;
use app\admin\model\Banner;
use app\admin\model\Enter;
use app\common\controller\Api;
use app\common\library\token\driver\Redis;
use app\common\model\Activity;
use app\common\model\Article;
use app\common\model\Block;
use app\common\model\CryHelp;
use app\common\model\Follow;
use app\common\model\Heart;
use app\common\model\Jpush;
use app\common\model\motion\Num;
use app\common\model\Rescue;
use app\common\model\Step;
use fast\Tree;
use JPush\Client;
use think\Config;
use think\Db;
use think\Exception;

/**
 * 首页接口
 */
class Index extends Api
{

    protected $noNeedLogin = ['findUser', 'test', 'jpush', 'testRedis', 'guardRedis','getArticleDetail'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $heart = new Heart();
        $todayTime = todayTime(time());
        $beginToday = $todayTime['beginTime'];
        $endToday = $todayTime['endTime'];
        $user = $this->auth->getUser();
        $data['max'] = $heart->where('user_id', $user->id)->where('createtime', 'between', "$beginToday,$endToday")->max('heart_rate');
        if ($data['max'] == '0') {
            $data['min'] = 0;
            $data['avg'] = 0;
        } else {
            $data['min'] = $heart->where('user_id', $user->id)->where('createtime', 'between', "$beginToday,$endToday")->min('heart_rate');
            $data['avg'] = $heart->where('user_id', $user->id)->where('createtime', 'between', "$beginToday,$endToday")->avg('heart_rate');
            $data['avg'] = round($data['avg']);
        }

        $data['longTask'] = Num::longTask($user->id, time());
        $step = Step::getTodayStepNumber($user->id, time());
        $data['stepNumber'] = $step['step'];
        $data['calorie'] = $step['calorie'];

        $isCry = CryHelp::checkIsCry($user->id);
        $isRescue = Rescue::checkIsRescue($user->id);
        if ($isCry == null && $isRescue != null) {
            if($isRescue['status'] == '1') {
                $data['isSignal'] = 3;
                $data['signal'] = [
                    'role' => 'rescue', // 未选择
                    'cry_id' => $isRescue->cry_id,
                ];
            } else {
                $data['isSignal'] = 2;
                $data['signal'] = [
                    'role' => 'rescue', // 已接单
                    'cry_id' => $isRescue->cry_id,
                ];
            }
        } elseif ($isRescue == null && $isCry != null) {
            $data['isSignal'] = 2;
            $data['signal'] = [
                'role' => 'cry',
                'cry_id' => $isCry->id,
            ];
        } elseif ($isCry != null && $isRescue != null) {
            $data['isSignal'] = 2;
            $data['signal'] = [
                'role' => 'cry',
                'cry_id' => $isCry->id,
            ];

            $isRescue->status = '4';
            $isRescue->save();
        } else {
            $data['isSignal'] = 1;
            $data['signal'] = [];
        }

        $data['video'] = Article::field('id,title,images,createtime')->where('type','2')->order('weigh desc')->find();
        $url = Config::get('url');
        $data['video']['images'] = $url . $data['video']['images'];
        $this->success('请求成功', $data);
    }

    public function findUser()
    {
        Db::startTrans();
        try {
            $isSave = Db::table('User')->where('id', '1')->setField('nickname', 'lilinGeger');
            if ($isSave == 0) {
                exception('更新用户信息失败,回滚!', '10020');
            }
            Db::table('User')->where('id', '1')->setField('username', 'linpang');
            Db::commit();
        } catch (Exception $e) {
            $this->error($e->getMessage());
            Db::rollback();
        }
        $this->success('修改信息成功!');
    }

    // 社区版块 热门页面
    public function communityHot()
    {
        // banner
        $data['banner'] = Banner::getBanner();
        // 我关注的版块
        $user = $this->auth->getUser();
        // 获取关注的版块分类id
        $category_id = $this->request->post('category_id');
        $data['myFollows'] = Follow::getUserFollows($user['id'], '2', $category_id);

        if (!is_array($data['myFollows'])) {
            $this->error($data['myFollows'], '', '40001');
        }
        // 获取page参数
        $page = $this->request->post('page/s');
        $data['hotArticle'] = model('Article')->getHotArticle(empty($data['myFollows']) ? '' : $data['myFollows']['0']['id'], $page);
        $this->success('请求数据成功', $data, '1');
    }

    // 社区 全部版块
    public function blockAll()
    {
        $data['banner'] = Banner::getBanner();
        // 已关注的版块
        $user = $this->auth->getUser();
        $data['myFollows'] = Follow::getUserFollows($user['id'], '2');
        // 剩余版块
        $data['blockArr'] = Follow::getUserNoFollows($user['id']);

        $this->success('请求数据成功', $data, '1');
    }

    // 板块详情
    public function getCategoryDetail()
    {
        $model = new Article();
        $user = $this->auth->getUser();
        // 获取版块id  页码
        $id = $this->request->post('category_id/s');
        $page = $this->request->post('page/s');
        $p = $this->request->post('p/s');
        // 推荐用户
        $data['recommend'] = $model->recommendUser($id, $user['id'], $p);
        // 文章
        $data['list'] = $model->getHotArticle($id, $page);

        $this->success('请求数据成功', $data);
    }

    // 获取文章详情
    public function getArticleDetail()
    {
        // $user = $this->auth->getUser();
        $article_id = $this->request->post('article_id');
        if (empty($article_id)) {
            $this->error('参数不可为空 - article_id');
        }
        $detail = Article::getArticleDetail($article_id);

        $this->success('请求数据成功', $detail);
    }

    // 搜索文章
    public function search()
    {
        $param = [
            'keyWord' => 'search/s',
            'page' => 'page/s'
        ];
        $param = $this->buildParam($param);
        $model = new Article();
        $where['title'] = ['like', '%' . $param['keyWord'] . '%'];
        $list = $model->where($where)
            ->limit('10')
            ->page($param['page'])
            ->order('createtime', 'desc')
            ->select();
        $total = $model->where($where)->count();

        if (!empty($list)) {
            $list = $model->splicingUrl($list);
        }
        $data['list'] = $list;
        $data['total'] = $total;
        $this->success('请求成功', $data);
    }

    public function test()
    {
        // echo phpinfo(); die;
        $client = new Client('137258992', 'Assoofnoenandfnesdfdsf');
        $push = $client->push();
        $push->setCid('123123123')
            ->setPlatform('all')
            ->addAllAudience('all')
            ->setNotificationAlert('alert')
            ->iosNotification('hello')
            ->addWinPhoneNotification('hello')
            ->send();
        halt($push);
    }

    // 视频列表
    public function videoList()
    {
        $model = new Article();
        $page = $this->request->param('page/s');
        $list = $model->getHotArticle('', $page, '2');
        $this->success('请求成功', $list);
    }

    // 城市列表
    public function getCityList()
    {

        $array = Db::name('area')->field('pid,id,name')->where('level','neq','3')->select();
        $tree = new Tree();
        $tree->init($array,'pid');
        $result = $tree->getTreeArray('0','name','false');
        $this->success('请求成功!',$result);
    }

    // 活动列表
    public function getActivityList()
    {
        $model = new Activity();
        $city_id = $this->request->param('city_id/s');
        $image = Admin::where('city',$city_id)->value('image');
        $url = Config::get('url');
        $imageUrl = $url . $image;
        $list = collection($model->where('city_id',$city_id)->select())->toArray();
        foreach ($list as &$value) {
            $value['adminImage'] = $imageUrl;
        }
        unset($value);
        $this->success(
            '请求成功!',$list
        );
    }

    // 报名活动
    public function signUp()
    {
        $user = $this->auth->getUser();
        if($user->is_enter == '1') {
            $this->error('您已报名并且以通过审核!不可重复申请!');
        }
        $param = [
            'full_name' => 'name/s',
            'jointime'  => 'jointime/s',
            'mobile'    => 'mobile/s',
            'wechat_id' => 'wechat/s',
            'city_name' => 'city_name/s',
            'city_id'   => 'city_id/s',
        ];
        $params = $this->buildParam($param);
        $code = $this->request->param('code/s');
        $result = \app\common\library\Sms::check($params['mobile'],$code,'enter');
        if(!$result) {
            $this->error('验证码错误');
        }
        $params['user_id'] = $user->id;
        $model = new Enter();
        $info = $model->create($params);
        if($info) {
            $this->success('报名成功!');
        } else {
            $this->error('报名失败!');
        }

    }

    // 申请证书
    public function applyPass()
    {
        $user = $this->auth->getUser('您的证书已申请成功!不可重复申请!');

        if($user->is_pass == '1') {
            $this->error();
        }
        $model = new Pass();
        $param = [
            'full_name' => 'name/s',
            'mobile'    => 'mobile/s',
            'id_photo'  => 'id_photo/s',
            'prove_images'  => 'prove/a',
            'prove_desc'    => 'prove_desc/s',
            'city_name' => 'city_name/s',
            'city_id'   => 'city_id/s',
        ];

        $params = $this->buildParam($param);
        $code = $this->request->param('code/s');
        $result = \app\common\library\Sms::check($params['mobile'],$code,'apply');
        if(!$result) {
            $this->error('验证码错误');
        }
        $params['prove_images'] = implode(',',$params['prove_images']);
        $params['user_id'] = $user->id;

        $info = $model->create($params);
        if($info) {
            $this->success('申请成功!');
        } else {
            $this->error('申请失败!');
        }
    }

    public function getCase()
    {
        $model = new Block();
        $list = $model->field('id,title')
            ->with(['BlockCategory'=>function($query) {
                $query->field('id,title,block_id');
            }])->select();
        $list = collection($list)->toArray();
        $user = $this->auth->getUser();
        $inArr = explode(',',$user->block_category_ids);
        if($inArr != '') {
            foreach($list as &$value) {
                if(is_array($value['block_category'])) {
                    foreach ($value['block_category'] as &$v) {
                        if(in_array($v['id'],$inArr)) {
                            $v['isBind'] = true;
                        } else {
                            $v['isBind'] = false;
                        }
                    }
                    unset($v);
                }
            }
            unset($value);
        }
        $this->success('请求成功!',$list);
    }

    public function jpush()
    {
        $Jpush = new Jpush();
        $extras = [
            'type'      => '1',
            'cry_id'    => '29',
        ];
        $result = $Jpush->push(['10'],'','',$extras);
        halt($result);
    }

    public function testRedis()
    {
        $redis = \mikkle\tp_redis\Redis::instance();
        for($i=0;$i<50;$i++) {
            try {
                $redis->lpush('click', $i);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    public function guardRedis()
    {
        $redis = \mikkle\tp_redis\Redis::instance();
        halt($redis);
        echo $redis->Llen('click');die;
        while(true){
            try {
                $value = $redis->lpop('click');
                if(!$value) {
                    break;
                }
                dump($value);
            } catch(Exception $e) {
                echo $e->getMessage();
            }

        }
    }

}
