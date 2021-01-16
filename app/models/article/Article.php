<?php

namespace app\models\article;

use app\models\store\StoreProduct;
use app\models\system\SystemAdmin;
use think\facade\Db;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * TODO 文章Model
 * Class Article
 * @package app\models\article
 */
class Article extends BaseModel
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
    protected $name = 'article';

    use ModelTrait;

    public function profile()
    {
        return $this->hasOne(StoreProduct::class, 'id', 'product_id')->field('store_name,image,price,id,ot_price');
    }

    protected function getImageInputAttr($value)
    {
        return explode(',', $value) ?: [];
    }


    /**
     * TODO 获取一条新闻
     * @param int $id
     * @return array|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getArticleOne($id = 0)
    {
        if (!$id) return [];
        $list = self::where('status', 1)->where('hide', 0)->where('id', $id)->order('id desc')->find();
        if ($list) {
            $list->store_info = $list->profile ? $list->profile->toArray() : null;
            $list = $list->hidden(['hide', 'status', 'admin_id', 'mer_id'])->toArray();
            $list["content"] = Db::name('articleContent')->where('nid', $id)->value('content');
            $list["content"] = htmlspecialchars_decode($list["content"]);
            return $list;
        } else return [];
    }

    /**
     * TODO 获取某个分类底下的文章
     * @param $cid
     * @param $page
     * @param $limit
     * @param string $field
     * @return mixed
     */
    public static function cidByArticleList($cid, $page, $limit, $field = 'id,title,image_input,visit,add_time,synopsis,url')
    {
        $model = new self();
//        if ($cid) $model->where("`cid` LIKE '$cid,%' OR `cid` LIKE '%,$cid,%' OR `cid` LIKE '%,$cid' OR `cid`=$cid ");
//        if ((int)$cid) $model = $model->where("CONCAT(',',cid,',')  LIKE '%,$cid,%'");
        if ((int)$cid) $model = $model->where('cid', $cid);
        $model = $model->field($field);
        $model = $model->where('status', 1);
        $model = $model->where('hide', 0);
        $model = $model->order('sort DESC,add_time DESC');
        if ($page) $model = $model->page($page, $limit);
        return $model->select();
    }

    /**
     * TODO 获取热门文章
     * @param string $field
     * @return mixed]
     */
    public static function getArticleListHot($field = 'id,title,image_input,visit,add_time,synopsis,url')
    {
        $model = new self();
        $model = $model->field($field);
        $model = $model->where('status', 1);
        $model = $model->where('hide', 0);
        $model = $model->where('is_hot', 1);
        $model = $model->order('sort DESC,add_time DESC');
        return $model->select();
    }

    /**
     * TODO 获取轮播文章
     * @param string $field
     * @return mixed
     */
    public static function getArticleListBanner($field = 'id,title,image_input,visit,add_time,synopsis,url')
    {
        $model = new self();
        $model = $model->field($field);
        $model = $model->where('status', 1);
        $model = $model->where('hide', 0);
        $model = $model->where('is_banner', 1);
        $model = $model->order('sort DESC,add_time DESC');
        $model = $model->limit(sys_config('news_slides_limit') ?? 3);
        return $model->select();
    }

    //后台模型

    /**
     * 获取配置分类
     * @param array $where
     * @return array
     */
    public static function getAll($where = array())
    {
        $model = new self;
//        if($where['status'] !== '') $model = $model->where('status',$where['status']);
//        if($where['access'] !== '') $model = $model->where('access',$where['access']);
        if ($where['title'] !== '') $model = $model->where('title', 'LIKE', "%$where[title]%");
        if ($where['cid'] !== '')
            $model = $model->where('cid', 'in', $where['cid']);
        else
            if ($where['merchant'])
                $model = $model->where('mer_id', '>', 0);
            else
                $model = $model->where('mer_id', 0);
        $model = $model->where('status', 1)->where('hide', 0);
        $model = $model->order('sort desc,id desc');
//        return self::page($model,function($item){
//            if(!$item['mer_id']) $item['admin_name'] = '总后台管理员---》'.SystemAdmin::where('id',$item['admin_id'])->value('real_name');
//            else $item['admin_name'] = Merchant::where('id',$item['mer_id'])->value('mer_name').'---》'.MerchantAdmin::where('id',$item['admin_id'])->value('real_name');
//            $item['content'] = ArticleContent::where('nid',$item['id'])->value('content');
//            $item['catename'] = ArticleCategory::where('id',$item['cid'])->value('title');
//            $item['store_name'] = $item->profile->store_name ?? '';
//        },$where);
        $count = $model->count();
        $list = $model->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->each(function ($item) {
                if (!$item['mer_id']) $item['admin_name'] = '总后台管理员---》' . SystemAdmin::where('id', $item['admin_id'])->value('real_name');
                else $item['admin_name'] = Merchant::where('id', $item['mer_id'])->value('mer_name') . '---》' . MerchantAdmin::where('id', $item['admin_id'])->value('real_name');
                $item['content'] = ArticleContent::where('nid', $item['id'])->value('content');
                $item['catename'] = ArticleCategory::where('id', $item['cid'])->value('title');
                $item['store_name'] = $item->profile->store_name ?? '';
            });

        return compact('count', 'list');
    }

    /**
     * 获取指定字段的值
     * @return array
     */
    public static function getNews()
    {
        return self::where('status', 1)->where('hide', 0)->order('id desc')->column('title', 'id');
    }

    /**
     * 新增或修改文章内容
     * @param $id
     * @param $content
     * @return bool|int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function setContent($id, $content)
    {
        $count = ArticleContent::where('nid', $id)->count();
        $data['nid'] = $id;
        $data['content'] = $content;
        if ($count) {
            $contentSql = ArticleContent::where('nid', $id)->value('content');
            if ($contentSql == $content) $res = true;
            else $res = ArticleContent::where('nid', $id)->update(['content' => $content]);
            if ($res !== false) $res = true;
        } else {
            $res = ArticleContent::insert($data);
        }
        return $res;
    }

    /**
     * 给表中的字符串类型追加值
     * 删除所有有当前分类的id之后重新添加
     * @param $cid
     * @param $id
     * @return bool
     */
    public static function saveBatchCid($cid, $id)
    {
        $res_all = self::where('cid', 'LIKE', "%$cid%")->select();//获取所有有当前分类的图文
        foreach ($res_all as $k => $v) {
            $cid_arr = explode(',', $v['cid']);
            if (in_array($cid, $cid_arr)) {
                $key = array_search($cid, $cid_arr);
                array_splice($cid_arr, $key, 1);
            }
            if (empty($cid_arr)) {
                $data['cid'] = 0;
                self::edit($data, $v['id']);
            } else {
                $data['cid'] = implode(',', $cid_arr);
                self::edit($data, $v['id']);
            }
        }
        $res = self::where('id', 'IN', $id)->select();
        foreach ($res as $k => $v) {
            if (!in_array($cid, explode(',', $v['cid']))) {
                if (!$v['cid']) {
                    $data['cid'] = $cid;
                } else {
                    $data['cid'] = $v['cid'] . ',' . $cid;
                }
                self::edit($data, $v['id']);
            }
        }
        return true;
    }
}
