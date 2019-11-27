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

class Application_Form_Contact extends Zend_Form
{
    public function init()
    {
        $this->setAttrib('id', 'contact_form')
          ->setAttrib('class', 'global_form')      
          ->setMethod("POST")
          ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
        
        // to show the errors above the form
        $this->setDecorators(array(
            'FormElements',
            'Form',
            array('FormErrors', array('placement' => 'prepend'))
        ));        
        
        // Full Name
        $this->addElement('Text', 'fullname', array(
          'label' => 'Full Name',
          'placeholder' => 'Full Name',
          'allowEmpty' => false,
          'required' => true,
          'tabindex' => 1,
          'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 512)),
          ),
        ));
        $this->fullname->removeDecorator('Errors');
        
        // Email
        $this->addElement('Text', 'email', array(
          'label' => 'Email Address',
          'placeholder' => 'Email Address',
          'allowEmpty' => false,
          'required' => true,
          'tabindex' => 2,
          'validators' => array(
            array('NotEmpty', true),
            array('EmailAddress', true)
          ),
        ));
        $this->email->removeDecorator('Errors');
        $this->email->getValidator('NotEmpty')->setMessage('Please enter a valid email address.', 'isEmpty');
        
        // Office / Mobile Number
        $this->addElement('Text', 'phone_no', array(
          'label' => 'Office / Mobile Number',
          'placeholder' => 'Office / Mobile Number',
          'allowEmpty' => false,
          'required' => true,
          'tabindex' => 3,
          'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 50)),
            //array('Regex', true, array('/^((07)[ ]?(\([0-9 ]{0,5}\))?[ ]?[0-9 ]{6,20})$/i'))
          ),
        ));
        $this->phone_no->removeDecorator('Errors');

        // Subject
        $this->addElement('Text', 'subject', array(
          'label' => 'Subject',
          'placeholder' => 'Subject',
          'allowEmpty' => false,
          'required' => true,
          'tabindex' => 4,
          'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 256)),
          ),
        ));
        $this->subject->removeDecorator('Errors');
        
        // Body
        $this->addElement('Textarea', 'message', array(
          'label' => 'Message',
          'placeholder' => 'Message',
          'allowEmpty' => false,
          'required' => true,
          'tabindex' => 5,
          'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 1024)),
          ),
        ));
        $this->message->removeDecorator('Errors');

        // Submit Button
        $this->addElement('Button', 'submit', array(
          'label' => 'Send Message',
          'type' => 'submit',
          'tabindex' => 6,
          'ignore' => true,
        ));                
    }
}