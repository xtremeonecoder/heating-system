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

class Application_Form_Invoice_Payment extends Zend_Form
{
  public function init()
  {
    // Init form
    $tabindex = 1;
    $this->setAttrib('id', 'make_payment')
      ->setAttrib('class', 'global_form')      
      ->setMethod("POST")
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    // to show the errors above the form
    $this->setDecorators(array(
        'FormElements',
        'Form',
        array('FormErrors', array('placement' => 'prepend'))
    ));        
    
    // Init payment method
    $this->addElement('Select', 'payment_method', array(
      'label' => 'Select Payment Method',
      'placeholder' => 'Select Payment Method',
      'required' => true,
      'allowEmpty' => false,
      'tabindex' => $tabindex++,
      'autofocus' => 'autofocus',
      'class' => 'span5',        
      'multiOptions' => array(
        '' => 'Select Payment Method',
        'visa' => 'Visa Card',
        'master' => 'Master Card'
      ),
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true)
      )
    ));
    $this->payment_method->removeDecorator('Errors');
    $this->payment_method->getValidator('NotEmpty')
            ->setMessage('Please select payment method.', 'isEmpty');
    
    // card number
    $this->addElement('Text', 'cardnumber', array(
      'label' => 'Card Number',
      'placeholder' => 'Card Number',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true)
      ),
      'tabindex' => $tabindex++,
      'class' => 'span4',
    ));
    $this->cardnumber->removeDecorator('Errors');
    $this->cardnumber->getValidator('NotEmpty')
            ->setMessage('Please provide card number.', 'isEmpty');

    // card name
    $this->addElement('Text', 'cardname', array(
      'label' => 'Name on Card',
      'placeholder' => 'Name on Card',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true)
      ),
      'tabindex' => $tabindex++,
      'autofocus' => 'autofocus',
      'class' => 'span4',
    ));
    $this->cardname->removeDecorator('Errors');
    $this->cardname->getValidator('NotEmpty')
            ->setMessage('Please provide the name on card.', 'isEmpty');
    
    // expiry date
    $this->addElement('Text', 'expirydate', array(
      'label' => 'Expiry Date',
      'placeholder' => 'Expiry Date (Ex: MM/YYYY)',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true)
      ),
      'tabindex' => $tabindex++,
      'autofocus' => 'autofocus',
      'class' => 'span4',
    ));
    $this->expirydate->removeDecorator('Errors');
    $this->expirydate->getValidator('NotEmpty')
            ->setMessage('Please card expiry date.', 'isEmpty');

    // security code
    $this->addElement('Text', 'securitycode', array(
      'label' => 'Security Code',
      'placeholder' => 'Security Code (Ex: ***)',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true)
      ),
      'tabindex' => $tabindex++,
      'autofocus' => 'autofocus',
      'class' => 'span4',
    ));
    $this->securitycode->removeDecorator('Errors');
    $this->securitycode->getValidator('NotEmpty')
            ->setMessage('Please provide card security code.', 'isEmpty');
    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Make Payment',
      'type' => 'submit',
      'style' => 'margin-top: 15px;',
      'ignore' => true,
      'tabindex' => $tabindex++,
    ));
  }  
}