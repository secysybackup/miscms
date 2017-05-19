<?php

class PluginAction extends PublicAction
{

    function _initialize()
    {
        parent::_initialize();
        $this->path = './Plugin/';
        $this->db = M('Plugin');
    }

    public function index()
    {
        $this->display();
    }

    public function engines()
    {
        $this->display();
    }

    //百度地图
    public function baidumap()
    {
        if (IS_POST) {
            $baidumap['bdmap_x'] = $_POST['bdmap_x'];
            $baidumap['bdmap_y'] = $_POST['bdmap_y'];
            $baidumap['bdmap_name'] = $_POST['bdmap_name'];
            $baidumap['bdmap_address'] = $_POST['bdmap_address'];
            $baidumap['bdmap_tel'] = $_POST['bdmap_tel'];
            $data['config'] = json_encode($baidumap);

            $r = M('Plugin')->where("name='Baidumap' and lang=".LANG_ID)->save($data);

            if ($r) {
                $result = M('Plugin')->where("name='Baidumap' and lang=".LANG_ID)->find();
                F('Baidumap_'.LANG_NAME, $result);
                $this->success('提交成功！');
            } else {
                $this->error('提交失败！');
            }
        } else {
            $result = M('Plugin')->where("name='Baidumap' and lang=".LANG_ID)->find();
            $data = json_decode($result['config'], true);
            $this->assign('data', $data);
            $this->display();
        }
    }

    //主营产品
    public function mainpro()
    {
        if (IS_POST) {
            $data = array();
            foreach($_POST['product_name'] as $key=>$value){
                $data[$key]['product_name'] = $value;
                $data[$key]['product_url'] = $_POST['product_url'][$key];
            }

            $mainpro = json_encode($data);

            $r = M('Plugin')->where("name='Mainpro' and lang=".LANG_ID)->save(array('config'=>$mainpro));

            if($r){
                $result = M('Plugin')->where("name='Mainpro' and lang=".LANG_ID)->find();
                F('Mainpro_'.LANG_NAME, $result);
                $this->success('保存成功!');
            }else{
                $this->error('没有发生更改!');
            }
            exit;
        }

        $result = M('Plugin')->where("name='Mainpro' and lang=".LANG_ID)->find();
        $mainpro = json_decode($result['config'], true);
        $this->assign('mainpro', $mainpro);
        $this->display();
    }

    //热门关键词
    public function hotwords()
    {
        if (IS_POST) {
            $data = array();
            foreach($_POST['name'] as $key=>$value){
                $data[$key]['name'] = $value;
                $data[$key]['url'] = $_POST['url'][$key];
            }

            $hotwords = json_encode($data);

            $r = M('Plugin')->where("name='Hotwords' and lang=".LANG_ID)->save(array('config'=>$hotwords));

            if($r){
                $result = M('Plugin')->where("name='Hotwords' and lang=".LANG_ID)->find();
                F('Hotwords_'.LANG_NAME, $result);
                $this->success('保存成功!');
            }else{
                $this->error('没有发生更改!');
            }
            exit;
        }

        $result = M('Plugin')->where("name='Hotwords' and lang=".LANG_ID)->find();
        $hotwords = json_decode($result['config'], true);
        $this->assign('hotwords', $hotwords);
        $this->display();
    }


    public function statistics()
    {
        if (IS_POST) {

            file_put_contents('./public/statistics.html', $_POST['code']);

            $config = json_encode($_POST);

            M('Plugin')->where("name='Statistics'")->save(array('config'=>$config));

            $this->success('保存成功!');

        } else {

            $code = file_get_contents('./public/statistics.html');
            $result = M('Plugin')->where("name='Statistics'")->find();
            $data = json_decode($result['config'], true);
            $this->assign('data', $data);
            $this->assign('code', $code);

            $this->display();
        }
    }


    //b2b网站大全
    function b2b() {
        $result = M('Plugin')->where("name='b2b'")->find();
        $data = json_decode($result['config'], true);
        $this->assign('b2b', $data);
        $this->display();
    }

    function b2b_edit() {
        if (IS_POST) {
            $data = array();
            $b2b['company'] = $_POST['company'];
            $b2b['username'] = $_POST['username'];
            $b2b['email'] = $_POST['email'];
            $b2b['phone'] = $_POST['phone'];
            $b2b['password'] = $_POST['password'];
            $data['config'] = json_encode($b2b);

            $r = M('Plugin')->where("name='b2b'")->save($data);

            if($r){
                $this->success('保存成功!');
            }else{
                $this->error('没有发生更改!');
            }
            exit;
        }
        $result = M('Plugin')->where("name='b2b'")->find();
        $data = json_decode($result['config'], true);
        $this->assign('b2b', $data);
        $this->display();
    }
}