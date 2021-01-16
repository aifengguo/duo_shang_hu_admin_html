<?php

namespace app\adminapi\controller;


use crmeb\basic\BaseBusiness;
use crmeb\business\order\StoreOrder;
use crmeb\repositories\AuthRepository;
use crmeb\services\CacheService;
use crmeb\services\upload\Upload;
use Firebase\JWT\JWT;
use app\models\system\SystemAdmin;
use think\facade\Cache;
use think\facade\Log;
use think\Request;
use \crmeb\utils\Captcha;
use think\facade\Config;

class Test
{

    public function index()
    {
//        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjcm1lYnByby5uZXQiLCJhdWQiOiJjcm1lYnByby5uZXQiLCJpYXQiOjE1ODY0MTIwMDUsIm5iZiI6MTU4NjQxMjAwNSwiZXhwIjoxNTg2NDIyODA1LCJqdGkiOnsiaWQiOjEsInR5cGUiOiJhZG1pbiJ9fQ.k3lM-a2w9x9angt1Dkt5UrvcuXg04kfmTYv3wBdacyM";
//
//
//        $tks = explode('.', $token);
//        if (count($tks) != 3) {
//            //throw new UnexpectedValueException('Wrong number of segments');
//        }
//        list($headb64, $bodyb64, $cryptob64) = explode('.', $token);
//        $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
//        dump($payload, date('Y-m-d H:i:s', $payload->exp), $payload->jti->id);
//        $order = new StoreOrder();
//        $order->getList(['page' => \request()->param('page', 1), 'limit' => \request()->param('limit', 10)]);

//        $res = AuthRepository::getTokenBucket('3132');
//        $redis = Cache::store('redis');
//        dump($redis->get('3123'));
//        Log::info('订单支付成功下发队列消息成功订单号为:WXQWEWQREWREWR123123123' );
//        $res = \think\facade\Queue::push(\crmeb\jobs\OrderJob::class, ['data' => [['order_id'=>'wx34234324'],13213]]);
//        var_dump($res);
//        var_dump(CacheService::get('captcha'));

//        $upload = new Upload('jinshan');
//        $upload->to()->validate()->move();
        $orderId = 'wx34234324asdasdq341332';
        $res = \crmeb\utils\Queue::instance()->job(\crmeb\jobs\TemplateJob::class)->log('测试写入' . $orderId)->data([
            'order_id' => $orderId
        ])->push();
        dump($res);
    }

    public function show()
    {
        $captcha = new Captcha();
        $generate = $captcha->generate();
        CacheService::set('captcha', $generate, 1800);
        return $captcha->create('', $generate, false);
    }

    public function login(Request $request)
    {
        dump($request->adminInfo());
    }

    public function test()
    {
        $systemAdmin = SystemAdmin::login('123456', '123456');
        $res = SystemAdmin::createToken($systemAdmin, 'admin', ['exp' => time() + 30]);
        dump($res, date('Y-m-d H:i:s', $res['params']['exp']));
    }
}