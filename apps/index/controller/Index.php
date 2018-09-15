<?php
namespace app\index\controller;

use app\index\controller\Base;

class Index extends Base
{
	
	
	
    public function indexAction()
    {
    	$this->assign("page_title", "首页");
        return $this -> fetch();
    }
    
    public function downloadAction()
    {
    	if(!$this -> checkLogin()) 
    		return $this->redirect('user/login');
    		
    	$this->assign("page_title", "下载中心");
        return $this -> fetch();
    }
    
    
}
