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
    function _initialize()
    {
        parent::_initialize();
        $this->pmzt_model = M("Pmzt");
        $this->changci_model = M("Changci");
        $this->pmgezi_model = M("Pmgezi");
		$this->pmjilu_model = M("Pmjilu");
		$this->gezi_model = M("Gezi");
		$this->xuetongshu_model = M("Xuetongshu");
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
            $remain_minutes = floor(($remain_time - $remain_hour*60*60)/60); //剩余的分钟数
            $remain_minute = sprintf("%02d",$remain_minutes); //剩余的分钟数
            $remain_seconds = ($remain_time - $remain_hour*60*60 - $remain_minute*60); //剩余的秒数
            $remain_second=sprintf("%02d",$remain_seconds);
            $paimaList[$k]['daojishi'] = $remain_hour.':'.$remain_minute.':'.$remain_second;
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
        $remain_minutes = floor(($remain_time - $remain_hour*60*60)/60); //剩余的分钟数
        $remain_minute = sprintf("%02d",$remain_minutes); //剩余的分钟数
        $remain_seconds = ($remain_time - $remain_hour*60*60 - $remain_minute*60); //剩余的秒数
        $remain_second=sprintf("%02d",$remain_seconds);
        $times = $remain_hour.':'.$remain_minute.':'.$remain_second;
        if($times > 0){
            $times = $times;
        }else{
            $times = '已结束';
        }
       //获取该场次下面的所有鸽子信息
        $map['cid'] = $id;
        $geziList = $this->pmgezi_model->where($map)->field('huanhao,cid,sequence,addtime')->select();
		$article = array();
        foreach ($geziList as $k => $val){
            $gzmap = array('huanhao'=>$val['huanhao']);
            $geziInfo = $this->gezi_model->where($gzmap)->find();
			$xuetongInfo = $this->xuetongshu_model->where($gzmap)->getField('info');
			$xuetongInfo = htmlspecialchars_decode($xuetongInfo);
			//血统 前4个p标签内容
			$xuetong = array_slice($this->preg_get($xuetongInfo),0,5);
			$geziInfo['xuetong'] = $xuetong;
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
        $huanhao = I('get.id');
        $where = array();
        $where['huanhao'] = $huanhao;
        $data = $this->gezi_model->where($where)->find();
        //获取目录路径
        $path = 'default/tupian/';
		$data['pic'] = "$path"."$huanhao".'-pigeon.jpg';
		$data['yjpic'] = "$path"."$huanhao".'-eye.jpg';
		$data['xtpic'] = "$path"."$huanhao".'-ancestry.jpg';
        $geziInfo = $this->xuetongshu_model->where($where)->field('info,finfo,minfo')->find();
        $data['xuetong'] = $geziInfo;//htmlspecialchars_decode($data['info']);

        $list = $this->pmgezi_model->where($where)->find();
        $this->assign('data', $data);
        $this->assign('list', $list);
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


