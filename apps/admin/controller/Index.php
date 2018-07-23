<?php
namespace app\admin\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        
        //判断是否登录
        if(true) {
        	return $this -> redirect('admin/login');
        }
        
    }
}
