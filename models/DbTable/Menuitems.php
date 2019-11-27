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

class Application_Model_DbTable_Menuitems extends Aninda_Db_Table_Abstract
{
    protected $_name = 'menuitems';
    protected $_serializedColumns = array('params');
    protected $_rowClass = 'Application_Model_Menuitem';

    public function getMenus($params = array(), $notShow = array()){
        $select = $this->select();

        // by name
        if(isset($params['name'])){
            $select->where('name = ?', $params['name']);
        }

        // by menu
        if(isset($params['menu']) && is_string($params['menu'])){
            $select->where('menu = ?', $params['menu']);
        }elseif(isset($params['menu']) && is_array($params['menu'])){
            $select->where('menu IN (?)', $params['menu']);
        }

        // dont show?
        if(count($notShow)>0){
            $select->where('name NOT IN (?)', $notShow);
        }

        // by submenu
        if(isset($params['submenu'])){
            $select->where('submenu = ?', $params['submenu']);
        }

        // by enabled
        if(isset($params['enabled'])){
            $select->where('enabled = ?', (int) $params['enabled']);
        }else{
            $select->where('enabled = ?', 1);
        }

        // order
        $select->order('order ASC');

        // get menues
        return $this->fetchAll($select);
    }
}
