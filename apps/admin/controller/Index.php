<?php
namespace app\admin\controller;

use think\Controller;

class Index extends Controller
{
    public function indexAction()
    {
        
        //判断是否登录
        if(true) {
        	return $this -> redirect('index/login');
        }
        
    }
    
    public function loginAction()
    {
    	$this ->assign("page_title", "后台登录");
        return $this -> fetch();
    }
    
    public function logoutAction()
    {
        return $this -> fetch();
    }
}
