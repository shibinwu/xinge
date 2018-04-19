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
		$this->display();
    }
	// 修改个人信息
    public function xggrxx() {
		$this->assign($this->user);
		$this->display();
    }
	// 站内信
    public function znxx() {
		$this->display();
    }
}