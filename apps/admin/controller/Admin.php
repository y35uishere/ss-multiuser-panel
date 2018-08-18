<?php
namespace app\admin\controller;

use think\Controller;

class Admin extends Controller
{
	public function index()
    {
    	
        return $this -> fetch();
    }
    
    public function login()
    {
    	$this ->assign("page_title", "后台登录");
        return $this -> fetch();
    }
    
    public function logout()
    {
        return $this -> fetch();
    }
}
