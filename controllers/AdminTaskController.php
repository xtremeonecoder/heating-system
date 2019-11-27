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

class AdminTaskController extends Zend_Controller_Action
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
                ->getView()->headTitle('List of Scheduled Tasks');

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
                = new Application_Form_Admin_Task_Filter();

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
        $itemPerPage = 5;
        $table = $helper->getTable("schedules");
        $paginator = $table->getSchedulePaginator($values);
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
                ->getView()->headTitle('Create Schedule for Task');

        // render form
        $this->view->form = $form
                = new Application_Form_Admin_Task_Create();

        // check ticket id
        $ticketId = $this->_getParam('id', false);        
        if(!$ticketId){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tickets', true);
        }
        
        // check schedule can be created?
        $ticket = $this->_helper->getHelper('DbTable')
                ->getTable('tickets')->getTicket($ticketId);
        if(!$ticket || !$ticket->canSchedule()){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tickets', true);
        }
        
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
        $values['user_id'] = (int) $ticket->user_id;
        $values['ticket_id'] = (int) $ticket->ticket_id;
        $values['technician_id'] = (int) $values['technician'];
        
        // start transaction
        $table = $this->_helper->getHelper('DbTable')->getTable("schedules");
        $db = $table->getAdapter();
        $db->beginTransaction();

        try{
            // create schedule
            $schedule = $table->createRow();
            $schedule->setFromArray($values);
            $schedule->save();

            // update tasks
            $ticket->scheduled = (int) $schedule->schedule_id;
            $ticket->modified_date = @date('Y-m-d H:i:s', time());
            $ticket->save();
            
            // send email
            $member = $this->_helper
                    ->getHelper('User')->getUser($values['user_id']);
            $technician = $this->_helper
                    ->getHelper('User')->getUser($values['technician_id']);
            $member->notifyScheduleToMember($technician, $ticket, $schedule);
            $technician->notifyScheduleToTechnician($member, $ticket, $schedule);
            
            // Commit
            $db->commit();
            
            // redirect to schedule list
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'admin_tasks', true);
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
                ->getView()->headTitle('Edit Schedule for Task');

        // check schedule id
        $scheduleId = $this->_getParam('id', false);        
        if(!$scheduleId){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tasks', true);
        }
        
        // get and check schedule
        $schedule = $this->_helper->getHelper('DbTable')
                ->getTable('schedules')->getSchedule($scheduleId);
        if(!$schedule || !$schedule->schedule_id){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tasks', true);
        }
        
        // render form
        $this->view->form = $form
                = new Application_Form_Admin_Task_Create();
        
        // populate form with values
        $values = $schedule->toArray();
        $values['technician'] = $values['technician_id'];
        $form->populate($values);
        
        // check can schedule or not?
        $ticket = $schedule->getTicket();
        if(!$schedule->canEdit()){
            return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tasks', true);
        }
        
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

        // get viewer
        $values['technician_id'] = (int) $values['technician'];
        
        // start transaction
        $table = $this->_helper
                ->getHelper('DbTable')->getTable("schedules");
        $db = $table->getAdapter();
        $db->beginTransaction();

        try{
            // update schedule
            $schedule->setFromArray($values);
            $schedule->save();

            // update tasks
            $ticket->scheduled = (int) $schedule->schedule_id;
            $ticket->modified_date = @date('Y-m-d H:i:s', time());
            $ticket->save();
            
            // send email
            $member = $this->_helper
                    ->getHelper('User')->getUser($schedule->user_id);
            $technician = $this->_helper
                    ->getHelper('User')->getUser($schedule->technician_id);
            $member->notifyScheduleToMember($technician, $ticket, $schedule, true);
            $technician->notifyScheduleToTechnician($member, $ticket, $schedule, true);
            
            // Commit
            $db->commit();
            
            // redirect to schedule list
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'admin_tasks', true);
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
        
        // get schedule ids and page id
        $page = $this->_getParam('pg', false);
        $scheduleIds = $this->_getParam('id', false);
        $scheduleIds = explode(',', $scheduleIds);
        
        // check schedule ids
        if(count($scheduleIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("schedules");
            $schedules = $table->fetchAll(array('schedule_id IN (?)' => $scheduleIds));
            if(count($schedules)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    // delete schedules
                    $commit = false;
                    foreach($schedules as $schedule){
                        $ticket = $schedule->getTicket();
                        if($ticket->status == 2){
                            $ticket->status = 1;
                        }if($schedule->enabled){
                            $ticket->scheduled = 0;
                        }
                        $ticket->modified_date = @date('Y-m-d H:i:s', time());
                        $ticket->save();
                        
                        // send email
                        if($schedule->enabled){
                            $member = $this->_helper
                                    ->getHelper('User')->getUser($schedule->user_id);
                            $technician = $this->_helper
                                    ->getHelper('User')->getUser($schedule->technician_id);
                            $member->cancelScheduleMember($technician, $ticket, $schedule);
                            $technician->cancelScheduleTechnician($member, $ticket, $schedule);
                        }
                        
                        // delete
                        $schedule->delete();
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
        
        // redirect to schedule list
        if($page > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('page' => $page), 'admin_tasks', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tasks', true);
    }
   
    public function closeAction()
    {
        // check user loggedin?
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return $this->_helper->redirector
                    ->gotoRoute(array(), 'member_login', true);
        }
        
        // get schedule ids and page id
        $page = $this->_getParam('pg', false);
        $scheduleIds = $this->_getParam('id', false);
        $scheduleIds = explode(',', $scheduleIds);
        
        // check schedule ids
        if(count($scheduleIds)>0){
            $table = $this->_helper->getHelper('DbTable')->getTable("schedules");
            $schedules = $table->fetchAll(array('schedule_id IN (?)' => $scheduleIds));
            if(count($schedules)>0){
                // start transaction
                $db = $table->getAdapter();
                $db->beginTransaction();

                try{
                    // close schedules
                    $commit = false;
                    foreach($schedules as $schedule){
                        $ticket = $schedule->getTicket();
                        if($ticket->status == 2){
                            $ticket->status = 1;
                        }
                        $ticket->scheduled = 0;
                        $ticket->modified_date = @date('Y-m-d H:i:s', time());
                        $ticket->save();
                        
                        // send email
                        $member = $this->_helper
                                ->getHelper('User')->getUser($schedule->user_id);
                        $technician = $this->_helper
                                ->getHelper('User')->getUser($schedule->technician_id);
                        $member->cancelScheduleMember($technician, $ticket, $schedule);
                        $technician->cancelScheduleTechnician($member, $ticket, $schedule);
                        
                        // close schedule
                        $schedule->enabled = 0;
                        $schedule->save();
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
        
        // redirect to schedule list
        if($page > 1){
            return $this->_helper->redirector
                    ->gotoRoute(array('page' => $page), 'admin_tasks', true);
        }
        return $this->_helper->redirector
                ->gotoRoute(array(), 'admin_tasks', true);
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
                ->getView()->headTitle('List of Closed Scheduled Tasks');

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
                = new Application_Form_Admin_Task_Filter();

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
        $itemPerPage = 5;
        $table = $helper->getTable("schedules");
        $paginator = $table->getSchedulePaginator($values);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $paginator->setItemCountPerPage($itemPerPage);
        $this->view->paginator =$paginator;
        $this->view->formValues = $valuesCopy;
        $this->view->itemPerPage = $itemPerPage;
    }
}
