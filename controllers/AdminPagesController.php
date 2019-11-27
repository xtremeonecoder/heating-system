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

class AdminPagesController extends Zend_Controller_Action
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
                && !$viewer->isAdmin() && !$viewer->isSuperAdmin()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'user_dashboard', true);
        }
    }

    public function indexAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // redirect to browse
        return $this->_helper->redirector
                ->gotoRoute(array('action' => 'browse'), 'admin_courier_general', true);
    }

    public function browseAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Admin Page List');

        // Get DbTable
        $helper = $this->_helper->getHelper('DbTable');

        // merge values
        $values = array(
            'order' => 'page_id',
            'direction' => 'DESC'
        );

        // get companies
        $itemPerPage = 20;
        $table = $helper->getTable("pages");
        $paginator = $table->getPagePaginator($values);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $paginator->setItemCountPerPage($itemPerPage);
        $this->view->paginator =$paginator;
        $this->view->itemPerPage = $itemPerPage;
    }

    public function addAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Admin Add New Page');

        // render form
        $this->view->form = $form
                = new Application_Form_Admin_Page_Add();

        // Check form posted
        if(!$this->getRequest()->isPost()){
            return;
        }

        // Check form valid
        if(!$form->isValid($this->getRequest()->getPost())){
            return;
        }

        //get form values
        $values = $form->getValues();
        $values['enabled'] = 1;
        $values['creation_date'] = @date('Y-m-d H:i:s', time());
        $values['modified_date'] = @date('Y-m-d H:i:s', time());

        // start transaction
        $table = $this->_helper->getHelper('DbTable')->getTable("pages");
        $db = $table->getAdapter();
        $db->beginTransaction();

        try{
            $page = $table->createRow();
            $page->setFromArray($values);
            $page->save();

            // Commit
            $db->commit();

            // redirect to page list
            return $this->_helper->redirector
                    ->gotoRoute(array('action' => 'browse'), 'admin_page_general', true);
        }catch(Exception $e){
            $db->rollBack();
            $form->addError($e->getMessage());
            throw $e;
        }
    }

    public function editAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // data for layout
        $pageId = $this->_getParam('id', false);
        $this->_helper->layout()
                ->getView()->headTitle('Admin Page Edit');

        // forward to error
        if(!$pageId){
           return $this->_forward('error', 'error', 'default', array());
        }

        // render form
        $this->view->form = $form
                = new Application_Form_Admin_Page_Edit();

        // get page
        $table = $this->_helper->getHelper('DbTable')->getTable("pages");
        $page = $table->fetchRow(array('page_id = ?' => $pageId));

        // forward to error
        if(!$page OR !$page->page_id){
           return $this->_forward('error', 'error', 'default', array());
        }

        // populate form
        $form->populate($page->toArray());
        $this->view->page = $page;

        // Check form posted
        if(!$this->getRequest()->isPost()){
            return;
        }

        // Check form valid
        if(!$form->isValid($this->getRequest()->getPost())){
            return;
        }

        //get form values
        $values = $form->getValues();
        $values['modified_date'] = @date('Y-m-d H:i:s', time());

        // start transaction
        $db = $table->getAdapter();
        $db->beginTransaction();

        try{
            $page->setFromArray($values);
            $page->save();

            // Commit
            $db->commit();

            // redirect to page list
            return $this->_helper->redirector
                    ->gotoRoute(array('action' => 'browse'), 'admin_page_general', true);
        }catch(Exception $e){
            $db->rollBack();
            $form->addError($e->getMessage());
            throw $e;
        }
    }

    public function deleteAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // get ids and page id
        $pg = $this->_getParam('pg', false);
        $pageIds = $this->_getParam('id', false);
        $pageIds = explode(',', $pageIds);

        // check page ids
        if(count($pageIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("pages");
            $pages = $table->fetchAll(array('page_id IN (?)' => $pageIds));
            if(count($pages)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    foreach($pages as $page){
                        $page->delete();
                    }

                    // Commit
                    $db->commit();
                }catch(Exception $e){
                    $db->rollBack();
                    throw $e;
                }
            }
        }

        // redirect to page list
        if($pg > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('action' => 'browse', 'page' => $pg), 'admin_page_general', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array('action' => 'browse'), 'admin_page_general', true);
    }
}
