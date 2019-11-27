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

class AdminController extends Zend_Controller_Action
{
    public function init()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // get viewer
        $viewer = $this->_helper->getHelper('User')->getViewer();
        if($viewer && !$viewer->isModerator() 
                && !$viewer->isAdmin() 
                && !$viewer->isSuperAdmin()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'user_dashboard', true);
        }
        
        // specify layout type
        $this->view->layoutType = 2;
    }

    public function indexAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        
        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Admin Panel');
        
        // get vessel data from database
        $table = $this->_helper->getHelper('DbTable')->getTable("settings");
        $dataResult = array();
        
        // get port data
        $itemPerPage = 50;
        $paginator = Zend_Paginator::factory($dataResult);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $paginator->setItemCountPerPage($itemPerPage);
        $this->view->paginator = $paginator;
        $this->view->itemPerPage = $itemPerPage;
    }    
}
