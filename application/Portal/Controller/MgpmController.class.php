<?php
// +----------------------------------------------------------------------
// | 拍卖控制器
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 首页
 */
class MgpmController extends HomebaseController {
    //当前使用语言 常量  LANG_SET
    protected $pmzt_model;
    protected $changci_model;
    protected $pmgezi_model;
	protected $pmjilu_model;
	protected $gezi_model;
	protected $xuetongshu_model;
	protected $pmgezi_offer_model;
    function _initialize()
    {
        parent::_initialize();
        $this->pmzt_model = M("Pmzt");
        $this->changci_model = M("Changci");
        $this->pmgezi_model = M("Pmgezi");
		$this->pmjilu_model = M("Pmjilu");
		$this->gezi_model = M("Gezi");
		$this->xuetongshu_model = M("Xuetongshu");
		$this->pmgezi_offer_model = M("Pmgezi_offer");
		$this->assign('MENU', 'mgpm');
    }
    
	public function index() {
        $time = time();
		//正在拍卖的专题页面显示
        $paimaiWhere = $paimaiWhereD = array();
		$paimaiWhere['country1'] = 1;
		$paimaiWhere['start_time'] = array('ELT',$time);
		$paimaiWhere['end_time'] = array('EGT',$time);
        $paimaList = $this->changci_model->where($paimaiWhere)->field('id,cid,name,start_time,end_time')->order('end_time ASC')->select();
        foreach ($paimaList as $k => $val) {
            //场次下鸽子数
			$gezinum = $this->pmgezi_model->where(array('cid' => $val['id']))->count();
			$paimaList[$k]['gezinum'] = $gezinum;
			$paimaList[$k]['thumb'] = $this->pmzt_model->where(array('id' => $val['cid']))->getField('pics');
            //倒计时
            $remain_time = $val['end_time'] - $time; //剩余的秒数
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
			$paimaList[$k]['daojishi'] = $times;
        }
        //即将上线的条件
		$paimaiWhereD['country1'] = 1;
        $paimaiWhereD['start_time'] = array('EGT',$time);
        $paimaListed = $this->changci_model->where($paimaiWhereD)->field('id,cid,name,start_time,end_time')->order('start_time ASC')->limit(3)->select();
        foreach ($paimaListed as $k => $val) {
            //场次下鸽子数
			$gezinum = $this->pmgezi_model->where(array('cid' => $val['id']))->count();
			$paimaListed[$k]['gezinum'] = $gezinum;
			$paimaListed[$k]['thumb'] = $this->pmzt_model->where(array('id' => $val['cid']))->getField('pics');
        }
        $this->assign('paimaList', $paimaList);
		$this->assign('paimaListed', $paimaListed);
    	$this->display();
    }
	public function paimaishow() {
        //场次id
        $id = I('get.id');
		$where = $map = array();
        $where['id'] = $id;
		$where['country1'] = 1;
        //获取场次信息
        $info = $this->changci_model->where($where)->find();
		if(empty($info)){
			$this->error('无此拍卖信息',U('Portal/index/index'));exit;
		}
		//获取专题信息
		$ztinfo = $this->pmzt_model->where(array('id'=>$info['cid']))->field('id,ptname,zhaiyao,tname,pics')->find();
        //获取场次信息
        $changciList = $this->changci_model->where(array('country1'=>1,'cid'=>$info['cid']))->order('seq ASC')->select();
        foreach ($changciList as $key => $val){
            $changciList[$key]['count'] = $this->pmgezi_model->where(array('cid'=>$val['id']))->count();
        }
        //倒计时
        $remain_time = $info['end_time'] - time(); //剩余的秒数
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
       //获取该场次下面的所有鸽子信息
        $map['cid'] = $id;
        $geziList = $this->pmgezi_model->where($map)->field('id,huanhao,cid,sequence,addtime')->order('id ASC')->select();
		$article = array();
        foreach ($geziList as $k => $val){
            $gzmap = array('huanhao'=>$val['huanhao']);
            $geziInfo = $this->gezi_model->where($gzmap)->find();
			$xuetongInfo = $this->xuetongshu_model->where($gzmap)->getField('info');
			$xuetongInfo = htmlspecialchars_decode($xuetongInfo);
			//血统 前4个p标签内容
			$xuetong = array_slice($this->preg_get($xuetongInfo),0,5);
			$geziInfo['xuetong'] = $xuetong;
			$geziInfo['gid'] = $val['id'];
			$geziInfo['cid'] = $id;
			$article[$k] = $geziInfo;
        }
        //专题信息
        $this->assign('info', $info);
        $this->assign('ztinfo', $ztinfo);
        $this->assign('id', $id);//场次ID
        //场次信息
        $this->assign('changciList', $changciList);
        //鸽子信息
        $this->assign('article', $article);
        //场次时间
        $this->assign('times', $times);
    	$this->display();
    }
	//鸽子页面的信息
	public function gezishow() {
        //鸽子环号
        $id = I('get.id');
		$cid = I('get.cid');
		//场次  鸽子
		$gzWhere = array();
		$gzWhere['id'] = $id;
		$gzWhere['cid'] = $cid;
		$geziInfo = $this->pmgezi_model->where($gzWhere)->find();
		$this->pmgezi_model->where($gzWhere)->setInc('hits',1);
		//dump($geziInfo);exit;
		if(empty($geziInfo)){
			$this->error('参数有误!');exit;
		}
		//获取结拍时间
		$end_time = $this->changci_model->where(array('id'=>$cid))->getField('end_time');
		
		//结拍时间倒计时
		$remain_time = $end_time - time(); //剩余的秒数
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
		$this->assign('remain_time', $remain_time);
		$this->assign('times', $times);
		//获取鸽子信息
		$huanhao = $geziInfo['huanhao'];
        $where = array();
        $where['huanhao'] = $huanhao;
        $data = $this->gezi_model->where($where)->find();
        //获取目录路径
        $path = 'default/tupian/';
		$data['pic'] = "$path"."$huanhao".'-pigeon.jpg';
		$data['yjpic'] = "$path"."$huanhao".'-eye.jpg';
		$data['xtpic'] = "$path"."$huanhao".'-ancestry.jpg';
        $gezixueTong = $this->xuetongshu_model->where($where)->field('info,finfo,minfo')->find();
        $data['xuetong'] = $gezixueTong;//htmlspecialchars_decode($data['info']);
		//鸽子竞拍次数
		$geziInfo['count'] = $this->pmgezi_offer_model->where(array('cid'=>$geziInfo['id']))->count();
		if($geziInfo['count'] > 0){
			//获取最近出价记录
			$userOffer = $this->pmgezi_offer_model->where(array('cid'=>$geziInfo['id']))->order('id DESC')->find();
			$this->assign('userOffer', $userOffer);
			$fudu = lanhai_margin_price($userOffer['price']);
			$price = $fudu+$userOffer['price'];
			//获取最近出价者
			$userMod = M('Users');
			$userInfo = $userMod->where(array('id'=>$userOffer['uid']))->find();
			$this->assign('userInfo', $userInfo);
			//竞拍记录
			$geziJiLu = $this->pmgezi_offer_model->where(array('cid'=>$geziInfo['id']))->order('id DESC')->field('uid,price,addtime')->limit(8)->select();
			foreach($geziJiLu as $k=>$val){
				$geziJiLu[$k]['user_nicename'] = $userMod->where(array('id'=>$val['uid']))->getField('user_nicename');
			}
			$this->assign('geziJiLu', $geziJiLu);
		}else{
			$fudu = lanhai_margin_price($geziInfo['start_price']);
			$price = $geziInfo['start_price'];
		}
		$this->assign('fudu', $fudu);
		//上一个
		$listWhere = array();
		$listWhere['cid'] = $cid;
		$listWhere['id'] = array('GT',$id);
		$update = $list = $this->pmgezi_model->where($listWhere)->order('id DESC')->find();
		$this->assign('update',$update);
		//下一个
		$listWhere['id'] = array('LT',$id);
		$downdate = $list = $this->pmgezi_model->where($listWhere)->order('id DESC')->find();
		$this->assign('downdate',$downdate);
		//所有鸽子
		$allList = $this->pmgezi_model->where(array('cid'=>$cid))->order('id DESC')->select();
		foreach($allList as $k=>$val){
			$allList[$k]['title'] = $this->gezi_model->where(array('huanhao'=>$val['huanhao']))->getField('title');
		}
		$this->assign('allList',$allList);
		
		$this->assign('data', $data);
        $this->assign('price', $price);
        $this->assign('list', $geziInfo);
    	$this->display();
    }
	//专题页面显示
	public function rwjs() {
        $where = array();
       $id = I('get.id');
        $where['l'] = LANG_SET;
        $where['id'] = $id;
        $list = $this->pmzt_model
            ->where($where)
            ->find();
            $list['end_time']= $this->changci_model->where(array('cid'=>$list['id']))->order('end_time desc')->getField('end_time');
            //专题下鸽子总数
            $gezicount = 0;
            $arr =  $this->changci_model->where(array('cid' => $list['id']))->field('id')->select();
            foreach ($arr as $key => $vo){
                $gezinum = $this->pmgezi_model->where(array('cid' => $vo['id']))->count();
                $gezicount += $gezinum;
            }
            $list['gezicount'] = $gezicount;

        $this->assign('list', $list);
    	$this->display();
    }
	//获取血统P标签内容
	public function preg_get($xuetongInfo){
		$pattern = '/(<p>.*?<\/p>)/';
		$out = array();
		preg_match_all( $pattern , $xuetongInfo, $out );
		return $out[1];
	}
}


