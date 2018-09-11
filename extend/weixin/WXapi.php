<?php
namespace weixin;
/**
 * 微信公众号（服务号、订阅号）PHP DEMO
 */
class WXapi{

    /**
     * 微信配置信息
     * 获取方式：微信后台-》开发-》基本配置
     */
    public $_CONFIG = [
        // 服务号
        //'appID'     =>'wx057fc97a85beafc0',
        //'appsecret' =>'5d72c594b8f1ff2d73a69eeae621ae34',
		'appID'        => 'wxa6733157e23b51e7',
        'appsecret'    => 'd676967b3cc79626c78ae90977a39181',
    ];


    /**
     * 微信 URL
     */
    public $_URL = [
        'cgi-bin'   =>'https://api.weixin.qq.com/cgi-bin/',
        'mp'        =>'https://mp.weixin.qq.com/cgi-bin/',
        'sns'       =>'https://api.weixin.qq.com/sns/',
    ];
    public $DOMAIN = 'gzh.sshrp.com';

    /**
     * 构造方法
     * @author yanxuefa
     * @date   2018-01-04
     */
    public function __construct(){

        // 验证服务器可以接受微信发出的请求 （之后可以注释）
        // $this->checkServer();

        $this->DOMAIN = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'];


        // // $weixin = C('WEIXIN');
        // #读取配置信息
        // $this->appID        = $weixin['APPID'];
        // $this->appsecret    = $weixin['APPSECRET'];
        // $this->scope        = $weixin['SCOPE'];

        // #网站信息
        // $this->WEB_DOMAIN   = 'http://'.$_SERVER['SERVER_NAME'];
        // $this->WEB_URL      = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    }

    /**
     * 开始开发 填写服务器配置 验证消息的确来自微信服务器
     * @author yanxuefa
     * @date   2018-01-04
     * @return [type]     [description]
     */
    public function checkServer(){
        if(!empty($_GET['echostr'])){
            echo $_GET['echostr'];
            exit;
        }
    }

    /**
     * 对话服务 基础支持 获取access_token
     * @author yanxuefa
     * @date2018-01-04
     * @return         [type] [description]
     */
    public function token(){

        $url = $this->_URL['cgi-bin'].'token?grant_type=client_credential&appid='.$this->_CONFIG['appID'].'&secret='.$this->_CONFIG['appsecret'];
        $info = $this->http($url);
        if($info['code']!=200){

            // 一般执行到这了都是没有配额了， token做成缓存的吧
            echo 'token get error';
            pr($info);
        }

        $token = '';
        if(!empty($info['data']['access_token'])){
            $token = $info['data']['access_token'];
        }
        return $token;

        // 【警告】token 获取接口每天限制 2000 次，此处应做保存处理
        // $token = S('wx_token');
        // if(empty($token))
        // {
        //     $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appID.'&secret='.$this->appsecret;
        //     $info = $this->http($url);
        //     if(!empty($info['data']['access_token']))
        //     {
        //         $token = $info['data']['access_token'];
        //         S('wx_token',$token,7000);
        //     }
        // }
        // return $token;
    }

    /**
     * 对话服务 基础支持 获取微信服务器IP地址    
     * @author yanxuefa
     * @date2018-01-04
     * @return         [type] [description]
     */
    public function getcallbackip(){

        $url = $this->_URL['cgi-bin'].'getcallbackip?access_token='.$this->token();
        return $this->http($url);

    }

    /**
     * 对话服务 接收消息 接收普通消息    
     * @author yanxuefa
     * @date2018-01-04
     * @return         [type] [description]
     */
    public function get_msg(){

        $xml = file_get_contents('php://input');
		dump($xml);
        $xmlObj = $this->xmlToObj($xml);
        if(empty($xmlObj)){
            return '';
        }
       
	    // 处理普通消息
        if ($xmlObj->MsgType == 'text')
        {
            $this->send_msg($xmlObj);
            exit();
        }

        // 处理事件消息
        if($xmlObj->MsgType=='event'){
            $this->get_msg_event($xmlObj);
            exit();
        }
		
		if($xmlObj->MsgType == 'image')
		{
			exit();
		}

        // 处理普通消息
        $this->get_msg_base($xmlObj);
    }

    /**
     * 对话服务 接收消息 被动回复用户消息    
     * @author yanxuefa
     * @date2018-01-04
     * @return         [type] [description]
     */
    private function send_msg($xmlObj){
        if($xmlObj->Event=='LOCATION'){
            exit();
        }
        
        $xml  = '<ToUserName><![CDATA['.$xmlObj->FromUserName.']]></ToUserName>';
        $xml .= '<FromUserName><![CDATA['.$xmlObj->ToUserName.']]></FromUserName>';
        $xml .= '<CreateTime>'.$xmlObj->CreateTime.'</CreateTime>';
        if (strpos($xmlObj->Content, '客服') !== false)
        {
            $xml .= '<MsgType><![CDATA[transfer_customer_service]]></MsgType>';
        }
        else
        {
            $xml .= '<MsgType><![CDATA[text]]></MsgType>';
            $xml .= '<Content><![CDATA['.$xmlObj->Content.']]></Content>';
        }
        $xml .= '<Content><![CDATA['.$xmlObj->Content.']]></Content>';
        exit('<xml>'.$xml.'</xml>');
    }

    /**
     * 对话服务 接收消息 接收事件消息    
     * @author yanxuefa
     * @date2018-01-04
     * @return         [type] [description]
     */
    private function get_msg_event($xmlObj){

        /**
         * Event:
         *      subscribe(订阅)、unsubscribe(取消订阅)
         *      LOCATION 上报地理位置事件
         *      CLICK 自定义菜单事件
         */
        
        if($xmlObj->Event=='subscribe'){
        }

        $xmlObj->ToUserName;//开发者微信号
        $xmlObj->FromUserName;//发送方帐号（一个OpenID）
        $xmlObj->CreateTime;//消息创建时间 （整型）
        $xmlObj->MsgType;//消息类型（text、image、、、、、、、、、、）
    }

    /**
     * 对话服务 接收消息 接收普通消息    
     * @author yanxuefa
     * @date2018-01-04
     * @return         [type] [description]
     */
    private function get_msg_base($xmlObj){

        /**
         * MsgType:
         *      text 文本消息
         *      image 图片消息
         *      voice 语音消息
         *      video 视频消息
         *      shortvideo 小视频消息
         *      location 地理位置消息
         *      link 链接消息
         */
        $xmlObj->ToUserName;//开发者微信号
        $xmlObj->FromUserName;//发送方帐号（一个OpenID）
        $xmlObj->CreateTime;//消息创建时间 （整型）
        $xmlObj->MsgType;//消息类型（text、image、、、、、、、、、、）
        $xmlObj->MsgId;//消息id，64位整型
    }

    /**
     * 自定义菜单 设置
     * @author yanxuefa
     * @date2018-01-07
     * @return         [type] [description]
     */
    public function menu_create( $arr ){
        
        $url = $this->_URL['cgi-bin'].'menu/create?access_token='.$this->token();
        return $this->http($url,json_encode(['button'=>$arr],JSON_UNESCAPED_UNICODE));
    }

    /**
     * 自定义菜单 查询
     * @author yanxuefa
     * @date2018-01-07
     * @return         [type] [description]
     */
    public function menu_get(){

        $url = $this->_URL['cgi-bin'].'menu/get?access_token='.$this->token();
        return $this->http($url);

    }

    /**
     * 自定义菜单 删除
     * @author yanxuefa
     * @date2018-01-07
     * @return         [type] [description]
     */
    public function menu_delete(){
        $url = $this->_URL['cgi-bin'].'menu/delete?access_token='.$this->token();
        return $this->http($url);

    }

    /**
     * 获取用户 open id
     * @author yanxuefa
     * @date2018-01-07
     * @param          string $scope [description]
     * @return         [type]        [description]
     */
    public function sns_get_open_id( $scope='snsapi_base' ){
        $url = $this->DOMAIN.$_SERVER['REQUEST_URI'];
        if(isset($_GET['code'])){

            // 第二步：通过code换取网页授权access_token
            $info = $this->sns_access_token($_GET['code']);
			// var_dump($info);die;
            if(!empty($info['data']['openid'])){
                return $info['data']; //['openid']
            }
            return '';
        }

        // 第一步：用户同意授权，获取code
        $oauth2_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->_CONFIG['appID'].'&redirect_uri='.urlencode($url).'&response_type=code&scope='.$scope.'&state=#wechat_redirect';
        header("location:".$oauth2_url);
        exit;
    }

    /**
     * 获取 用户 open id
     * @author yanxuefa
     * @date2018-01-07
     * @param          [type] $code [description]
     * @return         [type]       [description]
     */
    private function sns_access_token( $code ){

        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->_CONFIG['appID'].'&secret='.$this->_CONFIG['appsecret'].'&code='.$code.'&grant_type=authorization_code';
        return $this->http($url);
    }

    /**
     * 获取用户列表
     * @author yanxuefa
     * @date2018-01-07
     * @return         [type] [description]
     */
    public function user_get(){

        $url = $this->_URL['cgi-bin'].'user/get?access_token='.$this->token();
        return $this->http($url);
    }

    /**
     * 设置用户备注名
     * @author yanxuefa
     * @date2018-01-07
     * @return         [type] [description]
     */
    public function user_updateremark( $openid, $remark ){

        $url = $this->_URL['cgi-bin'].'user/info/updateremark?access_token='.$this->token();
        $data = [
            'openid'=>$openid,
            'remark'=>$remark,
        ];
        return $this->http($url,json_encode($data));
    }

    /**
     * 获取用户基本信息(UnionID机制)
     * @author yanxuefa
     * @date2018-01-07
     * @return         [type] [description]
     */
    public function user_info( $open_id, $access_token){

        $url = $this->_URL['sns'].'userinfo?access_token='.$access_token.'&openid='.$open_id.'&lang=zh_CN';
        return $this->http($url);

    }

    /**
     * 上传图文消息内的图片获取URL
     * @author yanxuefa
     * @date   2018-01-08
     * @param  [type]     $url [description]
     * @return [type]          [description]
     */
    public function media_uploadimg($url){
        if(class_exists('CURLFile')){
            $data = array('media' => new CURLFile(realpath($url)));
        }else{
            $data = array('media'=>'@'.$url);
        }
        $url = $this->_URL['cgi-bin'].'media/uploadimg?access_token='.$this->token();
        return $this->http($url,$data);
    }

    /**
     * 上传资源获得资源 media_id
     * @author yanxuefa
     * @date   2018-01-08
     * @param  [type]     $url [description]
     * @return [type]          [description]
     */
    public function media_upload($url,$type='image'){
        if(class_exists('CURLFile')){
            $data = array('media' => new CURLFile(realpath($url)));
        }else{
            $data = array('media'=>'@'.$url);
        }
        $url = $this->_URL['cgi-bin'].'media/upload?access_token='.$this->token().'&type='.$type;
        return $this->http($url,$data);
    }

    /**
     * 上传图文消息素材
     * @author yanxuefa
     * @date   2018-01-08
     * @param  [type]     $url [description]
     * @return [type]          [description]
     */
    public function media_uploadnews($data){

        $url = $this->_URL['cgi-bin'].'media/uploadnews?access_token='.$this->token();
        return $this->http($url,json_encode(['articles'=>$data],JSON_UNESCAPED_UNICODE));
    }

    /**
     * 根据标签进行群发
     * @author yanxuefa
     * @date   2018-01-08
     * @return [type]     [description]
     */
    public function message_sendall( $media_id ){

        $data = [
            'msgtype'=>'mpnews',//图文消息为mpnews，文本消息为text，语音为voice，音乐为music，图片为image，视频为video，卡券为wxcard
            'send_ignore_reprint'=>1,//图文消息被判定为转载时，是否继续群发。 1为继续群发（转载），0为停止群发。 该参数默认为0。
            'filter'=>[
                'is_to_all'=>true,//选择true该消息群发给所有用户，选择false可根据tag_id发送给指定群组的用户
                // 'tag_id'=>2,
            ],
            'mpnews'=>[
                'media_id'=>$media_id,
            ],
        ];

        $url = $this->_URL['cgi-bin'].'message/mass/sendall?access_token='.$this->token();
        return $this->http($url,json_encode($data));

    }


    /**
     * 长链接转短链接接口
     * @author yanxuefa
     * @date   2018-01-08
     * @param  [type]     $url [description]
     * @return [type]          [description]
     */
    public function shorturl($url){
        $data = [
            'action'=>'long2short',
            'long_url'=>$url,
        ];
        
        $url = $this->_URL['cgi-bin'].'shorturl?access_token='.$this->token();
        return $this->http($url,json_encode($data));
    }


    /**
     * 生成带参数的二维码
     * @author yanxuefa
     * @date   2018-01-08
     * @param  [type]     $url [description]
     * @return [type]          [description]
     */
    public function qrcode_create($key='test'){
        /**
         * action_name介绍：
         * 
         * QR_SCENE         临时 整型参数值
         * QR_STR_SCENE     临时 字符串参数值
         * 
         * QR_LIMIT_SCENE       永久 整型参数值
         * QR_LIMIT_STR_SCENE   永久 字符串参数值
         */
        
        $data = [
            'action_name'=>'QR_STR_SCENE',
            'expire_seconds'=>2592000,
            'action_info'=>[
                // 'scene'=>['scene_id'=>1]
                'scene'=>['scene_str'=>$key]
            ]
        ];
        
        $url = $this->_URL['cgi-bin'].'qrcode/create?access_token='.$this->token();
        return $this->http($url,json_encode($data));
    }

    /**
     * 通过ticket换取二维码
     * @author yanxuefa
     * @date   2018-01-08
     * @param  [type]     $url [description]
     * @return [type]          [description]
     */
    public function showqrcode( $ticket, $name='qrcode'){
        
        $url = $this->_URL['mp'].'showqrcode?ticket='.urlencode($ticket);
        $img = $this->http($url,'',true);

        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Content-Disposition: attachment; filename=".$name.".jpg");
        echo $img;
        exit;
    }

    /**
     * jssdk 生成签名
     * @author yanxuefa
     * @date   2018-01-08
     * @return [type]     [description]
     */
    public function jssdk_sign(){

        $arr = array(
            'noncestr'      =>$this->createNonceStr(),
            'jsapi_ticket'  =>$this->ticket_getticket(),
            'timestamp'     =>time(),
            'url'           =>$this->DOMAIN.$_SERVER['REQUEST_URI'],
            );
        ksort($arr);
        $result = array();
        foreach($arr as $k=>$v)
        {
            $result[] = $k.'='.$v;
        }
        $str = implode('&',$result);

        $arr['sign']    = sha1($str);
        $arr['app_id']  = $this->_CONFIG['appID'];
        return $arr;
    }

    /**
     * 生成随机字符串
     * @author yanxuefa
     * @date   2018-01-08
     * @param  integer    $length [description]
     * @return [type]             [description]
     */
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * jssdk 获取 ticket
     * @author yanxuefa
     * @date   2018-01-08
     * @return [type]     [description]
     */
    public function ticket_getticket(){

        $url = $this->_URL['cgi-bin'].'ticket/getticket?access_token='.$this->token().'&type=jsapi';
        $info = $this->http($url);
        return $info['data']['ticket'];

        // $jsapi_ticket = S('wx.jsapi_ticket');
        // if(empty($jsapi_ticket))
        // {
        //     $info = $this->http('https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$this->getAccountToken().'&type=jsapi');
        //     if(!empty($info['data']['ticket']))
        //     {
        //         $jsapi_ticket = $info['data']['ticket'];
        //         S('wx.jsapi_ticket',$jsapi_ticket,7000);
        //     }
        // }
        // return $jsapi_ticket;
    }
    /**
     * ###########################################################################################################
     * ########################### 以下方法为公共方法 ############################################################
     * ###########################################################################################################
     */
    
    /**
     * [xmlToObj description]
     * @author yanxuefa
     * @date2018-01-05
     * @param          string $xml [description]
     * @return         [type]      [description]
     */
    private function xmlToObj($xml=''){
        if(empty($xml)){
            return '';
        }

        $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        if(empty($xml)){
            return '';
        }
        return $xml;
    }

    /**
     * http 请求
     * @author yanxuefa
     * @date   2018-01-08
     * @param  [type]     $url             请求的地址 
     * @param  string     $data            post 时填写
     * @param  boolean    $return_original 是否返回微信接口返回的原始数据
     * @return [type]                      [description]
     */
    private function http( $url, $data='',$return_original=false){

        //基础配置
        $option = array(

            //要访问的地址
            CURLOPT_URL=>$url,

            //超时时间 5 秒
            CURLOPT_TIMEOUT=>30,

            //自动设置Referer
            CURLOPT_AUTOREFERER=>true,

            //将获取的信息以文件流的形式返回
            CURLOPT_RETURNTRANSFER=>true,
            
            //对认证证书来源的检查
            CURLOPT_SSL_VERIFYPEER=>0,
            
            //从证书中检查SSL加密算法是否存在
            CURLOPT_SSL_VERIFYHOST=>2,
            
            //自动跳转
            CURLOPT_FOLLOWLOCATION=>1
            );

        //POST
        if($data)
        {
            $option[CURLOPT_POST]                   = true;
            if(defined(CURLOPT_SAFE_UPLOAD)){
                // $option[CURLOPT_SAFE_UPLOAD]        = false;
            }
            $option[CURLOPT_POSTFIELDS]             = $data;
        }
        
        //初始化
        $ch = curl_init();

        //配置参数
        curl_setopt_array($ch, $option);

        //执行获得返回值
        $c = curl_exec($ch);

        #出错
        if (curl_errno($ch) || empty($c)){

            // 重试机制 3 次
            if(!isset($_GET['curl_http_num'])){
                $_GET['curl_http_num']=0;
            }
            $_GET['curl_http_num']++;
            if($_GET['curl_http_num']==3){
                return $this->result( 1, curl_error($ch) );
            }
            return $this->http( $url, $data,$return_original );
        }

        //关闭
        curl_close($ch);
        if($return_original){
            return $c;
        }
        $r = json_decode($c,true);
        if(empty($r)){
            return $this->result( 1, '微信服务器繁忙，请稍候再试！'.$c );
        }
        if(!empty($r['errcode']))
        {
            return $this->result( $r['errcode'], $r['errmsg'] );
        }
        return $this->result( 200, '', $r );
    }

    /**
     * 返回结果
     * @author yanxuefa
     * @date   2018-01-04
     * @param  [type]     $code [200 成功 非 200 失败]
     * @param  [type]     $msg  [description]
     * @param  array      $data [description]
     * @return [type]           [description]
     */
    public function result( $code, $msg='', $data=[] ){
        return array(
            'code'=>$code,
            'info'=>$msg,
            'data'=>$data
        );
    }
	
	/**
    * 获取所有客服
    */
    public function getkflist()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token='.$this->token();
        return $this->http($url);
    }
	
	function pr( $var = '', $is_exit=true ){
		echo '<pre>';
		if(empty($var)){
			var_dump($var);
		}else{
			print_R($var);
		}
		echo '</pre>';
    if($is_exit)exit;
	}
	
	/**
     * 模板消息发送
     * @author yanxuefa
     * @date   2018-04-12
     * @param  array      $data [description]
     * @return [type]           [description]
     */
    public function message_template_send( $data = [] ){
        $url = $this->_URL['cgi-bin'].'message/template/send?access_token='.$this->token();
        return $this->http($url,json_encode($data));
    }
}
