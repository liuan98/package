<?php
/**
 * Created by PhpStorm.
 * User: liuan
 * Date: 2020/8/4
 * Time: 11:00
 */
namespace Project\WeChatH5;

class WeChatH5{

    public function __construct()
    {

    }

    public function WeChatH5(){
        $key = '6082f34026647a65a2530ba21c3d213d';//秘钥在微信开放平台中的
        $appid = 'wxd678efh567hg6787';//微信分配的公众账号ID
        $mch_id = '1230000109';//微信支付分配的商户号
        $nonce_str = md5(time().mt_rand(0,1000));//随机字符串
        $body = '腾讯充值中心-QQ会员充值';//商品简单描述
        $notify_url = 'http://www.weixin.qq.com/wxpay/pay.php';//商品简单描述
        $out_trade_no = '20150806125346';//订单号
        $scene_info = '{"h5_info":{"type":"Wap","wap_url":"http://www.weixin.qq.com","wap_name":"支付"}}';//场景信息 必要参数
        $spbill_create_ip = $_SERVER['REMOTE_ADDR'];
        $total_fee = '1';//金额
        $trade_type = 'MWEB';//H5支付的交易类型为MWEB

        $signA ="appid=$appid&body=$body&mch_id=$mch_id&nonce_str=$nonce_str&notify_url=$notify_url&out_trade_no=$out_trade_no&scene_info=$scene_info&spbill_create_ip=$spbill_create_ip&total_fee=$total_fee&trade_type=$trade_type";
        $strSignTmp = $signA."&key=$key";
        $sign = strtoupper(MD5($strSignTmp));//签名

        $parameter = [
            'appid' => $appid,//微信分配的公众账号ID
            'mch_id' => $mch_id,//微信支付分配的商户号
            'nonce_str' => $nonce_str,//随机字符串
            'sign' => $sign,//签名
            'body' => $body,//商品简单描述
            'out_trade_no' => $out_trade_no,//订单号
            'total_fee' => $total_fee,//金额
            'spbill_create_ip' => $spbill_create_ip,//获取用户ip
            'notify_url' => $notify_url,//回调地址
            'trade_type' => $trade_type,//H5支付的交易类型为MWEB
            'scene_info' => $scene_info,//该字段用于上报支付的场景信息,针对H5支付有以下三种场景,请根据对应场景上报
        ];

        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $rest = $this->httpRequest($url,$parameter);
        return $rest;
    }

    function httpRequest($url, $post_data = '', $method = 'GET')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($post_data != '') {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            }
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}