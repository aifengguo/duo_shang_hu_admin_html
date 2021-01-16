<?php

namespace app\adminapi\controller\v1\system;

use app\adminapi\controller\AuthController;
use app\models\store\StoreProduct;
use app\models\system\SystemAttachment;
use crmeb\services\UtilService;
use think\facade\Db;
use think\facade\Config;

/**
 * 清除默认数据理控制器
 * Class SystemClearData
 * @package app\admin\controller\system
 */
class SystemClearData extends AuthController
{
    /**
     * 统一方法
     * @param $type
     */
    public function index($type)
    {
        switch ($type) {
            case 'temp':
                return $this->userTemp();
                break;
            case 'recycle':
                return $this->recycleProduct();
                break;
            case 'user':
                return $this->userRelevantData();
                break;
            case 'store':
                return $this->storeData();
                break;
            case 'category':
                return $this->categoryData();
                break;
            case 'order':
                return $this->orderData();
                break;
            case 'kefu':
                return $this->kefuData();
                break;
            case 'wechat':
                return $this->wechatData();
                break;
            case 'attachment':
                return $this->attachmentData();
                break;
            case 'wechatuser':
                return $this->wechatuserData();
                break;
            case 'article':
                return $this->articledata();
                break;
            case 'system':
                return $this->systemdata();
                break;
            default:
                return $this->fail('参数有误');
        }
    }

    /**
     * 清除用户生成的临时附件
     * @param int $type
     * @throws \Exception
     */
    public function userTemp()
    {
        SystemAttachment::where('module_type', 2)->delete();
        return $this->success('清除数据成功!');
    }

    //清除回收站商品
    public function recycleProduct()
    {
        StoreProduct::where('is_del', 1)->delete();
        return $this->success('清除数据成功!');
    }

    //清除用户数据
    public function userRelevantData()
    {
        self::clearData('user_recharge', 1);
        self::clearData('user_address', 1);
        self::clearData('user_bill', 1);
        self::clearData('user_enter', 1);
        self::clearData('user_extract', 1);
        self::clearData('user_notice', 1);
        self::clearData('user_notice_see', 1);
        self::clearData('wechat_qrcode', 1);
        self::clearData('wechat_message', 1);
        self::clearData('store_visit', 1);
        self::clearData('store_coupon_user', 1);
        self::clearData('store_coupon_issue_user', 1);
        self::clearData('store_bargain_user', 1);
        self::clearData('store_bargain_user_help', 1);
        self::clearData('store_product_reply', 1);
        self::clearData('store_product_cate', 1);
        self::clearData('routine_qrcode', 1);
        self::clearData('routine_form_id', 1);
        self::clearData('user_sign', 1);
        self::clearData('user_task_finish', 1);
        self::clearData('user_level', 1);
        self::clearData('user_token', 1);
        self::clearData('user_group', 1);
        self::clearData('user_visit', 1);
        self::clearData('user_label', 1);
        self::clearData('user_label_relation', 1);
        $this->delDirAndFile('./public/uploads/store/comment');
        self::clearData('store_product_relation', 1);
        return $this->success('清除数据成功!');
    }

    //清除商城数据
    public function storeData()
    {
        self::clearData('store_coupon', 1);
        self::clearData('store_coupon_issue', 1);
        self::clearData('store_bargain', 1);
        self::clearData('store_combination', 1);
        self::clearData('store_product_attr', 1);
        self::clearData('store_product_cate', 1);
        self::clearData('store_product_attr_result', 1);
        self::clearData('store_product_attr_value', 1);
        self::clearData('store_product_description', 1);
        self::clearData('store_product_rule', 1);
        self::clearData('store_seckill', 1);
        self::clearData('store_product', 1);
        self::clearData('store_visit', 1);
        return $this->success('清除数据成功!');
    }

    //清除商品分类
    public function categoryData()
    {
        self::clearData('store_category', 1);
        return $this->success('清除数据成功!');
    }

