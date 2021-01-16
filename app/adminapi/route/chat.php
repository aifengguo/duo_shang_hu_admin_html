<?php

use think\facade\Route;

/**
 * 客服管理 相关路由
 */
Route::group('chat', function () {
    //发生客服图文消息
    Route::get('send_news/:id', 'v1.chat.WeChatNewsCategory/send_news');
    
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRole::class
]);