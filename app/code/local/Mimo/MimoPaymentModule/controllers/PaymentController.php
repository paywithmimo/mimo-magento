<?php
/**
 * MiMo Payment Module for Magento > 1.6
 *
 * @category    MiMo
 * @package     MiMo_MiMoPaymentModule
 * @copyright   Copyright (c) 2012 Mimo Inc. (http://www.mimo.com.ng)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mimo_MimoPaymentModule_PaymentController extends Mage_Core_Controller_Front_Action {
	public function redirectAction() {
		$this->loadLayout();
		$block = $this->getLayout()->createBlock('Mage_Core_Block_Template','MimoPaymentModule',array('template' => 'MimoPaymentModule/redirect.phtml')); 
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();
	}
}