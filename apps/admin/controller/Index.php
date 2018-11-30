<?php
namespace app\admin\controller;

use think\Controller;

class Index extends Base
{
    public function indexAction()
    {
        //判断是否登录
        if(!$this->checkLogin())
        	return $this -> redirect('index/login');


        $node = db('ss_node') -> paginate(2);
        $user = db('user') -> paginate(2);

        $page_node = $node -> render();
        $page_user = $user -> render();

        //Ajax处理
        if($this->request->isAjax() && input('type') == 'node'){
            $this -> assign("node_list", $node);
            $this -> assign("page_node", $page_node);
            return $this->fetch('index/ajaxNode');
        }
        else if($this->request->isAjax() && input('type') == 'user'){
            $this -> assign("user_list", $user);
            $this -> assign("page_user", $page_user);
            return $this->fetch('index/ajaxUser');
        }


        //用户统计信息
        $info = \think\Db::query('select count(*) as user, sum(money) as money from user;')[0];
        //在线用户
        $info =array_merge($info, \think\Db::query ('select count(*) as online from user where ('.time().' - t) < 100')[0]);
        //节点数
        $info = array_merge($info, \think\Db::query('select count(*) as node from ss_node;')[0]);



        $this -> assign("node_list", $node);
        $this -> assign("user_list", $user);
        $this -> assign("page_node", $page_node);
        $this -> assign("page_user", $page_user);
        $this -> assign('user_name', cookie('username'));
        $this -> assign('info', $info);
        $this -> assign('page_title', '后台管理');
        return $this -> fetch();
    }


    public function loginAction()
    {
        if($this -> checkLogin())
            return $this->redirect('index');

        $this ->assign("page_title", '后台登录');

        $id = input('?post.id')?input('post.id'):'';
        $pw = input('?post.pw')?input('post.pw'):'';

        if(!input('?post.id') && !input('?post.pw'))
            return $this -> fetch();
        else if (!empty($id) && !empty($pw)) {
            $query = \think\Db::query('select * from ss_user_admin left JOIN user on (ss_user_admin.uid = user.uid) where user_name = "' . $id . '"')[0];  //防注入


            if(!isset($query) || empty($query) || $query['pass'] != $this -> ssp_secret($pw))
                return $this -> error('账号或密码错误', 'login',-1, 2);


            cookie('username', $id);
            //将所有查询改为ssp_session
            cookie('session', $this -> ssp_secret($id . $this -> ssp_secret($pw)));

            return $this->success('欢迎回来，' . $id, 'index');
        }
        else if (empty($id) || empty($pw)) {
            return $this->error('账号或密码错误', 'login', -1, 2);
        }


    }

    public function logoutAction()
    {
        if($this->checkLogin()){
            cookie('session', null);
            cookie('username', null);
            return $this -> success('登出成功！', 'login',0 , 2);
        }
        else
            return $this->redirect('login');

    }
}
