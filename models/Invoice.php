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

class Application_Model_Invoice extends Zend_Db_Table_Row_Abstract
{
    public function getIdentity(){
        return (int) $this->invoice_id;
    }

    public function getTitle(){
        return $this->title;
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
    
    public function getSchedule(){
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        return $helper->getTable('schedules')->getSchedule($this->schedule_id);
    }
    
    public function getAmount(){
        return $this->amount;
    }
    
    public function isEnabled(){
        return $this->enabled;
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

    public function canBeDelete(){
        if($this->status==2 && $this->isEnabled()){return true;}
        return false;
    }
    
    public function canBeEdit(){
        if($this->status!=2 && $this->isEnabled()){return true;}
        return false;
    }
    
    public function getStatusWord(){
        $status = $this->status;
        if($status==1){return "{$this->getEnabledWord()}<br /><span class=\"status red\">Payment Pending</span>";}
        elseif($status==2){return "{$this->getEnabledWord()}<br /><span class=\"status green\">Payment Made</span>";}
        return null;
    }
}
