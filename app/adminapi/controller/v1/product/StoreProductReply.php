<?php

namespace app\adminapi\controller\v1\product;

use app\adminapi\controller\AuthController;
use crmeb\services\FormBuilder as Form;
use crmeb\services\UtilService;
use crmeb\traits\CurdControllerTrait;
use crmeb\services\UtilService as Util;
use app\models\store\StoreProductReply as ProductReplyModel;
use think\facade\Route as Url;

/**
 * 评论管理 控制器
 * Class StoreProductReply
 * @package app\admin\controller\store
 */
class StoreProductReply extends AuthController
{

    use CurdControllerTrait;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 20],
            ['is_reply', ''],
            ['store_name', ''],
            ['account', ''],
            ['data', ''],
            ['product_id', 0]
        ]);
        $list = ProductReplyModel::sysPage($where);
        return $this->success($list);
    }

    /**
     * 删除
     * @param $id
     * @return \think\response\Json|void
     */
    public function delete($id)
    {
        if (!$id) return $this->fail('数据不存在');
        $data['is_del'] = 1;
        if (!ProductReplyModel::edit($data, $id))
            return $this->fail(ProductReplyModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return $this->success('删除成功!');
    }

    /**
     * 回复评论
     */
    public function set_reply($id)
    {
        $data = Util::postMore([
            'content',
        ]);
        if ($data['content'] == '') return $this->fail('请输入回复内容');
        $save['merchant_reply_content'] = $data['content'];
        $save['merchant_reply_time'] = time();
        $save['is_reply'] = 1;
        $res = ProductReplyModel::edit($save, $id);
        if (!$res)
            return $this->fail(ProductReplyModel::getErrorInfo('回复失败,请稍候再试!'));
        else
            return $this->success('回复成功!');
    }

    /**
     * 添加虚拟评论表单
     * @return mixed
     */
    public function fictitious_reply()
    {
        list($product_id) = Util::postMore([
            ['product_id', 0],
        ], $this->request, true);
        if ($product_id == 0) {
            $field[] = Form::frameImageOne('image', '商品', Url::buildUrl('admin/store.StoreProduct/index', array('fodder' => 'image')))->icon('ios-add')->width('60%')->height('536px')->setProps(['srcKey' => 'image']);
        } else {
            $field[] = Form::hidden('product_id', $product_id);
        }
        $field[] = Form::input('nickname', '用户名称')->col(Form::col(24));
        $field[] = Form::input('comment', '评价文字')->type('textarea');
        $field[] = Form::number('product_score', '商品分数')->col(8)->value(5)->min(1)->max(5);
        $field[] = Form::number('service_score', '服务分数')->col(8)->value(5)->min(1)->max(5);
        $field[] = Form::frameImageOne('avatar', '用户头像', Url::buildUrl('admin/widget.images/index', array('fodder' => 'avatar')))->icon('ios-add')->width('50%')->height('396px');
        $field[] = Form::frameImages('pics', '评价图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'pics', 'type' => 'many')))->maxLength(5)->icon('ios-add')->width('50%')->height('396px')->spin(0)->setProps(['srcKey' => 'att_dir']);
        return $this->makePostForm('添加虚拟评论', $field, Url::buildUrl('/product/reply/save_fictitious_reply'), 'POST');
    }

    /**
     * 添加虚拟评论
     * @return mixed
     */
    public function save_fictitious_reply()
    {
        $data = Util::postMore([
            ['image', ''],
            ['nickname', ''],
            ['avatar', ''],
            ['comment', ''],
            ['pics', []],
            ['product_score', 0],
            ['service_score', 0],
            ['product_id', ''],
        ]);
        if ($data['product_id'] == '') $data['product_id'] = $data['image']['product_id'];
        $this->validate(['product_id' => $data['product_id'], 'nickname' => $data['nickname'], 'avatar' => $data['avatar'], 'comment' => $data['comment'], 'product_score' => $data['product_score'], 'service_score' => $data['service_score']], \app\adminapi\validates\product\StoreProductReplyValidate::class, 'save');
        $data['uid'] = 0;
        $data['oid'] = 0;
        $data['unique'] = uniqid();
        $data['reply_type'] = 'product';
        $data['add_time'] = time();
        $data['pics'] = json_encode(array_column($data['pics'], 'att_dir'));
        unset($data['image']);
        ProductReplyModel::create($data);
        return $this->success('添加成功!');
    }
}
