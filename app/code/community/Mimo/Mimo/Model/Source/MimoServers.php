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
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * Authorizenet Payment Action Dropdown source
 *
 * @author      Mimo Inc <mimo.com>
 */
class Mimo_Mimo_Model_Source_MimoServers
{
	
    const SERVER_TEST  = 'Test';
    const SERVER_LIVE  = 'Live';
    
    public function toOptionArray()
    {
        return array(
           	array(
                'value' => self::SERVER_LIVE,
                'label' => Mage::helper('paygate')->__('Live')
            ),
            array(
                'value' => self::SERVER_TEST,
                'label' => Mage::helper('paygate')->__('Staging')
            ),
        );
    }
}
