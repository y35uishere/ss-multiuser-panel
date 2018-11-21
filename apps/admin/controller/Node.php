<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jake
 * Date: 11/21/2018
 * Time: 6:32 PM
 */

namespace app\admin\controller;
use think\Controller;

class Node extends Base
{


    public function infoAction(){
        $type = 0;

        if(!input('?id')){
            $type = 1;

        }


        //防止SQL注入
        $id = (int)input('id',-1);

        $info = db('ss_node')->where('id', $id) -> find();

        $this->assign('id', $id);
        $this->assign('type', $type);
        $this->assign('info', $info);
        $this->assign('page_title', '节点修改');
        return $this ->fetch();
    }

    public function handlerAction(){

    }
}