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

class Application_Model_Ticket extends Zend_Db_Table_Row_Abstract
{
    public function getIdentity(){
        return (int) $this->ticket_id;
    }

    public function getTitle(){
        return $this->title;
    }

    public function getDescription(){
        return $this->description;
    }

    public function getHref($params = array()){
        $params = array_merge(array(
            'route' => 'user_tickets',
            'action' => 'details',
            'id' => $this->getIdentity(),
            'reset' => true
        ), $params);
        $route = $params['route'];
        $reset = $params['reset'];
        unset($params['route']);
        unset($params['reset']);
        return Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble($params, $route, $reset);
    }

    public function getReference(){
        return $this->ref_number;
    }

    public function getPriority(){
        return $this->priority;
    }

    public function getPriorityWord(){
        $priority = ucwords($this->priority);
        if($priority=='Lower'){return '<span class="status green">'.$priority.'</span>';}
        elseif($priority=='Medium'){return '<span class="status yellow">'.$priority.'</span>';}
        elseif($priority=='Higher'){return '<span class="status red">'.$priority.'</span>';}
        return null;
    }    
    
    public function getStatus(){
        return $this->status;
    }
    
    public function getStatusWord(){
        $status = $this->status;
        if($status==1){return '<span class="status green">Open</span>';}
        elseif($status==2){return '<span class="status yellow">Processing</span>';}
        elseif($status==3){return '<span class="status red">Closed</span>';}
        elseif($status==4){return '<span class="status blue">Reopened</span>';}
        return null;
    }    

    public function isResolved(){
        return $this->resolved;
    }
    
    public function getResolvedWord(){
        $resolved = $this->resolved;
        if($resolved==1){return '<span class="status green">RESOLVED</span>';}
        elseif($resolved==0){return '<span class="status red">NOT RESOLVED</span>';}
        return null;
    }    
    
    public function isScheduled(){
        return $this->scheduled;
    }

    public function canSchedule(){
        if(!$this->scheduled && !$this->resolved
                && !$this->user_delete && !$this->admin_delete
                && !in_array($this->status, array(2, 3))) {
            return true;
        }
        
        return false;
    }
    
    public function canCreateInvoice(){
        return (boolean) ($this->resolved && !$this->invoice_id);
    }
    
    public function isPaymentMade(){
        $invoice = $this->getInvoice();
        if($invoice && isset($invoice->status) && $invoice->status == 2){
            return true;
        }
        
        return false;
    }
    
    public function canMakePayment(){
        return (boolean) ($this->resolved 
                && $this->invoice_id 
                && !$this->isPaymentMade());
    }
    
    public function isProcessing(){
        if($this->scheduled && !$this->resolved){
            $schedule = $this->getSchedule();
            if($schedule && $schedule->scheduled_date 
                    && $schedule->scheduled_date == date('Y-m-d', time())){
                return true;
            }
        }
        
        return false;
    }
    
    public function isScheduleOver(){
        if($this->scheduled && !$this->resolved){
            $schedule = $this->getSchedule();
            if($schedule && $schedule->scheduled_date 
                    && strtotime($schedule->scheduled_date) 
                    < strtotime(date('Y-m-d', time()))){
                return true;
            }
        }
        
        return false;
    }
    
    public function isUpcoming(){
        if($this->scheduled && !$this->resolved){
            $schedule = $this->getSchedule();
            if($schedule && $schedule->scheduled_date 
                    && strtotime($schedule->scheduled_date) 
                    == strtotime(date('Y-m-d', time()).' +1 day')){
                return true;
            }
        }
        
        return false;
    }
    
    public function getSchedule(){
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        return $helper->getTable('schedules')->getSchedule($this->scheduled);
    }

    public function getSchedules(){
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        return $helper->getTable('schedules')
                ->getSchedules(array('ticket_id' => $this->ticket_id));
    }
    
    public function getScheduledWord(){
        $scheduled = $this->scheduled;
        if($scheduled==0){return '<span class="status red">Not Scheduled</span>';}
        elseif($scheduled!=0){
            if($this->isProcessing()){
                return '<span class="status yellow">Processing</span>';                
            }elseif($this->isScheduleOver()){
                return '<span class="status brown">Deadline Over</span>';
            }elseif($this->isUpcoming()){
                return '<span class="status brown">Upcoming</span>';
            }
            return '<span class="status brown">Scheduled</span>';            
        }
        return null;
    }    

    public function isEnabled(){
        return $this->enabled;
    }
    
    public function getUser(){
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('User');
        return $helper->getUser($this->user_id);
    }
    
    public function getInvoice(){
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        return $helper->getTable('invoices')->getInvoice($this->invoice_id);
    }
    
    public function getInvoices(){
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        return $helper->getTable('invoices')
                ->getInvoices(array('ticket_id' => $this->ticket_id));
    }
}
