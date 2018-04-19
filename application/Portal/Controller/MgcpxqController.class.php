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
class MgcpxqController extends HomebaseController {

    //当前使用语言 常量  LANG_SET
    protected $yaopin_model;
    protected $article_model;
    function _initialize()
    {
        parent::_initialize();
        $this->yaopin_model = M("Yaopin");
        $this->article_model = M("Article");
    }

	public function index() {
        $id = I('get.id');
		$class_id = I('get.class_id');
        $where = $listWhere = array();
        $where['id'] = $id;
	    $data = $this->yaopin_model->where($where)->find();
        $this->assign('data',$data);
		//访问量加1
		$this->yaopin_model->where($where)->setInc('hits',1);
		//鸽子列表
		$listWhere['class_id'] = $class_id;
	    $list = $this->yaopin_model->where($listWhere)->order('addtime DESC')->select();
		foreach($list as $val){
			unset($val['id']);
			$val['class_id'] = 2;
			$this->yaopin_model->add($val);
			$val['class_id'] = 3;
			$this->yaopin_model->add($val);
		}
        $this->assign('list',$list);
		$this->assign('id',$id);
		//上一个
		$listWhere['id'] = array('GT',$id);
		$update = $list = $this->yaopin_model->where($listWhere)->order('addtime DESC')->find();
		$this->assign('update',$update);
		//下一个
		$listWhere['id'] = array('LT',$id);
		$downdate = $list = $this->yaopin_model->where($listWhere)->order('addtime DESC')->find();
		$this->assign('downdate',$downdate);
		
    	$this->display(":mgcpxq");
    }
}


