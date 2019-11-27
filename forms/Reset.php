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

class Application_Form_Reset extends Zend_Form
{
  public function init()
  {
    // Init form
    $tabindex = 1;
    $this->setAttrib('id', 'reset_form')
      ->setAttrib('class', 'global_form')
      ->setMethod("POST")
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    // to show the errors above the form
    $this->setDecorators(array(
        'FormElements',
        'Form',
        array('FormErrors', array('placement' => 'prepend'))
    ));

    // Init new password
    //$password = Zend_Registry::get('Zend_Translate')->_('Password');
    $this->addElement('Password', 'password', array(
      'label' => 'New Password',
      'placeholder' => 'New Password',
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
    $this->password->getValidator('NotEmpty')->setMessage('Please enter new password.', 'isEmpty');

    // Init confirm new password
    //$password = Zend_Registry::get('Zend_Translate')->_('Password');
    $this->addElement('Password', 'conpass', array(
      'label' => 'Confirm New Password',
      'placeholder' => 'Confirm New Password',
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
    $this->conpass->removeDecorator('Errors');
    $this->conpass->getValidator('NotEmpty')->setMessage('Please confirm new password.', 'isEmpty');

    // custom validation
    $specialValidator = new Aninda_Validate_Callback(array($this, 'checkPasswordConfirm'), $this->password);
    $specialValidator->setMessage('Password did not match!', 'invalid');
    $this->conpass->addValidator($specialValidator);

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Reset Password',
      'type' => 'submit',
      'ignore' => true,
      'tabindex' => $tabindex++,
    ));
  }

  public function checkPasswordConfirm($value, $passwordElement){
    return ( $value == $passwordElement->getValue() );
  }
}
