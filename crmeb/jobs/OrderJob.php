<?php


namespace crmeb\jobs;

use crmeb\basic\BaseJob;

/**
 * 订单消息队列
 * Class OrderJob
 * @package crmeb\jobs
 */
class OrderJob extends BaseJob
{
    /**
     * 执行订单支付成功发送消息
     * @param $data
     * @return bool
     */
    public function doJob($data)
    {
        event('OrderPaySuccess', $data);
        return true;
    }
}