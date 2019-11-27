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

class Application_Model_Paymentsetting extends Zend_Db_Table_Row_Abstract
{
    public function getIdentity(){
        return (int) $this->paymentsetting_id;
    }

    public function getCompanyId(){
        return $this->company_id;
    }
    
    public function getDuration(){
        return $this->duration;
    }
    
    public function getDurationText(){
        $duration = array(
            '7' => '7 days',
            '14' => '14 days',
            '30' => '30 days',
            '60' => '60 days',
            '90' => '90 days'
        );
        
        return isset($duration[$this->duration]) ? $duration[$this->duration] : 'Duration not set';
    }
    
    public function getDurationStart(){
        return $this->duration_start;
    }
    
    public function getDurationStartText(){
        $durationStart = array(
            'invoice_date' => 'From invoice date',
            'end_of_month' => 'From end of month'
        );
        
        return isset($durationStart[$this->duration_start]) ? $durationStart[$this->duration_start] : 'Duration start date not set';
    }
    
    public function getComment(){
        return $this->comment;
    }
    
    public function getCompany(){
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        $table = $helper->getTable("companies");
        return $table->fetchRow(array('company_id = ?' => $this->getCompanyId()));
    }    
}