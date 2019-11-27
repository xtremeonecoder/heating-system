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

class Application_Model_DbTable_Settings extends Aninda_Db_Table_Abstract
{
    protected $_name = 'settings';
    protected $_rowClass = 'Application_Model_Setting';


    public function getSettings($param){
        $select = $this->select();

        // enabled
        if(isset($param)){
            $select->where('name LIKE ?', "{$param}%");
        }

        return $this->fetchAll($select);
    }

    public function getSetting($param){
        $select = $this->select();

        // enabled
        if(isset($param)){
            $select->where('name = ?', $param);
        }

        return $this->fetchRow($select);
    }
}
