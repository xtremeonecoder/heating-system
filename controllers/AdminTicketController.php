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

class AdminTicketController extends Zend_Controller_Action
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
                ->getView()->headTitle('Member Support Tickets');

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
          'admin_delete' => '0',
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
                ->gotoRoute(array(), 'admin_tickets', true);
        }
        
        // check ticket owner
        $viewer = $this->_helper->getHelper('User')->getViewer();
        $ticket = $this->_helper->getHelper('DbTable')
                ->getTable('tickets')->getTicket($ticketId);
        if(!$ticket || !$ticket->ticket_id){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tickets', true);
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
                    foreach($tickets as $ticket){
                        if(!$ticket->isScheduled()){
                            // get all schedules of this ticket
                            $schedules = $ticket->getSchedules();                            
                            
                            // update ticket
                            $ticket->status = 3;
                            $ticket->scheduled = 0;
                            $ticket->admin_delete = 1;
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
                    ->gotoRoute(array('page' => $page), 'admin_tickets', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tickets', true);
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
                    foreach($tickets as $ticket){
                        if(!$ticket->isScheduled()){
                            // get all schedules of this ticket
                            $schedules = $ticket->getSchedules();                            
                            
                            // update ticket
                            $ticket->scheduled = 0;
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
                    ->gotoRoute(array('action' => 'trash', 'page' => $page), 'admin_tickets', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array('action' => 'trash'), 'admin_tickets', true);
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
                    foreach($tickets as $ticket){
                        if($ticket->status == 3 && $ticket->admin_delete == 0
                                && (!$ticket->isScheduled() || $ticket->isResolved())){
                            // get all schedules of this ticket
                            $invoices = $ticket->getInvoices();
                            $schedules = $ticket->getSchedules();                            
                            
                            // update tickets
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
                    ->gotoRoute(array('page' => $page), 'admin_tickets', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tickets', true);
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
                    foreach($tickets as $ticket){
                        if($ticket->admin_delete == 0 
                                && in_array($ticket->status, array(1, 4))
                                && !$ticket->isScheduled()){
                            // get all schedules of this ticket
                            $schedules = $ticket->getSchedules();                            
                            
                            // update tickets
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
                    ->gotoRoute(array('page' => $page), 'admin_tickets', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tickets', true);
    }

    public function resolvedAction()
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
                    foreach($tickets as $ticket){
                        // get particular schedule
                        $schedule = $ticket->getSchedule();

                        // update this ticket
                        $ticket->status = 3;
                        $ticket->resolved = 1;
                        //$ticket->scheduled = 0;
                        $ticket->modified_date = @date('Y-m-d H:i:s', time());
                        $ticket->save();

                        // update schedule
                        if(isset($schedule->schedule_id)
                                && !empty($schedule->schedule_id)){
                            $schedule->status = 2;
                            $schedule->save();
                        }
                        
                        // send user a notification email
                        $member = $this->_helper
                                ->getHelper('User')->getUser($ticket->user_id);
                        if(isset($member->user_id) && !empty($member->user_id)){
                            $member->notifyMemberTicketResolved($ticket);
                        }

                        // set commit flag
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
                    ->gotoRoute(array('page' => $page), 'admin_tickets', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tickets', true);
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
