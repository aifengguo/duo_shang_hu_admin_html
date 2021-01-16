<?php

namespace app\adminapi\controller\v1\cms;

use app\adminapi\controller\AuthController;
use crmeb\services\{UtilService as Util};
use app\models\article\{ArticleCategory as ArticleCategoryModel, Article as ArticleModel};
use think\Request;

/**
 * 文章管理
 * Class Article
 * @package app\adminapi\controller\v1\cms
 */
class Article extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['title', ''],
            ['pid', 0],
            ['page', 1],
            ['limit', 10]
        ], $this->request);
        $where['cid'] = '';
        $pid = $where['pid'];
        $where['merchant'] = 0;//区分是管理员添加的图文显示  0 还是 商户添加的图文显示  1
        $cateList = ArticleCategoryModel::getArticleCategoryList();
        //获取分类列表
        if (count($cateList)) {
            $tree = sort_list_tier($cateList);
            if ($pid) {
                $pids = Util::getChildrenPid($tree, $pid);
                $where['cid'] = ltrim($pid . $pids);
            }
        }
        $list = ArticleModel::getAll($where);
        return $this->success($list);
    }

    /**
     *
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            ['id', 0],
            ['cid',''],
            'title',
            'author',
            'image_input',
            'content',
            'synopsis',
            'share_title',
            'share_synopsis',
            ['visit', 0],
            ['sort', 0],
            'url',
            ['is_banner', 0],
            ['is_hot', 0],
            ['status', 1],]);
        if (!$data['title']) return $this->fail('缺少参数');
        $content = $data['content'];
        unset($data['content']);
        if ($data['id']) {
            $id = $data['id'];
            unset($data['id']);
            $res = false;
            ArticleModel::beginTrans();
            $res1 = ArticleModel::edit($data, $id, 'id');
            $res2 = ArticleModel::setContent($id, $content);
            if ($res1 && $res2) {
                $res = true;
            }
            ArticleModel::checkTrans($res);
            if ($res)
                return $this->success('修改成功!', ['id' => $id]);
            else
                return $this->fail('修改失败，您并没有修改什么!', ['id' => $id]);
        } else {
            $data['add_time'] = time();
            $data['admin_id'] = $this->adminId;
//            $data['admin_id'] = 1;
            $res = false;
            ArticleModel::beginTrans();
            $res1 = ArticleModel::create($data);
            $res2 = false;
            if ($res1)
                $res2 = ArticleModel::setContent($res1->id, $content);
            if ($res1 && $res2) {
                $res = true;
            }
            ArticleModel::checkTrans($res);
            if ($res)
                return $this->success('添加成功!', ['id' => $res1->id]);
            else
                return $this->success('添加失败!', ['id' => $res1->id]);
        }
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        if ($id) {
            $info = ArticleModel::where('n.id', $id)->alias('n')->field('n.*,c.content')->join('ArticleContent c', 'c.nid=n.id', 'left')->find();
            if (!$info) return $this->fail('数据不存在!');
            $info['cid'] = intval($info['cid']);
        }
        return $this->success(compact('info'));
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $res = ArticleModel::del($id);
        if (!$res)
            return $this->fail('删除失败,请稍候再试!');
        else
            return $this->success('删除成功!');
    }

    //TODO 暂留

    /**
     * 分类显示列表
     * $param int $id 分类id
     * @return mixed
     */
    public function merchantIndex($id = 0)
    {
        $where = Util::getMore([
            ['title', '']
        ], $this->request);
        if ($id) $where['cid'] = $id;
        $where['merchant'] = 1;//区分是管理员添加的图文显示  0 还是 商户添加的图文显示  1
        $list = ArticleModel::getAll($where);
        return $this->success(compact('list'));
    }

    /**
     * 关联商品
     * @param int $id 文章id
     */
    public function relation($id = 0)
    {
        if (!$id) return $this->fail('缺少参数');
        list($product_id) = Util::postMore([
            ['product_id', 0]
        ], $this->request, true);
        if (ArticleModel::edit(['product_id' => $product_id], ['id' => $id]))
            return $this->success('保存成功');
        else
            return $this->fail('保存失败');
    }

    /**
     * 取消绑定的商品id
     * @param int $id
     */
    public function unrelation($id = 0)
    {
        if (!$id) return $this->fail('缺少参数');
        if (ArticleModel::edit(['product_id' => 0], $id))
            return $this->success('取消关联成功！');
        else
            return $this->fail('取消失败');
    }
}
