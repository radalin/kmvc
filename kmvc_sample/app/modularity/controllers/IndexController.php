<?php

namespace Ks\Modularity;

use \Kartaca\Kmvc\Controller as Controller;

/**
 * Modularity module's IndexController.
 *  Try drupal/modularity_index/ or drupal/modularity_index/index to view this module...
 *
 * @package default
 */
class IndexController extends Controller
{
    public function indexAction()
    {
        $this->_view->add("message", "Hello From Modularity Module!");
    }
}