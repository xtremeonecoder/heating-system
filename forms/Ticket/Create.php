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

class Application_Form_Ticket_Create extends Zend_Form
{
  public function init()
  {
    // Init form
    $tabindex = 1;
    $this->setAttrib('id', 'create_ticket')
      ->setAttrib('class', 'global_form')      
      ->setMethod("POST")
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    // to show the errors above the form
    $this->setDecorators(array(
        'FormElements',
        'Form',
        array('FormErrors', array('placement' => 'prepend'))
    ));        

    // ticket title
    $this->addElement('Text', 'title', array(
      'label' => 'Ticket Title',
      'placeholder' => 'Ticket Title',
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
      'class' => 'span5',
      'style' => 'margin-bottom: 25px;'
    ));
    $this->title->removeDecorator('Errors');
    $this->title->getValidator('NotEmpty')
            ->setMessage('Please give a title of the ticket.', 'isEmpty');
      
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
    
    // ticket message
    $this->addElement('Textarea', 'description', array(
      'label' => 'Description of Problem',
      'placeholder' => 'Description of Problem',
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
            ->setMessage('Please write problem description.', 'isEmpty');
    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Raise Ticket',
      'type' => 'submit',
      'style' => 'margin-top: 15px;',
      'ignore' => true,
      'tabindex' => $tabindex++,
    ));
  }  
}