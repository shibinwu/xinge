<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class MemberController extends MemberbaseController {
	
	function _initialize(){
		parent::_initialize();
	}
	
    // 会员中心首页
    public function index() {
		$this->assign($this->user);
    	$this->display();
    }
    
		
    // 密码服务
    public function mmfw() {
		if(IS_POST){
			$pass = I('post.pass');
			$pass1 = I('post.pass1');
			if(empty($pass) || empty($pass)){
				$this->error('密码不可为空');exit;
			}
			if($pass !== $pass1){
				$this->error('密码不一致');exit;
			}
			if(strlen($pass) < 6){
				$this->error('密码长度最少6位');exit;
			}
			$memberInfo = sp_get_current_user();
			$uid = $memberInfo['id'];	//当前用户ID
			$membersMod = M('Members');
			$data = array();
			$data['id'] = $uid;
			$data['password'] = md5($pass);
			$membersMod->save($data);
			$this->success('密码修改成功');exit;
		}
		$this->display();
    }
	// 修改个人信息
    public function xggrxx() {
		if(IS_POST){
			$memberInfo = sp_get_current_user();
			$uid = $memberInfo['id'];	//当前用户ID
			$membersMod = M('Members');
			$data = array();
			$data['id'] = $uid;
			$data['realname'] = I('post.realname');
			$data['mobile'] = I('post.mobile');
			$data['telephone'] = I('post.telephone');
			$data['email'] = I('post.email');
			$data['province'] = I('post.province');
			$data['address'] = I('post.address');
			$data['xuexi'] = serialize(array_filter($_POST['xuexi']));
			$data['jiege_address'] = I('post.jiege_address');
			$data['jiege_name'] = I('post.jiege_name');
			$data['xuetong_address'] = I('post.xuetong_address');
			$data['xuetong_name'] = I('post.xuetong_name');
			$membersMod->save($data);
			$where =  array();
			$where['id'] = $uid;
			$result = $membersMod->where($where)->find();
			$result['xuexi'] = unserialize($result['xuexi']);
			session('user',$result);
			$this->success('个人信息修改成功');exit;
		}
		$this->assign($this->user);
		$this->display();
    }
	// 站内信
    public function znxx() {
		$this->display();
    }
}