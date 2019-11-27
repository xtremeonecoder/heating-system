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

class AuthController extends Zend_Controller_Action
{
    public function init() {
        // implement meta data
        $this->view->layoutType = 2;
        $identity = "{$this->_getParam('module')}_{$this->_getParam('controller')}_{$this->_getParam('action')}";
        $pageInfo = $this->_helper->getHelper('Page')->getPage($identity);
        if($pageInfo && $pageInfo->page_id){
            $this->view->headMeta()->setName('keywords', $pageInfo->getMetaKeys());
            $this->view->headMeta()->setName('description', $pageInfo->getMetaDesc());
            $this->view->layoutType = (int) $pageInfo->getLayout();
        }
    }

    public function loginAction()
    {
        // check user loggedin?
        if(Zend_Auth::getInstance()->hasIdentity()){
            // get viewer
            $viewer = $this->_helper->getHelper('User')->getViewer();
            if($viewer->isModerator() || $viewer->isAdmin() || $viewer->isSuperAdmin()){
                return $this->_helper->redirector->gotoRoute(array(), 'admin_dashboard', true);
            }else{
                return $this->_helper->redirector->gotoRoute(array(), 'user_dashboard', true);
            }
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Member Login');

//        // implement meta
//        $identity = "{$this->_getParam('module')}_{$this->_getParam('controller')}_{$this->_getParam('action')}";
//        $pageInfo = $this->_helper->getHelper('Page')->getPage($identity);
//        if($pageInfo AND $pageInfo->page_id){
//            $this->view->headMeta()->setName('keywords', $pageInfo->getMetaKeys());
//            $this->view->headMeta()->setName('description', $pageInfo->getMetaDesc());
//        }
        
        // render login form
        $this->view->form = $form
                = new Application_Form_Login();

        // Check form posted
        if(!$this->getRequest()->isPost()){
            return;
        }

        // Check form valid
        if(!$form->isValid($this->getRequest()->getPost())){
            return;
        }

        // get auth and set credentials
        $db = $this->_getParam('db');
        $auth = Zend_Auth::getInstance();
        $table = $this->_helper
                    ->getHelper('DbTable')->getTable("users");
        $authAdapter = new Zend_Auth_Adapter_DbTable($db);
        $authAdapter->setTableName($table->info('name'))
                ->setIdentityColumn('email')
                ->setCredentialColumn('password')
                ->setCredentialTreatment('MD5(CONCAT(?, salt))');

        //get form values
        $values = $form->getValues();

        // delete captcha image
        $this->deleteCaptchaImages();

        // Set the input credential values
        $authAdapter->setIdentity($values['email']);
        $authAdapter->setCredential($values['password']);

        // Perform the authentication query, saving the result
        $result = $auth->authenticate($authAdapter);
        if($result->isValid()){
            $data = $authAdapter->getResultRowObject(null, 'password');
            $auth->getStorage()->write($data);

            // update last login
            $db = $table->getAdapter();
            $db->beginTransaction();

            try{
                $user = $table->fetchRow(array('user_id = ?' => (int) $data->user_id));
                $user->lastlogin = @date('Y-m-d H:i:s', time());
                $user->save();

                // Commit
                $db->commit();
            }catch(Exception $e){
                $db->rollBack();
                throw $e;
            }

            // redirect to admin area
            if($user->isModerator() || $user->isAdmin() || $user->isSuperAdmin()){
                return $this->_helper->redirector
                        ->gotoRoute(array(), 'admin_dashboard', true);
            }else{
                // redirect to user area
                return $this->_helper->redirector
                        ->gotoRoute(array(), 'user_dashboard', true);
            }
        }else{
            $form->getElement('email')->addError(
                $this->view->translate('Email or password does not matched!')
            );
        }
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        return $this->_helper->redirector
                ->gotoRoute(array(), 'member_login', true);
    }

    public function registerAction()
    {
        // check user loggedin?
        if(Zend_Auth::getInstance()->hasIdentity()){
            // get viewer
            $viewer = $this->_helper->getHelper('User')->getViewer();
            if($viewer->isModerator() || 
                    $viewer->isAdmin() 
                    || $viewer->isSuperAdmin()){
                return $this->_helper->redirector
                        ->gotoRoute(array(), 'admin_dashboard', true);
            }else{
                return $this->_helper->redirector
                        ->gotoRoute(array(), 'user_dashboard', true);
            }
        }

        // get data
        $preLoadData = array();
        $processSuccess = false;
        $isSave = $this->_getParam('save', false);
        $formClass = $viewScript = $dataVar = $finishedVar = $registrationStep = false;
        $session = new Zend_Session_Namespace('MEMBER_REGISTER');
        
        // evaluate form sequence
        if(!isset($session->captchaFinished) || empty($session->captchaFinished)){
            $preLoadData = array();
            $registrationStep = 'captcha';
        }elseif(!isset($session->emailFinished) || empty($session->emailFinished)){
            $preLoadData = array();
            $registrationStep = 'email';
        }elseif(!isset($session->contactFinished) || empty($session->contactFinished)){
            $preLoadData = array();
            $registrationStep = 'contact';
        }elseif(!isset($session->paymentFinished) || empty($session->paymentFinished)){
            $preLoadData = array();
            $registrationStep = 'payment';
        }

        // data for layout
        $this->_helper->layout()->getView()
                ->headTitle(ucwords($registrationStep).' Info');

        // evaluate step variables
        if($registrationStep){
            $dataVar = "{$registrationStep}Data";
            $finishedVar = "{$registrationStep}Finished";
            $viewScript = "auth/register/{$registrationStep}.phtml";
            $formClass = 'Application_Form_Register_'.  ucwords($registrationStep);
        }

        if($formClass){
            // load form
            $this->view->form = $form = new $formClass();

            // load preload data
            if(count($preLoadData)>0 && $dataVar){
                foreach($preLoadData as $key => $value){
                    $form->getElement($key)
                            ->setValue($session->$dataVar[$value]);
                }
            }

            // load data from session for edit
            if(!$this->getRequest()->isPost()){
                if(isset($session->$dataVar) 
                        && count($session->$dataVar)>0){
                    $form->populate($session->$dataVar);
                }
            }

            // Check form posted
            if($this->getRequest()->isPost() &&
                    $form->isValid($this->getRequest()->getPost())){

                // get form values
                $values = $form->getValues();

                // if user email exists?
                if($registrationStep == 'email'){
                    $emailValue = trim($values['email']);
                    $dbTableHelper = $this->_helper->getHelper('DbTable');
                    $userTable = $dbTableHelper->getTable("users");
                    $user = $userTable->fetchRow(array('email = ?' => $emailValue));
                    if(isset($user->user_id) && !empty($user->user_id)){
                        $session->unsetAll();
                        $key = $user->sendResetPasswordLink();
                        return $this->_helper->redirector
                                ->gotoRoute(array(), 'account_activation', true);
                    }
                }

                // delete captcha image
                if($registrationStep == 'captcha'){
                    $this->deleteCaptchaImages();
                }

                // assigning to session
                if($session){
                    $session->$finishedVar = true;
                    $session->$dataVar = $values;

                    // load the same url to complete other steps
                    if($registrationStep == 'payment'){
                        return $this->_helper->redirector
                                ->gotoRoute(array('save' => 'save'), 'member_signup', true);
                    }else{
                        return $this->_helper->redirector
                                ->gotoRoute(array(), 'member_signup', true);
                    }
                }
            }
        }elseif($isSave && $isSave == 'save'){
            // first check all the steps completed or not?
            if(!isset($session->captchaFinished) 
                    || !isset($session->emailFinished) 
                    || !isset($session->contactFinished)
                    || !isset($session->paymentFinished)){
                return $this->_helper->redirector
                        ->gotoRoute(array(), 'member_signup', true);
            }

            // first add company info and contact data
            // prepare address and other data
            $address = array();
            $values = $session->contactData;
            if(!empty($values['address1'])){
                $address[] = $values['address1'];
            }if(!empty($values['address2'])){
                $address[] = $values['address2'];
            }if(!empty($values['address3'])){
                $address[] = $values['address3'];
            }if(!empty($values['address4'])){
                $address[] = $values['address4'];
            }
            
            // prepare user address and ip address
            $values['address'] = implode(' ', $address);
            $remoteAddress = $this->getRequest()->getServer('REMOTE_ADDR');
            $values['ipaddress'] = !empty($remoteAddress)
                        ? $remoteAddress : $_SERVER['REMOTE_ADDR'];

            // get lat-lng from address
            $latLngAddr = !empty($values['address']) ? "{$values['address']}, " : "";
            $latLngAddr .= "{$values['city_name']}, {$values['postcode']}, {$values['country_name']}";
            $latLng = $this->_helper
                    ->getHelper('LatLng')
                    ->getLatLng($latLngAddr);
            $values['latitude'] = $latLng['lat'];
            $values['longitude'] = $latLng['lng'];

            // start transaction
            $dbTableHelper = $this->_helper->getHelper('DbTable');
            $userTable = $dbTableHelper->getTable("users");
            $db = $userTable->getAdapter();
            $db->beginTransaction();

            try{
                // prepare user data
                $values = array_merge($values, $session->emailData);
                $salt = mt_rand(100000, 999999);
                $values['salt'] = $salt;
                $values['level'] = 4;
                $values['active'] = 0;
                $values['technician'] = 0;
                $values['creation_date'] = @date('Y-m-d H:i:s', time());
                $values['modified_date'] = @date('Y-m-d H:i:s', time());
                $values['password'] = md5("{$values['password']}{$salt}");
                
                // create user                
                $user = $userTable->createRow();
                $user->setFromArray($values);
                $user->save();

                // can add payment terms info?
                $values = $session->paymentData;
                $values['enabled'] = 1;
                $values['user_id'] = $user->user_id;
                $values['creation_date'] = @date('Y-m-d H:i:s', time());
                $values['modified_date'] = @date('Y-m-d H:i:s', time());
                $psTable = $dbTableHelper->getTable("paymentsettings");
                $payment = $psTable->createRow();
                $payment->setFromArray($values);
                $payment->save();

                // Commit
                $db->commit();

                // remove session
                $session->unsetAll();

                // send confirmation and activation email
                $user->sendActiveAccountLink();
                $processSuccess = true;
            }catch(Exception $e){
                $db->rollBack();
                throw $e;
            }
        }

        // load custom view
        if($viewScript){
            return $this->renderScript($viewScript);
        }

        // redirect to signup
        if(!$processSuccess){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_signup', true);
        }
    }

    public function activationAction(){

    }

    public function forgotAction()
    {
        // check user loggedin?
        $this->view->success = false;
        if(Zend_Auth::getInstance()->hasIdentity()){
            // get viewer
            $viewer = $this->_helper->getHelper('User')->getViewer();
            if($viewer->isModerator() || $viewer->isAdmin() || $viewer->isSuperAdmin()){
                return $this->_helper->redirector
                        ->gotoRoute(array(), 'admin_dashboard', true);
            }else{
                return $this->_helper->redirector
                        ->gotoRoute(array(), 'user_dashboard', true);
            }
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Forgot Password');

//        // implement meta
//        $identity = "{$this->_getParam('module')}_{$this->_getParam('controller')}_{$this->_getParam('action')}";
//        $pageInfo = $this->_helper->getHelper('Page')->getPage($identity);
//        if($pageInfo AND $pageInfo->page_id){
//            $this->view->headMeta()->setName('keywords', $pageInfo->getMetaKeys());
//            $this->view->headMeta()->setName('description', $pageInfo->getMetaDesc());
//        }
        
        // render login form
        $this->view->form = $form
                = new Application_Form_ForgotPassword();

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
        
        // delete captcha image
        $this->deleteCaptchaImages();

        // update reset key
        $table = $this->_helper
                ->getHelper('DbTable')->getTable("users");
        $db = $table->getAdapter();
        $db->beginTransaction();

        try{
            $user = $table->fetchRow(array(
                'email = ?' => trim($values['email'])
            ));
            $user->sendResetPasswordLink();
            $user->save();

            // Commit
            $db->commit();
            $this->view->success = true;
        }catch(Exception $e){
            $db->rollBack();
            throw $e;
        }
    }
    
    public function resetAction(){
        // check key valid or not?
        $this->view->success = false;
        $resetKey = $this->_getParam('key', false);
        if(!$resetKey){
            return $this->_forward('error', 'error', 'default', array());
        }

        // get user by reset key
        $dbTableHelper = $this->_helper->getHelper('DbTable');
        $userTable = $dbTableHelper->getTable("users");
        $user = $userTable->fetchRow(array('resetkey = ?' => $resetKey));

        // check user exists?
        if(!isset($user->user_id) || empty($user->user_id)){
            return $this->_forward('error', 'error', 'default', array());
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Reset Account Password');

//        // implement meta
//        $identity = "{$this->_getParam('module')}_{$this->_getParam('controller')}_{$this->_getParam('action')}";
//        $pageInfo = $this->_helper->getHelper('Page')->getPage($identity);
//        if($pageInfo && $pageInfo->page_id){
//            $this->view->headMeta()->setName('keywords', $pageInfo->getMetaKeys());
//            $this->view->headMeta()->setName('description', $pageInfo->getMetaDesc());
//        }

        // render login form
        $this->view->form = $form
                = new Application_Form_Reset();

        // Check form posted
        if(!$this->getRequest()->isPost()){
            return;
        }

        // Check form valid
        if(!$form->isValid($this->getRequest()->getPost())){
            return;
        }

        // update new password
        $password = $this->getRequest()->getPost('password');
        $db = $userTable->getAdapter();
        $db->beginTransaction();

        try{
            $salt = trim(mt_rand(100000, 999999));
            $user->active = 1;
            $user->resetkey = '';
            $user->salt = $salt;
            $passwd = "{$password}{$salt}";
            $user->password = md5($passwd);
            $user->modified_date = @date('Y-m-d H:i:s', time());
            $user->save();

            // send email
            $user->sendResetPasswordNotification();

            // Commit
            $db->commit();
            $this->view->success = true;
        }catch(Exception $e){
            $db->rollBack();
            throw $e;
        }
    }

    public function activateAction(){
        // check key valid or not?
        $activeKey = $this->_getParam('key', false);
        if(!$activeKey){
            return $this->_forward('error', 'error', 'default', array());
        }

        // get user by active key
        $dbTableHelper = $this->_helper->getHelper('DbTable');
        $userTable = $dbTableHelper->getTable("users");
        $user = $userTable->fetchRow(array('activekey = ?' => $activeKey));

        // check user exists?
        if(!isset($user->user_id) || empty($user->user_id)){
            return $this->_forward('error', 'error', 'default', array());
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Member Account Activation');

        // update active key
        $db = $userTable->getAdapter();
        $db->beginTransaction();

        try{
            $user->active = 1;
            $user->activekey = '';
            $user->modified_date = @date('Y-m-d H:i:s', time());
            $user->save();

            // send mail
            $user->sendActivateAccountNotification();

            // Commit
            $db->commit();
        }catch(Exception $e){
            $db->rollBack();
            throw $e;
        }
    }

    // Delete all unnecessary captcha images
    public function deleteCaptchaImages()
    {
        // Delete captcha images
        $captchaDir = realpath(APPLICATION_PATH . '/../public/captcha');
        if($handle = opendir($captchaDir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && stripos($file, '.png')) {
                    $filePath = $captchaDir . '/' . $file;
                    if(stripos($file, '.png') AND is_file($filePath)){
                        @chmod($filePath, 0777);
                        @unlink($filePath);
                    }
                }
            }
            closedir($handle);
        }
    }

}
