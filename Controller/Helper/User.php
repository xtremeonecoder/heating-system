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

class Helper_User extends Zend_Controller_Action_Helper_Abstract
{
    public function getViewer(){
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return null;
        }

        $user = Zend_Auth::getInstance()->getIdentity();
        if(empty($user->user_id)){
            return null;
        }

        // get loggedin user
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        $table = $helper->getTable("users");
        return $table->fetchRow(array('user_id = ?' => (int) $user->user_id));
    }
    
    public function getUser($userId = null){
        // check user id?        
        if(!$userId){
            return null;
        }

        // get loggedin user
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        $table = $helper->getTable("users");
        $user = $table->fetchRow(array('user_id = ?' => (int) $userId));
        
        // check user?
        if(!$user || !isset($user->user_id) || empty($user->user_id)){
            return null;
        }
        
        return $user;
    }
}
