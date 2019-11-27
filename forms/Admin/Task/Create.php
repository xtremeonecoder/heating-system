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

class Application_Form_Admin_Task_Create extends Zend_Form
{
  public function init()
  {
    // Init form
    $tabindex = 1;
    $this->setAttrib('id', 'create_schedule')
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
    
    // ticket priority
    $this->addElement('Select', 'technician', array(
      'label' => 'Select a Technician',
      'placeholder' => 'Select a Technician',
      'required' => true,
      'allowEmpty' => false,
      'tabindex' => $tabindex++,
      'class' => 'span5',        
      'multiOptions' => $technicians,
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true)
      )
    ));
    $this->technician->removeDecorator('Errors');
    $this->technician->getValidator('NotEmpty')
            ->setMessage('Please select a technician for the ticket.', 'isEmpty');
    
    // scheduled_date
    $this->addElement('Text', 'scheduled_date', array(
      'label' => 'Schedule Date',
      'placeholder' => 'Schedule Date',
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
    $this->scheduled_date->removeDecorator('Errors');
    $this->scheduled_date->getValidator('NotEmpty')->setMessage('Please select a date for schedule.', 'isEmpty');
        
    // from_time
    $this->addElement('Text', 'from_time', array(
      'label' => 'Starting Time',
      'placeholder' => 'Starting Time',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true)
      ),    
      'tabindex' => $tabindex++,
      'class' => 'span9',
    ));
    $this->from_time->removeDecorator('Errors');
    $this->from_time->getValidator('NotEmpty')->setMessage('Please select task starting time.', 'isEmpty');

    // to_time
    $this->addElement('Text', 'to_time', array(
      'label' => 'Finishing Time',
      'placeholder' => 'Finishing Time',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        array('NotEmpty', true)
      ),    
      'tabindex' => $tabindex++,
      'class' => 'span8',
    ));
    $this->to_time->removeDecorator('Errors');
    $this->to_time->getValidator('NotEmpty')->setMessage('Please select task finishing time.', 'isEmpty');
    
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
      'label' => 'Save Schedule',
      'type' => 'submit',
      'style' => 'margin-top: 15px;',
      'ignore' => true,
      'tabindex' => $tabindex++,
    ));
  }  
}