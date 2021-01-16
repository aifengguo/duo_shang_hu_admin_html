<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\models\user;

use app\models\user\UserNoticeSee;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * TODO 用户通知Model
 * Class UserNotice
 * @package app\models\user
 */
class UserNotice extends BaseModel
{
    use ModelTrait;
    public static function getNotice($uid){
        $count_notice = self::where('uid','like',"%,$uid,%")->where("is_send",1)->count();
        $see_notice = UserNoticeSee::where("uid",$uid)->count();
        return $count_notice-$see_notice;
    }
    /**
     * @return array
     */
    public static function getNoticeList($uid,$page,$limit = 8){
        //定义分页信息
        $count = self::where('uid','like',"%,$uid,%")->count();
        $data["lastpage"] = ceil($count/$limit) <= ($page+1) ? 1 : 0;

        $where['uid'] = array("like","%,$uid,%");
//        $where['uid'] = array(array("like","%,$uid,%"),array("eq",""), 'or');
        $where['is_send'] = 1;
        $list = self::where($where)->field('id,user,title,content,add_time')->order("add_time desc")->limit($page*$limit,$limit)->select()->toArray();
        foreach ($list as $key => $value) {
            $list[$key]["add_time"] = date("Y-m-d H:i:s",$value["add_time"]);
            $list[$key]["is_see"] = UserNoticeSee::where("uid",$uid)->where("nid",$value["id"])->count() > 0 ? 1 : 0;
        }
        $data["list"] = $list;
        return $data;
    }
    /**
     * @return array
     */
    public static function seeNotice($uid,$nid){
        if(UserNoticeSee::where("uid",$uid)->where("nid",$nid)->count() <= 0){
            $data["nid"] = $nid;
            $data["uid"] = $uid;
            $data["add_time"] = time();
            UserNoticeSee::create($data);
        }
    }

    /**
     * @param array $where
     * @return array
     */
    public static function getList($where=[]){
        $model = new self;
        $model->order('id desc');
        if(!empty($where)){
            $list=($list=$model->page((int)$where['page'],(int)$where['limit'])->select()) && count($list) ? $list->toArray() : [];
            foreach ($list as &$item){
                if($item["uid"] != ''){
                    $uids = explode(",",$item["uid"]);
                    array_splice($uids,0,1);
                    array_splice($uids,count($uids)-1,1);
                    $item["uid"] = $uids;
                }
                $item['send_time']=date('Y-m-d H:i:s',$item['send_time']);
            }
            $count=self::count();
            return compact('count','list');
        }
        return self::page($model,function($item,$key){
            if($item["uid"] != ''){
                $uids = explode(",",$item["uid"]);
                array_splice($uids,0,1);
                array_splice($uids,count($uids)-1,1);
                $item["uid"] = $uids;
            }
        });
    }

}