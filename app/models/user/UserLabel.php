<?php

namespace app\models\user;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class UserLabel extends BaseModel
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
    protected $name = 'user_label';

    use ModelTrait;

    /**
     * 会员标签列表
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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