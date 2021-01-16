<?php

namespace app\models\user;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 会员等级Model
 * Class UserLevel
 * @package app\models\user
 */
class UserGroup extends BaseModel
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
    protected $name = 'user_group';

    use ModelTrait;

    /**
     * 会员分组列表
     * @param $where
     * @return array
     */
    public static function getList($where)
    {
        $model = new self();
        $count = $model->count();
        if ($where['limit'] != '') $model = $model->page((int)$where['page'], (int)$where['limit']);
        $list = $model->select();
        return compact('count', 'list');
    }
}