<?php
class Mimo_MimoPaymentModule_Model_Observer
{
    public function __construct()
    {
    	
    }
    /**
     * Applies the special price percentage discount
     * @param   Varien_Event_Observer $observer
     * @return  Xyz_Catalog_Model_Price_Observer
     */
    public function save_mimo_transaction_id($observer)
    {
      
    	$table_sql = 'CREATE  TABLE IF NOT EXISTS `mimo_transaction_log` (

  `nMimoTransactionLogID` INT(11) NOT NULL AUTO_INCREMENT ,

  `sMageOrderID` VARCHAR(200) NOT NULL ,

  `sMimoTransactionID` VARCHAR(200) NULL ,

  `sMimoOrderStatus` TINYINT(1) NOT NULL DEFAULT 0 ,

  `dDateAdded` DATETIME NOT NULL , 

  PRIMARY KEY (`nMimoTransactionLogID`) )';
    	
    	$write = Mage::getSingleton('core/resource')->getConnection('core_write');
    	
    	// This will create transaction log table if not exists.
    	$readresult=$write->query($table_sql);
    	
    	$order = $observer->getOrder();
    	
    	$mimo_id = Mage::getSingleton('core/session')->getMiMoTransactionID();
    	
    	$query = "INSERT INTO mimo_transaction_log (sMageOrderID, sMimoTransactionID,sMimoOrderStatus, dDateAdded) values (:mage_order_id, :mimo_transaction_id, 0, NOW())";
    	
    	$binds = array(
    			'mage_order_id'      => $order->getIncrementId(),
    			'mimo_transaction_id'     => $mimo_id
    	);
    	
    	$write->query($query, $binds);
    	 
      	return $this;
    }
}