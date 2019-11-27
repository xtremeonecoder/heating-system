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

class Application_Form_Admin_Invoice_Filter extends Zend_Form
{
  public function init()
  {
    $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
    $this
      ->clearDecorators()
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'clear'))
      ;

    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setMethod('GET');

    $keyword = new Zend_Form_Element_Text('keyword');
    $keyword
      ->setLabel('Keyword:')
      ->setAttribs(array(
          'class' => 'span',
          'placeholder' => 'Search by keyword'
      ))
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'margin-right: 10px; float: left;'));

    $status = new Zend_Form_Element_Select('status');
    $status
      ->setLabel('Status:')
      ->setAttribs(array(
          'class' => 'span'
      ))
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'margin-right: 10px; float: left;'))
      ->setMultiOptions(array(
        '' => 'Search by payment status',
        '1' => 'Payment Pending',
        '2' => 'Payment Made'
      ))
      ->setRegisterInArrayValidator(false);

    $submit = new Zend_Form_Element_Button('search', array('type' => 'submit'));
    $submit
      ->setLabel('Search')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'buttons'))
      ->addDecorator('HtmlTag', array('tag' => 'div'));

//    $this->addElement('Hidden', 'order', array(
//      'order' => 10001,
//    ));
//
//    $this->addElement('Hidden', 'direction', array(
//      'order' => 10002,
//    ));
//
//    $this->addElement('Hidden', 'user_id', array(
//      'order' => 10003,
//    ));
    
    $this->addElements(array(
      $keyword,
      $status,
      $submit
    ));

    // Set default action without URL-specified params
    $params = array();
    foreach (array_keys($this->getValues()) as $key) {
      $params[$key] = null;
    }
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble($params));
  }
}