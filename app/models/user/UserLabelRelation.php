<?php

namespace app\models\user;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class UserLabelRelation extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'user_label_relation';

    use ModelTrait;

    public static function saveUserLabel($uids, $labels)
    {
        self::whereIn('uid', $uids)->delete();
        $data = [];
        foreach ($uids as $uid) {
            foreach ($labels as $label) {
                $data[] = ['uid' => $uid, 'label_id' => $label];
            }
        }
        $res = true;
        if(!empty($data))
            $res = self::insertAll($data);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }
}
