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
class MgpmController extends HomebaseController {
    //当前使用语言 常量  LANG_SET
    protected $pmzt_model;
    protected $pmproduct_model;
    protected $pmjilu_model;
    function _initialize()
    {
        parent::_initialize();
        $this->pmzt_model = M("Pmzt");
        $this->pmproduct_model = M("Pmproduct");
        $this->pmjilu_model = M("Pmjilu");
    }
	public function index() {
        $where = array();
        $request = I('request.');
        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title'] = array('like', "%$keyword%");
        }
        $where['l'] = LANG_SET;
        $count = $this->pmzt_model->where($where)->count();
        $page = $this->page($count, 8);
        $list = $this->pmzt_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->field('id,adduser,seq,tname,start_time,end_time,cn_show,zhaiyao,tuijian,addtime')
            ->select();
        foreach ($list as $k => $val) {
            $list[$k]['nums'] = $this->pmproduct_model->where(array('cid' => $val['id']))->count();
            //倒计时
            $remain_time = $val['end_time'] - time(); //剩余的秒数
            $remain_hours = floor($remain_time/(60*60)); //剩余的小时
            $remain_hour = sprintf("%02d",$remain_hours); //剩余的小时
            $remain_minutes = floor(($remain_time - $remain_hour*60*60)/60); //剩余的分钟数
            $remain_minute = sprintf("%02d",$remain_minutes); //剩余的分钟数
            $remain_seconds = ($remain_time - $remain_hour*60*60 - $remain_minute*60); //剩余的秒数
            $remain_second=sprintf("%02d",$remain_seconds);
            $list[$k]['daojishi'] = $remain_hour.':'.$remain_minute.':'.$remain_second;

        }

        $where['l'] = LANG_SET;
        //即将上线的条件
        $temp = time();
        $where['start_time'] = array('gt',"$temp");
        $count = $this->pmzt_model->where($where)->count();
        $page = $this->page($count, 3);
        $data = $this->pmzt_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->field('id,tname,start_time,end_time,cn_show,zhaiyao,tuijian,addtime')
            ->select();
        foreach ($data as $k => $val) {
            $data[$k]['nums'] = $this->pmproduct_model->where(array('cid' => $val['id']))->count();
        }
        $this->assign('list', $list);
        $this->assign('data', $data);
        $this->assign("page", $page->show('Admin'));

    	$this->display(":mgpm");
    }
}