    //清除订单数据
    public function orderData()
    {
        self::clearData('store_order', 1);
        self::clearData('store_order_cart_info', 1);
        self::clearData('store_order_status', 1);
        self::clearData('store_pink', 1);
        self::clearData('store_cart', 1);
        self::clearData('store_order_status', 1);
        return $this->success('清除数据成功!');
    }

    //清除客服数据
    public function kefuData()
    {
        self::clearData('store_service', 1);
        $this->delDirAndFile('./public/uploads/store/service');
        self::clearData('store_service_log', 1);
        return $this->success('清除数据成功!');
    }

    //清除微信管理数据
    public function wechatData()
    {
        self::clearData('wechat_media', 1);
        self::clearData('wechat_reply', 1);
        self::clearData('cache', 1);
        $this->delDirAndFile('./public/uploads/wechat');
        return $this->success('清除数据成功!');
    }

    //清除所有附件
    public function attachmentData()
    {
        self::clearData('system_attachment', 1);
        self::clearData('system_attachment_category', 1);
        $this->delDirAndFile('./public/uploads/');
        return $this->success('清除上传文件成功!');
    }

    //清除微信用户
    public function wechatuserData()
    {
        self::clearData('wechat_user', 1);
        self::clearData('user', 1);
        return $this->success('清除数据成功!');
    }

    //清除内容分类
    public function articledata()
    {
        self::clearData('article_category', 1);
        self::clearData('article', 1);
        self::clearData('article_content', 1);
        return $this->success('清除数据成功!');
    }

    //清除系统记录
    public function systemdata()
    {
        self::clearData('system_notice_admin', 1);
        self::clearData('system_log', 1);
        return $this->success('清除数据成功!');
    }

    //清除制定表数据
    public function clearData($table_name, $status)
    {
        $table_name = config('database.connections.' . config('database.default'))['prefix'] . $table_name;
        if ($status) {
            @db::execute('TRUNCATE TABLE ' . $table_name);
        } else {
            @db::execute('DELETE FROM' . $table_name);
        }

    }

    //递归删除文件
    function delDirAndFile($dirName, $subdir = true)
    {
        if ($handle = @opendir("$dirName")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dirName/$item"))
                        $this->delDirAndFile("$dirName/$item", false);
                    else
                        @unlink("$dirName/$item");
                }
            }
            closedir($handle);
            if (!$subdir) @rmdir($dirName);
        }
    }

    /**
     * 替换域名方法
     * @return mixed
     */
    public function replaceSiteUrl()
    {
        list($url) = UtilService::postMore([
            ['url','']
        ], $this->request, true);
        if (!$url)
            return $this->fail('请输入需要更换的域名');
        if (!verify_domain($url))
            return $this->fail('域名不合法');
        $siteUrl = sys_config('site_url');
        $siteUrlJosn = str_replace('http://', 'http:\\/\\/', $siteUrl);
        $valueJosn = str_replace('http://', 'http:\\/\\/', $url);
        $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');
        $sql = [
            "UPDATE `{$prefix}system_attachment` SET `att_dir` = replace(att_dir ,'{$siteUrl}','{$url}'),`satt_dir` = replace(satt_dir ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_product` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`slider_image` = replace(slider_image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_product_attr_value` SET `image` = replace(image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_seckill` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_combination` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_bargain` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_config` SET `value` = replace(value ,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}article_category` SET `image` = replace(`image` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article` SET `image_input` = replace(`image_input` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article_content` SET `content` = replace(`content` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_category` SET `pic` = replace(`pic` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_group_data` SET `value` = replace(value ,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}store_product_description` SET `description`= replace(description,'{$siteUrl}','{$url}')"
        ];
        try {
            foreach ($sql as $item) {
                db::execute($item);
            }
        } catch (\Throwable $e) {
            return $this->fail('替换失败，失败原因：' . $e->getMessage());
        }
        return $this->success('替换成功！');
    }
}