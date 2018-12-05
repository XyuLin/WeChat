<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\model\Article;
use app\common\model\BlockCategory;
use app\common\model\Care;
use app\common\model\Collection;
use app\common\model\Comment;
use app\common\model\Follow;
use app\common\model\Heart;
use app\common\model\Jpush;
use app\common\model\Like;
use app\common\model\News;
use app\common\model\Step;
use fast\Random;
use think\Config;
use think\Db;
use think\Exception;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third','thirdParty'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $user = $this->auth->getUser();
        // 统计吧
        $article = Article::where('user_id',$user->id)->count();
        $collection = Collection::where('user_id',$user->id)->count();
        $follow = Follow::where('type','1')->where('user_id',$user->id)->count();
        $fans = Follow::where('type','1')->where('follow_id',$user->id)->count();

        $url = Config::get('url');
        $data  = [
            'nickname'  =>  $user->nickname,
            'avatar'    =>  $url . $user->avatar,
            'article'   =>  $article,
            'collection'=>  $collection,
            'follow'    =>  $follow,
            'fans'      =>  $fans,
        ];

        $this->success('请求成功',$data);
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo()
    {
        $userInfo = $this->auth->getUserinfo();
        $this->success('请求成功',$userInfo);
    }

    /**
     * 修改个人信息
     */
    public function editUserInfo()
    {
        $user = $this->auth->getUser();
        $param = [
            'nickname'              => 'nickname/s',
            'gender'                => 'sex/d',
            'age'                   => 'age/s',
            'height'                => 'height/s',
            'weight'                => 'weight/s',
            'blood_type'            => 'blood/d',
            'address'               => 'address/s',
            'urgent_phone_one'      => 'one_phone/s',
            'urgent_contact_one'    => 'one_contact/s',
            'urgent_phone_two'      => 'two_phone/s',
            'urgent_contact_two'    => 'two_contact/s',
            'avatar'                => 'avatar/s',
        ];

        $save_param = array_filter($this->buildParam($param));
        $save_param['id'] = $user['id'];
        $result = $this->editData(false,false,'User',$save_param);
        if($result['code'] == '1') {
            $this->success($result['msg']);
        } else {
            $this->error($result['msg']);
        }
    }

    /**
     * 会员登录
     * 
     * @param string $account 账号
     * @param string $password 密码
     */
    public function login()
    {
        $account = $this->request->request('account');
        $password = $this->request->request('password');
        if (!$account || !$password)
        {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret)
        {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        }
        else
        {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     * 
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha)
        {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$"))
        {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin'))
        {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user)
        {
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        }
        else
        {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret)
        {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        }
        else
        {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     * 
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email 邮箱
     * @param string $mobile 手机号
     */
    public function register()
    {
        // $username = $this->request->request('username');
        $password = $this->request->request('password');
        // $email = $this->request->request('email');
        $mobile = $this->request->request('mobile');
        $code = $this->request->request('code');
        if (!$password)
        {
            $this->error(__('Invalid parameters'));
        }
//        if ($email && !Validate::is($email, "email"))
//        {
//            $this->error(__('Email is incorrect'));
//        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$"))
        {
            $this->error(__('Mobile is incorrect'));
        }
        $result = Sms::check($mobile,$code,'register');
        if(!$result) {
            $this->error('验证码错误');
        }
        $ret = $this->auth->register($password,$mobile, []);
        if ($ret)
        {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        }
        else
        {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @param string $avatar 头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio 个人简介
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->request('username');
        $nickname = $this->request->request('nickname');
        $bio = $this->request->request('bio');
        $avatar = $this->request->request('avatar');
        $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
        if ($exists)
        {
            $this->error(__('Username already exists'));
        }
        $user->username = $username;
        $user->nickname = $nickname;
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success('修改成功','','1');
    }

    /**
     * 修改邮箱
     * 
     * @param string $email 邮箱
     * @param string $captcha 验证码
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->request('captcha');
        if (!$email || !$captcha)
        {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email"))
        {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find())
        {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result)
        {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     * 
     * @param string $email 手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha)
        {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$"))
        {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find())
        {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result)
        {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     * 
     * @param string $platform 平台名称
     * @param string $code Code码
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->request("platform");
        $code = $this->request->request("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform]))
        {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);

        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result)
        {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret)
            {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }


    public function thirdParty()
    {
        $platform = $this->request->request("platform");
        $openid = $this->request->param('openid');
        $loginret = \addons\third\library\Service::connects($platform, $openid);
        if ($loginret)
        {
            $data = [
                'userinfo'  => $this->auth->getUserinfo()
            ];
            $this->success(__('Logged in successful'), $data);
        }
    }

    /**
     * 重置密码
     * 
     * @param string $mobile 手机号
     * @param string $newpassword 新密码
     * @param string $captcha 验证码
     */
    public function resetpwd()
    {
        $type = $this->request->request("type");
        $mobile = $this->request->request("mobile");
        $email = $this->request->request("email");
        $newpassword = $this->request->request("newpassword");
        $captcha = $this->request->request("captcha");
        if (!$newpassword || !$captcha)
        {
            $this->error(__('Invalid parameters'));
        }
        if ($type == 'mobile')
        {
            if (!Validate::regex($mobile, "^1\d{10}$"))
            {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user)
            {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret)
            {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        }
        else
        {
            if (!Validate::is($email, "email"))
            {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user)
            {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret)
            {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret)
        {
            $this->success(__('Reset password successful'));
        }
        else
        {
            $this->error($this->auth->getError());
        }
    }

    // 关注用户或板块
    public function follow()
    {
        $user = $this->auth->getUser();
        $follow_id = $this->request->post('follow_id');
        $type = $this->request->post('type');

        $model = new Follow();
        // 判断用户是否已经关注，已关注则取消关注
        $params = [
            'user_id'   => $user['id'],
            'follow_id' => $follow_id,
            'type'      => $type,
        ];

        if($id = $model->where($params)->value('id')) {
            if($model->where('id',$id)->delete()) {
                $msg = '取消关注成功';
            } else {
                $this->error('取消关注失败','');
            }
        } else {
            if($model->create($params)) {
                $msg = '关注成功';
            } else {
                $this->error('关注失败','');
            }
        }

        $this->success($msg);
    }

    // 关注用户或板块
    public function collection()
    {
        $user = $this->auth->getUser();
        $article_id = $this->request->post('article_id');

        $model = new Collection();
        // 判断用户是否已经关注，已关注则取消关注
        $params = [
            'user_id'   => $user['id'],
            'article_id' => $article_id,
        ];

        if($id = $model->where($params)->value('id')) {
            if($model->where('id',$id)->delete()) {
                $msg = '取消收藏成功';
            } else {
                $this->error('取消收藏失败','');
            }
        } else {
            if($model->create($params)) {
                $msg = '收藏成功';
            } else {
                $this->error('收藏失败','');
            }
        }

        $this->success($msg);
    }

    // 评论回复文章
    public function comment()
    {
        // 获取登录用户信息
        $user = $this->auth->getUser();
        // 获取评论信息
        $param = [
            'article_id'    => 'id/d',
            'comments'      => 'comments/s',
            'parent_id'     => 'parent_id/d',
        ];

        $save_data = $this->buildParam($param);
        $save_data['user_id'] = $user['id'];
        // 验证parent_id 参数
        if($save_data['parent_id'] != 0) {
            $exist = Comment::get($save_data['parent_id']);
            if(!$exist) $this->error('参数错误 - parent_id');
        }
        $info = Article::get($save_data['article_id']);
        if($info == null) {
            $this->error('参数错误 - id');
        }
        Db::startTrans();
        try {
            $comment = $this->editData(false,'Comment','Comment',$save_data);
            if($comment['code'] == '1') {
                // 评论成功，增加评论数
                Article::plusLessOneType($save_data['article_id'],'comment','inc');
                $message = News::addNewMessage($save_data['user_id'],'3',$save_data['article_id'],$comment['data']->id);
                if($message->getCustomError()) {
                    throw new Exception($message->getCustomError());
                }
            } else {
                throw new Exception($comment['msg']);
            }
            Db::commit();
        } catch (Exception $exception) {
            Db::rollback();
            $this->error($exception->getMessage());
        }
        $this->success($comment['msg']);

    }

    // 点赞
    public function pointLike()
    {
        $user = $this->auth->getUser();
        $param = [
            'like_id'   => 'id/d',
            'type'      => 'type/d'
        ];

        $save_data = $this->buildParam($param);
        $save_data['user_id'] = $user['id'];

        $model = new Like();
        // 检测参数是否虚假
        $exist = $model::isExist($save_data);
        if(!$exist) $this->error('参数错误或缺少参数');
        // 查询是否已经点赞，否则取消点赞
        $detail = $model->where($save_data)->find();
        $is_comment = $save_data['type'] == '2' ? true : false;
        Db::startTrans();
        try {
            if($detail != null) {
                if($model->where('id',$detail['id'])->delete()) {
                    $result = Article::plusLessOneType($save_data['like_id'],'like','dec',$is_comment);
                    if($result != true) {
                        throw new Exception('减少点赞数量失败');
                    }
                    $msg = '取消点赞成功';
                } else {
                    // $this->error('取消点赞失败');
                    throw new Exception('取消点赞失败');
                }
            } else {
                if($model->allowField(true)->save($save_data)) {
                    $result = Article::plusLessOneType($save_data['like_id'],'like','inc',$is_comment);
                    if($result != true) {
                        throw new Exception('增加点赞数量失败');
                    }
                    if($is_comment == false) {
                        $message = News::addNewMessage($save_data['user_id'],'1',$save_data['like_id'],$model->id);
                        if($message->getCustomError()) {
                            throw new Exception($message->getCustomError());
                        }
                    }
                    $msg = '点赞成功';
                } else {
                    // $this->error('点赞失败');
                    throw new Exception('点赞失败');
                }
            }
            Db::commit();
        } catch (Exception $exception){
            Db::rollback();
            $this->error($exception->getMessage());
        }
        $this->success($msg);
    }

    // 发表文章
    public function publishArticle()
    {
        $user = $this->auth->getUser();
        $param = [
            'block_category_id' => 'category_id/s',
            'title'             => 'title/s',
            'content'           => 'content/s',
            'images'            => 'image/a'
        ];
        $save_data = $this->buildParam($param);
        // 判断用户传入的模型id 是否存在
        $block_category = new BlockCategory();
        if(!$block_category->where('id',$save_data['block_category_id'])->find()) {
            $this->error('参数错误 - category_id');
        }
        $save_data['images'] = implode(',',$save_data['images']);
        $save_data['user_id'] = $user['id'];

        Db::startTrans();
        try {
            $result = $this->editData(false, 'Article', 'Article',$save_data);
            if($result['code'] == '1') {
                // 需要加入定位。
            } else {
                throw new Exception($result['msg']);
            }
            Db::commit();
        } catch (Exception $exception) {
            Db::rollback();
            $this->error($exception->getMessage());
        }

        $this->success('文章发布成功!');
    }

    // 我他发布的文章
    public function getMyPublishArticle()
    {
        $user = $this->auth->getUser();
        $user_id = $user->id;
        $page = $this->request->param('page/s');
        if($his = $this->request->param('his/s')) {
            $info = $user->where('id',$his)->find();
            if($info == null) {
                $this->error('参数错误(无效) - his');
            } else {
                $user_id = $info->id;
            }
        }
        $model = new Article();
        $list = $model->getPublish($user_id,$page);

        $this->success('请求成功',$list);
    }

    // 我的回复
    public function getMyComment()
    {
        $user = $this->auth->getUser();
        $page = $this->request->param('page/s');
        $model = new Comment();
        $list = $model->getComment($user['id'],$page);

        $this->success('请求成功',$list);
    }

    // 我的收藏
    public function getMyCollection()
    {
        $user = $this->auth->getUser();
        $page = $this->request->param('page/s');
        $model = new Collection();
        $list = $model->getCollection($user['id'],$page);

        $this->success('请求成功',$list);
    }

    // 我的关注(粉丝)
    // type = 1 我关注的人
    // type = 2 关注我的人
    public function getMyfollow()
    {
        $user = $this->auth->getUser();
        $page = $this->request->param('page/s');
        $type = $this->request->param('type/s');

        if($type != '1' && $type != 2) return $this->error('参数错误 - type ');

        $model = new Follow();
        $list = $model::getMyFans($user->id,$type,$page);

        $this->success('请求成功',$list);
    }

    /**
     *  我牵挂的人
     */
    public function getMyCare()
    {
        $user = $this->auth->getUser();
        $model = new Care();
        $myCare = $model->where('user_id',$user['id'])->where('adopt','2')->column('care_id,memo_name');
        $ids = array_keys($myCare);
        $list = \app\common\model\User::getUserList($ids);
        foreach ($list as $key => &$value) {
                $value['memo_name'] = $myCare[$value['id']];
        }
        unset($value);
        $this->success('请求成功', $list);
    }

    /**
     * 添加牵挂的人
     */
    public function addCare()
    {
        $user = $this->auth->getUser();
        $care_id = $this->request->param('care_id');
        $tips = $this->request->param('tips');
        $memo_name = $this->request->param('memo_name');
        if($user['id'] == $care_id) $this->error('不可添加自己为牵挂的人!');
        $care = $user->where('id',$care_id)->find();
        if(empty($care)) $this->error('参数错误, 没有此用户');
        $model = new Care();
        $param = [
            'care_id'   => $care_id,
            'user_id'   => $user['id'],
        ];
        $isExist = $model->where($param)->select();
        if($isExist != null) {
            $this->error('用户已添加过 '.$care['nickname'].' 请勿重复添加');
        }
        if(empty($memo_name)) {
            $memo_name = $care['nickname'];
        }
        $param = array_merge($param,[
            'memo_name' => $memo_name,
            'tips'      => $tips,
        ]);
        $result = $model->allowField(true)->save($param);
        if($result != false) {
            $this->success('添加牵挂的人成功!');
        } else {
            $this->error('添加牵挂的人失败!');
        }
    }

    /**
     * 新的牵挂
     * adopt 0 未通过 1 已拒绝 2 已通过
     */
    public function newCare()
    {
        $user = $this->auth->getUser();
        $model = new Care();
        $list = $model->where('care_id',$user['id'])->select();
        $url = Config::get('url');
        foreach ($list as &$value) {
            $user = \app\common\model\User::get($value['care_id']);
            $value['avatar'] = $url . $user['avatar'];
        }
        unset($value);

        $this->success('请求成功',$list);
    }

    /**
     * 同意用户牵挂
     */
    public function confirmAdopt()
    {
        $user = $this->auth->getUser();
        $id = $this->request->param('care_id');
        $operate = $this->request->param('operate');
        if($operate == 'true') {
            $operate = '2';
        } else {
            $operate = '1';
        }
        $model = new Care();
        $info = $model->find($id);
        if(empty($info)) $this->error('参数错误 - care_id');
        if($info['adopt'] != 0) $this->error('不可重复操作!');
        $info->adopt = $operate;
        if($info->save() > 0) {
            $this->success('通过成功!');
        } else {
            $this->error('操作失败!');
        }
    }

    /**
     * 查找(查看)牵挂的人信息
     */

    public function getCareInfo()
    {
        $user = $this->auth->getUser();
        $str = $this->request->param('only');
        $isNum = preg_match('/[0-9]/', $str);
        if($isNum) {
            if(strlen($str) == 11) {
                $where['mobile'] = $str;
            } else {
                $where['id'] = $str;
            }
        } else {
            $where['uuid'] = ['like','%'.$str.'%'];
        }
        $model = new \app\common\model\User();
        $ids = $model->where($where)->value('id');
        if(empty($ids)) {
            $this->error('没有此用户!');
        } else {
            // 判断是否已经为好友。或者是需要添加
            $userInfo = $model::getUserList($ids);
            $isExist = Care::where('user_id',$user['id'])->where('care_id',$ids)->find();
            if($isExist) {
                $isCare = 'true';
            } else {
                if($ids == $user['id']) {
                    $isCare = 'true';
                } else {
                    $isCare = 'false';
                }
            }
            $data = array_merge($userInfo[0],[
                'isCare' => $isCare,
            ]);
            $this->success('请求成功',$data);
        }
    }

    /**
     * 修改牵挂的人备注名
     *
     */
    public function editMemoName()
    {
        $user = $this->auth->getUser();
        $id = $this->request->param('care_id');
        $name = $this->request->param('name');
        $model = new Care();

        $info = $model->where('id',$id)->where('user_id',$user['id'])->find();
        if(empty($info)) {
            $this->error('参数错误 - care_id');
        } else {
            $info->memo_name = $name;
            if($info->save() > 0) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败,请重试!');
            }
        }
    }

    /**
     * 查看信息
     */
    public function viewMessage()
    {
        $user = $this->auth->getUser();
        $news_id = $this->request->param('news_id');
        $result = News::viewMessage($user['id'],$news_id);
        if($result->getCustomError()) {
            $this->error($result->getCustomError());
        } else {
            if($result->type == '3') {
                $article = Article::getArticleDetail($result->article_id,$user['id']);
                $this->success('请求成功',$article);
            } else {
                $this->success('请求成功!');
            }
        }
    }

    /**
     * 我的新消息
     */
    public function getMyMessages()
    {
        $user = $this->auth->getUser();
        $type = $this->request->param('type/s');
        $News = new News;
        $result = $News->getMyMessage($user['id'],$type);

        $this->success('请求成功',$result);
    }

    public function myQrCode()
    {
        $user = $this->auth->getUser();

        $list = $user::getUserList($user->id);
        $info = $list[0];
        $url = Config::get('url');

        $info['qrCode'] = $url . '/qrCode/' .$user->id . '.jpg';
        $this->success('请求成功!',$info);
    }

    // 传入用户今日心率
    public function afferentHeart()
    {
        $model = new Heart();
        $user = $this->auth->getUser();
        $param['heart_rate'] = $this->request->param('heart_rate');
        $param['user_id'] = $user->id;
        $result = $model->afferentHeart($param);

        if($result == false) {
            $this->error('记录心率失败!');
        }
        $this->success('记录心率成功!');
    }

    // 传入用户今日总步数
    public function afferentStep()
    {
        $user =  $this->auth->getUser();
        $param['step'] = $this->request->param('step_num/s');
        $param['user_id'] = $user->id;

        $model = new Step();
        $result = $model->afferentStep($param);
        if($result == false) {
            $this->error('记录步数失败!');
        }
        $this->success('记录成功');
    }

    //  传入用户经纬度
    public function afferentLatLng()
    {
        $map = new \app\common\model\Map();
        $user = $this->auth->getUser();
        $param = [
            'lat' => 'lat/s',
            'lng' => 'lng/s',
        ];
        $param = $this->buildParam($param);
        $result = $map->depositInLatLng($user->id,$param['lat'],$param['lng']);
        if($result !== false) {
            $this->success('更新位置成功!');
        } else {
            $this->error('更新位置失败!');
        }
    }

    // 呼救
    public function callHelp()
    {
        $user = $this->auth->getUser();
        $cry_id = $this->request->param('cry_id');
        $lat = $this->request->param('lat');
        $lng = $this->request->param('lng');
        // 呼救
        $cryHelp = new \app\common\model\CryHelp();
        $rescue = new \app\common\model\Rescue();
        $cryHelp->startTrans();
        $rescue->startTrans();
        try{
            $cry_id = $cryHelp->callForHelp($user->id,$cry_id);
            if($cry_id !== false) {
                // 检索附近的用户
                $map = new \app\common\model\Map();
                $ids = $map->retrieval($user->id,$lat,$lng);
                if($ids === false) {
                    throw new Exception('没有获取当前用户的位置','0');
                }
                // 如果当前附近没有用户，则直接返回空
                if(empty($ids)) {
                    throw new Exception('附近未检测到用户','0');
                }
                $result = $rescue->pushAid($cry_id,$ids);
                // 如果添加求救信号出错 则回滚数据
                if($result == false) {
                    throw new Exception('添加求救信号失败','0');
                } elseif(is_string($result)) {
                    throw new Exception($result,'0');
                }
                // 推送用户
                $jpush = new Jpush();
                $extras = [
                    'type' => '1',
                    'cry_id' => $cry_id,
                ];
                $jpushResult = $jpush->push($ids,'救救我','我快不行了',$extras);

                if($jpushResult['code'] == '0') {
                    throw new Exception($jpushResult['msg'],'0');
                }

                // 提交事务
                $cryHelp->commit();
                $rescue->commit();
            } else {
                throw new Exception('施救者已上限','0');
            }
        } catch (\Exception $e) {
            $rescue->rollback();
            $cryHelp->rollback();
            if($e->getCode() == '1') {
                $this->success($e->getMessage());
            } else {
                $this->error($e->getMessage());
            }
        }
        $msg = [
            'cry_id' => $cry_id,
            'isReceipt' => $rescue->getIsReceipt($cry_id),
        ];
        $this->success('求救信号已发出!',$msg);
    }

    // 呼救状态
    public function callStatus()
    {
        $user = $this->auth->getUser();
        $cry_id = $this->request->param('cry_id');
        $rescue = new \app\common\model\Rescue();
        $result = $rescue->getStatus($user,$cry_id);
    }

    // 接单
    public function receipt()
    {
        $user = $this->auth->getUser();
        $cry_id = $this->request->param('cry_id');
        $model = new \app\common\model\Rescue();
        $info = $model->receipt($user->id,$cry_id);

        if($info->getCustomError() != '') {
            $this->error($info->getCustomError());
        } else {
            $this->success('接单成功!');
        }


    }

    // 拒绝
    public function refuse()
    {
        $user = $this->auth->getUser();
        $cry_id = $this->request->param('cry_id');
        $model = new \app\common\model\Rescue();
        $info = $model->where('invited_id',$user->id)->where('cry_id',$cry_id)->find();
        if($info == null) {
            $this->error('参数错误 - cry_id');
        }
        if($info->status == '1') {
            $info->status = '4';
            $info->save();
            $this->success('拒绝成功!');
        } else {
            $this->error('您已接单暂时不可以拒绝!');
        }
    }

    // 呼救完成或取消
    public function helpComplete()
    {
        $user = $this->auth->getUser();
        $cry_id = $this->request->param('cry_id');
        $model = new \app\common\model\CryHelp();

        $result = $model->complete($user->id,$cry_id);

        if($result->getCustomError()) {
            $this->error($result->getCustomError());
        }

        if($result->status == '4') {
            $msg = '求救取消!';
        } elseif ($result->status == '3') {
            $msg = '求救完成!';
        } else {
            $msg = '发生错误!';
        }

        $this->success($msg);
    }

    public function getHelpInfo()
    {
        $user = $this->auth->getUser();
        $model = new \app\common\model\CryHelp();
        $cry_id = $this->request->param('cry_id');

        $result = $model->getDetail($cry_id,$user);

        $this->success('请求成功!',$result);
    }

}
