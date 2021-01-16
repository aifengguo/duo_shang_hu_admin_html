<?php

use think\facade\Route;

/**
 * 文件下载、导出相关路由
 */
Route::group(function () {

    //下载备份记录表
    Route::get('backup/download', 'v1.system.SystemDatabackup/downloadFile');
    //首页统计数据
    Route::get('home/header', 'Common/homeStatics');
    //首页订单图表
    Route::get('home/order', 'Common/orderChart');
    //首页用户图表
    Route::get('home/user', 'Common/userChart');
    //
    Route::get('home/rank', 'Common/purchaseRanking');
    // 消息提醒
    Route::get('jnotice', 'Common/jnotice');
    //验证授权
    Route::get('check_auth', 'Common/check_auth');
    //授权
    Route::get('auth', 'Common/auth');

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRole::class
]);;

