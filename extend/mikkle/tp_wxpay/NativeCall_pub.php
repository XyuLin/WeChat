<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/8/30
 * Time: 9:21
 */
namespace mikkle\tp_wxpay;




/**
 * 请求商家获取商品信息接口
 */
class NativeCall_pub extends Wxpay_server_pub
{
    /**
     * 生成接口参数xml
     */
    function createXml()
    {
        if($this->returnParameters["return_code"] == "SUCCESS"){
            $this->returnParameters["appid"] = config('wechat_appid');//公众账号ID
            $this->returnParameters["mch_id"] = config('wechat_mchid');//商户号
            $this->returnParameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->returnParameters["sign"] = $this->getSign($this->returnParameters);//签名
        }
        return $this->arrayToXml($this->returnParameters);
    }

    /**
     * 获取product_id
     */
    function getProductId()
    {
        $product_id = $this->data["product_id"];
        return $product_id;
    }

}

