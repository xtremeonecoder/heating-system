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

class Application_Form_Login extends Zend_Form
{
  public function init()
  {
    // Init form
    $tabindex = 1;
    $this->setAttrib('id', 'login_form')
      ->setAttrib('class', 'global_form')
      ->setMethod("POST")
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'member_login', true));

    // to show the errors above the form
    $this->setDecorators(array(
        'FormElements',
        'Form',
        array('FormErrors', array('placement' => 'prepend'))
    ));

    // Init email
    //$email = Zend_Registry::get('Zend_Translate')->_('Email Address');
    $this->addElement('Text', 'email', array(
      'label' => 'Email Address',
      'placeholder' => 'Email Address',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true),
        array('EmailAddress', true)
      ),

      // Fancy stuff
      'tabindex' => $tabindex++,
      'autofocus' => 'autofocus',
      'inputType' => 'email',
      'class' => 'span5'
    ));
    $this->email->removeDecorator('Errors');
    $this->email->getValidator('NotEmpty')->setMessage('Please enter a valid email address.', 'isEmpty');

    // custom validation
    $specialValidator = new Aninda_Validate_Callback(array($this, 'checkAccountActive'), $this->email);
    $specialValidator->setMessage('The account you are trying to login is not active!', 'invalid');
    $this->email->addValidator($specialValidator);

    // Init password
    //$password = Zend_Registry::get('Zend_Translate')->_('Password');
    $this->addElement('Password', 'password', array(
      'label' => 'Password',
      'placeholder' => 'Password',
      'required' => true,
      'allowEmpty' => false,
      'class' => 'span5',
      'tabindex' => $tabindex++,
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true)
      )
    ));
    $this->password->removeDecorator('Errors');
    $this->password->getValidator('NotEmpty')->setMessage('Please enter your account password.', 'isEmpty');

    // Captcha verification
    $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
    $captchaDir = realpath(APPLICATION_PATH . '/../public/captcha');
    @chmod($captchaDir, 0777);
    $this->addElement('Captcha', 'captcha', array(
        'label' => 'Captcha Verification',
        'description' => 'Type the characters you see in the picture.',
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
    $this->captcha->getDecorator('Description')->setOptions(array('placement' => 'PREPEND'));
    //$this->captcha->getValidator('NotEmpty')->setMessage('Please enter correct captcha code.', 'isEmpty');
    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Sign In',
      'type' => 'submit',
      'ignore' => true,
      'tabindex' => $tabindex++,
    ));

//    // Init remember me
//    $this->addElement('Checkbox', 'remember', array(
//      'label' => 'Remember Me',
//      'tabindex' => $tabindex++,
//    ));
//
//    $this->addDisplayGroup(array(
//      'submit',
//      'remember'
//    ), 'buttons');
  }

  public function checkAccountActive($email, $emailElement){
    $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
    $table = $helper->getTable("users");
    $user = $table->fetchRow(array('email = ?' => $email));

    // check user exists?
    if(isset($user->user_id) AND !empty($user->user_id)){
        // check active?
        if(!$user->isActive()){
           return false;
        }
    }

    return true;
  }
}
