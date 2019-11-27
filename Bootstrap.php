<?php
/**
 * Heating Support System
 *
 * @category   Application_Core
 * @package    heating-system
 * @author     Suman Barua
 * @developer  Suman Barua <sumanbarua576@gmail.com>
 */

/**
 * @category   Application_Core
 * @package    heating-system
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initIniSet(){
        if(get_magic_quotes_gpc() === 1){
            $_GET = json_decode(stripslashes(json_encode($_GET, JSON_HEX_APOS)), true);
            $_POST = json_decode(stripslashes(json_encode($_POST, JSON_HEX_APOS)), true);
            $_COOKIE = json_decode(stripslashes(json_encode($_COOKIE, JSON_HEX_APOS)), true);
            $_REQUEST = json_decode(stripslashes(json_encode($_REQUEST, JSON_HEX_APOS)), true);
        }
    }

    protected function _initControllerHelpers(){
        // set action helper path
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/Controller/Helper', 'Helper');
    }

    // title init
    protected function _initTitle(){
        $view = $this->bootstrap('view')->getResource('view');
        $view->headTitle('Heating Support System');
    }

    protected function _initAutoload(){
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath' => APPLICATION_PATH
        ));
        $moduleLoader->addResourceType('', '../library/Aninda', 'Aninda');
        return $moduleLoader;
    }

    protected function _initRoutes(){
        $manifest = include(APPLICATION_PATH . '/configs/manifest.php');
        $frontController = Zend_Controller_Front::getInstance();
        $router = $frontController->getRouter();

        if(count($manifest)>0){
            foreach($manifest as $name => $params){
                $urlPattern = $params['route'];
                $routeDefaults = isset($params['defaults']) ? $params['defaults'] : array();
                $routeRequirements = isset($params['reqs']) ? $params['reqs'] : array();
                if(isset($params['method']) AND ($params['method'] == 'regex')){
                    $route = new Zend_Controller_Router_Route_Regex($urlPattern, $routeDefaults, $routeRequirements);
                }else{
                    $route = new Zend_Controller_Router_Route($urlPattern, $routeDefaults, $routeRequirements);
                }
                $router->addRoute($name, $route);
            }
        }
    }
    
    protected function _initPaginator(){
        // Set up default paginator options
        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial(array(
          'pagination/_scriptPagination.phtml',
          'default'
        ));
    }
    
}
