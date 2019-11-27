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

class Application_Model_DbTable_Pages extends Aninda_Db_Table_Abstract
{
    protected $_name = 'pages';
    protected $_rowClass = 'Application_Model_Page';

    public function getPagePaginator($params = array()){
        return Zend_Paginator::factory($this->getPages($params));
    }

    public function getPages($params = array()){
        $select = $this->select();

        // order
        if(isset($params['order']) AND isset($params['direction'])){
            $select->order("{$params['order']} {$params['direction']}");
        }elseif(isset($params['order'])){
            $select->order("{$params['order']} ASC");
        }else{
            $select->order('page_id ASC');
        }

        // limit
        if(isset($params['limit'])){
            $select->limit($params['limit']);
        }

        return $this->fetchAll($select);
    }
}
