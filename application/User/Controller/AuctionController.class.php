<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class AuctionController extends MemberbaseController {
	
	function _initialize(){
		parent::_initialize();
	}
	
    // 会员中心首页
    public function index() {
		//$this->assign($this->user);
		$memberInfo = sp_get_current_user();
		$uid = $memberInfo['id'];	//当前用户ID
		$pmgeziOfferMod = M('PmgeziOffer');//出价记录表
		$changciMod = M('Changci');//场次表
		$pmgeziMod = M('Pmgezi');//场次鸽子表
		$pmztMod = M('Pmzt');//专题表
		$geziMod = M('Gezi');//鸽子表
		//获取用户竞拍记录
		$pmgeziOfferWhere = array();
		$pmgeziOfferWhere['uid'] = $uid;
		$list = $pmgeziOfferMod->where($pmgeziOfferWhere)->distinct(true)->field('cid')->order('id DESC')->select();
		foreach($list as $k=>$val){
			$pmgeziInfo = $pmgeziMod->where(array('id'=>$val['cid']))->find();
			$changciInfo = $changciMod->where(array('id'=>$pmgeziInfo['cid']))->find();
			$ztname = $pmztMod->where(array('id'=>$changciInfo['cid']))->getField('tname');//
			//专题名称
			$list[$k]['ztname'] = $ztname;
			$list[$k]['huanhao'] = $pmgeziInfo['huanhao'];
			$list[$k]['sequence'] = $pmgeziInfo['sequence'];//koop  鸽子编号
			//本鸽子最高价
			$list[$k]['zprice'] = $pmgeziOfferMod->where(array('cid'=>$val['cid']))->order('id DESC')->getField('price');
			//剩余时间
            $remain_time = $changciInfo['end_time'] - time(); //剩余的秒数
            $remain_hours = floor($remain_time/(60*60)); //剩余的小时
            $remain_hour = sprintf("%02d",$remain_hours); //剩余的小时
			if($remain_hour < 24){
				$remain_minutes = floor(($remain_time - $remain_hour*60*60)/60); //剩余的分钟数
				$remain_minute = sprintf("%02d",$remain_minutes); //剩余的分钟数
				$remain_seconds = ($remain_time - $remain_hour*60*60 - $remain_minute*60); //剩余的秒数
				$remain_second=sprintf("%02d",$remain_seconds);
				$times = $remain_hour.':'.$remain_minute.':'.$remain_second;
				if($remain_time > 0){
					$times = $times;
				}else{
					$times = '已结束';
				}
			}else{
				$times = intval($remain_hour/24).'天';
			}
			$list[$k]['times'] = $times;
			$list[$k]['price'] = $pmgeziOfferMod->where(array('uid'=>$uid,'cid'=>$val['cid']))->order('id DESC')->getField('price');
			//鸽子名称
			$list[$k]['gzname'] = $geziMod->where(array('huanhao'=>$pmgeziInfo['huanhao']))->getField('title');
		}
		//dump($list);
		$this->assign('list',$list);
    	$this->display();
    }
    // 代理出价
    public function agent() {
		$memberInfo = sp_get_current_user();
		$uid = $memberInfo['id'];	//当前用户ID
		$pmgeziOfferMod = M('PmgeziOffer');//出价记录表
		$pmgeziOfferAgentMod = M('PmgeziOfferAgent');//代理出价记录表
		$changciMod = M('Changci');//场次表
		$pmgeziMod = M('Pmgezi');//场次鸽子表
		$pmztMod = M('Pmzt');//专题表
		$geziMod = M('Gezi');//鸽子表
		//获取用户竞拍记录
		$pmgeziOfferWhere = array();
		$pmgeziOfferWhere['a.uid'] = $uid;
		$pmgeziOfferWhere['b.end_time'] = array('GT',time());
		//$list = $pmgeziOfferMod->where($pmgeziOfferWhere)->distinct(true)->field('cid')->order('id DESC')->select();
		$list = $pmgeziOfferMod->alias('a')->join('lanhai_changci b ON b.id= a.cid')->where($pmgeziOfferWhere)->distinct(true)->field('a.cid')->order('a.id DESC')->select();
		//echo $pmgeziOfferMod->getLastSql();
		//dump($list);exit;
		foreach($list as $k=>$val){
			$pmgeziInfo = $pmgeziMod->where(array('id'=>$val['cid']))->find();
			$changciInfo = $changciMod->where(array('id'=>$pmgeziInfo['cid']))->find();
			$ztname = $pmztMod->where(array('id'=>$changciInfo['cid']))->getField('tname');
			//专题名称
			$list[$k]['ztname'] = $ztname;
			$list[$k]['huanhao'] = $pmgeziInfo['huanhao'];
			$list[$k]['sequence'] = $pmgeziInfo['sequence'];//koop  鸽子编号
			//本鸽子最高价
			$list[$k]['zprice'] = $pmgeziOfferMod->where(array('cid'=>$val['cid']))->order('id DESC')->getField('price');
			//本人出价
			$list[$k]['price'] = $pmgeziOfferMod->where(array('uid'=>$uid,'cid'=>$val['cid']))->order('id DESC')->getField('price');
			//本人代理出价
			$agent_price = $pmgeziOfferAgentMod->where(array('uid'=>$uid,'pid'=>$val['cid']))->order('id DESC')->find();
			$list[$k]['agent_price'] = $agent_price['agent_price'];
				$list[$k]['status'] = $agent_price['status'];
			//$list[$k]['agent_price'] = $pmgeziOfferAgentMod->where(array('uid'=>$uid,'pid'=>$val['cid']))->order('id DESC')->find('agent_price');
			//鸽子名称
			$list[$k]['gzname'] = $geziMod->where(array('huanhao'=>$pmgeziInfo['huanhao']))->getField('title');
		}
		//dump($list);
		$this->assign('list',$list);
    	$this->display();
    } 
		
    // 关注拍卖鸽子
    public function followg() {
		$this->display();
    }
	// 关注拍卖会
    public function followh() {
		$this->display();
    }
	// 成功竞拍
    public function syccessj() {
		$this->display();
    }
}