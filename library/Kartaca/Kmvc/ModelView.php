<?php

namespace Kartaca\Kmvc;

use \Kartaca\Kmvc\ModelView\RenderType as RenderType;

class ModelView
{
    /**
     * Render Type for the view.
     *
     * @var RenderType
     */
    protected $_renderType;
    
    /**
     * Location of the views...
     *
     * @var string
     **/
    protected $_viewFile;
    
    /**
     * Arguments to pass to the view layer
     *
     * @var array
     **/
    protected $_args = array();
    
    /**
     * Constructor
     *
     * @param string $view view name relative to the views folder.
     */
    public function __construct($path, RenderType $renderType = null)
    {
        if (null === $renderType) {
            $this->_renderType = RenderType::FULL;
        }
        $this->_viewFile = $path;
    }
    
    /**
     * Sets the render type
     *
     * @param RenderType $rt 
     * @return ModelView
     */
    public function setRenderType($rt)
    {
        $this->_renderType = $rt;
        return $this;
    }
    
    /**
     * Retuns the render type
     *
     * @return RenderType
     */
    public function getRenderType()
    {
        return $this->_renderType;
    }
    
    /**
     * Sets the render type to None
     *
     * @return ModelView
     */
    public function setNoRender()
    {
        $this->_renderType = RenderType::NONE;
        return $this;
    }
    
    /**
     * Sets the render type to None
     *
     * @return ModelView
     */
    public function setPartialRender()
    {
        $this->_renderType = RenderType::PARTIAL;
        return $this;
    }
    
    /**
     * Adds an element to the arguments...
     *
     * @param string $key key used in the map. Avoid sending a different type...
     * @param mixed $val Value for the key, it can be anything.
     * @return ModelView
     */
    public function add($key, $val)
    {
        if (!is_string($key)) {
            throw new \Exception("Key can only be a string value");
        }
        $this->_args[$key] = $val;
        return $this;
    }
    /**
     * Finds the correct template and renders the phtml based on RenderType
     * Based on different render types, the output of this method will vary:
     *  RenderType::NONE: It returns null and nothing is outputted except the output given in the action
     *  RenderType::JSON: It returns null and outputs a Json string based on the arguments added in the action
     *  RenderType::PARTIAL: It returns null and outputs a rendered HTML
     *  RenderType::FULL: It returns the rendered HTML which will be wrapped by the layout
     *  RenderType::CRAWLER: It returns the rendered HTML which will be wrapped by the layout.
     *   Different from the FULL, It returns the full HTML regardless of PARTIAL settings in the action
     *
     * @return mixed null if the output will not be wrapped with layout or string if the layout will wrap it.
     */
    public function render()
    {
        $_content = null;
        $_rt = $this->_renderType;
        if ($_rt === RenderType::NONE) {
            return null;
        }
        if ($_rt == RenderType::JSON) {
            echo json_encode($this->_args);
            return null;
        }
        if (in_array($_rt, array(RenderType::FULL, RenderType::PARTIAL, RenderType::CRAWLER))) {
            //Now Assign arguments as variables...
            foreach ($this->_args as $key => $val) {
                $$key = $val;
            }
            ob_start();
            require($this->_viewFile);
            $_content = ob_get_clean();
        }
        if ($_rt === RenderType::PARTIAL) {
            echo $_content;
            return null;
        }
        return $_content;
    }
}