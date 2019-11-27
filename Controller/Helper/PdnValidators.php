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

class Helper_PdnValidators extends Zend_Controller_Action_Helper_Abstract 
{
    public function isValidPostCode($postCode = false) 
    {
        if (!$postCode) {
            return false;
        }

        // validate postcode
        return true;
    }
}