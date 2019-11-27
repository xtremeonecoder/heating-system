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

class Aninda_View_Helper_RegistrationProgressWidget extends Zend_View_Helper_Abstract 
{
    public function registrationProgressWidget($step = false) {
        return $this->view->partial(
            'helper-scripts/_registrationProgressWidget.phtml',
            array('step' => $step)
        );
    }
}