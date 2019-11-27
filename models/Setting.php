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

class Application_Model_Setting extends Zend_Db_Table_Row_Abstract
{
    public function getSettingValue(){
        return trim($this->value);
    }
    
    public function addSettingValue($value = null){
        $this->value = trim($value);
    }
    
    public function removeSettingValue(){
        $this->value = null;
    }
    
    public function getUnserializeData(){
        if($this->value){
            return unserialize($this->value);
        }else{
            return array();
        }
    }
}
