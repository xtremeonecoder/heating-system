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

class UserSettingsController extends Zend_Controller_Action
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
        if($viewer && !$viewer->isUser()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'admin_dashboard', true);
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
        
        // remove technician's menus
        if($viewer && !$viewer->isTechnician()){
            $this->view->notShowMain = array('core_main_user_task_list');                    
        }
    }

    public function indexAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector->gotoRoute(array(), 'member_login', true);
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Member Dashboard');

//        // implement meta
//        $identity = "{$this->_getParam('module')}_{$this->_getParam('controller')}_{$this->_getParam('action')}";
//        $pageInfo = $this->_helper->getHelper('Page')->getPage($identity);
//        if($pageInfo && $pageInfo->page_id){
//            $this->view->headMeta()->setName('keywords', $pageInfo->getMetaKeys());
//            $this->view->headMeta()->setName('description', $pageInfo->getMetaDesc());
//        }

        //$viewer = $this->_helper->getHelper('User')->getViewer();
    }
    
    public function changePasswordAction()
    {
        // check user loggedin?
        $this->view->success = false;
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // get user
        $member = $this->_helper
                ->getHelper('User')->getViewer();
        
        // check user exists?
        if(!$member || !$member->user_id){
            return $this->_forward('error', 'error', 'default', array());
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Change Account Password');

//        // implement meta
//        $identity = "{$this->_getParam('module')}_{$this->_getParam('controller')}_{$this->_getParam('action')}";
//        $pageInfo = $this->_helper->getHelper('Page')->getPage($identity);
//        if($pageInfo && $pageInfo->page_id){
//            $this->view->headMeta()->setName('keywords', $pageInfo->getMetaKeys());
//            $this->view->headMeta()->setName('description', $pageInfo->getMetaDesc());
//        }

        // render login form
        $this->view->form = $form
                = new Application_Form_User_ChangePassword();

        // Check form posted
        if(!$this->getRequest()->isPost()){
            return;
        }

        // Check form valid
        if(!$form->isValid($this->getRequest()->getPost())){
            return;
        }

        // get form values
        $values = $form->getValues();
        $currentPassword = md5("{$values['curpass']}{$member->salt}");

        // check current password?
        if($currentPassword !== $member->password){
            return $form->getElement('curpass')->addError(
                $this->view->translate('Current password does not matched!')
            );
        }
        
        // update new password
        // get auth and set credentials        
        $dbTableHelper = $this->_helper->getHelper('DbTable');
        $userTable = $dbTableHelper->getTable("users");        
        $password = $values['password'];
        $db = $userTable->getAdapter();
        $db->beginTransaction();

        try{
            $salt = trim(mt_rand(100000, 999999));
            $member->active = 1;
            $member->resetkey = '';
            $member->salt = $salt;
            $passwd = "{$password}{$salt}";
            $member->password = md5($passwd);
            $member->modified_date = @date('Y-m-d H:i:s', time());
            $member->save();

            // send email
            $member->sendResetPasswordNotification();

            // Commit
            $db->commit();
            $this->view->success = true;
        }catch(Exception $e){
            $db->rollBack();
            throw $e;
        }        
    }
}
