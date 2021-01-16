<?php

namespace app\api\controller\store;

use app\models\store\StoreDescription;
use app\models\system\SystemAttachment;
use app\models\store\StoreOrder;
use app\models\system\SystemStore;
use app\models\store\StoreProduct;
use app\models\store\StoreProductAttr;
use app\models\store\StoreProductRelation;
use app\models\store\StoreProductReply;
use app\models\user\User;
use app\Request;
use crmeb\services\QrcodeService;
use crmeb\services\UploadService;
use crmeb\services\UtilService;
use app\models\routine\RoutineCode;

/**
 * 商品类
 * Class StoreProductController
 * @package app\api\controller\store
 */
class StoreProductController
{
    /**
     * 商品列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        $data = UtilService::getMore([
            [['sid', 'd'], 0],
            [['cid', 'd'], 0],
            ['keyword', ''],
            ['priceOrder', ''],
            ['salesOrder', ''],
            [['news', 'd'], 0],
            [['page', 'd'], 0],
            [['limit', 'd'], 0],
            [['type', 0], 0]
        ], $request);
        return app('json')->successful(StoreProduct::getProductList($data, $request->uid()));
    }

    /**
     * 商品分享二维码 推广员
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function code(Request $request, $id)
    {
        if (!$id || !($storeInfo = StoreProduct::getValidProduct($id, 'id'))) return app('json')->fail('商品不存在或已下架');
        $userType = $request->get('user_type', 'wechat');
        $user = $request->user();
        try {
            switch ($userType) {
                case 'wechat':
                    //公众号
                    $name = $id . '_product_detail_' . $user['uid'] . '_is_promoter_' . $user['is_promoter'] . '_wap.jpg';
                    $url = QrcodeService::getWechatQrcodePath($name, '/pages/goods_details/index?id=' . $id . '&spread=' . $user['uid']);
                    if ($url === false)
                        return app('json')->fail('二维码生成失败');
                    else
                        return app('json')->successful(['code' => image_to_base64($url)]);
                    break;
                case 'routine':
                    //小程序
                    $name = $id . '_' . $user['uid'] . '_' . $user['is_promoter'] . '_product.jpg';
                    $imageInfo = SystemAttachment::getInfo($name, 'name');
                    $siteUrl = sys_config('site_url');
                    if (!$imageInfo) {
                        $data = 'id=' . $id;
                        if ($user['is_promoter'] || sys_config('store_brokerage_statu') == 2) $data .= '&pid=' . $user['uid'];
                        $res = RoutineCode::getPageCode('pages/goods_details/index', $data, 280);
                        if (!$res) return app('json')->fail('二维码生成失败');
                        $uploadType = (int)sys_config('upload_type', 1);
                        $upload = UploadService::init();
                        $res = $upload->to('routine/product')->validate()->stream($res, $name);
                        if ($res === false) {
                            return app('json')->fail($upload->getError());
                        }
                        $imageInfo = $upload->getUploadInfo();
                        $imageInfo['image_type'] = $uploadType;
                        if ($imageInfo['image_type'] == 1) $remoteImage = UtilService::remoteImage($siteUrl . $imageInfo['dir']);
                        else $remoteImage = UtilService::remoteImage($imageInfo['dir']);
                        if (!$remoteImage['status']) return app('json')->fail('小程序二维码未能生成');
                        SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                        $url = $imageInfo['dir'];
                    } else $url = $imageInfo['att_dir'];
                    if ($imageInfo['image_type'] == 1) $url = $siteUrl . $url;
                    return app('json')->successful(['code' => $url]);
            }
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage(), [
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * 商品详情
     * @param Request $request
     * @param $id
     * @param int $type
     * @return mixed
     */
    public function detail(Request $request, $id, $type = 0)
    {
        if (!$id || !($storeInfo = StoreProduct::getValidProduct($id))) return app('json')->fail('商品不存在或已下架');
        $siteUrl = sys_config('site_url');
        $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['slider_image'] = json_decode($storeInfo['slider_image'], true);
        $storeInfo['code_base'] = QrcodeService::getWechatQrcodePath($id . '_product_detail_wap.jpg', '/pages/goods_details/index?id=' . $id);
        $uid = $request->uid();
        $data['uid'] = $uid;
        $storeInfo['description'] = StoreDescription::getDescription($id);
        //替换windows服务器下正反斜杠问题导致图片无法显示
        $storeInfo['description'] = preg_replace_callback('#<img.*?src="([^"]*)"[^>]*>#i', function ($imagsSrc) {
            return isset($imagsSrc[1]) && isset($imagsSrc[0]) ? str_replace($imagsSrc[1], str_replace('\\', '/', $imagsSrc[1]), $imagsSrc[0]) : '';
        }, $storeInfo['description']);
        $storeInfo['userCollect'] = StoreProductRelation::isProductRelation($id, $uid, 'collect');
        $storeInfo['userLike'] = StoreProductRelation::isProductRelation($id, $uid, 'like');
        list($productAttr, $productValue) = StoreProductAttr::getProductAttrDetail($id, $uid, $type);
        set_view($uid, $id, $storeInfo['cate_id'], 'viwe');
        $data['storeInfo'] = StoreProduct::setLevelPrice($storeInfo, $uid, true);
        $data['similarity'] = StoreProduct::cateIdBySimilarityProduct($storeInfo['cate_id'], 'id,store_name,image,price,sales,ficti', 4);
        $data['productAttr'] = $productAttr;
//        $prices = array_column($productValue, 'price');
//        array_multisort($prices, SORT_ASC, SORT_NUMERIC, $productValue);
//        $keys = array_keys($productValue);
//        $productValue = array_combine($keys, $productValue);
        $data['productValue'] = $productValue;
        $data['priceName'] = 0;
        if ($uid) {
            $user = $request->user();
            if (!$user->is_promoter) {
                $price = StoreOrder::where(['paid' => 1, 'refund_status' => 0, 'uid' => $uid])->sum('pay_price');
                $status = is_brokerage_statu($price);
                if ($status) {
                    User::where('uid', $uid)->update(['is_promoter' => 1]);
                    $user->is_promoter = 1;
                }
            }
            if ($user->is_promoter) {
                $data['priceName'] = StoreProduct::getPacketPrice($storeInfo, $productValue);
            }
            if (!strlen(trim($data['priceName'])))
                $data['priceName'] = 0;
        }
        $data['reply'] = StoreProductReply::getRecProductReply($storeInfo['id']);
        $data['replyCount'] = StoreProductReply::productValidWhere()->where('product_id', $storeInfo['id'])->count();
        if ($data['replyCount']) {
            $goodReply = StoreProductReply::productValidWhere()->where('product_id', $storeInfo['id'])->where('product_score', 5)->count();
            $data['replyChance'] = $goodReply;
            if ($goodReply) {
                $data['replyChance'] = bcdiv($goodReply, $data['replyCount'], 2);
                $data['replyChance'] = bcmul($data['replyChance'], 100, 2);
            }
        } else $data['replyChance'] = 100;
        $data['mer_id'] = $storeInfo['mer_id'];
        $data['system_store'] = ($res = SystemStore::getStoreDispose()) ? $res : [];
        $data['good_list'] = StoreProduct::getGoodList(18, 'image,store_name,price,id,ot_price');
        $data['mapKey'] = sys_config('tengxun_map_key');
        $data['store_self_mention'] = (int)sys_config('store_self_mention') ?? 0;//门店自提是否开启
        $data['activity'] = StoreProduct::activity($data['storeInfo']['id'], false);
        return app('json')->successful($data);
    }

