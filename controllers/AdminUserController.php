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

class AdminUserController extends Zend_Controller_Action
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
        $identity = "{$this->_getParam('module')}_{$this->_getParam('controller')}_{$this->_getParam('action')}";
        $pageInfo = $this->_helper->getHelper('Page')->getPage($identity);
        if($pageInfo && $pageInfo->page_id){
            $this->view->headMeta()->setName('keywords', $pageInfo->getMetaKeys());
            $this->view->headMeta()->setName('description', $pageInfo->getMetaDesc());
            $this->view->layoutType = (int) $pageInfo->getLayout();
        }
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
                ->getView()->headTitle('List of Active Members');

//        // implement meta
//        $identity = "{$this->_getParam('module')}_{$this->_getParam('controller')}_{$this->_getParam('action')}";
//        $pageInfo = $this->_helper->getHelper('Page')->getPage($identity);
//        if($pageInfo && $pageInfo->page_id){
//            $this->view->headMeta()->setName('keywords', $pageInfo->getMetaKeys());
//            $this->view->headMeta()->setName('description', $pageInfo->getMetaDesc());
//        }

        // filter form
        $page = $this->_getParam('page', 1);
        $this->view->formFilter = $formFilter 
                = new Application_Form_Admin_User_Filter();

        // Process form
        $values = array();
        $allData = $this->_getAllParams();
        $helper = $this->_helper->getHelper('DbTable');
        
        // validate
        if($formFilter->isValid($allData)){
            $values = $formFilter->getValues();
        }
        
        // unset empty value
        foreach($values as $key => $value){
          if($value == ''){
            unset($values[$key]);
          }
        }
        
        // merge values
        $values = array_merge(array(
          'level' => 4,
          'active' => 1,
          'order' => 'modified_date',
          'direction' => 'DESC'
        ), $values);

        $this->view->assign($values);
        
        // Filter out junk
        $valuesCopy = array_filter($values);
       
        // get tickets
        $itemPerPage = 5;
        $table = $helper->getTable("users");
        $paginator = $table->getUserPaginator($values);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $paginator->setItemCountPerPage($itemPerPage);
        $this->view->paginator =$paginator;
        $this->view->formValues = $valuesCopy;
        $this->view->itemPerPage = $itemPerPage;
    }
        
    public function inactiveAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('List of Inactive Members');

//        // implement meta
//        $identity = "{$this->_getParam('module')}_{$this->_getParam('controller')}_{$this->_getParam('action')}";
//        $pageInfo = $this->_helper->getHelper('Page')->getPage($identity);
//        if($pageInfo && $pageInfo->page_id){
//            $this->view->headMeta()->setName('keywords', $pageInfo->getMetaKeys());
//            $this->view->headMeta()->setName('description', $pageInfo->getMetaDesc());
//        }

        // filter form
        $page = $this->_getParam('page', 1);
        $this->view->formFilter = $formFilter 
                = new Application_Form_Admin_User_Filter();

        // Process form
        $values = array();
        $allData = $this->_getAllParams();
        $helper = $this->_helper->getHelper('DbTable');
        
        // validate
        if($formFilter->isValid($allData)){
            $values = $formFilter->getValues();
        }
        
        // unset empty value
        foreach($values as $key => $value){
          if($value == ''){
            unset($values[$key]);
          }
        }
        
        // merge values
        $values = array_merge(array(
          'level' => 4,
          'active' => 0,
          'order' => 'modified_date',
          'direction' => 'DESC'
        ), $values);

        $this->view->assign($values);
        
        // Filter out junk
        $valuesCopy = array_filter($values);
       
        // get tickets
        $itemPerPage = 5;
        $table = $helper->getTable("users");
        $paginator = $table->getUserPaginator($values);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $paginator->setItemCountPerPage($itemPerPage);
        $this->view->paginator =$paginator;
        $this->view->formValues = $valuesCopy;
        $this->view->itemPerPage = $itemPerPage;
    }
        
    public function technicianAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('List of Technicians');

