<?php
namespace app\admin\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        
        //判断是否登录
        if(true) {
        	return $this -> redirect('index/login');
        }
        
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
