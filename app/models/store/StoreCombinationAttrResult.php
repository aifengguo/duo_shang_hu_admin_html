<?php
/**
 * @author: lofate
 * @day: 2019/12/19
 */

namespace app\models\store;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreCombinationAttrResult extends BaseModel
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_combination_attr_result';

    use ModelTrait;

    protected $insert = ['change_time'];

    protected static function setChangeTimeAttr($value)
    {
        return time();
    }

    protected static function setResultAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    public static function setResult($result, $product_id)
    {
        $result = self::setResultAttr($result);
        $change_time = self::setChangeTimeAttr(0);
        return self::insert(compact('product_id', 'result', 'change_time'), true);
    }

    public static function getResult($productId)
    {
        return json_decode(self::where('product_id', $productId)->value('result'), true) ?: [];
    }

    public static function clearResult($productId)
    {
        return self::del($productId);
    }

}