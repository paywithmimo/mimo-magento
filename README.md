## Requirements
- [PHP](http://www.php.net/)
- [CURL PHP](http://php.net/manual/en/book.curl.php)
- [JSON PHP](http://php.net/manual/en/book.json.php)

## Installation Guide:

Place app/code/local/Mimo directory into your magento_directory/app/code/local folder. If you do not have local folder under /app/code/ then you can create new.

Place app/design/adminhtml/default/mimo folder and place into your magento_directory/app/design/adminhtml/default folder

Place app/design/frontend/base/default/template/MimoPaymentModule folder place into your magento_directory/app/design/frontend/base/default/template folder

Place app/etc/modules/Mimo_MimoPaymentModule.xml file and place into your magento_directory/app/etc/modules floder

Open app/etc/local.xml file and copy and paste below content in your magento_directory/app/etc/local.xml file before ending of </config> tag.

<pre>
&lt;stores&gt;
	&lt;admin&gt;
		&lt;design&gt;
		&lt;theme&gt;
			&lt;default&gt;mimo&lt;/default&gt;
		&lt;/theme&gt;
		&lt;/design&gt;
	&lt;/admin&gt;
&lt;/stores&gt;
</pre>
Important: 
Don't replace whole local.xml file.
Copy and paste only above content only.


## Configuration Guide:

Now you can set up your MiMo module. Login with your magento admin panel and go through below step for configure MiMo model. 

1) Go to system/Configuration/Advance/advance/Disable Modules Output/Mimo_MimoPaymentModule. Enable this module if it is not already enabled.
2) Go to system/Configuration/Sales/checkout/checkout options/Enable Onepage Checkout. Select No from dropdown. So simply you just need to disable one page checkout.
3) Go to system/Configuration/SALES/Payment Methods/MiMO. From here you need to configure MiMo Payment method related changes

-- Enabled : Select yes 
-- Payment Action : Authorize and Capture
-- Payment Method Name : Place the name which you want to display in Checkout Process
-- New Order Status : Select the Order status which are done using MiMo
-- API Key : Provide API key given by MiMo payment getway
-- API Secret : Provide API Secret given by MiMo
-- MiMo Payment Platform : Select Mode in which you want to Run MiMo Payment Gateway

Note: If you are not getting above module configuration, then make sure you have cleared you magento cache.