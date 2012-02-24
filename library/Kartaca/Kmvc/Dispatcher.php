<?php

namespace Kartaca\Kmvc;

use Kartaca\Kmvc\ModelView\RenderType as RenderType;

/**
 * Dispatcher that finds the correct route, creates the controllers and actions
 *  calls the action and prints the ModelView.
 */
class Dispatcher
{
    protected static $_appPrefixes = array();
    
    /**
     * Application path where the Controller directory exists.
     *
     * @var string
     */
    protected $_appPath;
    
    /**
     * Default Namespace if nothing given it's \ by default that is none
     *
     * @var string
     */
    protected $_defaultNamespace;
    
    /**
     * Prefix for the urls.
     *
     * @var string
     **/
    protected $_appPrefix;
    
    /**
     * Constructor
     *
     * @param string $appPath Application Path
     * @param string $defaultNamespace default namespace for the project, it's \ by default
     */
    public function __construct($options)
    {
        if (isset($options["appPath"])) {
            $this->_appPath = $options["appPath"];
        } else {
            throw new \Exception("App Path should be defined!");
        }
        if (isset($options["defaultNamespace"])) {
            $this->_defaultNamespace = $options["defaultNamespace"];
        } else {
            throw new \Exception("Default Namespace should be defined!");
        }
        $this->_appPrefix = "";
        if (isset($options["appPrefix"]) && !empty($options["appPrefix"])) {
            $this->_appPrefix = $options["appPrefix"];
            self::$_appPrefixes[] = $this->_appPrefix;
        }
    }
    
    /**
     * Returns application path
     *
     * @return string
     */
    public function getAppPath()
    {
        return $this->_appPath;
    }
    
    /**
     * Returns the defaultnamespace
     *
     * @return string
     */
    public function getDefaultNamespace()
    {
        return $this->_defaultNamespace;
    }
    
    /**
     * Checks if a given route exists or not...
     *  Returns true or false based on the existence of the route
     *
     * @param string $route URL where the page is called.
     * @param string $appPrefix Application prefix URL
     * @return boolean true if both controller and action exists
     */
    public function routeExists($route)
    {
        list($_controllerName, $_actionName) = $this->getControllerAndAction($route, $this->_defaultNamespace);
        return class_exists($_controllerName) && method_exists($_controllerName, $_actionName);
    }
    
    /**
     * Returns controller and action as an array.
     *  First being the controller class name and second being the action method name
     *
     * @param string $route URL where the page is called
     * @param string $defaultNamespace default namespace for the project
     * @param array $returnOptions associative array containing module, controller and action names
     * @return array
     */
    public static function getControllerAndAction($route, $defaultNamespace, $returnOptions = false)
    {
        $_dispatch = preg_split("/\//", $route);
        $_dispatch = array_map(function($_item) {
            return strtolower($_item);
        }, $_dispatch);
        if (in_array($_dispatch[1], self::$_appPrefixes)) {
            $_dispatch = array_slice($_dispatch, 1);
        }
        $_moduleName = "";
        $_controllerName = $_dispatch[1];
        $_actionName = "index";
        if (isset($_dispatch[2]) && null !== $_dispatch[2]) {
            $_actionName = $_dispatch[2];
        }
        if (preg_match("/_/", $_controllerName)) {
            list($_moduleName, $_controllerName) = preg_split("/_/", $_controllerName, 2);
        }
        $_fullActionName = strtolower($_actionName) . "Action";
        $_fullControllerName = $defaultNamespace
            . "\\"
            . ($_moduleName !== "" ? ucfirst($_moduleName) . "\\" : "" )
            . ucfirst($_controllerName)
            . "Controller";
        if (class_exists($_fullControllerName) && method_exists($_fullControllerName, $_fullActionName)) {
            $_result = array($_fullControllerName, $_fullActionName);
            if ($returnOptions) {
                $_result[] = array(
                    "module" => $_moduleName,
                    "controller" => $_controllerName,
                    "action" => $_actionName,
                );
            }
            return $_result;
        }
        return array("", "");
    }
    
    /**
     * Dispatches the request to the Controller's Action!
     *  Currently it just returns
     *
     * @param string $route URL that we are looking for something like music_index/index or index/index
     * @param string $options Options for the app
     * @return mixed rendered HTML if the content will be wrapped by the layout, null if layout is will not be wrapped
     */
    public static function dispatch($route, $options = null)
    {
        list($_controllerName, $_actionName, $_options) = self::getControllerAndAction($route, $options["defaultNamespace"], true);
        $_filePath = $options["appPath"];
        if (isset($_options["module"]) && !empty($_options["module"])) {
            $_filePath .= "/" . $_options["module"];
        }
        $_filePath .= "/views/%s/%s.phtml";
        $_params = array(
            "filePath" => $_filePath,
            "module" => $_options["module"],
            "controller" => $_options["controller"],
            "action" => $_options["action"],
        );
        $_controller = new $_controllerName($_params);
        $_controller->$_actionName();
        if ($_controller->getView()->getRenderType() === RenderType::NONE) {
            return null;
        }
        //Check the render and intercept it if required...
        if (isset($_REQUEST["_f"]) && $_REQUEST["_f"] === "json") {
            $_controller->getView()->setRenderType(RenderType::JSON);
        } else if (isset($_REQUEST["_escaped_fragment_"])) {
            //TODO: This part might require a proper handling in the Drupal part so this might be a bit problematic...
            $_controller->getView()->setRenderType(RenderType::CRAWLER);
        }
        $_content = $_controller->getView()->render(); 
        if (null === $_content) {
            //If content is not passed then return null to intercept creation of the layout...
            return null;
        }
        return $_content;
    }
}