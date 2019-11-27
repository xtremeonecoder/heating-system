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

class Application_Form_Ticket_Priority extends Zend_Form
{
  public function init()
  {
    // Init form
    $tabindex = 1;
    $this->setAttrib('id', 'priority_ticket')
      ->setAttrib('class', 'global_form')      
      ->setMethod("POST")
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    // to show the errors above the form
    $this->setDecorators(array(
        'FormElements',
        'Form',
        array('FormErrors', array('placement' => 'prepend'))
    ));        

    // ticket priority
    $this->addElement('Select', 'priority', array(
      'label' => 'Select Ticket Priority',
      'placeholder' => 'Select Ticket Priority',
      'required' => true,
      'allowEmpty' => false,
      'tabindex' => $tabindex++,
      'class' => 'span5',        
      'multiOptions' => array(
        '' => 'Select Ticket Priority',
        'higher' => 'Problems need to be solved with higher priority',
        'medium' => 'Problems can be solved with medium priority',
        'lower' => 'Problems may be solved with lower priority'
      ),
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true)
      )
    ));
    $this->priority->removeDecorator('Errors');
    $this->priority->getValidator('NotEmpty')
            ->setMessage('Please select priority of the ticket.', 'isEmpty');

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'style' => 'margin-top: 15px;',
      'ignore' => true,
      'tabindex' => $tabindex++,
    ));
  }  
}