//        // implement meta
//        $identity = "{$this->_getParam('module')}_{$this->_getParam('controller')}_{$this->_getParam('action')}";
//        $pageInfo = $this->_helper->getHelper('Page')->getPage($identity);
//        if($pageInfo && $pageInfo->page_id){
//            $this->view->headMeta()->setName('keywords', $pageInfo->getMetaKeys());
//            $this->view->headMeta()->setName('description', $pageInfo->getMetaDesc());
//        }

        // filter form
        $page = $this->_getParam('page', 1);
        $this->view->formFilter = $formFilter 
                = new Application_Form_Admin_User_Filter();

        // Process form
        $values = array();
        $allData = $this->_getAllParams();
        $helper = $this->_helper->getHelper('DbTable');
        
        // validate
        if($formFilter->isValid($allData)){
            $values = $formFilter->getValues();
        }
        
        // unset empty value
        foreach($values as $key => $value){
          if($value == ''){
            unset($values[$key]);
          }
        }
        
        // merge values
        $values = array_merge(array(
          'level' => 4,
          //'active' => 1,
          'technician' => 1,
          'order' => 'modified_date',
          'direction' => 'DESC'
        ), $values);

        $this->view->assign($values);
        
        // Filter out junk
        $valuesCopy = array_filter($values);
       
        // get tickets
        $itemPerPage = 5;
        $table = $helper->getTable("users");
        $paginator = $table->getUserPaginator($values);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $paginator->setItemCountPerPage($itemPerPage);
        $this->view->paginator =$paginator;
        $this->view->formValues = $valuesCopy;
        $this->view->itemPerPage = $itemPerPage;
    }
        
    public function detailsAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Member Details');

        // check ticket id
        $userId = $this->_getParam('id', false);        
        if(!$userId){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_users', true);
        }
        
        // get user
        $member = $this->_helper->getHelper('DbTable')
                ->getTable('users')->getUser($userId);
        if(!$member || !$member->user_id){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_users', true);
        }
        
        $this->view->member = $member;
    }
    
    public function activateAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        
        // get member ids and page id
        $page = $this->_getParam('pg', false);
        $memberIds = $this->_getParam('id', false);
        $memberIds = explode(',', $memberIds);
        
        // check member ids
        if(count($memberIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("users");
            $members = $table->fetchAll(array('user_id IN (?)' => $memberIds));
            if(count($members)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    $commit = false;
                    foreach($members as $member){
                        $member->active = 1;
                        $member->modified_date = @date('Y-m-d H:i:s', time());
                        $member->save();
                        $commit = true;
                    }
                    
                    // Commit
                    if($commit){
                        $db->commit();        
                    }
                }catch(Exception $e){
                    $db->rollBack();
                    throw $e;
                }                
            }
        }
        
        // redirect to ticket list
        if($page > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('action' => 'inactive', 'page' => $page), 'admin_users', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array('action' => 'inactive'), 'admin_users', true);
    }

    public function inactivateAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        
        // get member ids and page id
        $page = $this->_getParam('pg', false);
        $memberIds = $this->_getParam('id', false);
        $memberIds = explode(',', $memberIds);
        
        // check member ids
        if(count($memberIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("users");
            $members = $table->fetchAll(array('user_id IN (?)' => $memberIds));
            if(count($members)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    $commit = false;
                    foreach($members as $member){
                        $member->active = 0;
                        $member->modified_date = @date('Y-m-d H:i:s', time());
                        $member->save();
                        $commit = true;
                    }
                    
                    // Commit
                    if($commit){
                        $db->commit();        
                    }
                }catch(Exception $e){
                    $db->rollBack();
                    throw $e;
                }                
            }
        }
        
        // redirect to ticket list
        if($page > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('page' => $page), 'admin_users', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_users', true);
    }
    
    public function makeTechnicianAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        
        // get member ids and page id
        $page = $this->_getParam('pg', false);
        $memberIds = $this->_getParam('id', false);
        $memberIds = explode(',', $memberIds);
        
        // check member ids
        if(count($memberIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("users");
            $members = $table->fetchAll(array('user_id IN (?)' => $memberIds));
            if(count($members)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    $commit = false;
                    foreach($members as $member){
                        $member->technician = 1;
                        $member->modified_date = @date('Y-m-d H:i:s', time());
                        $member->save();
                        $commit = true;
                    }
                    
                    // Commit
                    if($commit){
                        $db->commit();        
                    }
                }catch(Exception $e){
                    $db->rollBack();
                    throw $e;
                }                
            }
        }
        
        // redirect to ticket list
        if($page > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('page' => $page), 'admin_users', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_users', true);
    }

    public function deleteTechnicianAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        
        // get member ids and page id
        $page = $this->_getParam('pg', false);
        $memberIds = $this->_getParam('id', false);
        $memberIds = explode(',', $memberIds);
        
        // check member ids
        if(count($memberIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("users");
            $members = $table->fetchAll(array('user_id IN (?)' => $memberIds));
            if(count($members)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    $commit = false;
                    foreach($members as $member){
                        $member->technician = 0;
                        $member->modified_date = @date('Y-m-d H:i:s', time());
                        $member->save();
                        $commit = true;
                    }
                    
                    // Commit
                    if($commit){
                        $db->commit();        
                    }
                }catch(Exception $e){
                    $db->rollBack();
                    throw $e;
                }                
            }
        }
        
        // redirect to technician list
        if($page > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('action' => 'technician', 'page' => $page), 'admin_users', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array('action' => 'technician'), 'admin_users', true);
    }
    
    public function trashAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Deleted Support Tickets in Trash');

//        // implement meta
//        $identity = "{$this->_getParam('module')}_{$this->_getParam('controller')}_{$this->_getParam('action')}";
//        $pageInfo = $this->_helper->getHelper('Page')->getPage($identity);
//        if($pageInfo && $pageInfo->page_id){
//            $this->view->headMeta()->setName('keywords', $pageInfo->getMetaKeys());
//            $this->view->headMeta()->setName('description', $pageInfo->getMetaDesc());
//        }

        // filter form
        $page = $this->_getParam('page', 1);
        $this->view->formFilter = $formFilter 
                = new Application_Form_Admin_Ticket_Filter();

        // Process form
        $values = array();
        $allData = $this->_getAllParams();
        $helper = $this->_helper->getHelper('DbTable');
        
        // validate
        if($formFilter->isValid($allData)){
            $values = $formFilter->getValues();
        }
        
        // unset empty value
        foreach($values as $key => $value){
          if($value == ''){
            unset($values[$key]);
          }
        }
        
        // merge values
        $values = array_merge(array(
          'admin_delete' => '1',
          'order' => 'modified_date',
          'direction' => 'DESC'
        ), $values);

        $this->view->assign($values);
        
        // Filter out junk
        $valuesCopy = array_filter($values);
       
        // get tickets
        $itemPerPage = 5;
        $table = $helper->getTable("tickets");
        $paginator = $table->getTicketPaginator($values);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $paginator->setItemCountPerPage($itemPerPage);
        $this->view->paginator =$paginator;
        $this->view->formValues = $valuesCopy;
        $this->view->itemPerPage = $itemPerPage;        
    }
}
