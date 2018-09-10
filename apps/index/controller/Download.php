<?php
namespace app\index\controller;

use app\index\controller\Base;

class Download extends Base
{
	
	
	
    public function indexAction()
    {
    	$this->assign("page_title", "下载中心");
        return $this -> fetch();
    }
    
    
}
