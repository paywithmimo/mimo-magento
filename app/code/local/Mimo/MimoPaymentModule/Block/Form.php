<?php
/**
 * MiMo Payment Module for Magento > 1.6
 *
 * @category    MiMo
 * @package     MiMo_MiMoPaymentModule
 * @copyright   Copyright (c) 2012 Mimo Inc. (http://www.mimo.com.ng)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mimo_MimoPaymentModule_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * Set template and redirect message
     */
    public function __construct()
    {
	    parent::__construct();
	    $this
		    ->setTemplate('MimoPaymentModule/form.phtml')
			->setRedirectMessage(
				Mage::helper('paypal')->__('You will be redirected to the MiMo website when you place an order.')
			);
    }
}
