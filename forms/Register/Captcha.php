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

class Application_Form_Register_Captcha extends Zend_Form
{
  public function init()
  {
    // Init form
    $tabindex = 1;
    $this->setAttrib('id', 'captcha_form')
      ->setAttrib('class', 'global_form')      
      ->setMethod("POST")
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'member_signup', true));

    // to show the errors above the form
    $this->setDecorators(array(
        'FormElements',
        'Form',
        array('FormErrors', array('placement' => 'prepend'))
    ));        
        
    // Captcha verification
    $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
    $captchaDir = realpath(APPLICATION_PATH . '/../public/captcha');
    @chmod($captchaDir, 0777);
    $this->addElement('Captcha', 'captcha', array(
        'label' => 'Enter the captcha code to proceed',
        //'description' => 'Type the characters you see in the picture.',  
        'style' => 'margin-top: 10px; display: block;',
        'tabindex' => $tabindex++,
        'class' => 'span5',
        'placeholder' => 'Type Captcha Code Here',
        'captcha' => array(
            'captcha' => 'Image',  
            'wordLen' => 4,  
            'timeout' => 300,  
            'font' => $captchaDir . '/font/ARIALN.TTF',  
            'imgDir' => $captchaDir,  
            'imgUrl' => $view->baseUrl() . '/public/captcha/',
            'lineNoiseLevel' => 2,
            'dotNoiseLevel' => 2,
        ),
    ));  
    $this->captcha->removeDecorator('Errors');
    //$this->captcha->getDecorator('Description')->setOptions(array('placement' => 'PREPEND'));
    //$this->captcha->getValidator('NotEmpty')->setMessage('Please enter correct captcha code.', 'isEmpty');
    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Continue',
      'type' => 'submit',
      'ignore' => true,
      'tabindex' => $tabindex++,
    ));
  }
}