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

class InvoiceController extends Zend_Controller_Action
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
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('My Payment Invoices');

//        // implement meta
//        $identity = "{$this->_getParam('module')}_{$this->_getParam('controller')}_{$this->_getParam('action')}";
//        $pageInfo = $this->_helper->getHelper('Page')->getPage($identity);
//        if($pageInfo && $pageInfo->page_id){
//            $this->view->headMeta()->setName('keywords', $pageInfo->getMetaKeys());
//            $this->view->headMeta()->setName('description', $pageInfo->getMetaDesc());
//        }

        // get loggedin user
        $viewer = $this->_helper->getHelper('User')->getViewer();
        
        // filter form
        $page = $this->_getParam('page', 1);
        $this->view->formFilter = $formFilter 
                = new Application_Form_Invoice_Filter();

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
          'user_id' => $viewer->getIdentity(),
          //'enabled' => '1',
          'order' => 'modified_date',
          'direction' => 'DESC'
        ), $values);

        $this->view->assign($values);
        
        // Filter out junk
        $valuesCopy = array_filter($values);
       
        // get invoices
        $itemPerPage = 6;
        $table = $helper->getTable("invoices");
        $paginator = $table->getInvoicePaginator($values);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $paginator->setItemCountPerPage($itemPerPage);
        $this->view->paginator =$paginator;
        $this->view->formValues = $valuesCopy;
        $this->view->itemPerPage = $itemPerPage;        
    }
    
    public function paymentAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Create Support Ticket');

        // render form
        $this->view->form = $form
                = new Application_Form_Invoice_Payment();

        // check invoice id
        $invoiceId = $this->_getParam('id', false);        
        if(!$invoiceId){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'user_invoices', true);
        }
        
        // get loggedin user
        $viewer = $this->_helper->getHelper('User')->getViewer();
        
        // check invoice and owner
        $invoice = $this->_helper->getHelper('DbTable')
                ->getTable('invoices')->getInvoice($invoiceId);
        if(!$invoice || !$invoice->invoice_id || !$viewer->isOwner($invoice)){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'user_invoices', true);
        }
        
        // can make payment?
        $ticket = $invoice->getTicket();
        if(!$ticket || !$ticket->canMakePayment()){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'user_invoices', true);
        }
        
        // send to view
        $this->view->invoice = $invoice;
        
        // Check form posted
        if(!$this->getRequest()->isPost()){
            return;
        }

        // Check form valid
        if(!$form->isValid($this->getRequest()->getPost())){
            return;
        }

        // start transaction
        $table = $this->_helper->getHelper('DbTable')->getTable("invoices");
        $db = $table->getAdapter();
        $db->beginTransaction();

        try{
            // update invoice
            $invoice->status = 2;
            $invoice->modified_date = @date('Y-m-d H:i:s', time());
            $invoice->save();

            // Commit
            $db->commit();
            
            // redirect to invoice details page
            return $this->_helper->redirector
                    ->gotoRoute(array('action' => 'details', 'id' => $invoice->getIdentity()), 'user_invoices', true);
        }catch(Exception $e){
            $db->rollBack();
            $form->addError($e->getMessage());
            throw $e;
        }
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
                ->getView()->headTitle('Payment Invoice Details');

        // check invoice id
        $invoiceId = $this->_getParam('id', false);        
        if(!$invoiceId){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'user_invoices', true);
        }
        
        // get loggedin user
        $viewer = $this->_helper->getHelper('User')->getViewer();
        
        // check invoice and owner
        $invoice = $this->_helper->getHelper('DbTable')
                ->getTable('invoices')->getInvoice($invoiceId);
        if(!$invoice || !$invoice->invoice_id || !$viewer->isOwner($invoice)){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'user_invoices', true);
        }
        
        $this->view->invoice = $invoice;
    }    
}
