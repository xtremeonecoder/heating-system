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

class Aninda_View_Helper_SiteFooterWidget extends Zend_View_Helper_Abstract
{
    public function siteFooterWidget($params = array()) {
        return $this->view->partial(
            'helper-scripts/_siteFooterWidget.phtml'
        );
    }
}
