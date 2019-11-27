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

class AdminInvoiceController extends Zend_Controller_Action
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
                ->getView()->headTitle('Service Payment Invoices');

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
                = new Application_Form_Admin_Invoice_Filter();

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
          'enabled' => '1',
          'order' => 'modified_date',
          'direction' => 'DESC'
        ), $values);

        $this->view->assign($values);
        
        // Filter out junk
        $valuesCopy = array_filter($values);
       
        // get tickets
        $itemPerPage = 6;
        $table = $helper->getTable("invoices");
        $paginator = $table->getInvoicePaginator($values);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $paginator->setItemCountPerPage($itemPerPage);
        $this->view->paginator =$paginator;
        $this->view->formValues = $valuesCopy;
        $this->view->itemPerPage = $itemPerPage;
    }
    
    public function createAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Create Payment Invoice');

        // render form
        $this->view->form = $form
                = new Application_Form_Admin_Invoice_Create();

        // check ticket id
        $ticketId = $this->_getParam('id', false);        
        if(!$ticketId){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tickets', true);
        }
        
        // check schedule can be created?
        $ticket = $this->_helper->getHelper('DbTable')
                ->getTable('tickets')->getTicket($ticketId);
        if(!$ticket || !$ticket->canCreateInvoice()){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tickets', true);
        }
        
        // send to view
        $this->view->ticket = $ticket;
        
        // Check form posted
        if(!$this->getRequest()->isPost()){
            return;
        }

        // Check form valid
        if(!$form->isValid($this->getRequest()->getPost())){
            return;
        }

        // get ticket creator
        $member = $ticket->getUser();
        
        // get ticket schedule
        $schedule = $ticket->getSchedule();
        
        //get form values
        $values = $form->getValues();
        $values['status'] = 1; // payment pending
        $values['enabled'] = 1; // enabled
        $values['user_id'] = (int) $member->getIdentity();
        $values['ticket_id'] = (int) $ticket->getIdentity();
        if(isset($schedule->schedule_id) && !empty($schedule->schedule_id)){
            $values['schedule_id'] = (int) $schedule->getIdentity();
        }
        $values['creation_date'] = @date('Y-m-d H:i:s', time());
        $values['modified_date'] = @date('Y-m-d H:i:s', time());

        // start transaction
        $table = $this->_helper->getHelper('DbTable')->getTable("invoices");
        $db = $table->getAdapter();
        $db->beginTransaction();

        try{
            // create invoice
            $invoice = $table->createRow();
            $invoice->setFromArray($values);
            $invoice->save();

            // update ticket
            $ticket->invoice_id = (int) $invoice->getIdentity();
            $ticket->modified_date = @date('Y-m-d H:i:s', time());
            $ticket->save();
            
            // update schedule
            if(isset($schedule->schedule_id) && !empty($schedule->schedule_id)){
                $schedule->invoice_id = (int) $invoice->getIdentity();
                $schedule->modified_date = @date('Y-m-d H:i:s', time());
                $schedule->save();
            }
            
            // send email
            $member->notifyInvoiceToMember($ticket, $schedule, $invoice);
            
            // Commit
            $db->commit();
            
            // redirect to invoice list
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'admin_invoices', true);
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

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Edit Payment Invoice');

        // check invoice id
        $invoiceId = $this->_getParam('id', false);        
        if(!$invoiceId){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_invoices', true);
        }
        
        // check invoice can be edited?
        $invoice = $this->_helper->getHelper('DbTable')
                ->getTable('invoices')->getInvoice($invoiceId);
        if(!$invoice || !$invoice->canBeEdit()){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_invoices', true);
        }
        
        // send to view
        $this->view->invoice = $invoice;
        
        // render form
        $this->view->form = $form
                = new Application_Form_Admin_Invoice_Create();
        $form->populate($invoice->toArray());
                
        // Check form posted
        if(!$this->getRequest()->isPost()){
            return;
        }

        // Check form valid
        if(!$form->isValid($this->getRequest()->getPost())){
            return;
        }

        // get ticket
        $ticket = $invoice->getTicket();
        
        // get ticket creator
        $member = $invoice->getMember();
        
        // get ticket schedule
        $schedule = $invoice->getSchedule();
        
        //get form values
        $values = $form->getValues();
        $values['modified_date'] = @date('Y-m-d H:i:s', time());

        // start transaction
        $table = $this->_helper->getHelper('DbTable')->getTable("invoices");
        $db = $table->getAdapter();
        $db->beginTransaction();

        try{
            // edit invoice
            $invoice->setFromArray($values);
            $invoice->save();

            // update ticket
            $ticket->invoice_id = (int) $invoice->getIdentity();
            $ticket->modified_date = @date('Y-m-d H:i:s', time());
            $ticket->save();
            
            // update schedule
            if(isset($schedule->schedule_id) && !empty($schedule->schedule_id)){
                $schedule->invoice_id = (int) $invoice->getIdentity();
                $schedule->modified_date = @date('Y-m-d H:i:s', time());
                $schedule->save();
            }
            
            // send reminder email
            $member->notifyInvoiceToMember($ticket, $schedule, $invoice, true);
            
            // Commit
            $db->commit();
            
            // redirect to invoice list
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'admin_invoices', true);
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
                ->gotoRoute(array(), 'admin_invoices', true);
        }
        
        // check ticket
        $invoice = $this->_helper->getHelper('DbTable')
                ->getTable('invoices')->getInvoice($invoiceId);
        if(!$invoice || !$invoice->invoice_id){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_invoices', true);
        }
        
        $this->view->invoice = $invoice;
    }
    
    public function deleteAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        
        // get invoice ids and page id
        $page = $this->_getParam('pg', false);
        $invoiceIds = $this->_getParam('id', false);
        $invoiceIds = explode(',', $invoiceIds);
        
        // check invoice ids
        if(count($invoiceIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("invoices");
            $invoices = $table->fetchAll(array('invoice_id IN (?)' => $invoiceIds));
            if(count($invoices)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    // check invoice
                    $commit = false;
                    foreach($invoices as $invoice){
                        if($invoice->canBeDelete()){
                            $invoice->enabled = 0;
                            $invoice->modified_date = @date('Y-m-d H:i:s', time());
                            $invoice->save();
                            $commit = true;
                        }
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
        
        // redirect to invoice list
        if($page > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('page' => $page), 'admin_invoices', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_invoices', true);
    }

    public function restoreAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        
        // get invoice ids and page id
        $page = $this->_getParam('pg', false);
        $invoiceIds = $this->_getParam('id', false);
        $invoiceIds = explode(',', $invoiceIds);
        
        // check invoice ids
        if(count($invoiceIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("invoices");
            $invoices = $table->fetchAll(array('invoice_id IN (?)' => $invoiceIds));
            if(count($invoices)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    // check invoice owner
                    $commit = false;
                    foreach($invoices as $invoice){
                        $invoice->enabled = 1;
                        $invoice->modified_date = @date('Y-m-d H:i:s', time());
                        $invoice->save();
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
        
        // redirect to invoice list
        if($page > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('action' => 'trash', 'page' => $page), 'admin_invoices', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array('action' => 'trash'), 'admin_invoices', true);
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
                ->getView()->headTitle('Deleted Service Payment Invoices');

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
                = new Application_Form_Admin_Invoice_Filter();

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
          'enabled' => '0',
          'order' => 'modified_date',
          'direction' => 'DESC'
        ), $values);

        $this->view->assign($values);
        
        // Filter out junk
        $valuesCopy = array_filter($values);
       
        // get tickets
        $itemPerPage = 6;
        $table = $helper->getTable("invoices");
        $paginator = $table->getInvoicePaginator($values);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $paginator->setItemCountPerPage($itemPerPage);
        $this->view->paginator =$paginator;
        $this->view->formValues = $valuesCopy;
        $this->view->itemPerPage = $itemPerPage;
    }
}
