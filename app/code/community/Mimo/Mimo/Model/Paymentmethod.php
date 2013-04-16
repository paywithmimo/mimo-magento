<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mimo
 * @package    Mimo (mimo.com)
 * @copyright  Copyright (c) 2011 Mimo (https://www.mimo.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mimo_Mimo_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract
{		
	protected $_code = 'mimo';
	
    protected $_canCapture                  = true;
	protected $_canReviewPayment            = false;
	protected $_canAuthorize           = true;

	protected $_formBlockType = 'mimo/form';
	protected $_infoBlockType = 'mimo/info';
	protected $_paymentMethod = 'mimo';  
	protected $_title;
	protected $_description;
	protected $_enabled;
	
	protected $_accountId;
	protected $_paymentKey;
	protected $_keyVerified = false;
	
	// Order specific fields
	protected $_order_status;
	/**
     * @var string error messages returned from Mimo
     */
    private $errorMessage = false; 
   
    
	
	/**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
   	public function assignData($data)
	{
		if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}                                
		$info = $this->getInfoInstance();
		//set mimo account number
		$info->setPoNumber($data->getPoNumber());        	
		return $this;
   	}	
	
	/**
     * Capture payment
	 * Corresponds with Admin payment method config Payment Action Authorize and Capture
     * Used for authorising AND capturing a transaction
     * @param   Varien_Object $orderPayment
	 * @param 	Varian_Object $amount
     * @return  Mimo_Mimo_Model_Paymentmethod
     */
    public function authorize(Varien_Object $payment, $amount)
    { 
    	$token = Mage::getSingleton('core/session')->getTokenMage('token');    
    if (!$amount) {
    	return $this->setError('Please enter a transaction ID.');
    }
    $params = array('notes'=>"Payment done",
    		'amount' => $amount    		 
    );    
   $url =  'https://staging.mimo.com.ng/partner/transfers?amount=' . $amount .'&notes=test&access_token='.$token;
   $params['access_token'] = $this->accessToken;
   // Encode POST data
   $data = json_encode($params);
   // Set request headers
   $headers = array('Accept: application/json', 'Content-type: application/json;charset=UTF-8');
  	$headers[] = 'Content-Length: ' . strlen($data);   
   // Set up our CURL request
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  'POST');
   curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
   curl_setopt($ch, CURLOPT_TIMEOUT, 5);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
   curl_setopt($ch, CURLOPT_HEADER, false);
   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
   curl_setopt($ch, CURLOPT_USERPWD, "mimo:mimo");
   // Windows require this certificate
   $ca = dirname(__FILE__);
   curl_setopt($ch, CURLOPT_CAINFO, $ca); // Set the location of the CA-bundle
   curl_setopt($ch, CURLOPT_CAINFO, $ca . '/cacert.pem'); // Set the location of the CA-bundle
   // Initiate request
   $rawData = curl_exec($ch);   
   // If HTTP response wasn't 200,
   // log it as an error!
   $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   
   if ($code !== 200 && $code!=='201' ) {
   	return array(
   			'Success' => false,
   			'Message' => "Request failed. Server responded with: {$code}"
   	);
   }   
   // All done with CURL
   curl_close($ch);
   // Otherwise, assume we got some
   // sort of a response
   return json_decode($rawData, true);    
    if (isset($response['error'])) {
    	$this->errorMessage = $response['error_description'];
    	return false;
    }
						
        return $this;
    }

}
	
	