<?php
/*
 *      _______ _     _       _     _____ __  __ ______
 *     |__   __| |   (_)     | |   / ____|  \/  |  ____|
 *        | |  | |__  _ _ __ | | _| |    | \  / | |__
 *        | |  | '_ \| | '_ \| |/ / |    | |\/| |  __|
 *        | |  | | | | | | | |   <| |____| |  | | |
 *        |_|  |_| |_|_|_| |_|_|\_\\_____|_|  |_|_|
 */
/*
 *     _________  ___  ___  ___  ________   ___  __    ________  _____ ______   ________
 *    |\___   ___\\  \|\  \|\  \|\   ___  \|\  \|\  \ |\   ____\|\   _ \  _   \|\  _____\
 *    \|___ \  \_\ \  \\\  \ \  \ \  \\ \  \ \  \/  /|\ \  \___|\ \  \\\__\ \  \ \  \__/
 *         \ \  \ \ \   __  \ \  \ \  \\ \  \ \   ___  \ \  \    \ \  \\|__| \  \ \   __\
 *          \ \  \ \ \  \ \  \ \  \ \  \\ \  \ \  \\ \  \ \  \____\ \  \    \ \  \ \  \_|
 *           \ \__\ \ \__\ \__\ \__\ \__\\ \__\ \__\\ \__\ \_______\ \__\    \ \__\ \__\
 *            \|__|  \|__|\|__|\|__|\|__| \|__|\|__| \|__|\|_______|\|__|     \|__|\|__|
 */
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 首页
 */
class MgcpController extends HomebaseController {

    //当前使用语言 常量  LANG_SET
    protected $yaopin_model;
    protected $article_model;
    function _initialize()
    {
        parent::_initialize();
        $this->yaopin_model = M("Yaopin");
        $this->article_model = M("Article");
        $this->assign('MENU', 'mgcp');
    }

