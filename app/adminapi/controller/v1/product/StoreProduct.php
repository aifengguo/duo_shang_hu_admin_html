<?php

namespace app\adminapi\controller\v1\product;

use app\adminapi\controller\AuthController;
use app\models\store\{
    StoreBargain,
    StoreCombination,
    StoreProductAttrValue,
    StoreProductCate,
    StoreProductAttr,
    StoreProductAttrResult,
    StoreProductRelation,
    StoreDescription,
    StoreProduct as ProductModel,
    StoreCategory,
    StoreSeckill
};
use app\models\system\ShippingTemplates;
use crmeb\services\UploadService;
use crmeb\traits\CurdControllerTrait;
use crmeb\services\UtilService as Util;

/**
 * 商品管理
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class StoreProduct extends AuthController
{

    use CurdControllerTrait;

    protected $bindModel = ProductModel::class;

    /**
     * 显示资源列表头部
     *
     * @return \think\Response
     */
    public function type_header()
    {
        //出售中商品
        $onsale = ProductModel::where('is_del', 0)->where('is_show', 1)->count();
        //待上架商品
        $forsale = ProductModel::where('is_del', 0)->where('is_show', 0)->count();
        //仓库中商品
        $warehouse = ProductModel::where('is_del', 0)->count();
        //已经售馨产品
        $outofstock = ProductModel::getModelObject(['type' => 4])->count();
        //警戒库存
        $policeforce = ProductModel::getModelObject(['type' => 5])->count();
        //回收站
        $recycle = ProductModel::where('is_del', 1)->count();
        $list = [
            ['type' => 1, 'name' => '出售中商品', 'count' => $onsale],
            ['type' => 2, 'name' => '仓库中商品', 'count' => $forsale],
//            ['type' => 3, 'name' => '仓库中商品', 'count' => $warehouse],
            ['type' => 4, 'name' => '已经售馨商品', 'count' => $outofstock],
            ['type' => 5, 'name' => '警戒库存', 'count' => $policeforce],
            ['type' => 6, 'name' => '商品回收站', 'count' => $recycle],
        ];
        return $this->success(compact('list'));
    }

    /**
     * 显示资源列表
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['store_name', ''],
            ['cate_id', ''],
            ['excel', 0],
            ['type', 1]
        ]);
        return $this->success(ProductModel::ProductList($where));
    }

    /**
     * 修改状态
     * @param string $is_show
     * @param string $id
     * @return mixed
     */
    public function set_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && $this->fail('缺少参数');
        if (ProductModel::be(['id' => $id, 'is_del' => 1])) return $this->fail('商品已删除，不能上架');
        $res = ProductModel::where(['id' => $id])->update(['is_show' => (int)$is_show]);
        if ($res) {
            return $this->success($is_show == 1 ? '上架成功' : '下架成功');
        } else {
            return $this->fail($is_show == 1 ? '上架失败' : '下架失败');
        }
    }

    /**
     * 快速编辑
     * @param string $field
     * @param string $id
     * @param string $value
     * @return mixed
     */
    public function set_product($id = '')
    {
        $data = Util::postMore([
            ['field', ''],
            ['value', '']
        ]);
        $data['field'] == '' || $id == '' || $data['value'] == '' && $this->fail('缺少参数');
        if (ProductModel::where(['id' => $id])->update([$data['field'] => $data['value']]))
            return $this->success('保存成功');
        else
            return $this->fail('保存失败');
    }

    /**
     * 设置批量商品上架
     * @return mixed
     */
    public function product_show()
    {
        $post = Util::postMore([
            ['ids', []]
        ]);
        if (empty($post['ids'])) {
            return $this->fail('请选择需要上架的商品');
        } else {
            $res = ProductModel::where('id', 'in', $post['ids'])->update(['is_show' => 1]);
            if ($res !== false)
                return $this->success('上架成功');
            else
                return $this->fail('上架失败');
        }
    }

    /**
     * 获取规则属性模板
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_rule()
    {
        return $this->success(\app\models\store\StoreProductRule::field(['rule_name', 'rule_value'])->select()->each(function ($item) {
            $item['rule_value'] = json_decode($item['rule_value'], true);
        })->toArray());
    }

    /**
     * 获取商品详细信息
     * @param int $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_product_info($id = 0)
    {
        $list = StoreCategory::getTierList(null, 1);
        $menus = [];
        foreach ($list as $menu) {
            $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name'], 'disabled' => $menu['pid'] == 0 ? 0 : 1];//,'disabled'=>$menu['pid']== 0];
        }
        $data['tempList'] = ShippingTemplates::order('sort', 'desc')->field(['id', 'name'])->select()->toArray();
        $data['cateList'] = $menus;
        $data['productInfo'] = [];
        if ($id) {
            $productInfo = ProductModel::get($id);
            if (!$productInfo) {
                return $this->fail('修改的商品不存在');
            }
            $productInfo['cate_id'] = explode(',', $productInfo['cate_id']);
            $productInfo['give_integral'] = floatval($productInfo['give_integral']);
            $productInfo['description'] = StoreDescription::getDescription($id);
            $productInfo['slider_image'] = is_string($productInfo['slider_image']) ? json_decode($productInfo['slider_image'], true) : [];
            if ($productInfo['spec_type'] == 1) {
                $result = StoreProductAttrResult::getResult($id);
                foreach ($result['value'] as $k => $v) {
                    $num = 1;
                    foreach ($v['detail'] as $dv) {
                        $result['value'][$k]['value' . $num] = $dv;
                        $num++;
                    }
                }
                $productInfo['items'] = $result['attr'];
                $productInfo['attrs'] = $result['value'];
                $productInfo['attr'] = ['pic' => '', 'price' => 0, 'cost' => 0, 'ot_price' => 0, 'stock' => 0, 'bar_code' => '', 'weight' => 0, 'volume' => 0, 'brokerage' => 0, 'brokerage_two' => 0];
            } else {
                $result = StoreProductAttrValue::where('product_id', $id)->where('type', 0)->find();
                $productInfo['items'] = [];
                $productInfo['attrs'] = [];
                $productInfo['attr'] = [
                    'pic' => $result['image'] ?? '',
                    'price' => $result['price'] ? floatval($result['price']) : 0,
                    'cost' => $result['cost'] ? floatval($result['cost']) : 0,
                    'ot_price' => $result['ot_price'] ? floatval($result['ot_price']) : 0,
                    'stock' => $result['stock'] ? floatval($result['stock']) : 0,
                    'bar_code' => $result['bar_code'] ?? '',
                    'weight' => $result['weight'] ? floatval($result['weight']) : 0,
                    'volume' => $result['volume'] ? floatval($result['volume']) : 0,
                    'brokerage' => $result['brokerage'] ? floatval($result['brokerage']) : 0,
                    'brokerage_two' => $result['brokerage_two'] ? floatval($result['brokerage_two']) : 0
                ];
            }
            if ($productInfo['activity']) {
                $activity = explode(',', $productInfo['activity']);
                foreach ($activity as $k => $v) {
                    if ($v == 1) {
                        $activity[$k] = '秒杀';
                    } elseif ($v == 2) {
                        $activity[$k] = '砍价';
                    } elseif ($v == 3) {
                        $activity[$k] = '拼团';
                    }
                }
                $productInfo['activity'] = $activity;
            } else {
                $productInfo['activity'] = ['秒杀', '砍价', '拼团'];
            }
            $data['productInfo'] = $productInfo;
        }
        return $this->success($data);
    }

    /**
     * 保存新建或编辑
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function save($id)
    {
        $data = Util::postMore([
            ['cate_id', []],
            'store_name',
            'store_info',
            'keyword',
            ['unit_name', '件'],
            ['image', []],
            ['slider_image', []],
            ['postage', 0],
            ['is_sub', 0],
            ['sort', 0],
            ['sales', 0],
            ['ficti', 100],
            ['give_integral', 0],
            ['is_show', 0],
            ['temp_id', 0],
            ['is_hot', 0],
            ['is_benefit', 0],
            ['is_best', 0],
            ['is_new', 0],
            ['mer_use', 0],
            ['is_postage', 0],
            ['is_good', 0],
            ['description', ''],
            ['spec_type', 0],
            ['video_link', ''],
            ['items', []],
            ['attrs', []],
            ['activity', []]
        ]);
        foreach ($data['activity'] as $k => $v) {
            if ($v == '秒杀') {
                $data['activity'][$k] = 1;
            } elseif ($v == '砍价') {
                $data['activity'][$k] = 2;
            } else {
                $data['activity'][$k] = 3;
            }
        }
        $data['activity'] = implode(',', $data['activity']);
        $detail = $data['attrs'];
        $data['price'] = min(array_column($detail, 'price'));
        $data['ot_price'] = min(array_column($detail, 'ot_price'));
        $data['cost'] = min(array_column($detail, 'cost'));
        $attr = $data['items'];
        unset($data['items'], $data['video'], $data['attrs']);
        if (count($data['cate_id']) < 1) return $this->fail('请选择商品分类');
        $cate_id = $data['cate_id'];
        $data['cate_id'] = implode(',', $data['cate_id']);
        if (!$data['store_name']) return $this->fail('请输入商品名称');
        if (count($data['image']) < 1) return $this->fail('请上传商品图片');
        if (count($data['slider_image']) < 1) return $this->fail('请上传商品轮播图');
        $data['image'] = $data['image'][0];
        $data['slider_image'] = json_encode($data['slider_image']);
        $data['stock'] = array_sum(array_column($detail, 'stock'));
        ProductModel::beginTrans();
        foreach ($detail as &$item) {
            if (($item['brokerage'] + $item['brokerage_two']) > $item['price']) {
                return $this->fail('一二级返佣相加不能大于商品售价');
            }
        }
        if ($id) {
            unset($data['sales']);
            ProductModel::edit($data, $id);
            $description = $data['description'];
            unset($data['description']);
            StoreDescription::saveDescription($description, $id);
            StoreProductCate::where('product_id', $id)->delete();
            $cateData = [];
            foreach ($cate_id as $cid) {
                $cateData[] = ['product_id' => $id, 'cate_id' => $cid, 'add_time' => time()];
            }
            StoreProductCate::insertAll($cateData);
            if ($data['spec_type'] == 0) {
                $attr = [
                    [
                        'value' => '规格',
                        'detailValue' => '',
                        'attrHidden' => '',
                        'detail' => ['默认']
                    ]
                ];
                $detail[0]['value1'] = '规格';
                $detail[0]['detail'] = ['规格' => '默认'];
            }

            $attr_res = StoreProductAttr::createProductAttr($attr, $detail, $id);
            if ($attr_res) {
                ProductModel::commitTrans();
                return $this->success('修改成功!');
            } else {
                ProductModel::rollbackTrans();
                return $this->fail(StoreProductAttr::getErrorInfo());
            }
        } else {
            $data['add_time'] = time();
            $data['code_path'] = '';
            $res = ProductModel::create($data);
            $description = $data['description'];
            StoreDescription::saveDescription($description, $res['id']);
            $cateData = [];
            foreach ($cate_id as $cid) {
                $cateData[] = ['product_id' => $res['id'], 'cate_id' => $cid, 'add_time' => time()];
            }
            StoreProductCate::insertAll($cateData);
            if ($data['spec_type'] == 0) {
                $attr = [
                    [
                        'value' => '规格',
                        'detailValue' => '',
                        'attrHidden' => '',
                        'detail' => ['默认']
                    ]
                ];
                $detail[0]['value1'] = '规格';
                $detail[0]['detail'] = ['规格' => '默认'];
            }
            $attr_res = StoreProductAttr::createProductAttr($attr, $detail, $res['id']);
            if ($attr_res) {
                ProductModel::commitTrans();
                return $this->success('添加商品成功!');
            } else {
                ProductModel::rollbackTrans();
                return $this->fail(StoreProductAttr::getErrorInfo());
            }
        }
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return $this->fail('数据不存在');
        if (!ProductModel::be(['id' => $id])) return $this->fail('商品数据不存在');
        if (ProductModel::be(['id' => $id, 'is_del' => 1])) {
            $data['is_del'] = 0;
            if (!ProductModel::edit($data, $id))
                return $this->fail(ProductModel::getErrorInfo('恢复失败,请稍候再试!'));
            else
                return $this->success('成功恢复商品!');
        } else {
            $data['is_del'] = 1;
            $data['is_show'] = 0;
            if (!ProductModel::edit($data, $id))
                return $this->fail(ProductModel::getErrorInfo('删除失败,请稍候再试!'));
            else
                return $this->success('成功移到回收站!');
        }
    }

    /**
     * 生成属性
     * @param int $id
     */
    public function is_format_attr($id)
    {
        $data = Util::postMore([
            ['attrs', []],
            ['items', []]
        ]);
        $attr = $data['attrs'];
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
            sort($item['detail'], SORT_STRING);
            $suk = implode(',', $item['detail']);
            if ($id) {
                $sukValue = StoreProductAttrValue::where('product_id', $id)->where('type', 0)->where('suk', $suk)->column('bar_code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two', 'suk');
                if (!count($sukValue)) {
                    $sukValue[$suk]['pic'] = '';
                    $sukValue[$suk]['price'] = 0;
                    $sukValue[$suk]['cost'] = 0;
                    $sukValue[$suk]['ot_price'] = 0;
                    $sukValue[$suk]['stock'] = 0;
                    $sukValue[$suk]['bar_code'] = '';
                    $sukValue[$suk]['weight'] = 0;
                    $sukValue[$suk]['volume'] = 0;
                    $sukValue[$suk]['brokerage'] = 0;
                    $sukValue[$suk]['brokerage_two'] = 0;
                }
            } else {
                $sukValue[$suk]['pic'] = '';
                $sukValue[$suk]['price'] = 0;
                $sukValue[$suk]['cost'] = 0;
                $sukValue[$suk]['ot_price'] = 0;
                $sukValue[$suk]['stock'] = 0;
                $sukValue[$suk]['bar_code'] = '';
                $sukValue[$suk]['weight'] = 0;
                $sukValue[$suk]['volume'] = 0;
                $sukValue[$suk]['brokerage'] = 0;
                $sukValue[$suk]['brokerage_two'] = 0;
            }
            foreach (array_keys($detail) as $k => $title) {
                $header[$k]['title'] = $title;
                $header[$k]['align'] = 'center';
                $header[$k]['minWidth'] = 120;
            }
            foreach (array_values($detail) as $k => $v) {
                $valueNew[$count]['value' . ($k + 1)] = $v;
                $header[$k]['key'] = 'value' . ($k + 1);
            }
            $valueNew[$count]['detail'] = $detail;
            $valueNew[$count]['pic'] = $sukValue[$suk]['pic'] ?? '';
            $valueNew[$count]['price'] = $sukValue[$suk]['price'] ? floatval($sukValue[$suk]['price']) : 0;
            $valueNew[$count]['cost'] = $sukValue[$suk]['cost'] ? floatval($sukValue[$suk]['cost']) : 0;
            $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
            $valueNew[$count]['stock'] = $sukValue[$suk]['stock'] ? intval($sukValue[$suk]['stock']) : 0;
            $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
            $valueNew[$count]['weight'] = $sukValue[$suk]['weight'] ? floatval($sukValue[$suk]['weight']) : 0;
            $valueNew[$count]['volume'] = $sukValue[$suk]['volume'] ? floatval($sukValue[$suk]['volume']) : 0;
            $valueNew[$count]['brokerage'] = $sukValue[$suk]['brokerage'] ? floatval($sukValue[$suk]['brokerage']) : 0;
            $valueNew[$count]['brokerage_two'] = $sukValue[$suk]['brokerage_two'] ? floatval($sukValue[$suk]['brokerage_two']) : 0;
            $count++;
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '售价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 95];
        $header[] = ['title' => '成本价', 'slot' => 'cost', 'align' => 'center', 'minWidth' => 95];
        $header[] = ['title' => '原价', 'slot' => 'ot_price', 'align' => 'center', 'minWidth' => 95];
        $header[] = ['title' => '库存', 'slot' => 'stock', 'align' => 'center', 'minWidth' => 95];
        $header[] = ['title' => '商品编号', 'slot' => 'bar_code', 'align' => 'center', 'minWidth' => 120];
        $header[] = ['title' => '重量(KG)', 'slot' => 'weight', 'align' => 'center', 'minWidth' => 95];
        $header[] = ['title' => '体积(m³)', 'slot' => 'volume', 'align' => 'center', 'minWidth' => 95];
        $header[] = ['title' => '操作', 'slot' => 'action', 'align' => 'center', 'minWidth' => 70];
        $info = ['attr' => $attr, 'value' => $valueNew, 'header' => $header];
        return $this->success(compact('info'));
    }

    public function set_attr($id)
    {
        if (!$id) return $this->fail('商品不存在!');
        list($attr, $detail) = Util::postMore([
            ['items', []],
            ['attrs', []]
        ], null, true);
        $res = StoreProductAttr::createProductAttr($attr, $detail, $id);
        if ($res)
            return $this->success('编辑属性成功!');
        else
            return $this->fail(StoreProductAttr::getErrorInfo());
    }

    public function clear_attr($id)
    {
        if (!$id) return $this->fail('商品不存在!');
        if (false !== StoreProductAttr::clearProductAttr($id) && false !== StoreProductAttrResult::clearResult($id))
            return $this->success('清空商品属性成功!');
        else
            return $this->fail(StoreProductAttr::getErrorInfo('清空商品属性失败!'));
    }

    /**
     * 点赞
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function collect($id)
    {
        if (!$id) return $this->fail('数据不存在');
        $product = ProductModel::get($id);
        if (!$product) return $this->fail('数据不存在!');
        $this->assign(StoreProductRelation::getCollect($id));
        return $this->fetch();
    }

    /**
     * 收藏
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function like($id)
    {
        if (!$id) return $this->fail('数据不存在');
        $product = ProductModel::get($id);
        if (!$product) return $this->fail('数据不存在!');
        $this->assign(StoreProductRelation::getLike($id));
        return $this->fetch();
    }

    /**
     * 修改商品价格
     */
    public function edit_product_price()
    {
        $data = Util::postMore([
            ['id', 0],
            ['price', 0],
        ]);
        if (!$data['id']) return $this->fail('参数错误');
        $res = ProductModel::edit(['price' => $data['price']], $data['id']);
        if ($res) return $this->success('修改成功');
        else return $this->fail('修改失败');
    }

    /**
     * 修改商品库存
     *
     */
    public function edit_product_stock()
    {
        $data = Util::postMore([
            ['id', 0],
            ['stock', 0],
        ]);
        if (!$data['id']) return $this->fail('参数错误');
        $res = ProductModel::edit(['stock' => $data['stock']], $data['id']);
        if ($res) return $this->success('修改成功');
        else return $this->fail('修改失败');
    }

    /**
     * 获取选择的商品列表
     * @return mixed
     */
    public function search_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['cate_id', 0],
            ['store_name', '']
        ]);
        $list = ProductModel::getList($where);
        return $this->success($list);
    }

    /**
     * 获取某个商品规格
     * @return mixed
     */
    public function get_attrs()
    {
        list($id, $type) = Util::getMore([
            ['id', 0],
            ['type', 0],
        ], $this->request, true);
        return $this->success(ProductModel::getAttrs($id, $type));
    }

    /**
     * 商品添加修改获取运费模板
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_template()
    {
        return $this->success(ShippingTemplates::order('sort desc,id desc')->field(['id', 'name'])->select()->toArray());
    }

    public function getTempKeys()
    {
        $upload = UploadService::init();
        return $this->success($upload->getTempKeys());
    }

    /**
     * 检测商品是否开活动
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function check_activity($id)
    {
        if ($id != 0) {
            $res1 = StoreSeckill::where('product_id', $id)->where('is_del', 0)->find();
            $res2 = StoreBargain::where('product_id', $id)->where('is_del', 0)->find();
            $res3 = StoreCombination::where('product_id', $id)->where('is_del', 0)->find();
            if ($res1 || $res2 || $res3) {
                return $this->fail('该商品有活动开启，无法删除属性');
            } else {
                return $this->success('删除成功');
            }
        } else {
            return $this->fail();
        }
    }
}
