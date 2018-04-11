<?php
namespace News\Controller;

use Common\Controller\AdminbaseController;

class NewsadminController extends AdminbaseController
{
    //当前使用语言 常量  LANG_SET 
    protected $article_model;
    protected $year_model;
    protected $ozzb_model;
    protected $ozzbxq_model;
    protected $label_model;
    protected $yzzb_model;
    protected $category_model;

    function _initialize()
    {
        parent::_initialize();
        $this->article_model = M("Article");
        $this->year_model = M("Year");
        $this->ozzb_model = M("Ozzb");
        $this->ozzbxq_model = M("Ozzbxq");
        $this->label_model = M("Label");
        $this->yzzb_model = M("Yzzb");
        $this->category_model = M("Category");

    }
    // 后台年份添加
    public function yearadd()
    {
        if (IS_POST) {
            $article = I("post.post");
            $result = $this->year_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $this->display();
    }
    // 后台年份列表
    public function yearindex()
    {
        $where = array();
        $where['l'] = LANG_SET;
        $count = $this->year_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->year_model
            ->where($where)
            ->order("yname DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    // 后台年份编辑
    public function yearedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $article = I("post.post");
            $result = $this->year_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this->year_model->where($where)->find();
        $this->assign('post', $info);
        $this->display();
    }
    // 后台新闻列表
    public function newsindex()
    {
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

        $count = $this->article_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->article_model
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $arr = array();
        foreach ($list as $k => $val) {

            $list[$k]['column'] = $this->category_model->where(array('id' => $val['cid']))->getfield('name');
            $arr[] = $this->category_model->where(array('id' => $val['cid']))->getfield('name');
        }
        $arrs = array_unique($arr);

        $this->assign('list', $list);
        $this->assign('arr', $arrs);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }



    // 后台新闻添加
    public function newsadd()
    {
        if (IS_POST) {
            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");
            $id = $_POST['post']['cid'];
            $article['cat_name'] = $this->category_model->where("id = '$id'")->getField('name');
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->article_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $info = $this->category_model->where('pid = 0 AND channelid =1')->getField('id,name');
        $this->assign('info',$info);
        $this->display();
    }

    // 后台新闻编辑
    public function newsedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['pics'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['country'] = implode(",",array_filter(array($_POST['post']['country1'],$_POST['post']['country2'],$_POST['post']['country3'])));
//            $img = new \Think\Image(); //实例化
//            $img->open($_POST['post']['tpic']); //打开被处理的图片
//            $img->thumb(100,100); //制作缩略图(100*100)
//            $img->save($smallimg_path); //保存缩略图到服务器
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            if(!isset($article[tuijian])){
                $article[tuijian] = 0;
            }elseif(!isset($article[zhiding])){
                $article[zhiding] = 0;
            }elseif(!isset($article[working])){
                $article[working] = 0;
            }
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->article_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this->article_model->where($where)->find();
        $data = $this-> category_model ->where('pid = 0 AND channelid =1')->getField('id,name');

        $this->assign('post', $info);
        $this->assign('data', $data);
        $this->display();
    }

    // 后台新闻删除
    public function newsdelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->article_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->article_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }
    //后台铭鸽咨询列表
    public function mgzxindex()
    {
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
        $where['cid']= 0;
        $count = $this->article_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->article_model
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $arr = array();
        foreach ($list as $k => $val) {

            $list[$k]['column'] = $this->category_model->where(array('id' => $val['cid']))->getfield('name');
            $arr[] = $this->category_model->where(array('id' => $val['cid']))->getfield('name');
        }
        $arrs = array_unique($arr);

        $this->assign('list', $list);
        $this->assign('arr', $arrs);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }
    // 后台铭鸽咨询添加
    public function mgzxadd()
    {
        if (IS_POST) {
            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");
            $article['created_date'] = time();
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->article_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $this->display();
    }

    // 后台铭鸽咨询编辑
    public function mgzxedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['pics'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['country'] = implode(",",array_filter(array($_POST['post']['country1'],$_POST['post']['country2'],$_POST['post']['country3'])));
//            $img = new \Think\Image(); //实例化
//            $img->open($_POST['post']['tpic']); //打开被处理的图片
//            $img->thumb(100,100); //制作缩略图(100*100)
//            $img->save($smallimg_path); //保存缩略图到服务器
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            if(!isset($article[tuijian])){
                $article[tuijian] = 0;
            }elseif(!isset($article[zhiding])){
                $article[zhiding] = 0;
            }elseif(!isset($article[working])){
                $article[working] = 0;
            }
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->article_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this->article_model->where($where)->find();
        $data = $this-> category_model ->where('pid = 0 AND channelid =1')->getField('id,name');

        $this->assign('post', $info);
        $this->assign('data', $data);
        $this->display();
    }

    // 后台铭鸽咨询删除
    public function mgzxdelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->article_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->article_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }
    //后台名人铭系列表
    public function mrmxindex()
    {
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
        $where['cid'] = 1;
        $count = $this->article_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->article_model
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $arr = array();
        foreach ($list as $k => $val) {

            $list[$k]['column'] = $this->category_model->where(array('id' => $val['cid']))->getfield('name');
            $arr[] = $this->category_model->where(array('id' => $val['cid']))->getfield('name');
        }
        $arrs = array_unique($arr);

        $this->assign('list', $list);
        $this->assign('arr', $arrs);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }
    // 后台名人铭系添加
    public function mrmxadd()
    {
        if (IS_POST) {
            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");
            $article['created_date'] = time();
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->article_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $this->display();
    }

    // 后台名人铭系编辑
    public function mgmxedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['pics'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['country'] = implode(",",array_filter(array($_POST['post']['country1'],$_POST['post']['country2'],$_POST['post']['country3'])));
//            $img = new \Think\Image(); //实例化
//            $img->open($_POST['post']['tpic']); //打开被处理的图片
//            $img->thumb(100,100); //制作缩略图(100*100)
//            $img->save($smallimg_path); //保存缩略图到服务器
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            if(!isset($article[tuijian])){
                $article[tuijian] = 0;
            }elseif(!isset($article[zhiding])){
                $article[zhiding] = 0;
            }elseif(!isset($article[working])){
                $article[working] = 0;
            }
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->article_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this->article_model->where($where)->find();
        $data = $this-> category_model ->where('pid = 0 AND channelid =1')->getField('id,name');

        $this->assign('post', $info);
        $this->assign('data', $data);
        $this->display();
    }

    // 后台名人铭系删除
    public function mrmxdelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->article_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->article_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }
    //后台欧洲战报列表
    public function ozzbindex()
    {
        $where = array();
        $where['l'] = LANG_SET;
        $count = $this->ozzb_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->ozzb_model
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }
    //后台欧洲战报添加
    public function ozzbadd()
    {
        if (IS_POST) {
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");
            //把时间转换成时间戳
            $article['jige'] = strtotime($article['jige']);
            $article['fangfei'] = strtotime($article['fangfei']);
            if($article['bid']){
                $article['bid'] =  implode(',',$article['bid']);
            }
            $result = $this->ozzb_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $data = $this->year_model->where(array('type'=>0))->order('yname desc')->getField('id,yname',true);
        $arr = $this->label_model->where(array('type'=>1))->getField('id,name');
        $this->assign('data',$data);
        $this->assign('arr',$arr);
        $this->display();
    }

    // 后台欧洲战报编辑
    public function ozzbedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $article = I("post.post");
            //把时间转换成时间戳
            $article['jige'] = strtotime($article['jige']);
            $article['fangfei'] = strtotime($article['fangfei']);
            $result = $this->ozzb_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this->ozzb_model->where($where)->find();
        $data = $this->year_model->where(array('type'=>0))->order('yname desc')->getField('id,yname',true);
        $arr = $this->label_model->where(array('type'=>1))->getField('id,name');

        $this->assign('post', $info);
        $this->assign('data', $data);
        $this->assign('arr', $arr);
        $this->display();
    }

    // 后台欧洲战报删除
    public function ozzbdelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->ozzb_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->ozzb_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    //后台欧洲战报列表
    public function ozzbxqindex()
    {
        $where = array();
        $where['l'] = LANG_SET;
        $count = $this->ozzbxq_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->ozzbxq_model
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $key => $val){
            $list[$key]['name'] = $this->ozzb_model->where(array('id' => $val['sid']))->getField('name');
        }

        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    //后台欧洲战报详情添加
    public function ozzbxqadd()
    {
        if (IS_POST) {
            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $article['addtime'] = time();

            $result = $this->ozzbxq_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $data = $this->ozzb_model->getField('id,name',true);

        $this->assign('data',$data);
        $this->display();
    }

    public function yzzbindex()
    {
        $where = array();
        $where['l'] = LANG_SET;
        $count = $this->yzzb_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->yzzb_model
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }
    //后台亚洲战报添加
    public function yzzbadd()
    {
        if (IS_POST) {
            $_POST['post']['pic'] = implode(',',$_POST['photos_url']);
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $article['addtime'] = time();
            if($article['bid']){
                $article['bid'] =  implode(',',$article['bid']);
            }

            $result = $this->yzzb_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $data = $this->year_model->where(array('type'=>1))->order('yname desc')->getField('id,yname',true);
        $arr = $this->label_model->where(array('type'=>1))->getField('id,name');
        $this->assign('data',$data);
        $this->assign('arr',$arr);
        $this->display();
    }
}
