<?php

//Unsilence the line below, to enable namespaces for this module...
//namespace Kmfoo;

use \Kartaca\Kmvc\Controller as Controller;
use \Kartaca\Kmvc\ModelView\RenderType as RenderType;

class IndexController extends Controller
{    
    public function indexAction()
    {
        $this->_view->setRenderType(RenderType::JSON);
        $_node = node_load(1);
        $this->_view->add("node", $_node);
    }
}