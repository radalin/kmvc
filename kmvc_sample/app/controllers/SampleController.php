<?php

namespace Ks;

use \Kartaca\Kmvc\Controller as Controller;
use \Kartaca\Kmvc\ModelView\RenderType as RenderType;

class SampleController extends Controller
{
    /**
     * Index action for the sample controller
     *  Try drupal/sample/index to view this Action...
     *
     * @return void
     * @author roy simkes
     */
    public function indexAction()
    {
        $_node = node_load(1);
        drupal_set_title("");
        $this->_view->add("node", $_node);
    }
    
    public function postAction()
    {
        $_who = "World";
        if (isset($this->getParams()->who)) {
            $_who = $this->getParams()->who;
        }
        $this->_view->add("who", $_who);
    }
    
    public function viewEditAction()
    {
        $this->_view->setTemplate("sample/post");
        $this->_view->add("who", "Viewer");
    }
    
    public function noviewAction()
    {
        $this->setNoRender();
        //$this->_view->setNoRender(); //This one also does the same thing...
        echo "Hello World!";
    }
    
    public function jsonAction()
    {
        $this->_view->setRenderType(RenderType::JSON);
        $_node = node_load(1);
        $this->_view->add("node", $_node);
    }
}