<?php
namespace app\index\controller;

use app\index\controller\Base;

class User extends Base
{
	
	
    public function index()
    {
        $this -> assign("page_title", "个人中心");
        return $this -> fetch();
    }
    
    public function login()
    {
    	$this -> assign("page_title", "用户登录");
    	
    	if(session('?username')) 
    		return $this->redirect('user/index');	
    	
    	
        if(!isset($_POST['id']) && !isset($_POST['pw'])) 
        	return $this -> fetch();
        	
        
        else if (!empty($_POST['id']) && !empty($_POST['pw'])) {
        	$query = db('user')->where('user_name', $_POST['id'])->select();
        	$query = $query[0];
        	
        	if(!isset($query) || empty($query) || $query['pass'] != $this -> ssp_secret($_POST['pw']))
        		return $this -> error("账号或密码错误", "login");

        	
        	
        	
        	session('username', $_POST['id']);
        	session('is_login', 1);
        	return $this->success("欢迎回来，" . $_POST['id'], 'user/index');
        }
        else if (empty($_POST['id']) || empty($_POST['pw'])) {
        	return $this->error('账号或密码不能为空', 'login');
        }
        
        
    }
    
    public function register()
    {
		$this -> assign("page_title", "用户注册");
		return $this -> fetch();
	}
	
	public function forgot()
    {
		$this -> assign("page_title", "找回密码");
		return $this -> fetch();
	}
	
	public function logout()
	{
		session('username', null);
		session('is_login', null);
		
		return $this->success('登出成功', 'login');
	}
}
