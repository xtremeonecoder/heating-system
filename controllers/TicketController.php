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

class TicketController extends Zend_Controller_Action
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
                ->getView()->headTitle('My Support Tickets');

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
                = new Application_Form_Ticket_Filter();

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
          'user_delete' => '0',
          'order' => 'modified_date',
          'direction' => 'DESC'
        ), $values);

        $this->view->assign($values);
        
        // Filter out junk
        $valuesCopy = array_filter($values);
       
        // get tickets
        $itemPerPage = 6;
        $table = $helper->getTable("tickets");
        $paginator = $table->getTicketPaginator($values);
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
                ->getView()->headTitle('Create Support Ticket');

        // render form
        $this->view->form = $form
                = new Application_Form_Ticket_Create();

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
        $values['status'] = 1;
        $values['enabled'] = 1;
        $values['creation_date'] = @date('Y-m-d H:i:s', time());
        $values['modified_date'] = @date('Y-m-d H:i:s', time());

        // get viewer
        $viewer = $this->_helper->getHelper('User')->getViewer();
        if(!isset($viewer->user_id) || empty($viewer->user_id)){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        $values['user_id'] = $viewer->getIdentity();
        
        // ticket reference number
        $values['ref_number'] = mt_rand(100000, 999999);
        
        // start transaction
        $table = $this->_helper->getHelper('DbTable')->getTable("tickets");
        $db = $table->getAdapter();
        $db->beginTransaction();

        try{
            // create ticket
            $ticket = $table->createRow();
            $ticket->setFromArray($values);
            $ticket->save();

            // send email
            $viewer->sendCreateTicketNotification($values['ref_number']);
            
            // Commit
            $db->commit();
            
            // redirect to ticket list
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'user_tickets', true);
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
                ->getView()->headTitle('Support Ticket Details');

        // check ticket id
        $ticketId = $this->_getParam('id', false);        
        if(!$ticketId){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'user_tickets', true);
        }
        
        // check ticket owner
        $viewer = $this->_helper->getHelper('User')->getViewer();
        $ticket = $this->_helper->getHelper('DbTable')
                ->getTable('tickets')->getTicket($ticketId);
        if(!$ticket || $ticket->user_id != $viewer->user_id){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'user_tickets', true);
        }
        
        $this->view->ticket = $ticket;
    }
    
    public function deleteAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        
        // get ticket ids and page id
        $page = $this->_getParam('pg', false);
        $ticketIds = $this->_getParam('id', false);
        $ticketIds = explode(',', $ticketIds);
        
        // check ticket ids
        if(count($ticketIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("tickets");
            $tickets = $table->fetchAll(array('ticket_id IN (?)' => $ticketIds));
            if(count($tickets)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    // check ticket owner
                    $commit = false;
                    $viewer = $this->_helper->getHelper('User')->getViewer();
                    foreach($tickets as $ticket){
                        if($viewer->user_id == $ticket->user_id
                                && !$ticket->isScheduled()){
                            // get all schedules of this ticket
                            $schedules = $ticket->getSchedules();                            
                            
                            // update ticket
                            $ticket->status = 3;
                            $ticket->scheduled = 0;
                            $ticket->user_delete = 1;
                            $ticket->modified_date = @date('Y-m-d H:i:s', time());
                            $ticket->save();
                            
                            // update ticket schedules
                            if(count($schedules)>0){
                                foreach($schedules as $schedule){
                                    $schedule->enabled = 0;
                                    $schedule->save();
                                }
                            }
                            
                            // set commit flag                            
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
        
        // redirect to ticket list
        if($page > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('page' => $page), 'user_tickets', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array(), 'user_tickets', true);
    }

    public function restoreAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        
        // get ticket ids and page id
        $page = $this->_getParam('pg', false);
        $ticketIds = $this->_getParam('id', false);
        $ticketIds = explode(',', $ticketIds);
        
        // check ticket ids
        if(count($ticketIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("tickets");
            $tickets = $table->fetchAll(array('ticket_id IN (?)' => $ticketIds));
            if(count($tickets)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    $commit = false;
                    $viewer = $this->_helper->getHelper('User')->getViewer();
                    foreach($tickets as $ticket){
                        if($viewer->user_id == $ticket->user_id
                                && !$ticket->isScheduled()){
                            // get all schedules of this ticket
                            $schedules = $ticket->getSchedules();                            
                            
                            // update ticket
                            $ticket->user_delete = 0;
                            //$ticket->admin_delete = 0;
                            $ticket->scheduled = 0;
                            $ticket->modified_date = @date('Y-m-d H:i:s', time());
                            $ticket->save();

                            // update ticket schedules
                            if(count($schedules)>0){
                                foreach($schedules as $schedule){
                                    $schedule->enabled = 0;
                                    $schedule->save();
                                }
                            }
                            
                            // set commit flag                            
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
        
        // redirect to ticket list
        if($page > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('action' => 'trash', 'page' => $page), 'user_tickets', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array('action' => 'trash'), 'user_tickets', true);
    }

    public function reopenAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        
        // get ticket ids and page id
        $page = $this->_getParam('pg', false);
        $ticketIds = $this->_getParam('id', false);
        $ticketIds = explode(',', $ticketIds);
        
        // check ticket ids
        if(count($ticketIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("tickets");
            $tickets = $table->fetchAll(array('ticket_id IN (?)' => $ticketIds));
            if(count($tickets)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    $commit = false;
                    $viewer = $this->_helper->getHelper('User')->getViewer();
                    foreach($tickets as $ticket){
                        if($viewer->user_id == $ticket->user_id 
                                && $ticket->status == 3 && $ticket->user_delete == 0
                                && (!$ticket->isScheduled() || $ticket->isResolved())){
                            // get all schedules of this ticket
                            $invoices = $ticket->getInvoices();
                            $schedules = $ticket->getSchedules();
                            
                            // update ticket
                            $ticket->status = 4;
                            $ticket->resolved = 0;
                            $ticket->scheduled = 0;
                            $ticket->invoice_id = 0;
                            $ticket->user_delete = 0;
                            $ticket->admin_delete = 0;
                            $ticket->modified_date = @date('Y-m-d H:i:s', time());
                            $ticket->save();
                            
                            // update ticket schedules
                            if(count($schedules)>0){
                                foreach($schedules as $schedule){
                                    $schedule->enabled = 0;
                                    $schedule->save();
                                }
                            }

                            // update ticket invoices
                            if(count($invoices)>0){
                                foreach($invoices as $invoice){
                                    $invoice->enabled = 0;
                                    $invoice->save();
                                }
                            }
                            
                            // set commit flag                                                        
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
        
        // redirect to ticket list
        if($page > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('page' => $page), 'user_tickets', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array(), 'user_tickets', true);
    }
    
    public function closeAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        
        // get ticket ids and page id
        $page = $this->_getParam('pg', false);
        $ticketIds = $this->_getParam('id', false);
        $ticketIds = explode(',', $ticketIds);
        
        // check ticket ids
        if(count($ticketIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("tickets");
            $tickets = $table->fetchAll(array('ticket_id IN (?)' => $ticketIds));
            if(count($tickets)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    $commit = false;
                    $viewer = $this->_helper->getHelper('User')->getViewer();
                    foreach($tickets as $ticket){
                        if($viewer->user_id == $ticket->user_id 
                                && in_array($ticket->status, array(1, 4))
                                && $ticket->user_delete == 0
                                && !$ticket->isScheduled()){
                            // get all schedules of this ticket
                            $schedules = $ticket->getSchedules();                            
                            
                            // update ticket
                            $ticket->status = 3;
                            $ticket->scheduled = 0;
                            $ticket->modified_date = @date('Y-m-d H:i:s', time());
                            $ticket->save();
                            
                            // update ticket schedules
                            if(count($schedules)>0){
                                foreach($schedules as $schedule){
                                    $schedule->enabled = 0;
                                    $schedule->save();
                                }
                            }
                            
                            // set commit flag                                                        
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
        
        // redirect to ticket list
        if($page > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('page' => $page), 'user_tickets', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array(), 'user_tickets', true);
    }

    public function priorityAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }

        // page title
        $this->_helper->layout()
                ->getView()->headTitle('Change Priority of Support Ticket');

        // check ticket id
        $ticketId = $this->_getParam('id', false);        
        if(!$ticketId){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'user_tickets', true);
        }
        
        // check ticket owner
        $viewer = $this->_helper->getHelper('User')->getViewer();
        $ticket = $this->_helper->getHelper('DbTable')
                ->getTable('tickets')->getTicket($ticketId);
        if(!$ticket || $ticket->user_id != $viewer->user_id 
                || $ticket->user_delete == 1
                || $ticket->isScheduled()){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'user_tickets', true);
        }
        
        // render form
        $this->view->form = $form
                = new Application_Form_Ticket_Priority();
        $form->populate(array('priority' => $ticket->priority));

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

        // start transaction
        $table = $this->_helper->getHelper('DbTable')->getTable("tickets");
        $db = $table->getAdapter();
        $db->beginTransaction();

        try{
            // create ticket
            $ticket->priority = $values['priority'];
            $ticket->modified_date = @date('Y-m-d H:i:s', time());
            $ticket->save();

            // Commit
            $db->commit();
            
            // redirect to ticket list
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'user_tickets', true);
        }catch(Exception $e){
            $db->rollBack();
            $form->addError($e->getMessage());
            throw $e;
        }
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
                ->getView()->headTitle('My Deleted Support Tickets in Trash');

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
                = new Application_Form_Ticket_Filter();

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
          'user_delete' => '1',
          'order' => 'modified_date',
          'direction' => 'DESC'
        ), $values);

        $this->view->assign($values);
        
        // Filter out junk
        $valuesCopy = array_filter($values);
       
        // get tickets
        $itemPerPage = 6;
        $table = $helper->getTable("tickets");
        $paginator = $table->getTicketPaginator($values);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $paginator->setItemCountPerPage($itemPerPage);
        $this->view->paginator =$paginator;
        $this->view->formValues = $valuesCopy;
        $this->view->itemPerPage = $itemPerPage;        
    }
}
