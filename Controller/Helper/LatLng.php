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

class Helper_LatLng extends Zend_Controller_Action_Helper_Abstract 
{
    public function getLatLng($address = false)
    {
        // check address
        if(!$address){
            return false;
        }

        // get google api key
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('DbTable');
        $apiKey = $helper->getTable("settings")->getSetting('google.api.key');

        // get lat-lng from address
        $data = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://maps.googleapis.com/maps/api/geocode/xml?address='.urlencode($address).'&key='.$apiKey->value);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $xmlResponse = curl_exec($ch);
        curl_close($ch);
        
        // parse xml
        $xml = new SimpleXMLElement($xmlResponse);
        $data['lat'] = (string) $xml->result->geometry->location->lat;
        $data['lng'] = (string) $xml->result->geometry->location->lng;        
        return $data;
    }
}