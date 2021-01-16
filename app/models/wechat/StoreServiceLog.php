<?php

namespace app\models\wechat;

use app\models\user\User;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 客服管理 model
 * Class StoreProduct
 * @package app\models\store
 */
class StoreServiceLog extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_service_log';

    use ModelTrait;

    protected function getAddTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * @param $where
     * @return array
     */
    public static function getChatList($where, $mer_id)
    {
        $model = new self;
        $model = $model->where('mer_id', $mer_id);
        $model = $model->whereIn('uid', [$where['uid'], $where['to_uid']]);
        $model = $model->whereIn('to_uid', [$where['uid'], $where['to_uid']]);
        $count = $model->count();
        $model->order("add_time desc");
        $list = $model->select()
            ->each(function ($item) use ($mer_id) {
                $user = StoreService::field("nickname,avatar")->where('mer_id', $mer_id)->where(array("uid" => $item["uid"]))->find();
                if (!$user) $user = User::field("nickname,avatar")->where(array("uid" => $item["uid"]))->find();
                $item["nickname"] = $user["nickname"];
                $item["avatar"] = $user["avatar"];
            });
        return compact('count', 'list');
    }
}