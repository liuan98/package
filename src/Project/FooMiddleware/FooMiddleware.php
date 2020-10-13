<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/27 0027
 * Time: 21:20
 */
declare(strict_types=1);

namespace Project\FooMiddleware;

use App\Controller\IndexController;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

class FooMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 根据具体业务判断逻辑走向，这里假设用户携带的token有效
//        $token = $this->request->input('token');
        $token= $this->request->getHeaderLine('token');
        $redis = $this->container->get(\Hyperf\Redis\Redis::class);
        $code = $redis->get('token'.$token);

        if ($code) {
            return $handler->handle($request);
        }
        if(!$code){
            return $this->response->json(
                [
                    'code' => 401,
                    'data' => [
                        'error' => 'token为空,或已已失效',
                    ],
                ]
            );
        }

    }
}