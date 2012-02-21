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
     * Constructor
     *
     */
    public function __construct($viewFile)
    {
        $this->_view = new ModelView($viewFile);
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