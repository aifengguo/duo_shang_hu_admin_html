<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */

namespace app\models\wechat;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\models\article\Article as ArticleModel;

/**
 * 图文消息 model
 * Class WechatNewsCategory
 * @package app\models\wechat
 */
class WechatNewsCategory extends BaseModel
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
    protected $name = 'wechat_news_category';

    use ModelTrait;

    /**
     * 获取配置分类
     * @param array $where
     * @return array
     */
    public static function getAll($where = array()){
        $model = new self;
//        if($where['status'] !== '') $model = $model->where('status',$where['status']);
//        if($where['access'] !== '') $model = $model->where('access',$where['access']);
        if($where['cate_name'] !== '') $model = $model->where('cate_name','LIKE',"%$where[cate_name]%");
        $model = $model->where('status',1);
        $model = $model->order('add_time desc');
        $count = $model->count();
        $list=$model
            ->page((int)$where['page'],(int)$where['limit'])
            ->select()
            ->each(function ($item){
            $new = ArticleModel::where('id','in',$item['new_id'])->where('hide',0)->select();
            if($new) $new = $new->toArray();
            $item['new'] = $new;
        });
        return compact('count','list');
    }

    /**
     * 获取一条图文
     * @param int $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function getWechatNewsItem($id = 0){
        if(!$id) return [];
        $list = self::where('id',$id)->where('status',1)->field('cate_name as title,new_id')->find();
        if($list){
            $list = $list->toArray();
            $new = ArticleModel::where('id','in',$list['new_id'])->where('hide',0)->select();
            if($new) $new = $new->toArray();
            $list['new'] = $new;
        }
        return $list;

    }

    /**
     * 发送客服消息选择文章列表
     * @param $where
     * @return array
     */
    public static function list($where){
        $list=self::where('cate_name','LIKE',"%$where[cate_name]%")
            ->where('status',1)
            ->page((int)$where['page'],(int)$where['limit'])
            ->select()
            ->each(function ($item){
            $item['new']=ArticleModel::where('id','in',$item['new_id'])->where('hide',0)->select();
        });
        return ['list'=>$list];
    }
}