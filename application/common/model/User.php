<?php

namespace app\common\model;

use Endroid\QrCode\QrCode;
use think\Model;

/**
 * 会员模型
 */
class User Extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
    ];

    /**
     * 获取个人URL
     * @param   string  $value
     * @param   array   $data
     * @return string
     */
    public function getUrlAttr($value, $data)
    {
        return "/u/" . $data['id'];
    }

    /**
     * 获取头像
     * @param   string    $value
     * @param   array     $data
     * @return string
     */
    public function getAvatarAttr($value, $data)
    {
        return $value ? $value : '/assets/img/avatar.png';
    }

    /**
     * 获取会员的组别
     */
    public function getGroupAttr($value, $data)
    {
        return UserGroup::get($data['group_id']);
    }

    /**
     * 获取验证字段数组值
     * @param   string    $value
     * @param   array     $data
     * @return  object
     */
    public function getVerificationAttr($value, $data)
    {
        $value = array_filter((array) json_decode($value, TRUE));
        $value = array_merge(['email' => 0, 'mobile' => 0], $value);
        return (object) $value;
    }

    /**
     * 设置验证字段
     * @param mixed $value
     * @return string
     */
    public function setVerificationAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value) : $value;
        return $value;
    }

    /**
     * 变更会员积分
     * @param int $score    积分
     * @param int $user_id  会员ID
     * @param string $memo  备注
     */
    public static function score($score, $user_id, $memo)
    {
        $user = self::get($user_id);
        if ($user)
        {
            $before = $user->score;
            $after = $user->score + $score;
            $level = self::nextlevel($after);
            //更新会员信息
            $user->save(['score' => $after, 'level' => $level]);
            //写入日志
            ScoreLog::create(['user_id' => $user_id, 'score' => $score, 'before' => $before, 'after' => $after, 'memo' => $memo]);
        }
    }

    /**
     * 根据积分获取等级
     * @param int $score 积分
     * @return int
     */
    public static function nextlevel($score = 0)
    {
        $lv = array(1 => 0, 2 => 30, 3 => 100, 4 => 500, 5 => 1000, 6 => 2000, 7 => 3000, 8 => 5000, 9 => 8000, 10 => 10000);
        $level = 1;
        foreach ($lv as $key => $value)
        {
            if ($score >= $value)
            {
                $level = $key;
            }
        }
        return $level;
    }

    public static function getUserList($ids)
    {
        $list = self::field('id,nickname,avatar')->where('id','in',$ids)->select();
        $list = collection($list)->toArray();

        $url = \think\Config::get('url');
        foreach($list as &$item) {
            $item['avatar'] = $url. $item['avatar'];
        }
        unset($item);

        return $list;
    }

    public static function createCode($id)
    {
        $user = new User();
        $user = $user->where('id',$id)->find();

        $qrCode = new QrCode();

        $text = url('index/index/build',['code'=>'code'],'.html','http://www.teawant8.com');
        $qrCode
            ->setText($text)
            ->setSize('500')
            ->setPadding('15')
            ->setErrorCorrection('0')
            // ->setLogoSize('50')
            ->setLabelFontPath(ROOT_PATH . 'public/assets/fonts/fzltxh.ttf')
            //->setLabel('李琳')
            // ->setLabelFontSize('25')
            // ->setLabelHalign('0')
            // ->setLabelValign('2')
            ->setImageType($qrCode::IMAGE_TYPE_PNG);
        // dump($this->auth->avatar);die;
        // $qrCode->setLogo(ROOT_PATH . 'public' . $this->auth->avatar);
        $qrCode->save(ROOT_PATH. 'public/qrCode/'.$user->id .'.jpg');
        //return new \think\Response($qrCode->get(), 200, ['Content-Type' => $qrCode->getContentType()]);
    }

}
