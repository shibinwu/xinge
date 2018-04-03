<?php
namespace Label\Controller;

use Common\Controller\AdminbaseController;

class LabeladminController extends AdminbaseController
{
    //当前使用语言 常量  LANG_SET 
    protected $label_model;
    protected $category_model;
    function _initialize()
    {
        parent::_initialize();
        $this->label_model = M("Label");
        $this->category_model = M("Category");
    }
    // 后台标签列表
    public function index()
    {
        $where = array();
        $request = I('request.');

        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if($request['term'] == 1 && !empty($request['keyword'])){
            $where['type'] = 1;
            $keyword = $request['keyword'];
            $where['name'] = array('like', "%$keyword%");
        }elseif($request['term'] == 2 && !empty($request['keyword'])){
            $where['type'] = 2;
            $keyword = $request['keyword'];
            $where['name'] = array('like', "%$keyword%");
        }elseif(empty($request['term']) && !empty($request['keyword'])){
            $keyword = $request['keyword'];
            $where['name'] = array('like', "%$keyword%");
        }elseif($request['term'] == 2 && empty($request['keyword'])){
            $where['type'] = 2;
        }elseif($request['term'] == 1 && empty($request['keyword'])){
            $where['type'] = 1;
        }

        $where['l'] = LANG_SET;

        $count = $this->label_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->label_model
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

    // 后台标签添加
    public function add()
    {
        if (IS_POST) {
            $_POST['post']['created_by'] = get_current_admin_id();
            $label = I("post.post");
            $result = $this->label_model->add($label);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $this->display();
    }

    // 后台标签编辑
    public function edit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            unset($_POST['post']['post_author']);
            $label = I("post.post");
            $result = $this->label_model->save($label);
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
        $info = $this->label_model->where($where)->find();
        $this->assign('post', $info);
        $this->display();
    }

    // 后台标签删除
    public function delete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->label_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->label_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }
}
