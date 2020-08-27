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

    public function WeChatH5($data){
        $ip =$_SERVER;
        $create_ip  =  strstr ( $ip['SSH_CLIENT'],' ',true );//获取ip

        $nonce = md5(time().mt_rand(0,1000));//随机字符串

        $money = $data['price'] * 100; //商品价格
        // 前台请求的参数
        $title = $data['name'];//商品名称

        $nonce_str = $nonce; //随机字符串
        //var_dump($nonce_str,__LINE__);
        $appid = $data['appid'];//"wx8b25a352402a35ec"; //在微信开放平台中的　appid(先要创建一个移动应用)
        $mch_id = $data['mch_id'];//"1271505401";  //商户号，在商户平台中查看
        $key = $data['key'];//"GZxundongkeji2017070707070707070"; //在微信开放平台中的　
        $notify_url = $data['notify_url'];//"http://lightning.xundong.top/Pay"; //用户支付完后微信会来触发这个脚本，是处理业务逻辑的地方
        //订单号可以灵活使用，比如我这个地方把userid加进去，在异步回调的时候方便直接操作用户
        $out_trade_no = $data['number'];//订单号
        $spbill_create_ip =  $create_ip;//获取ip
//            var_dump($spbill_create_ip,__LINE__);

        //场景信息
        $scene_info = "{'h5_info': {'type'':'Wap','wap_url': 'http://www.lpjphp.cn','wap_name': 'h5pay'}}";

        // 下面的参数含义直接看文档
        $tmpArr = array(
            'appid'=>$appid,//不要填成了 公众号原始id
            'scene_info'=>$scene_info,
            'body'=>$title,
            'mch_id'=>$mch_id,
            'nonce_str'=>$nonce_str,
            'notify_url'=>$notify_url,
            'out_trade_no'=>$out_trade_no,
            'spbill_create_ip'=>$spbill_create_ip,
            'total_fee'=>$money,
            'trade_type'=>'MWEB'
        );
        // 签名逻辑官网有说明，签名步骤就不解释了
        ksort($tmpArr);

        $buff = "";
        foreach ($tmpArr as $k => $v)
        {
            $buff .= $k . "=" . $v . "&";
        }
        $buff = trim($buff, "&");
        $stringSignTemp=$buff."&key=$key";
        $sign= strtoupper(md5($stringSignTemp)); //签名
//            var_dump($sign,__LINE__);

        $xml = "<xml>
            <appid>".$appid."</appid>
            <body>".$title."</body>
            <mch_id>".$mch_id."</mch_id>
            <nonce_str>".$nonce_str."</nonce_str>
            <notify_url>".$notify_url."</notify_url>
            <out_trade_no>".$out_trade_no."</out_trade_no>
            <spbill_create_ip>".$spbill_create_ip."</spbill_create_ip>
            <total_fee>".$money."</total_fee>
            <trade_type>MWEB</trade_type>
            <sign>".$sign."</sign>
            <scene_info>".$scene_info."</scene_info>
            </xml> ";

        $posturl = "https://api.mch.weixin.qq.com/pay/unifiedorder";

        $ch = curl_init($posturl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        curl_close($ch);

        $xmlobj = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA )),true);
//            var_dump($xmlobj,__LINE__);

        if ($xmlobj['return_code'] == 'SUCCESS' && $xmlobj['return_code'] == 'SUCCESS') {
            return $xmlobj;
        }else{
            return '失败';
        }
    }

}