    /**
     * 为你推荐
     *
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function product_hot(Request $request)
    {
        list($page, $limit) = UtilService::getMore([
            [['page', 'd'], 0],
            [['limit', 'd'], 0]
        ], $request, true);
        if (!$limit) return app('json')->successful([]);
        $productHot = StoreProduct::getHotProductLoading('id,image,store_name,cate_id,price,unit_name,ot_price', (int)$page, (int)$limit);
        if (!empty($productHot)) {
            foreach ($productHot as $k => $v) {
                $productHot[$k]['activity'] = StoreProduct::activity($v['id']);
            }
        }
        return app('json')->successful($productHot);
    }

    /**
     * 获取首页推荐不同类型商品的轮播图和商品
     * @param Request $request
     * @param $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function groom_list(Request $request, $type)
    {
        list($page, $limit) = UtilService::getMore([
            [['page', 'd'], 0],
            [['limit', 'd'], 0]
        ], $request, true);
        $info['banner'] = [];
        $info['list'] = [];
        if ($type == 1) {//TODO 精品推荐
            $info['banner'] = sys_data('routine_home_bast_banner') ?: [];//TODO 首页精品推荐图片
            $info['list'] = StoreProduct::getBestProduct('id,image,store_name,cate_id,price,ot_price,IFNULL(sales,0) + IFNULL(ficti,0) as sales,unit_name,sort', 0, $request->uid(),true, $page, $limit);//TODO 精品推荐个数
        } else if ($type == 2) {//TODO  热门榜单
            $info['banner'] = sys_data('routine_home_hot_banner') ?: [];//TODO 热门榜单 猜你喜欢推荐图片
            $info['list'] = StoreProduct::getHotProduct('id,image,store_name,cate_id,price,ot_price,unit_name,sort,IFNULL(sales,0) + IFNULL(ficti,0) as sales', 0, $request->uid(), $page, $limit);//TODO 热门榜单 猜你喜欢
        } else if ($type == 3) {//TODO 首发新品
            $info['banner'] = sys_data('routine_home_new_banner') ?: [];//TODO 首发新品推荐图片
            $info['list'] = StoreProduct::getNewProduct('id,image,store_name,cate_id,price,ot_price,unit_name,sort,IFNULL(sales,0) + IFNULL(ficti,0) as sales', 0, $request->uid(),true, $page, $limit);//TODO 首发新品
        } else if ($type == 4) {//TODO 促销单品
            $info['banner'] = sys_data('routine_home_benefit_banner') ?: [];//TODO 促销单品推荐图片
            $info['list'] = StoreProduct::getBenefitProduct('id,image,store_name,cate_id,price,ot_price,stock,unit_name,sort,IFNULL(sales,0) + IFNULL(ficti,0) as sales',0, $page, $limit);//TODO 促销单品
        }
        return app('json')->successful($info);
    }

    /**
     * 商品评价数量和好评度
     * @param $id
     * @return mixed
     */
    public function reply_config($id)
    {
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        return app('json')->successful(StoreProductReply::productReplyCount($id));
    }

    /**
     * 获取商品评论
     * @param Request $request
     * @param $id
     * @param $type
     * @return mixed
     */
    public function reply_list(Request $request, $id)
    {
        list($page, $limit, $type) = UtilService::getMore([
            [['page', 'd'], 0], [['limit', 'd'], 0], [['type', 'd'], 0]
        ], $request, true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        $list = StoreProductReply::getProductReplyList($id, (int)$type, $page, $limit);
        return app('json')->successful($list);
    }

}