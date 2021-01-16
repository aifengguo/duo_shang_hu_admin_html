<?php
namespace crmeb\subscribes;

/**
 * 商品事件
 * Class ProductSubscribe
 * @package crmeb\subscribes
 */
class ProductSubscribe
{


    public function handle()
    {

    }

    /**
     * 加入购物车成功之后
     * @param $event
     */
    public function onStoreProductSetCartAfter($event)
    {
       list($cartInfo, $userInfo) = $event;
       //$cartInfo 购物车信息
       //$userInfo 用户信息
    }

    /**
     * 用户操作商品添加事件  用户点赞商品  用户收藏商品
     * @param $event
     */
    public function onStoreProductUserOperationConfirmAfter($event){
       list($category, $productId, $relationType, $uid) = $event;
       //$category 商品类型
       //$productId 商品编号
       //$relationType 操作类型   like  点赞  collect 收藏
       //$uid 用户编号
    }

    /**
     * 用户操作商品取消事件    用户取消点赞商品  用户取消收藏商品
     * @param $event
     */
    public function onStoreProductUserOperationCancelAfter($event){
        list($category, $productId, $relationType, $uid) = $event;
        //$category 商品类型
        //$productId 商品编号
        //$relationType 操作类型   like  点赞  collect 收藏
        //$uid 用户编号
    }
}