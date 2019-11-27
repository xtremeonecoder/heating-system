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

class Application_Model_Menuitem extends Zend_Db_Table_Row_Abstract
{
    public function getIdentity(){
        return (int) $this->id;
    }

    public function getName(){
        return $this->name;
    }

    public function getLabel(){
        return $this->label;
    }

    public function getParams(){
        $params = array();
        if(!empty($this->params)){
          $values = Zend_Json::decode($this->params);
          if(count($values)>0){
              $params['route'] = $values['route'];
              unset($values['route']);
              $params['params'] = $values;
          }
        }

        return $params;
    }

    public function getMenu(){
        return $this->menu;
    }

    public function getIcon(){
        return $this->icon;
    }

    public function getPage(){
        return unserialize($this->page);
    }

    public function hasSubMenu(){
        return $this->submenu;
    }

    public function getSubMenu(){
        if($this->hasSubMenu()){
            $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
            $table = $helper->getTable("menuitems");
            $select = $table->select()
                    ->where('menu = ?', $this->getName())
                    ->where('enabled = ?', 1)
                    ->order('order ASC');
            return $table->fetchAll($select);
            //return $table->fetchAll(array('menu = ?' => $this->getName(), 'enabled = ?' => 1));
        }

        return null;
    }

    public function isEnabled(){
        return (int) $this->enabled;
    }

    public function getOrder(){
        return (int) $this->order;
    }
}
