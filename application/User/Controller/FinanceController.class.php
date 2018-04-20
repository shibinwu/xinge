<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class FinanceController extends MemberbaseController {
	
	function _initialize(){
		parent::_initialize();
	}
	
    // 财务首页
    public function index() {
		$this->assign($this->user);
    	$this->display();
    }
    // 保障金认证
    public function safeauth() {
		$this->display();
    }
	// 保证金申退
    public function safeback() {
		$this->display();
    }
	// 付款登记
    public function paymentrecord() {
		$this->display();
    }
}