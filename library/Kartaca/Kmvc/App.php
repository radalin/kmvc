<?php

namespace Kartaca\Kmvc;

class App
{       
    /**
     * Contains information if it's definitions are done once or not...
     * @var boolean
     */
    private static $_instance = null;

    /**
     * Private Constructor to prevent initialization
     *
     * @author roy simkes
     */
    private function __construct()
    {
        $this->bootstrap();
    }

    /**
     * GetInstance method for the singleton pattern
     *
     * @return void
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Adds another app to the kmvc module...
     *
     * @param array $options options for the current app...
     * @return App
     */
    public function addApp($options = null)
    {
        if (!isset($options["appName"]) || empty($options["appName"])) {
            throw new \Exception("A unique name should be defined");
        }
        $options = array_merge(
            array(
                "appPrefix" => "",
                "defaultNamespace" => "",
                "appPath" => "",
                "setIncludePath" => true,
            ),
            $options
        );
        if (empty($options["appPath"])) {
            $options["appPath"] = $this->_findAppPath();
        }
        if ($options["setIncludePath"]) {
            set_include_path(implode(PATH_SEPARATOR, array(
                    get_include_path(),
                    $options["appPath"]
                )
            ));
        }
        $_dispatcher = new Dispatcher($options);
        $this->_initRouter($_dispatcher, $options);
        return $this;
    }

    /**
     * Bootstraps the Kmvc Application.
     *
     * @return void
     */
    protected function bootstrap($options = null)
    {
        $this->_initConstants();
        $this->_initIncludePath();
        $this->_initAutoloader();
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
                //If no namespace or module defined, don't check here...
                if ($_splitCount > 1) {
                    $_filePath = lcfirst($_splitted[$_splitCount - 2])
                        . "/controllers/"
                        . $_splitted[$_splitCount - 1]
                        . ".php";
                    if (stream_resolve_include_path($_filePath) !== false) {
                        require_once($_filePath);
                    }
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
     * Initializes the app include path, using debug_backtrace...
     *
     * TODO: This was not a very clean solution, I'm sure it will work pretty well,
     *  but again,... debug_backtrace...
     *
     * @throws Exception if no app path is found.
     * @return void
     **/
    protected function _findAppPath()
    {
        $_callStack = debug_backtrace();
        for ($_i = 0, $_counter = count($_callStack); $_i < $_counter; $_i++) {
            if (isset($_callStack[$_i]["file"])) {
                //if the file name ends with .module then we found the folder path...
                if (1 >= preg_match("/\.module$/", $_callStack[$_i]["file"])) {
                    $_appPath = realpath(dirname($_callStack[$_i]["file"]) . "/app");
                    if (false !== $_appPath) {
                        return $_appPath;
                    }
                }
            }
        }
        throw new \Exception("App Path cannot be found. Please specify it during initialization.");
    }
    
    /**
     * Initializes application's router on Drupal.
     *  This creates a last hook for the requests before 404 errors.
     *  It dynamically creates a route if the application can dispatch to the url.
     *
     * TODO: Document the _escaped_fragment_ logic here...
     * @return void
     */
    protected function _initRouter($dispatcher, $options)
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
                $menu_item['page_arguments'] = array($loc, $options);
                menu_set_item(preg_replace("/\//", "", $loc, 1), $menu_item);
            }
        }
    }
    
    /**
     * Menu Hook initializer for Drupal...
     *
     * @return void
     */
    public function initMenuHook()
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