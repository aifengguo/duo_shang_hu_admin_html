<?php


namespace app\models\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 店员 model
 * Class SystemStore
 * @package app\admin\model\system
 */
class SystemStoreStaff extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'system_store_staff';

    /**
     * 时间戳获取器转日期
     * @param $value
     * @return false|string
     */
    public static function getAddTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 判断是否是有权限核销的店员
     * @param $uid
     * @return int
     */
    public static function verifyStatus($uid)
    {
        return self::where('uid', $uid)->where('status', 1)->where('verify_status', 1)->count();
    }

    /**
     * 获取店员列表
     * @param array $where
     * @return array
     */
    public static function getList($where = [])
    {
        $model = new self();
        $model = $model->alias('a')
            ->join('wechat_user u', 'u.uid=a.uid')
            ->join('system_store s', 'a.store_id = s.id')
            ->field('a.id,u.nickname,a.avatar,a.staff_name,a.status,a.add_time,s.name');
        if (isset($where['store_id']) && $where['store_id'] != '' && $where['store_id'] > 0) {
            $model = $model->where('store_id', $where['store_id']);
        }
        $count = $model->count();
        $list = $model->page((int)$where['page'], (int)$where['limit'])->select();
        if (count($list)) {
            $list = $list->toArray();
        } else {
            $list = [];
        }
        return compact('count', 'list');
    }
}