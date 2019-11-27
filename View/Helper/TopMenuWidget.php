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

class Aninda_View_Helper_TopMenuWidget extends Zend_View_Helper_Abstract
{
    public function topMenuWidget($params = array()) {
        // check for menu?
        if(!isset($params['menu']) || empty($params['menu'])){
            return null;
        }

        // get table
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        $table = $helper->getTable("menuitems");

        // get page body identity
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $identity = $request->getModuleName() . '_' .
                $request->getControllerName() . '_' .
                $request->getActionName();

        // get menus
        $notShow = array();
        if(isset($params['notshow']) && count($params['notshow'])>0){$notShow = $params['notshow'];}
        $navigation = $table->getMenus($params['menu'], $notShow);

        // call partial
        return $this->view->partial(
            'helper-scripts/_topMenuWidget.phtml',
            array('navigation' => $navigation, 'identity' => $identity, 'params' => $params)
        );
    }
}
