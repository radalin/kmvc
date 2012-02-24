<?php

namespace Kartaca\Kmvc;

//TODO: Perhaps I should name this as app? I don't know it yet...
class Bootstrap
{       
    /**
     * Options for the bootstrapper.
     * Currently there is no need for this, but what if I change this as an App?
     *
     * @var string
     */ 
    private $_options = array();
    
    /**
     * Contains information if it's definitions are done once or not...
     * @var boolean
     */
    private static $_definedOnce = false;
    
    /**
     * Bootstraps the Gnc Application.
     *
     * @return void
     */
    public function bootstrap($options = null)
    {
        $this->_options = array_merge(
            array(
                "appPrefix" => "",
                //TODO: There is a problem with defaultNamespace...
                "defaultNamespace" => "\\",
            ),
            $options
        );
        if (!self::$_definedOnce) {
            $this->_initConstants();
            $this->_initIncludePath();
            $this->_initAutoloader();
            self::$_definedOnce = true;
        }
        if (null !== $options) {
            $_dispatcher = new Dispatcher($this->_options);
            $this->_initRouter($this->_options["appName"], $this->_options["appPrefix"], $_dispatcher);
        }
    }
    
    /**
     * Autoloader for library classes and controllers
     *
     * @return void
     */
    protected function _initAutoloader()
    {
        spl_autoload_register(function($className) {
            $_filePath = preg_replace("/\\\/", "/", $className) . ".php";
            if (stream_resolve_include_path($_filePath) !== false) {
                require_once $_filePath;
            } else if (preg_match("/Controller$/", $className)) {
                $_splitted = preg_split("/\\\/", $className);
                $_splitCount = count($_splitted);
                $_filePath = "controllers/"
                    . $_splitted[$_splitCount - 1]
                    . ".php";
                if (stream_resolve_include_path($_filePath) !== false) {
                    require_once("$_filePath");
                }
                $_filePath = lcfirst($_splitted[$_splitCount - 2])
                    . "/controllers/"
                    . $_splitted[$_splitCount - 1]
                    . ".php";
                
                if (stream_resolve_include_path($_filePath) !== false) {
                    require_once($_filePath);
                }
            }
        });
    }
    
    /**
     * Initializes constants on the app..
     *
     * @return void
     */
    protected function _initConstants()
    {
        define("KMVC_MODULE_PATH", realpath(dirname(__FILE__) . '/../../..'));
        define("KMVC_NAMESPACE", "Kartaca\Kmvc");
    }
    
    /**
     * Initializes the include path
     *
     * @return void
     */
    protected function _initIncludePath()
    {
        set_include_path(implode(PATH_SEPARATOR, array(
                get_include_path(),
                KMVC_MODULE_PATH . "/library",
            )
        ));
    }
    
    /**
     * Initializes application's router on Drupal.
     *  This creates a last hook for the requests before 404 errors.
     *  It dynamically creates a route if the application can dispatch to the url.
     *
     * TODO: AppName should be used for multiple modules to use the same kmvc module...
     * TODO: Document the _escaped_fragment_ logic here...
     * @return void
     */
    protected function _initRouter($appName, $appPrefix, $dispatcher)
    {
        if (isset($_REQUEST["_escaped_fragment_"])) {
            $_fragment = $_REQUEST["_escaped_fragment_"];
            //strip the first and trailing slashes...
            if (substr($_fragment, -1) === "/") {
                $_fragment = substr($_fragment, 0, strlen($_fragment) - 1);
            }
            //Add first slash
            if (substr($_fragment, 0, 1) === "/") {
                $_fragment = substr($_fragment, -1 * (strlen($_fragment) - 1));
            }
            $_REQUEST["q"] = $_fragment;
            $_GET["q"] = $_fragment;
        }
        $item = menu_get_item();
        if (!$item) {
            global $base_url;
            if (isset($_REQUEST["q"])) {
                $loc = $_REQUEST["q"];
            } else { //try the clean urls...
                //FIXME: Look out for hard coded http...
                $loc = preg_replace("/" . preg_replace("/\//", '\/', $base_url) . "/", "", "http://" . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'], 1);
                list($loc) = explode("?", $loc);
            }
            //Strip of the last slash...
            if (substr($loc, -1) === "/") {
                $loc = substr($loc, 0, strlen($loc) - 1);
            }
            //Add first slash
            if (substr($loc, 0, 1) !== "/") {
                $loc = "/" . $loc;
            }
            if ($dispatcher->routeExists($loc)) {
                $menu_item = menu_get_item('krtc/dispatcher');
                $menu_item['page_arguments'] = array($loc, $this->_options);
                menu_set_item(preg_replace("/\//", "", $loc, 1), $menu_item);
            }
        }
    }
    
    /**
     * Menu Hook initializer for Drupal...
     *
     * @return void
     */
    public static function initMenuHook()
    {
        $_items['krtc/dispatcher'] = array(
           'title' => 'kmvc',
           'page callback' => "\Kartaca\Kmvc\Dispatcher::dispatch",
           'page arguments' => array("", 'index/index'),
           'access callback' => TRUE,
           'type' => MENU_CALLBACK,
         );
         return $_items;
    }
}