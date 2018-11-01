<?php
namespace app\admin\controller;

use think\Controller;

class Index extends Base
{
    public function indexAction()
    {

        
        //判断是否登录
        if(!$this->checkLogin()) {
        	return $this -> redirect('index/login');
        }



        
    }


    public function loginAction()
    {
        if($this -> checkLogin())
            return $this->redirect('index');

        $this ->assign("page_title", '后台登录');

        if(!input('?post.id') && !input('?post.pw'))
            return $this -> fetch();
        else if (!empty(input('post.id')) && !empty(input('post.pw'))) {
            $query = \think\Db::query('select * from ss_user_admin left JOIN user on (ss_user_admin.uid = user.uid) where user_name = "' . input('post.id') . '"')[0];  //防注入


            //$query = isset($query[0])?$query[0]:'';

            if(!isset($query) || empty($query) || $query['pass'] != $this -> ssp_secret(input('post.pw')))
                return $this -> error('账号或密码错误', 'login',-1, 2);


            session('username', input('post.id'));
            //将所有查询改为ssp_session
            session('ssp_session', $this -> ssp_secret(input('post.id') . $this -> ssp_secret($_POST['pw'])));

            return $this->success('欢迎回来，' . input('post.id'), 'index');
        }
        else if (empty(input('post.id')) || empty(input('post.pw'))) {
            return $this->error('账号或密码错误', 'login', -1, 2);
        }


    }

    public function logoutAction()
    {
        if($this->checkLogin()){
            session('ssp_session', null);
            session('username', null);
            return $this -> success('登出成功！', 'login',0 , 2);
        }
        else
            return $this->redirect('login');

    }
}
