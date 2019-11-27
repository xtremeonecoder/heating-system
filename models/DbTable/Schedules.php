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

class Application_Model_DbTable_Schedules extends Aninda_Db_Table_Abstract
{
    protected $_name = 'schedules';
    protected $_rowClass = 'Application_Model_Schedule';

    public function getSchedulePaginator($params = array()){
        return Zend_Paginator::factory($this->getSchedules($params));
    }

    public function getSchedules($params = array()){
        $select = $this->select();

        // like search
        if(isset($params['keyword'])){
            $select->where("
                    description LIKE ? OR
                    scheduled_date LIKE ? OR
                    from_time LIKE ? OR
                    to_time LIKE ?
                ", 
                "%{$params['keyword']}%");
        }
        
        // user id
        if(isset($params['user_id']) && !empty($params['user_id'])){
            $select->where('user_id = ?', (int) $params['user_id']);
        }
        
        // technician_id
        if(isset($params['technician_id']) && !empty($params['technician_id'])){
            $select->where('technician_id = ?', (int) $params['technician_id']);
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
            $select->order('schedule_id ASC');
        }

        // limit
        if(isset($params['limit'])){
            $select->limit($params['limit']);
        }
        
        //echo $select; exit();
        return $this->fetchAll($select);
    }
    
    public function getSchedule($scheduleId = null){        
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
    
    public function getScheduleByTicket($ticketId = null){        
        if(!$ticketId){
            return null;
        }
        
        $select = $this->select();
        
        // schedule_id 
        if(isset($ticketId) && !empty($ticketId)){
            $select->where('enabled = ?', 1)
                   ->where('ticket_id = ?', (int) $ticketId);
        }
        
        //echo $select; exit();
        return $this->fetchRow($select);
    }
}
