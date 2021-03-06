<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/25 0025
 * Time: 20:38
 */
namespace Project\Login;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

class Login{
    protected $expire = 0;
    protected $authkey = '';
    protected $container;
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    public function __construct(ContainerInterface $container,HttpResponse $response,RequestInterface $request)
    {
        $this->authkey = md5(md5('cwt0627'));
        $this->container = $container;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param $time//过期时间
     * @return string
     * User: liuan
     * Date: 2020/7/28 11:28
     */
    public function Login($time){
        $str="QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";

        $name = substr(str_shuffle($str),26,10);
        $password = md5(substr(str_shuffle($str),26,10));

        $token = base64_encode($this->authcode($name . '|' . $password, 'ENCODE', $this->authkey,$this->expire));

        $redis = $this->container->get(\Hyperf\Redis\Redis::class);
        $redis->set('token'.$token,$token,$time);

        return $token;
    }

    /**
     * Notes:
     * User: liuan
     * Date: 2020/7/28 0028
     * Time: 22:40
     * @return mixed
     * 退出
     */
    public function Logout(){
        $token = $this->request->input('token');

        $redis = $this->container->get(\Hyperf\Redis\Redis::class);
        $code = $redis->get('token'.$token);

        $data = $redis->del('token'.$code);
        if($data){
            return $this->response->json(
                [
                    'code' => 200,
                    'data' => [
                        'error' => '成功登出',
                    ],
                ]
            );
        }else{
            return $this->response->json(
                [
                    'code' => -1,
                    'data' => [
                        'error' => '登出失败',
                    ],
                ]
            );
        }

    }

    /**
     * Notes:
     * User: liuan
     * Date: 2020/7/27 0027
     * Time: 21:02
     * @param $string
     * @param string $operation
     * @param string $key
     * @param int $expiry
     * @return bool|string
     */
    public function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;
        $key = md5($key != '' ? $key : $GLOBALS['_W']['config']['setting']['authkey']);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }

    }
}