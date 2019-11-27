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

class Application_Form_Register_Contact extends Zend_Form
{
    public function init()
    {
        $tabindex = 1;
        $this->setAttrib('id', 'contact_form')
          ->setAttrib('class', 'global_form')      
          ->setMethod("POST")
          ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'member_signup', true));

        // to show the errors above the form
        $this->setDecorators(array(
            'FormElements',
            'Form',
            array('FormErrors', array('placement' => 'prepend'))
        ));        
        
        // Telephone Number
        $this->addElement('Text', 'phone_no', array(
          'label' => 'Phone Number',
          'placeholder' => 'Phone Number',
          'allowEmpty' => false,
          'required' => true,
          'tabindex' => $tabindex++,
          'class' => 'span5',
          'validators' => array(
            array('NotEmpty', true),
            //array('Regex', true, array('/^((\+44)?[ ]?(\([0-9 ]{0,5}\))?[ ]?[0-9 ]{6,20})$/i')),
            array('StringLength', false, array(1, 50)),
          ),
        ));
        $this->phone_no->removeDecorator('Errors');        
        //$this->phone_no->getValidator('Regex')->setMessage('Invalid telephone number entered!', 'regexNotMatch');
        
        // Mobile Number
        $this->addElement('Text', 'mobile_no', array(
          'label' => 'Mobile Number',
          'placeholder' => 'Mobile Number',
          'allowEmpty' => false,
          'required' => true,
          'tabindex' => $tabindex++,
          'class' => 'span5',
          'validators' => array(
            array('NotEmpty', true),
            //array('Regex', true, array('/^((07)[ ]?(\([0-9 ]{0,5}\))?[ ]?[0-9 ]{6,20})$/i')),
            array('StringLength', false, array(1, 50)),
          ),
        ));
        $this->mobile_no->removeDecorator('Errors');
        //$this->mobile_no->getValidator('Regex')->setMessage('Invalid mobile number entered!', 'regexNotMatch');
        
        // Complany Address Line-1
        $this->addElement('Text', 'address1', array(
          'label' => 'Address Line 1',
          'placeholder' => 'Address Line 1',
          'allowEmpty' => false,
          'required' => true,
          'class' => 'span5',
          'tabindex' => $tabindex++,
          'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 512)),
          ),
        ));
        $this->address1->removeDecorator('Errors');
        
        // Complany Address Line-2
        $this->addElement('Text', 'address2', array(
          'label' => 'Address Line 2 (Optional)',
          'placeholder' => 'Address Line 2 (Optional)',
          'allowEmpty' => true,
          'required' => false,
          'class' => 'span5',
          'tabindex' => $tabindex++,
          'validators' => array(
            //array('NotEmpty', true),
            //array('StringLength', false, array(1, 512)),
          ),
        ));
        $this->address2->removeDecorator('Errors');
        
        // Complany Address Line-3
        $this->addElement('Text', 'address3', array(
          'label' => 'Address Line 3 (Optional)',
          'placeholder' => 'Address Line 3 (Optional)',
          'allowEmpty' => true,
          'required' => false,
          'class' => 'span5',
          'tabindex' => $tabindex++,
          'validators' => array(
            //array('NotEmpty', true),
            //array('StringLength', false, array(1, 512)),
          ),
        ));
        $this->address3->removeDecorator('Errors');
        
        // Complany Address Line-4
        $this->addElement('Text', 'address4', array(
          'label' => 'Address Line 4 (Optional)',
          'placeholder' => 'Address Line 4 (Optional)',
          'allowEmpty' => true,
          'required' => false,
          'class' => 'span5',
          'tabindex' => $tabindex++,
          'validators' => array(
            //array('NotEmpty', true),
            //array('StringLength', false, array(1, 512)),
          ),
        ));
        $this->address4->removeDecorator('Errors');
        
        // Town / City Name
        $this->addElement('Text', 'city_name', array(
          'label' => 'Town / City',
          'placeholder' => 'Town / City',
          'allowEmpty' => false,
          'required' => true,
          'class' => 'span5',
          'tabindex' => $tabindex++,
          'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 64)),
          ),
        ));
        $this->city_name->removeDecorator('Errors');
                      
        // Post Code
        $this->addElement('Text', 'postcode', array(
          'label' => 'Post Code',
          'placeholder' => 'Post Code',
          'allowEmpty' => false,
          'required' => true,
          'class' => 'span5',
          'tabindex' => $tabindex++,
          'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 64))
          ),
        ));
        $this->postcode->removeDecorator('Errors');
        $specialValidator = new Aninda_Validate_Callback(array($this, 'checkValidPostcode'), $this->postcode);
        $specialValidator->setMessage('Invalid post code entered!', 'invalid');
        $this->postcode->addValidator($specialValidator);
                           
        // Country Name
        $this->addElement('Text', 'country_name', array(
          'label' => 'Country',
          'placeholder' => 'Country',
          'allowEmpty' => false,
          'required' => true,
          'class' => 'span5',
          'tabindex' => $tabindex++,
          'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 64)),
          ),
        ));
        $this->country_name->removeDecorator('Errors');
        
        // Submit Button
        $this->addElement('Button', 'submit', array(
          'label' => 'Continue',
          'type' => 'submit',
          'tabindex' => $tabindex++,
          'ignore' => true,
        ));                
    }
    
    public function checkValidPostcode($value, $postcodeElement){
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('PdnValidators');
        if(!empty($value) || $helper->isValidPostCode($value)){
            return true;
        }
        
        return false;
    }  
}