	public function index() {
	 	// $data1 = $this->yaopin_model->where('class_id =1')->select();
		// $data2 = $this->yaopin_model->where('class_id =2')->select();
		// $data3 = $this->yaopin_model->where('class_id =3')->select();
        $data = $this->yaopin_model->where('l = "zh-cn" and hiden = 0')->select();
        $data1 = $data2 = $data3 = array();
        foreach ($data as $val) {
        	if ($val['class_id'] == 1) {
        		$data1[] = $val;
        	}elseif ($val['class_id'] == 2) {
        		$data2[] = $val;
        	}elseif ($val['class_id'] == 3) {
        		$data3[] = $val;
        	}
        }
        $this->assign('data1',$data1);
        $this->assign('data2',$data2);
        $this->assign('data3',$data3);
    	$this->display();
    }
    public function detailZh()
    {
    	$id = I('get.id');
    	if (!$id) {
        	$this->error('缺少铭鸽的id');exit;
        }
		// $class_id = I('get.class_id');
        $where = $listWhere = array();
        $where['id'] = $id;

	    $data = $this->yaopin_model->where($where)->find();
	    if (!$data) {
        	$this->error('此铭鸽id错误，请再次确认');exit;
        }
	    // dump($data);
	    $user = session('user');
	    $drug_collection_id = M('drug_collection')->where('userid='.$user['id'].' and ypid='.$id)->getField('id');
	    $class_id = $data['class_id'];
	    $data['drug_collection_id'] = $drug_collection_id;
		//访问量加1
		$this->yaopin_model->where($where)->setInc('hits',1);
		//鸽子列表
		// $this->assign('id',$id);
		$whereclass = 'l = "zh-cn" and hiden = 0 and class_id='.$class_id;
		//select所有（下拉框）
		$allList = $this->yaopin_model->where($whereclass)->field('id,title')->order('id DESC')->select();
		//上一个
		$thePrevious = $this->yaopin_model->where('id < '.$id.' and '.$whereclass)->field('id')->order('id DESC')->limit(1)->find();
		//下一个
		$theLast = $this->yaopin_model->where('id > '.$id.' and '.$whereclass)->field('id')->order('id ASC')->limit(1)->find();
		if(sp_is_user_login()) {$sess = '1';}else{$sess = '2';}//判断用户是否登录
		$this->assign('thePrevious',$thePrevious);
		$this->assign('theLast',$theLast);
		$this->assign('allList',$allList);
		$this->assign('sess',$sess);
        $this->assign('data',$data);
    	$this->display();
    }
    public function buyinfo()
    {
    	$info=I("post.");
    	if (!$info['realname'] || !$info['phone'] || !$info['email'] || !$info['address']) {
    		$msg = '收件人/手机号码/邮箱/地址为必填项';
    		echo $msg;exit;
    	}else{
    		$phonereg = "/^1[3456789]\d{9}$/";
    		$emailreg = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/";
			if (!preg_match($phonereg, $info['phone'], $phonematches) || !preg_match($emailreg, $info['email'], $emailmatches)) {
    			$msg = '手机号码/邮箱格式错误';
    			echo $msg;exit;
			}
			if ($info['tel']) {
				$telreg = "/^([0-9]{3,4}-)?[0-9]{7,8}$/";
				if (!preg_match($telreg,$info['tel'],$telmatches)) {
	    			$msg = '电话号码格式错误';
	    			echo $msg;exit;
				}
			}
    	}
    	$zsorderMod = M('Zsorder');
    	$add['order_sn'] = $this->orderNum();//20
    	$add['pay_status'] = 0;
    	$user = session('user');
    	$add['user_id'] = $user['id'];
    	$add['shangping_id'] = $info['shangping_id'];
    	$add['num'] = $info['num'];
    	$add['fwprice'] = 0;
    	$add['fare'] = 0;
    	$add['huilv'] = 0;
    	$oneprice = $this->yaopin_model->where('id='.$info['shangping_id'])->getField('price');
    	$add['price'] = $info['num'];
    	$add['totalprice'] = $oneprice*$info['num'];
    	$add['realname'] = $info['realname'];
    	$add['address'] = $info['address'];
    	$add['email'] = $info['email'];
    	$add['tel'] = $info['tel'];
    	$add['phone'] = $info['phone'];
    	$add['remark'] = $info['remark'];
    	$add['addtime'] = time();
    	// print_r($add);
    	$yaopinorderMod = M('yaopin_order');
    	if (M('yaopin_order')->create($add)) {
    		$yaopinorderMod->add($add);
    	}
    	// $zsorderMod->add($add);
    }
    public function orderNum()
    {
    	$strnum = date('YmdHis').rand(100000,999999);
    	if (M('Zsorder')->where('order_sn='.$strnum)->count() > 0) {
    		$this->orderNum();
    	}
    	return $strnum;
    }
    public function drugCollection()
    {
    	$ypid = I('post.ypid');
    	$rt = array();
    	if (!$ypid) {
    		$rt['code'] = 2;
    		$rt['mag'] = '商品不存在';
    		echo json_encode($rt);exit;
    	}
    	$yaopinMod = M('yaopin');
    	if ($yaopinMod->where('id='.$ypid)->count()<1) {
    		$rt['code'] = 2;
    		$rt['mag'] = '商品不存在';
    		echo json_encode($rt);exit;
    	}
    	$user = session('user');
    	$collectionMod = M('drug_collection');
    	$whereyp = 'ypid='.$ypid.' and userid='.$user['id'];
    	$add = array();
    	$add['addtime'] = time();
    	if ($collectionMod->where($whereyp)->count()>0) {
    		$collectionMod->where($whereyp)->save($add);
    		$rt['code'] = 1;
    		echo json_encode($rt);exit;
    	}else{
	    	$add['userid'] = $user['id'];
	    	$add['ypid'] = $ypid;
	    	if ($collectionMod->create($add)) {
	    		$collectionMod->add($add);
	    		$rt['code'] = 1;
    			echo json_encode($rt);exit;
	    	}
    	}
    }
	/*public function chaxun() {
		$url = 'http://202.85.213.155/youpintong/AgentNewQueryInterfaceServlet';
		$data = array();
		
    	$this->display(":index");
    }
	function ccc(){
		$url='https://router.jd.com/api?v=1.2&method=public.product.base.query&access_token=73d13559c03d431bacffb90e4a5d08298&app_key=adf4a35940c7475bad3215d797c52549&sign_method=md5&format=json&timestamp=2018-02-09%2016:16:04&sign=CCC83908152F3AD8B787905F5F84F43F&param_json={%22sku%22:10183487366,%22areaId%22:%221732387%22}';
		$json = $this->http_get($url,$data);
		$data = json_decode($json,true);
		dump($data);
	}*/
	/**
	 * GET 请求
	 * @param string $url
	*/
	/*private function http_get($url){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}*/
	/**
	 * POST 请求
	 * @param string $url 
	 * @param array $param 
	 * @return string content
	*/
	/*public function http_post($url,$param){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
		}
		if (is_string($param)) {
			$strPOST = $param;
		} else {
			$aPOST = array();
			foreach($param as $key=>$val){
				$aPOST[] = $key."=".urlencode($val);
			}
			$strPOST =  join("&", $aPOST);
			
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
		$sContent = curl_exec($oCurl);
		if($error=curl_error($oCurl)){  
	        die($error);  
	    } 

		$aStatus = curl_getinfo($oCurl);
		
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}*/

}


