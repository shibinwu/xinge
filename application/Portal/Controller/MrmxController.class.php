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
class MrmxController extends HomebaseController {

    //当前使用语言 常量  LANG_SET
    protected $year_model;
    protected $article_model;
    protected $ozzb_model;
    protected $yzzb_model;
    function _initialize()
    {
        parent::_initialize();
        $this->year_model = M("Year");
        $this->article_model = M("Article");
        $this->ozzb_model = M("Ozzb");
        $this->yzzb_model = M("Yzzb");
    }
    //鸽闻中心首页面
	public function index() {
	    $data = $this->year_model->order('yname desc')->select();
	    $mgzx = $this->article_model->where(array('cid' => 0))->select();
	    $this->assign('mgzx',$mgzx);
	    foreach($data as $key => $vol){
	        if($vol['type'] == 0){
	            $yozzb[$vol['id']] = $vol['yname'];
            }elseif($vol['type'] == 1){
	            $yyzzb[$vol['id']] = $vol['yname'];
            }
        }
        $this->assign('yyzzb',$yyzzb);
        $this->assign('yozzb',$yozzb);
    	$this->display(":mrmx");
    }
    //铭鸽资讯列表显示
    public function mgzx() {
        $mgzx = $this->article_model->where(array('cid' => 0))->select();
        foreach ($mgzx as $key => $vol){
            $mgzx[$key]['time'] = date("Y-m-d",$vol['created_date']);
        }
        if($mgzx){
            $mgz['code'] = 1;
            $mgz['message'] = '成功';
            $mgz['data'] = $mgzx;
        }
        $this->ajaxReturn ($mgz);

    }
    //名人铭系列表显示
    public function mrmx() {
        $mgzx = $this->article_model->where(array('cid' => 1))->select();
        foreach ($mgzx as $key => $vol){
            $mgzx[$key]['time'] = date("Y-m-d",$vol['created_date']);
        }
        if($mgzx){
            $mgz['code'] = 1;
            $mgz['message'] = '成功';
            $mgz['data'] = $mgzx;
        }
        $this->ajaxReturn ($mgz);
    }
    //铭鸽资讯和名人铭系详情展示
    public function mrmgxq(){
        $id = I('get.id');
       $map = $this->article_model->where(array('id'=> $id))->find();
       $map['time'] = date("Y-m-d",$map['created_date']);
       if($map){
           $mrmgxq['code'] = 1;
           $mrmgxq['message'] = '成功';
           $mrmgxq['data'] = $map;
       }
       $this->ajaxReturn($mrmgxq);
    }
    //欧洲战报列表页面
    public function ozzb() {
        $id = I('get.id');
        if($id){
            $mgzx = $this->ozzb_model->where(array('yid' => $id))->select();
        }else{
            $yname = $this->year_model->where(array('type' => 0))->order('yname desc')->getField('id');
            $mgzx = $this->ozzb_model->where(array('yid' => $yname))->select();
        }
        foreach ($mgzx as $key => $vol){
            $mgzx[$key]['jige'] = date("d/m",$vol['jige']);
            $mgzx[$key]['fangfei'] = date("d/m",$vol['fangfei']);
        }
        if($mgzx){
            $mgz['code'] = 1;
            $mgz['message'] = '成功';
            $mgz['data'] = $mgzx;
        }
        $this->ajaxReturn ($mgz);
    }

    public function yzzb() {
        $id = I('get.id');
        if($id){
            $mgzx = $this->yzzb_model->where(array('yid' => $id))->select();
        }else{
            $yname = $this->year_model->where(array('type' => 1))->order('yname desc')->getField('id');
            $mgzx = $this->yzzb_model->where(array('yid' => $yname))->select();
        }
        if($mgzx){
            $mgz['code'] = 1;
            $mgz['message'] = '成功';
            $mgz['data'] = $mgzx;
        }
        $this->ajaxReturn ($mgz);
    }
}


