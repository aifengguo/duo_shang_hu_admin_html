<?php
/**
 * Created by CRMEB.
 * Copyright (c) 2017~2020 http://www.crmeb.com All rights reserved.
 * Author: wuhaotian <442384644@qq.com>
 * Date: 2020/5/7 11:30
 */

namespace app\models\user;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * TODO 用户访问记录
 * Class UserVisit
 * @package app\models\user
 */
class UserVisit extends BaseModel
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
    protected $name = 'user_visit';

    use ModelTrait;

}