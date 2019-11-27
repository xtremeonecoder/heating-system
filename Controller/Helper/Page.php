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

class Helper_Page extends Zend_Controller_Action_Helper_Abstract
{
    public function getPage($identity = false){
        // check identity?
        if(!$identity){ return null; }

        // get loggedin user
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        $table = $helper->getTable("pages");
        return $table->fetchRow(array('name = ?' => $identity, 'enabled = ?' => 1));
    }
}
