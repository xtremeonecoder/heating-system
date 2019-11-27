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

class Application_Model_DbTable_Tickets extends Aninda_Db_Table_Abstract
{
    protected $_name = 'tickets';
    protected $_rowClass = 'Application_Model_Ticket';

    public function getTicketPaginator($params = array()){
        return Zend_Paginator::factory($this->getTickets($params));
    }

    public function getTickets($params = array()){
        $select = $this->select();

        // like search
        if(isset($params['keyword'])){
            $select->where("
                    ref_number LIKE ? OR
                    title LIKE ? OR
                    priority LIKE ? OR
                    description LIKE ?
                ", 
                "%{$params['keyword']}%");
        }
        
        // user id
        if(isset($params['user_id']) && !empty($params['user_id'])){
            $select->where('user_id = ?', (int) $params['user_id']);
        }
        
        // priority
        if(isset($params['priority']) && !empty($params['priority'])){
            $select->where('priority = ?', $params['priority']);
        }

        // scheduled
        if(isset($params['scheduled']) && $params['scheduled'] == '0'){
            $select->where('scheduled = ?', 0);
        }elseif(isset($params['scheduled']) && $params['scheduled'] == '1'){
            $select->where('scheduled != ?', 0);
        }
        
        // status
        if(isset($params['status']) && !empty($params['status'])){
            $select->where('scheduled = ?', 0) // not scheduled
                   ->where('status = ?', (int) $params['status']);
        }

        // user deleted
        if(isset($params['user_delete'])){
            $select->where('user_delete = ?', (int) $params['user_delete']);
        }
        
        // admin deleted
        if(isset($params['admin_delete'])){
            $select->where('admin_delete = ?', (int) $params['admin_delete']);
        }      
        
        // order
        if(isset($params['order']) && isset($params['direction'])){
            $select->order("{$params['order']} {$params['direction']}");
        }elseif(isset($params['order'])){
            $select->order("{$params['order']} ASC");
        }else{
            $select->order('ticket_id ASC');
        }

        // limit
        if(isset($params['limit'])){
            $select->limit($params['limit']);
        }
        
        //echo $select; exit();
        return $this->fetchAll($select);
    }
    
    public function getTicket($ticketId = null){        
        if(!$ticketId){
            return null;
        }
        
        $select = $this->select();
        
        // ticket id
        if(isset($ticketId) && !empty($ticketId)){
            $select->where('ticket_id = ?', (int) $ticketId);
        }
        
        //echo $select; exit();
        return $this->fetchRow($select);
    }
}
