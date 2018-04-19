<?php
// +----------------------------------------------------------------------
// | 竞价和代理出价控制器
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\MemberbaseController; 
/**
 * 首页
 */
class MgpmController extends MemberbaseController {
    function _initialize(){
		parent::_initialize();
	}
    /*
	* 单次出价
	*/
	public function offer() {
		$cid = I('post.cid',0,'intval');  //pmgezi表ID
		$price = I('post.price',0,'intval');	//用户出价
		$memberInfo = sp_get_current_user();
		$uid = $memberInfo['id'];	//当前用户ID
		if(empty($cid) || empty($price) || empty($uid)){
			$this->error('参数错误,竞价失败!');exit;
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
			$this->error('无此竞拍信息!');exit;
		}
		//获取加价幅度
		//$margin_price = $pmgeziInfo['margin_price'];
		
		//此拍卖鸽子是否有订单生成，如果有则拍卖结束
		$pmgeziOrderModWhere = array();
		$pmgeziOrderModWhere['pid'] = $pmgeziInfo['id'];
		$count = $pmgeziOrderMod->where($pmgeziOrderModWhere)->count();
		if($count){
			$this->error('竞拍已结束!');exit;
		}
		
		//获取最近竞价信息
		$pmgeziOfferModWhere = array();
		$pmgeziOfferModWhere['cid'] = $pmgeziInfo['id'];
		$offerTime = $pmgeziOfferMod->where($pmgeziOfferModWhere)->order('id DESC')->find();
		//判断最后一次出价的是否本用户
		if($offerTime['uid'] == $uid){
			$this->error('您的竞价未被超越,出价失败!');exit;
		}
		
		//获取拍卖结拍时间
		$changciModWhere = array();
		$changciModWhere['id'] = $pmgeziInfo['cid'];
		$end_time = $changciMod->where($changciModWhere)->getField('end_time');
		
		$time = time();
		
		
		$newPrice = lanhai_margin_price($price)+$price;//新价格
		$pmgeziOfferAgentModWhere = array();
		$pmgeziOfferAgentModWhere['pid'] = $cid;
		$pmgeziOfferAgentModWhere['uid'] = $offerTime['uid'];
		$pmgeziOfferAgentModWhere['agent_price'] = array('EGT',$newPrice);
		$agentInfo = $pmgeziOfferAgentMod->where($pmgeziOfferAgentModWhere)->order('agent_price DESC')->find();//获取最高的代理竞价信息
		//判断竞价是否结束
		if($time-$end_time > 0){
			//判断是否结拍8分钟内下过订单
			if($end_time-$offerTime['addtime'] < 481){
				//是8分钟内下过订单  结拍时间后延8分钟 , 并增加竞价记录
				$data = array();
				$data['cid'] = $cid;
				$data['price'] = $price;
				$data['uid'] = $uid;
				$data['addtime'] = $time;
				$resources = $pmgeziOfferMod->add($data);
				if(empty($resources)){
					$this->error('竞拍失败，请重新竞拍');exit;
				}
				//如果有代理竞价高于用户竞价,自动超越
				//$newPrice += $margin_price+$price;
				if($agentInfo){
					$data = array();
					$data['cid'] = $cid;
					$data['price'] = $newPrice;
					$data['uid'] = $agentInfo['uid'];
					$data['addtime'] = $time;
					$pmgeziOfferMod->add($data);
				}
				$end_time = $time+480;
				$changciMod->where($changciModWhere)->save(array('end_time'=>$end_time));//结拍增加8分钟
				$this->success('竞拍成功');exit;
			}else{
				//已结拍，并生成订单
				$data = array();
				$data['pid'] = $cid;
				$data['price'] = $offerTime['price'];
				$data['uid'] = $offerTime['uid'];
				$data['addtime'] = time();
				$pmgeziOrderMod->add($data);
				$this->error('竞拍已结束!');exit;
			}
		}else{
			$data = array();
			$data['cid'] = $cid;
			$data['price'] = $price;
			$data['uid'] = $uid;
			$data['addtime'] = $time;
			$resources = $pmgeziOfferMod->add($data);
			if(empty($resources)){
				$this->error('竞拍失败，请重新竞拍');exit;
			}else{
				//如果有代理竞价高于用户竞价,自动超越
				//$newPrice = $margin_price+$price;
				if($agentInfo){
					$data = array();
					$data['cid'] = $cid;
					$data['price'] = $newPrice;
					$data['uid'] = $agentInfo['uid'];
					$data['addtime'] = $time;
					$pmgeziOfferMod->add($data);
				}
				$this->success('竞拍成功');exit;
			}
		}
    }
	/*
	* 代理出价
	*/
	public function agent(){
		$cid = I('post.cid',0,'intval');  //pmgezi表ID
		$price = I('post.price',0,'intval');	//用户代理竞价价格
		$memberInfo = sp_get_current_user();
		$uid = $memberInfo['id'];	//当前用户ID
		if(empty($cid) || empty($price) || empty($uid)){
			$this->error('参数错误,代理出价失败!');exit;
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
			$this->error('无此竞拍信息!');exit;
		}
		//此拍卖鸽子是否有订单生成，如果有则拍卖结束
		$pmgeziOrderModWhere = array();
		$pmgeziOrderModWhere['pid'] = $pmgeziInfo['id'];
		$count = $pmgeziOrderMod->where($pmgeziOrderModWhere)->count();
		if($count){
			$this->error('竞拍已结束!');exit;
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
			$this->error('您的代理出价低于竞价价格!');exit;
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
					$this->success('代理出价成功');exit;
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
				$this->success('代理出价出价成功');exit;
			}else{
				//已结拍，并生成订单
				$data = array();
				$data['pid'] = $cid;
				$data['price'] = $offerTime['price'];
				$data['uid'] = $offerTime['uid'];
				$data['addtime'] = time();
				$pmgeziOrderMod->add($data);
				$this->error('竞拍已结束!');exit;
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
				$this->success('代理出价成功');exit;
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
			$this->success('代理出价成功');exit;
		}
	}
	
}


