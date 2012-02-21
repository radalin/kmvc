<?php

namespace Kartaca\Kmvc;

class Controller
{
    protected $_renderView = true;
    
    /**
     * View Object
     *
     * @var \Kartaca\Kmvc\ModelView
     */
    protected $_view;
    
    /**
     * A StdClass containing query params
     *
     * @var StdClass
     */
    protected $_params = null;
    
    /**
     * Constructor
     *
     */
    public function __construct($params)
    {
        $this->_view = new ModelView($params);
    }
    
    /**
     * Returns a boolean showing if the resulting output will be HTML or not
     *
     * @return boolean
     */
    public function willRender()
    {
        return $_view->willRender();
    }
    
    /**
     * Returns parameter StdClass
     *
     * @return StdClass
     * @author roy simkes
     **/
    public function getParams()
    {
        if (null === $this->_params) {
            $this->_params = new \StdClass();
            foreach ($_REQUEST as $_key => $_val) {
                $this->_params->$_key = $_val;
            }
        }
        return $this->_params;
    }
    
    /**
     * Set the rendertype to discard any html wrapper
     *
     * @return boolean
     */
    public function setNoRender()
    {
        $this->_view->setRenderType(ModelView\RenderType::NONE);
    }
    
    /**
     * Returns the view object...
     *
     * @return ModelView
     */
    public function getView()
    {
        return $this->_view;
    }
}