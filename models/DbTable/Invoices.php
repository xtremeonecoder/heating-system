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

class Application_Model_DbTable_Invoices extends Aninda_Db_Table_Abstract
{
    protected $_name = 'invoices';
    protected $_rowClass = 'Application_Model_Invoice';

    public function getInvoicePaginator($params = array()){
        return Zend_Paginator::factory($this->getInvoices($params));
    }

    public function getInvoices($params = array()){
        $select = $this->select();

        // like search
        if(isset($params['keyword'])){
            $select->where("
                    title LIKE ? OR
                    description LIKE ? OR
                    amount LIKE ?
                ", 
                "%{$params['keyword']}%");
        }
        
        // user id
        if(isset($params['user_id']) && !empty($params['user_id'])){
            $select->where('user_id = ?', (int) $params['user_id']);
        }
        
        // schedule_id
        if(isset($params['schedule_id']) && !empty($params['schedule_id'])){
            $select->where('schedule_id = ?', (int) $params['schedule_id']);
        }  
        
        // ticket_id
        if(isset($params['ticket_id']) && !empty($params['ticket_id'])){
            $select->where('ticket_id = ?', (int) $params['ticket_id']);
        }     
        
        // status
        if(isset($params['status']) && !empty($params['status'])){
            $select->where('status = ?', (int) $params['status']);
        }

        // enabled
        if(isset($params['enabled']) && $params['enabled'] != ''){
            $select->where('enabled = ?', (int) $params['enabled']);
        }   
                
        // order
        if(isset($params['order']) && isset($params['direction'])){
            $select->order("{$params['order']} {$params['direction']}");
        }elseif(isset($params['order'])){
            $select->order("{$params['order']} ASC");
        }else{
            $select->order('invoice_id ASC');
        }

        // limit
        if(isset($params['limit'])){
            $select->limit($params['limit']);
        }
        
        //echo $select; exit();
        return $this->fetchAll($select);
    }
    
    public function getInvoice($invoiceId = null){        
        if(!$invoiceId){
            return null;
        }
        
        $select = $this->select();
        
        // invoice_id 
        if(isset($invoiceId) && !empty($invoiceId)){
            $select->where('enabled = ?', 1)
                   ->where('invoice_id = ?', (int) $invoiceId);
        }
        
        //echo $select; exit();
        return $this->fetchRow($select);
    }
    
    public function getInvoiceByTicket($ticketId = null){        
        if(!$ticketId){
            return null;
        }
        
        $select = $this->select();
        
        // ticket_id 
        if(isset($ticketId) && !empty($ticketId)){
            $select->where('enabled = ?', 1)
                   ->where('ticket_id = ?', (int) $ticketId);
        }
        
        //echo $select; exit();
        return $this->fetchRow($select);
    }

    public function getInvoiceBySchedule($scheduleId = null){        
        if(!$scheduleId){
            return null;
        }
        
        $select = $this->select();
        
        // schedule_id 
        if(isset($scheduleId) && !empty($scheduleId)){
            $select->where('enabled = ?', 1)
                   ->where('schedule_id = ?', (int) $scheduleId);
        }
        
        //echo $select; exit();
        return $this->fetchRow($select);
    }    
}
