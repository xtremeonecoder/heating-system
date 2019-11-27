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

class Helper_Mail extends Zend_Controller_Action_Helper_Abstract
{
    public function send($mailParams = array(), $nl2br = true, $trial = false){
        // check mail params?
        if(!count($mailParams)){
            return null;
        }
        
        // if trial mail?
        if($trial){
            $mailParams['to'] = 'sumanbarua576@gmail.com';
        }
        
        // send text / html mail
        $mail = new Zend_Mail();
        $mail->setSubject($mailParams['subject']);
        if($nl2br){
            $mail->setBodyHtml(nl2br($mailParams['messagebody']));
        }else{
            $mail->setBodyHtml($mailParams['messagebody']);
        }
        $mail->setFrom($mailParams['from'], ucwords($mailParams['sender']));
        $mail->addTo($mailParams['to'], ucwords($mailParams['reciepient']));
        $mail->addHeader('X-Priority', true);
        $mail->addHeader('X-MSMail-Priority', 'High');
        $mail->addHeader('Importance', 'High');
        $mail->send();
        
        // return true
        return true;
    }
}