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
    
    
}
