<?php

namespace app\models\store;

use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 商品规则值
 * @mixin think\Model
 */
class StoreProductCate extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_cate';
    use ModelTrait;
}
