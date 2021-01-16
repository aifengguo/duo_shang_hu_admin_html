<?php
namespace app\adminapi\validates\order;

use think\Validate;

/**
 *
 * Class StoreOrderValidate
 * @package app\adminapi\validates
 */
class StoreOrderValidate extends Validate
{

    protected $rule = [
        'order_id'      => ['require','length'=>'1,32','alphaNum'],
        'total_price'   => ['require','number'],
        'total_postage' => ['require','number'],
        'pay_price'     => ['require','number'],
        'pay_postage'   => ['require','number'],
        'gain_integral' => ['number'],
    ];

    protected $message = [
        'order_id.require'      => '订单号必须存在',
        'order_id.length'       => '订单号有误',
        'order_id.alphaNum'     => '订单号必须为字母和数字',
        'total_price.require'   => '订单金额必须填写',
        'total_price.number'    => '订单金额必须为数字',
        'pay_price.require'     => '订单金额必须填写',
        'pay_price.number'      => '订单金额必须为数字',
        'pay_postage.require'   => '订单邮费必须填写',
        'pay_postage.number'    => '订单邮费必须为数字',
        'gain_integral.number'  => '赠送积分必须为数字',
    ];

    protected $scene = [

    ];
}