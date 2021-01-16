<?php
/**
 * Created by CRMEB.
 * User: 136327134@qq.com
 * Date: 2019/4/12 11:19
 */

namespace crmeb\utils;

/**
 * 错误码统一存放类
 * Class ApiErrorCode
 * @package crmeb\services
 */
class ApiErrorCode
{

    const SUCCESS = [200, 'SUCCESS'];
    const ERROR = [400, 'ERROR'];

    const ERR_LOGIN = [410000, 'Landing overdue'];
    const ERR_AUTH = [400011, 'You do not have permission to access for the time being'];
    const ERR_RULE = [400012, 'Interface is not authorized, you cannot access'];
    const ERR_ADMINID_VOID = [400013, 'Failed to get administrator ID'];

}