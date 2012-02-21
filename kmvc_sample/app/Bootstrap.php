<?php

namespace Ks;

class Bootstrap
{   
    public static function bootstrap()
    {
        self::_initConstants();
        self::_initIncludePath();
        $_bootstrapper = new \Kartaca\Kmvc\Bootstrap();
        $_bootstrapper->bootstrap(array(
            "appPath" => KS_APP_PATH,
            "defaultNamespace" => KS_NAMESPACE,
            "appName" => "kmvc-sample",
        ));
    }
    
    /**
     * Initializes constants on the app..
     * You don't need to initialize these, but well it can be good.
     *
     * @return void
     */
    protected static function _initConstants()
    {
        define("KS_APP_PATH", realpath(dirname(__FILE__)));
        define("KS_NAMESPACE", "Ks");
    }
    
    /**
     * Defining APP_PATH is important for the autoloading to work well enough.
     *
     * @return void
     * @author roy simkes
     */
    protected static function _initIncludePath()
    {
        set_include_path(implode(PATH_SEPARATOR, array(
                KS_APP_PATH,
                realpath(KS_APP_PATH . "/../library"),
                get_include_path(),
            )
        ));
    }
    
    /**
     * Menu Hook initializer for Drupal...
     * Again you don't have to, but it might be better to move them here...
     *
     * @return void
     * @author roy simkes
     **/
    public static function initMenuHook()
    {
        $_items = array();
        return $_items;
    }
}