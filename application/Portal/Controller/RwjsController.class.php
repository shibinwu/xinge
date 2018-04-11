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
class RwjsController extends HomebaseController {
    //当前使用语言 常量  LANG_SET
    protected $pmzt_model;
    protected $changci_model;
    protected $pmgezi_model;
    function _initialize()
    {
        parent::_initialize();
        $this->pmzt_model = M("Pmzt");
        $this->changci_model = M("Changci");
        $this->pmgezi_model = M("Pmgezi");
    }
    //正在拍卖的专题页面显示
	public function index() {
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
    	$this->display(":rwjs");
    }
}


