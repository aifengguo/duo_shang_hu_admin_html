<?php

namespace app\models\wechat;

use app\models\wechat\StoreServiceLog as ServiceLogModel;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 客服管理 model
 * Class StoreProduct
 * @package app\models\store
 */
class StoreService extends BaseModel
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
    protected $name = 'store_service';

    use ModelTrait;

    protected function getAddTimeAttr($value)
    {
        if ($value) return date('Y-m-d H:i:s', $value);
        return $value;
    }

    /**
     * @param $mer_id
     * @return array
     */
    public static function getList($where)
    {
        $model = new self;
        $model = $model->where('mer_id', $where['mer_id'])->order('id desc');
        $count = $model->count();
        $list = $model->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->each(function ($item) {
                $item['wx_name'] = WechatUser::where(['uid' => $item['uid']])->value('nickname');
            });
        return compact('count', 'list');
    }

    /**
     * 获取聊天记录用户
     * @param $now_service
     * @param $mer_id
     * @return array|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getChatUser($now_service, $mer_id, $page, $limit)
    {
        $chat_list = ServiceLogModel::field("uid,to_uid")->where('mer_id', $mer_id)->where('to_uid|uid', $now_service["uid"])->group("uid,to_uid")->select();
        if (count($chat_list) > 0) {
            $chat_list = $chat_list->toArray();
            $arr_user = $arr_to_user = [];
            foreach ($chat_list as $key => $value) {
                array_push($arr_user, $value["uid"]);
                array_push($arr_to_user, $value["to_uid"]);
            }
            $uids = array_merge($arr_user, $arr_to_user);
            $uids = array_flip(array_flip($uids));
            $uids = array_flip($uids);
            unset($uids[$now_service["uid"]]);
            $uids = array_flip($uids);
            if (!count($uids)) return null;
            return WechatUser::field("uid,nickname,headimgurl")
                ->page((int)$page, (int)$limit)
                ->whereIn('uid', $uids)
                ->select()
                ->toArray();
        }
        return null;
    }
}