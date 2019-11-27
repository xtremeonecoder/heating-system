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

class Application_Model_Schedule extends Zend_Db_Table_Row_Abstract
{
    public function getIdentity(){
        return (int) $this->schedule_id;
    }
    
    public function getDescription(){
        return $this->description;
    }
    
    public function getTicket(){
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');    
        return $helper->getTable('tickets')->getTicket($this->ticket_id);
    }
    
    public function getMember(){
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('User');
        return $helper->getUser($this->user_id);
    }
    
    public function getTechnician(){
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('User');
        return $helper->getUser($this->technician_id);
    }
    
    public function getInvoice(){
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        return $helper->getTable('invoices')->getInvoice($this->invoice_id);
    }
    
    public function isEnabled(){
        return $this->enabled;
    }

    public function canEdit(){
        $ticket = $this->getTicket();
        if($ticket && $ticket->ticket_id 
                && $ticket->scheduled && !$ticket->resolved 
                && !in_array($ticket->status, array(2, 3))){
            return true;
        }
        
        return false;
    }
    
    public function getEnabledWord(){
        $enabled = $this->enabled;
        if($enabled==1){return '<span class="status green">Enabled</span>';}
        elseif($enabled==0){return '<span class="status red">Disabled</span>';}
        return null;
    }    
    
    public function getStatus(){
        return $this->status;
    }
    
    public function getStatusWord(){
        $status = $this->status;
        if($status==1){
            if($this->getTicket()->isProcessing()){
                return '<span class="status yellow">Processing</span>';                
            }elseif($this->getTicket()->isScheduleOver()){
                return '<span class="status brown">Deadline Over</span>';                
            }elseif($this->getTicket()->isUpcoming()){
                return '<span class="status brown">Upcoming</span>';                
            }
            return '<span class="status red">Task Pending</span>';            
        }
        elseif($status==2){return '<span class="status green">Task Finished</span>';}
        return null;
    }    
}
