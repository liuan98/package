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

    /**
     * @param $data
     * @return mixed|string
     * User: liuan
     * Date: 2020/8/27 15:06
     * h5支付
     */
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


    ///////////////////////////////////////
    /////////////////////////////////////////


    /**
     * @param $length
     * @return null|string
     * User: liuan
     * Date: 2020/8/25 11:02
     * 获取指定长度的随机字符串
     */
    public function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    /**
     * @param $arr
     * @return string
     * User: liuan
     * Date: 2020/8/25 11:03
     * 数组转xml
     */
    public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";

            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * @param $xml
     * @param $url
     * @param int $second
     * @param int $cert
     * @param $list
     * @return mixed|string
     * User: liuan
     * Date: 2020/8/27 15:10
     */
    public function postXmlCurl($xml,$url,$second=30, $cert=0 ,$list)
    {
        //初始化curl
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        //设置证书
        if($cert){
            //此处证书引用绝对路径
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'pem');
            curl_setopt($ch, CURLOPT_SSLCERT, $list['cert']);
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'pem');
            curl_setopt($ch, CURLOPT_SSLKEY, $list['key']);
        }
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data)
        {
            curl_close($ch);
            return $data;
        }
        else
        {
            $error = curl_errno($ch);
            //echo "curl出错，错误码:$error"."<br>";
            return json_encode(array('code'=>$error,'status'=>0));
            //return false;
        }
    }

    /**
     * @param $xmlstr
     * @return mixed
     * User: liuan
     * Date: 2020/8/25 11:22
     * xml转成数组
     */
    public function xmlstr_to_array($xmlstr) {

        //将XML转为array
        return json_decode(json_encode(simplexml_load_string($xmlstr, 'SimpleXMLElement',LIBXML_NOCDATA)), true);

    }

    /**
     * @param $order
     * @return array|int
     * User: liuan
     * Date: 2020/8/25 11:44
     * 微信退款方法
     */
    public function refundOrder($order){
        $key = $order['key'];//'GZxundongkeji2017070707070707070';//在微信开放平台中的

        //微信退款接口地址
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        $onoce_str = $this->getRandChar(32);//随机字符串
        $data["appid"] = $order['appid'];//'wx8b25a352402a35ec';//你得APPID
        $data["mch_id"] = $order['mch_id'];//'1271505401';//商户号
        $data["nonce_str"] = $onoce_str;
        $data["out_refund_no"] = $order['number'];//你得订单号
        $data["out_trade_no"] = $order['number'];//你得订单号
        //$data["transaction_id"] = $order->trade_no;//订单支付成功后微信返回的微信数据中订单号，传了订单号可不传这个参数
        $total_fee = $order['price'];//你得订单金额
        $data["refund_fee"] = $total_fee*100;//支付金额
        $data["total_fee"] = $total_fee*100;//支付金额

        // 签名逻辑官网有说明，签名步骤就不解释了
        ksort($data);

        $buff = "";
        foreach ($data as $k => $v)
        {
            $buff .= $k . "=" . $v . "&";
        }
        $buff = trim($buff, "&");
        $stringSignTemp=$buff."&key=$key";
        $data["sign"] =  strtoupper(md5($stringSignTemp)); //签名

        $xml = $this->arrayToXml($data);

        $list['cert'] = $order['cert'];
        $list['key'] = $order['key'];

        $response = $this->postXmlCurl($xml, $url ,30 ,1 , $list);

        //将微信返回的结果xml转成数组
        $result = $this->xmlstr_to_array($response);

        if($result['result_code'] && $result['result_code']=='SUCCESS'){
            //更新数据
            return '成功';
        }else{
            return '失败';
        }
    }

}