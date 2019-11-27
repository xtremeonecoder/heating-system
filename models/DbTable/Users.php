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

class Application_Model_DbTable_Users extends Aninda_Db_Table_Abstract
{
    protected $_name = 'users';
    protected $_rowClass = 'Application_Model_User';
    
    public function getUserPaginator($params = array()){
        return Zend_Paginator::factory($this->getUsers($params));
    }

    public function getUsers($params = array()){
        $select = $this->select();

        // like search
        if(isset($params['keyword'])){
            $select->where("
                    email LIKE ? OR
                    firstname LIKE ? OR
                    lastname LIKE ? OR
                    mobile_no LIKE ? OR
                    address LIKE ? OR
                    city_name LIKE ? OR
                    postcode LIKE ? OR
                    country_name LIKE ? OR
                    phone_no LIKE ?
                ", 
                "%{$params['keyword']}%");
        }
        
        // level
        if(isset($params['level']) && !empty($params['level'])){
            $select->where('level = ?', (int) $params['level']);
        }
        
        // activate
        if(isset($params['active'])){
            $select->where('active = ?', (int) $params['active']);
        }
        
        // technician
        if(isset($params['technician'])){
            $select->where('technician = ?', (int) $params['technician']);
        }
                
        // order
        if(isset($params['order']) && isset($params['direction'])){
            $select->order("{$params['order']} {$params['direction']}");
        }elseif(isset($params['order'])){
            $select->order("{$params['order']} ASC");
        }else{
            $select->order('user_id ASC');
        }

        // limit
        if(isset($params['limit'])){
            $select->limit($params['limit']);
        }
        
        //echo $select; exit();
        return $this->fetchAll($select);
    }
    
    public function getUser($userId = null){
        if(!$userId){
            return null;
        }
        
        $select = $this->select();
        
        // ticket id
        if(isset($userId) && !empty($userId)){
            $select->where('user_id = ?', (int) $userId);
        }
        
        //echo $select; exit();
        return $this->fetchRow($select);
    }
    
    public function getTechniciansAssoc(){
        $technicianAssoc = array('' => 'Select a Technician');
        $params = array(
            'level' => 4,
            'active' => 1,
            'technician' => 1,
            'order' => 'firstname'
        );
        
        $technicians = $this->getUsers($params);
        if(count($technicians)>0){
            foreach($technicians as $technician){
                $technicianAssoc[$technician->getIdentity()] = $technician->getTitle();
            }
        }
        
        return $technicianAssoc;
    }
}
