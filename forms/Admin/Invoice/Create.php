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

class Application_Form_Admin_Invoice_Create extends Zend_Form
{
  public function init()
  {
    // Init form
    $tabindex = 1;
    $this->setAttrib('id', 'create_invoice')
      ->setAttrib('class', 'global_form')      
      ->setMethod("POST")
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    // to show the errors above the form
    $this->setDecorators(array(
        'FormElements',
        'Form',
        array('FormErrors', array('placement' => 'prepend'))
    ));        
    
    $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');    
    $technicians = $helper->getTable('users')->getTechniciansAssoc();
    
    // title
    $this->addElement('Text', 'title', array(
      'label' => 'Invoice Title',
      'placeholder' => 'Invoice Title',
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
    $this->title->removeDecorator('Errors');
    $this->title->getValidator('NotEmpty')
            ->setMessage('Please provide invoice title.', 'isEmpty');
        
    // amount
    $this->addElement('Text', 'amount', array(
      'label' => 'Invoice Amount',
      'placeholder' => 'Invoice Amount (Ex: SEK 200.00)',
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
    $this->title->removeDecorator('Errors');
    $this->title->getValidator('NotEmpty')
            ->setMessage('Please provide invoice amount.', 'isEmpty');
    
    // invoice message
    $this->addElement('Textarea', 'description', array(
      'label' => 'Description of Invoice',
      'placeholder' => 'Description of Invoice',
      'required' => true,
      'allowEmpty' => false,
      'class' => 'span5',
      //'style' => 'height: 150px;',
      'tabindex' => $tabindex++,
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true),
        //array('StringLength', false, array(6, 32))
      )
    ));
    $this->description->removeDecorator('Errors');
    $this->description->getValidator('NotEmpty')
            ->setMessage('Please provide invoice description.', 'isEmpty');
    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Send Invoice',
      'type' => 'submit',
      'style' => 'margin-top: 15px;',
      'ignore' => true,
      'tabindex' => $tabindex++,
    ));
  }  
}