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

class Aninda_View_Helper_SiteHeaderWidget extends Zend_View_Helper_Abstract
{
    public function siteHeaderWidget($params = array()) {
        // get table
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        $table = $helper->getTable("menuitems");


        // get page body identity
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $identity = $request->getModuleName() . '_' .
                $request->getControllerName() . '_' .
                $request->getActionName();

        // get menus
        $viewer = Zend_Controller_Action_HelperBroker::getStaticHelper('User')->getViewer();
        if($viewer){
            $menuName = array('mini_menu_common', "mini_menu_{$viewer->getUserType()}");
        }else{
            $menuName = array('mini_menu');
        }
        $navigation = $table->getMenus(array('menu' => $menuName));

        // call partial
        return $this->view->partial(
            'helper-scripts/_siteHeaderWidget.phtml',
            array('navigation' => $navigation, 'identity' => $identity)
        );
    }
}
