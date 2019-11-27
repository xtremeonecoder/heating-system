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

class Application_Form_Register_Payment extends Zend_Form
{
  public function init()
  {
    // Init form
    $tabindex = 1;
    $this->setAttrib('id', 'payment_form')
      ->setAttrib('class', 'global_form')     
      ->setMethod("POST")
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'member_signup', true));

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
      'class' => 'span5',        
      'multiOptions' => array(
        '' => 'Select Payment Method',
        'visa' => 'Visa Card',
        'master' => 'Master Card'
        //'paypal' => 'Paypal'
      ),
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true)
      )
    ));
    $this->payment_method->removeDecorator('Errors');

    // Init comments
    $this->addElement('Textarea', 'comment', array(
      'label' => 'Additional Comments',
      'placeholder' => 'Additional Comments',
      'required' => false,
      'allowEmpty' => true,
      'tabindex' => $tabindex++,
      'class' => 'span8',        
      'style' => 'height: 150px;',
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        //array('NotEmpty', true)
      )
    ));
    $this->comment->removeDecorator('Errors');
    
    // add dummy element
    $this->addElement(
        'hidden',
        'dummy',
        array(
            'required' => false,
            'ignore' => true,
            'autoInsertNotEmptyValidator' => false,
            'decorators' => array(
                array(
                    'HtmlTag', array(
                        'tag'  => 'p',
                        'id' => 'dummy',
                        'style' => ''
                    )
                )
            )
        )
    );
    $this->dummy->clearValidators();          
    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Finish',
      'type' => 'submit',
      'ignore' => true,
      'tabindex' => $tabindex++,
    ));
  }
}