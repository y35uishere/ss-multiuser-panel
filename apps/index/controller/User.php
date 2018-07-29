<?php
namespace app\index\controller;

use app\index\controller\Base;

class User extends Base
{
	
	
    public function index()
    {
        
        return $this -> fetch();
    }
    
    public function login()
    {
    	if(session('?id')) {
    		return $this->redirect('user/index');	
    	}
    	
        if(!isset($_POST['id']) && !isset($_POST['pw'])) {
        	return $this -> fetch();
        }
        else if (!empty($_POST['id']) && !empty($_POST['pw'])) {
        	session('id', 'thinkphp');
        	return $this->redirect('user/index');
        }
        else if (empty($_POST['id']) || empty($_POST['pw'])) {
        	$this->error('账号或密码不能为空', 'user/login');
        }
        
        
    }
    
    public function register()
    {
		
		return $this -> fetch();
	}
	
	public function logout()
	{
		session('id', null);
		$this->success('登出成功', 'login');
		
		
	}
}
