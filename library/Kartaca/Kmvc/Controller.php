<?php

namespace Kartaca\Kmvc;

/**
 * Base Controller class for the controllers.
 *
 * TODO: Create an interface for this to allow different modules to implement their own Controller implementation
 */
class Controller
{
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
     * TODO: Document parameters
     *
     * @param array $params
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
     */
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