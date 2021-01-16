<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/28
 */

namespace app\models\store;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 订单修改状态记录Model
 * Class StoreOrderStatus
 * @package app\models\store
 */
class StoreOrderStatus extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_order_status';

    use ModelTrait;

    /**
     * 格式化时间
     * @param $name
     * @return false|string
     */
    public function getChangeTimeAttr($name)
    {
        return date('Y-m-d H:i:s',$name);
    }

    /**
     * 更新订单状态
     * @param $oid
     * @param $change_type
     * @param $change_message
     * @param null $change_time
     * @return StoreOrderStatus|\think\Model
     */
    public static function status($oid,$change_type,$change_message,$change_time = null)
    {
        if($change_time == null) $change_time = time();
        return self::create(compact('oid','change_type','change_message','change_time'));
    }

    /**
     * status 方法别名
     * @param $oid
     * @param $change_type
     * @param $change_message
     * @param null $change_time
     * @return StoreOrderStatus|\think\Model
     */
    public static function setStatus($oid,$change_type,$change_message,$change_time = null)
    {
        return self::status($oid,$change_type,$change_message,$change_time);
    }

    /**
     * @param $oid
     * @param $change_type
     * @return mixed
     */
    public static function getTime($oid,$change_type)
    {
        return self::where('oid',$oid)->where('change_type',$change_type)->value('change_time');
    }

    /**
     * 获取订单状态列表
     * @param $id
     * @param $page
     * @param $limit
     * @return array
     */
    public static function getOrderList($id,$page,$limit)
    {
        $list = self::where('oid',$id)->page($page,$limit)->select();
        return $list ? $list->toArray() : [];
    }

}