<?php
namespace User\Controller;

use Common\Controller\HomebaseController;

class IndexController extends HomebaseController {
    
    // 前台用户首页 (公开)
	public function index() {
	    
		$id=I("get.id",0,'intval');
		
		$users_model=M("Users");
		
		$user=$users_model->where(array("id"=>$id))->find();
		
		if(empty($user)){
			$this->error("查无此人！");
		}
		
		$this->assign($user);
		$this->display(":index");

    }
    
    // 前台ajax 判断用户登录状态接口
    function is_login(){
    	if(sp_is_user_login()){
    		$this->ajaxReturn(array("status"=>1,"user"=>sp_get_current_user()));
    	}else{
    		$this->ajaxReturn(array("status"=>0,"info"=>"此用户未登录！"));
    	}
    }

    //退出
    public function logout(){
    	$ucenter_syn=C("UCENTER_ENABLED");
    	$login_success=false;
    	if($ucenter_syn){
    		include UC_CLIENT_ROOT."client.php";
    		echo uc_user_synlogout();
    	}
    	session("user",null);//只有前台用户退出
    	redirect(__ROOT__."/");
    }
	
	/*
	* 代理出价操作
	*/
	public function agent(){
		$cid = I('post.cid',0,'intval');  //pmgezi表ID
		$price = I('post.price',0,'intval');	//用户代理竞价价格
		$memberInfo = sp_get_current_user();
		$uid = $memberInfo['id'];	//当前用户ID
		if(empty($uid)){
			echo 1;exit;
		}
		if(empty($cid) || empty($price) || empty($uid)){
			//$this->error('参数错误,代理出价失败!');exit;
			echo 2;exit;
		}
		$pmgeziMod = M('Pmgezi');//场次对应鸽子表
		$changciMod = M('Changci');//场次表
		$pmgeziOrderMod = M('PmgeziOrder');//订单表
		$pmgeziOfferMod = M('PmgeziOffer');//竞价表
		$pmgeziOfferAgentMod = M('PmgeziOfferAgent');//代理出价表
		
		//获取场次对应鸽子表信息
		$pmgeziModWhere = array();
		$pmgeziModWhere['id'] = $cid;
		$pmgeziInfo = $pmgeziMod->where($pmgeziModWhere)->find();
		if(empty($pmgeziInfo)){
			//$this->error('无此竞拍信息!');exit;
			echo 3;exit;
		}
		//此拍卖鸽子是否有订单生成，如果有则拍卖结束
		$pmgeziOrderModWhere = array();
		$pmgeziOrderModWhere['pid'] = $pmgeziInfo['id'];
		$count = $pmgeziOrderMod->where($pmgeziOrderModWhere)->count();
		if($count){
			//$this->error('竞拍已结束!');exit;
			echo 4;exit;
		}
		
		//获取最近竞价信息
		$pmgeziOfferModWhere = array();
		$pmgeziOfferModWhere['cid'] = $pmgeziInfo['id'];
		$offerTime = $pmgeziOfferMod->where($pmgeziOfferModWhere)->order('id DESC')->find();
		
		//获取加价幅度
		$margin_price = lanhai_margin_price($offerTime['price']);
		$newPrice = $margin_price+$offerTime['price'];//新价格
		//代理出价是否高于等于本次竞价标准
		if($price < $newPrice){
			//$this->error('您的代理出价低于竞价价格!');exit;
			echo 5;exit;
		}
		//获取拍卖结拍时间
		$changciModWhere = array();
		$changciModWhere['id'] = $pmgeziInfo['cid'];
		$end_time = $changciMod->where($changciModWhere)->getField('end_time');
		
		$time = time();
		//获取代理出价
		$znewPrice = lanhai_margin_price($newPrice)+$newPrice;//最新价格
		$pmgeziOfferAgentModWhere = array();
		$pmgeziOfferAgentModWhere['pid'] = $cid;
		$pmgeziOfferAgentModWhere['status'] = 1;
		$pmgeziOfferAgentModWhere['agent_price'] = array('EGT',$znewPrice);
		$agentInfo = $pmgeziOfferAgentMod->where($pmgeziOfferAgentModWhere)->order('agent_price DESC')->find();//获取最高的代理竞价信息
		//判断竞价是否结束
		if($time-$end_time > 0){
			//判断是否结拍8分钟内下过订单
			if($end_time-$offerTime['addtime'] < 481){
				//是8分钟内下过订单  结拍时间后延8分钟 , 并增加竞价记录
				
				//本人是否有设置代理出价
				$pmgeziOfferAgentModWhere = array();
				$pmgeziOfferAgentModWhere['pid'] = $cid;
				$pmgeziOfferAgentModWhere['uid'] = $uid;
				$offerData = $pmgeziOfferAgentMod->where($pmgeziOfferAgentModWhere)->find();
				if($offerData){
					$offerData['agent_price'] = $price;
					$offerData['status'] = 1;
					$pmgeziOfferAgentMod->save($offerData);
				}else{
					$offerData['pid'] = $cid;
					$offerData['agent_price'] = $price;
					$offerData['uid'] = $uid;
					$offerData['status'] = 1;
					$offerData['addtime'] = $time;
					$pmgeziOfferAgentMod->add($offerData);
				}
				//最高价格是否本人
				if($offerTime['uid'] == $uid){
					//$this->success('代理出价成功');exit;
					exit;
				}
				
				for($i = 0;$i >= 0;++$i){
					$time = time();
					if($i%2){
						if($znewPrice <= $agentInfo['agent_price']){
							$data = array();
							$data['cid'] = $cid;
							$data['price'] = $znewPrice;
							$data['uid'] = $agentInfo['uid'];
							$data['addtime'] = $time;
							$pmgeziOfferMod->add($data);
							$znewPrice += lanhai_margin_price($znewPrice);//最新价格
						}else{
							break;
						}
					}else{
						if($znewPrice <= $price){
							$data = array();
							$data['cid'] = $cid;
							$data['price'] = $znewPrice;
							$data['uid'] = $uid;
							$data['addtime'] = $time;
							$pmgeziOfferMod->add($data);
							$znewPrice += lanhai_margin_price($znewPrice);//最新价格
						}else{
							break;
						}
					}
				}
				
				$end_time = $time+480;
				$changciMod->where($changciModWhere)->save(array('end_time'=>$end_time));//结拍增加8分钟
				//$this->success('代理出价出价成功');exit;
				exit;
			}else{
				//已结拍，并生成订单
				$data = array();
				$data['pid'] = $cid;
				$data['price'] = $offerTime['price'];
				$data['uid'] = $offerTime['uid'];
				$data['addtime'] = time();
				$pmgeziOrderMod->add($data);
				//$this->error('竞拍已结束!');exit;
				echo 6;exit;
			}
		}else{
			//本人是否有设置代理出价
			$pmgeziOfferAgentModWhere = array();
			$pmgeziOfferAgentModWhere['pid'] = $cid;
			$pmgeziOfferAgentModWhere['uid'] = $uid;
			$offerData = $pmgeziOfferAgentMod->where($pmgeziOfferAgentModWhere)->find();
			if($offerData){
				$offerData['agent_price'] = $price;
				$offerData['status'] = 1;
				$pmgeziOfferAgentMod->save($offerData);
			}else{
				$offerData['pid'] = $cid;
				$offerData['agent_price'] = $price;
				$offerData['uid'] = $uid;
				$offerData['status'] = 1;
				$offerData['addtime'] = $time;
				$pmgeziOfferAgentMod->add($offerData);
			}
			//最高价格是否本人
			if($offerTime['uid'] == $uid){
				//$this->success('代理出价成功');exit;
				exit;
			}
			for($i = 0;$i >= 0;++$i){
				$time = time();
				if($i%2){
					if($znewPrice <= $agentInfo['agent_price']){
						$data = array();
						$data['cid'] = $cid;
						$data['price'] = $znewPrice;
						$data['uid'] = $agentInfo['uid'];
						$data['addtime'] = $time;
						$pmgeziOfferMod->add($data);
						$znewPrice += lanhai_margin_price($znewPrice);//最新价格
					}else{
						break;
					}
				}else{
					if($znewPrice <= $price){
						$data = array();
						$data['cid'] = $cid;
						$data['price'] = $znewPrice;
						$data['uid'] = $uid;
						$data['addtime'] = $time;
						$pmgeziOfferMod->add($data);
						$znewPrice += lanhai_margin_price($znewPrice);//最新价格
					}else{
						break;
					}
				}
			}
			//$this->success('代理出价成功');exit;
			exit;
		}
	}
	
}
