<?php
// +----------------------------------------------------------------------
// | 首页面
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 首页
 */
class IndexController extends HomebaseController {

    //当前使用语言 常量  LANG_SET
    protected $pmzt_model;
    protected $changci_model;
    protected $pmgezi_model;
    protected $yaopin_model;
    function _initialize()
    {
        parent::_initialize();
        $this->pmzt_model = M("Pmzt");
        $this->changci_model = M("Changci");
        $this->pmgezi_model = M("Pmgezi");
        $this->yaopin_model = M("Yaopin");
		$this->assign('MENU', 'index');
    }

    //正在拍卖的专题页面显示
    public function index() {
		/*正在拍卖块*/
		$time = time();
        $paimaiWhere = $paimaiWhereD = array();
		$paimaiWhere['country1'] = 1;
		$paimaiWhere['tuijian'] = 1;
		$paimaiWhere['start_time'] = array('ELT',$time);
		$paimaiWhere['end_time'] = array('EGT',$time);
        $paimaList = $this->changci_model->where($paimaiWhere)->field('id,cid,name,start_time,end_time')->order('end_time ASC')->select();
        foreach ($paimaList as $k => $val) {
			$paimaList[$k]['gezinum'] = $this->pmgezi_model->where(array('cid' => $val['id']))->count();//场次下鸽子数
			$paimaList[$k]['thumb'] = $this->pmzt_model->where(array('id' => $val['cid']))->getField('small_pics');//专题图片
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
        /*即将拍卖块*/
		$paimaiWhereD['country1'] = 1;
		$paimaiWhereD['tuijian'] = 1;
        $paimaiWhereD['start_time'] = array('EGT',$time);
        $paimaListed = $this->changci_model->where($paimaiWhereD)->field('id,cid,name,start_time,end_time,country')->order('start_time ASC')->limit(3)->select();
        foreach ($paimaListed as $k => $val) {
			$paimaListed[$k]['thumb'] = $this->pmzt_model->where(array('id' => $val['cid']))->getField('small_pics');
        }
        $yaopin = $this->yaopin_model->limit(6)->select();
        $this->assign('paimaList', $paimaList);
        $this->assign('yaopin', $yaopin);
        $this->assign('paimaListed', $paimaListed);
        $this->display(":index");
    }

}


