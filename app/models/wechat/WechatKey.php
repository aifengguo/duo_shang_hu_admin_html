<?php
namespace app\models\wechat;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 关键词model
 * Class WechatTemplate
 * @package app\models\wechat
 */
class WechatKey extends BaseModel
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
    protected $name = 'wechat_key';

}