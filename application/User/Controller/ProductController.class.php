<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class ProductController extends MemberbaseController {
	
	function _initialize(){
		parent::_initialize();
	}
	
    // 财务首页
    public function index() {
    	$collectionMod = M('drug_collection');

    	$user = session('user');
    	$where = 'userid='.$user['id'];
    	$info = $collectionMod->alias('coll')->join('lanhai_yaopin yp ON yp.id=coll.ypid')->field('coll.id,yp.bz,yp.price,yp.title,yp.guige,yp.stock')->where($where)->select();
        // dump($info);
    	$this->assign('info',$info);
		$this->assign($this->user);
    	$this->display();
    }
    // 药品订单
    public function order() {
    	$yaopinorderMod = M('yaopin_order');
    	$userinfo = session('user');
    	
    	$yaopinInfo = $yaopinorderMod->alias('yporder')->join('lanhai_yaopin yp ON yporder.shangping_id=yp.id')->where('user_id='.$userinfo['id'])->field('yporder.id,yporder.num,yporder.price,yporder.totalprice,yporder.pay_status,yp.title,yp.bz')->select();
    	// dump($yaopinInfo);
    	$this->assign('yaopinInfo',$yaopinInfo);
		$this->display();
    }
    public function deldrug()
    {
        $ypid = I('post.ypid');
        $str_rep_id = str_replace(" ","",$ypid);
        $id = substr($str_rep_id,0,strlen($str_rep_id)-1);
        $idarr = explode(',',$id);
        $drugcolMod = M('drug_collection');
        $user = session('user');
        $where = 'userid='.$user['id'];
        $delid = $str = '';

        //判断此项删除是当前用户的
        foreach ($idarr as $val) {
            $m = $drugcolMod->where($where." and id=$val")->count();
            if ($m>0) {
                $delid.= $val.',';
                $str.= 1;
            }else{
                $str.= 2;
            }
        }
        if (strstr($str,'2')) {
            echo json_encode(array('code'=>2,'msg'=>'所选药品不存在或不属于此用户'));exit;
        }
        $id = substr($delid,0,strlen($delid)-1);
        // $drugcolMod->where("id in ('$id')")->delete();
        echo json_encode(explode(',',$id));
    }
}