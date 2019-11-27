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

class Aninda_View_Helper_MainMenuWidget extends Zend_View_Helper_Abstract
{
    public function mainMenuWidget($params = array()) {
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
        $menuName = array('core_main');
        if($viewer && isset($viewer->user_id)){
            $menuName = array("core_main_{$viewer->getUserType()}");
        }        
        $notShow = array();
        if(isset($params['notshow']) && count($params['notshow'])>0){$notShow = $params['notshow'];}
        $navigation = $table->getMenus(array('menu' => $menuName), $notShow);

        // call partial
        return $this->view->partial(
            'helper-scripts/_mainMenuWidget.phtml',
            array('navigation' => $navigation, 'identity' => $identity)
        );
    }
}
