<?php

namespace app\adminapi\controller\v1\application\wechat;

use app\adminapi\controller\AuthController;
use app\models\article\Article as ArticleModel;
use app\models\article\ArticleContent;
use crmeb\services\{UtilService as Util, WechatService};
use app\models\wechat\{WechatReply, WechatNewsCategory as WechatNewsCategoryModel, WechatUser};

/**
 * 图文信息
 * Class WechatNewsCategory
 * @package app\admin\controller\wechat
 *
 */
class WechatNewsCategory extends AuthController
{
    /**
     * 图文消息列表
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['cate_name', '']
        ], $this->request);
        $list = WechatNewsCategoryModel::getAll($where);
        return $this->success($list);
    }


    /**
     * 图文详情
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function read($id)
    {
        $info = WechatNewsCategoryModel::where('id', $id)->find();
        $new = ArticleModel::alias('a')->where('id', 'in', $info['new_id'])
            ->join('article_content c', 'c.nid=a.id')
            ->field('a.*,c.content')
            ->where('hide', 0)
            ->select();
        if ($new) $new = $new->toArray();
        $info['new'] = $new;
        return $this->success(compact('info'));
    }

    /**
     * 删除图文
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!WechatNewsCategoryModel::del($id))
            return $this->fail(WechatNewsCategoryModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return $this->success('删除成功!');
    }

    /**
     * 新增或编辑保存
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function save()
    {
        $data = Util::postMore([
            ['list', []],
            ['id', 0]
        ]);
        try {
            $id = [];
            $countList = count($data['list']);
            if (!$countList) return $this->fail('请添加图文');
            ArticleModel::beginTrans();
            foreach ($data['list'] as $k => $v) {
                if ($v['title'] == '') return $this->fail('标题不能为空');
                if ($v['author'] == '') return $this->fail('作者不能为空');
                if ($v['content'] == '') return $this->fail('正文不能为空');
                if ($v['synopsis'] == '') return $this->fail('摘要不能为空');
                $v['status'] = 1;
                $v['add_time'] = time();
                if ($v['id']) {
                    $idC = $v['id'];
                    unset($v['id']);
                    ArticleModel::edit($v, $idC);
                    ArticleContent::where('nid', $idC)->update(['content' => $v['content']]);
                    $data['list'][$k]['id'] = $idC;
                    $id[] = $idC;
                } else {
                    unset($v['id']);
                    $res = ArticleModel::create($v)->toArray();
                    $id[] = $res['id'];
                    $data['list'][$k]['id'] = $res['id'];
                    ArticleContent::create(['content' => $v['content'], 'nid' => $res['id']]);
                }
            }
            $countId = count($id);
            if ($countId != $countList) {
                ArticleModel::checkTrans(false);
                if ($data['id']) return $this->fail('修改失败');
                else return $this->fail('添加失败');
            } else {
                ArticleModel::checkTrans(true);
                $newsCategory['cate_name'] = $data['list'][0]['title'];
                $newsCategory['new_id'] = implode(',', $id);
                $newsCategory['sort'] = 0;
                $newsCategory['add_time'] = time();
                $newsCategory['status'] = 1;
                if ($data['id']) {
                    WechatNewsCategoryModel::edit($newsCategory, $data['id']);
                    return $this->success('修改成功');
                } else {
                    WechatNewsCategoryModel::create($newsCategory);
                    return $this->success('添加成功');
                }
            }
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 发送消息
     * @param int $id
     * @param string $wechat
     * $wechat  不为空  发消息  /  空 群发消息
     */
    public function push()
    {
        $data = Util::postMore([
            ['id', 0],
            ['user_ids', '']
        ]);
        if (!$data['id']) return $this->fail('参数错误');
        $list = WechatNewsCategoryModel::getWechatNewsItem($data['id']);
        $wechatNews = [];
        if ($list) {
            if ($list['new'] && is_array($list['new'])) {
                foreach ($list['new'] as $kk => $vv) {
                    $wechatNews[$kk]['title'] = $vv['title'];
                    $wechatNews[$kk]['image'] = $vv['image_input'];
                    $wechatNews[$kk]['date'] = date('m月d日', time());
                    $wechatNews[$kk]['description'] = $vv['synopsis'];
                    $wechatNews[$kk]['id'] = $vv['id'];
                }
            }
        }
        if ($data['user_ids'] != '') {//客服消息
            $wechatNews = WechatReply::tidyNews($wechatNews);
            $message = WechatService::newsMessage($wechatNews);
            $errorLog = [];//发送失败的用户
            $user = WechatUser::where('uid', 'IN', $data['user_ids'])->column('nickname,subscribe,openid', 'uid');
            if ($user) {
                foreach ($user as $v) {
                    if ($v['subscribe'] && $v['openid']) {
                        try {
                            WechatService::staffService()->message($message)->to($v['openid'])->send();
                        } catch (\Exception $e) {
                            $errorLog[] = $v['nickname'] . '发送失败';
                        }
                    } else {
                        $errorLog[] = $v['nickname'] . '没有关注发送失败(不是微信公众号用户)';
                    }
                }
            } else return $this->fail('发送失败，参数不正确');
            if (!count($errorLog)) return $this->success('全部发送成功');
            else return $this->success(implode(',', $errorLog) . '，剩余的发送成功');
        } else {//群发消息
//        if($list){
//               if($list['new'] && is_array($list['new'])){
//                   foreach ($list['new'] as $kk=>$vv){
//                       $wechatNews[$kk]['title'] = $vv['title'];
//                       $wechatNews[$kk]['thumb_media_id'] = $vv['image_input'];
//                       $wechatNews[$kk]['author'] = $vv['author'];
//                       $wechatNews[$kk]['digest'] = $vv['synopsis'];
//                       $wechatNews[$kk]['show_cover_pic'] = 1;
//                       $wechatNews[$kk]['content'] = Db::name('articleContent')->where('nid',$vv["id"])->value('content');
//                       $wechatNews[$kk]['content_source_url'] = $vv['url'];
//                   }
//            }
//        }
            //6sFx6PzPF2v_Lv4FGOMzz-oQunU2Z3wrOWb-7zS508E
            //6sFx6PzPF2v_Lv4FGOMzz7SUUuamgWwlqdVfhQ5ALT4
//        foreach ($wechatNews as $k=>$v){
//            $material = WechatService::materialService()->uploadImage(UtilService::urlToPath($v['thumb_media_id']));
//            dump($material);
//            $wechatNews[$k]['thumb_media_id'] = $material->media_id;
//        }
//        $mediaIdNews = WechatService::uploadNews($wechatNews);
//        $res = WechatService::sendNewsMessage($mediaIdNews->media_id);
//        if($res->errcode) return Json::fail($res->errmsg);
//        else return Json::successful('推送成功');
//        dump($mediaIdNews);
//        dump($res);
        }
    }

    /**
     * 发送消息图文列表
     * @return mixed
     */
    public function send_news()
    {
//        if($id == '') return $this->fail('参数错误');
        $where = Util::getMore([
            ['cate_name', ''],
            ['page', 1],
            ['limit', 10]
        ], $this->request);
        return $this->success(WechatNewsCategoryModel::list($where));
    }

}