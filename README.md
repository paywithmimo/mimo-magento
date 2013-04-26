## Requirements
- [PHP](http://www.php.net/)
- [CURL PHP](http://php.net/manual/en/book.curl.php)
- [JSON PHP](http://php.net/manual/en/book.json.php)

## Installation Guide:

Place <strong>app/code/local/Mimo</strong> directory into your <strong>magento_directory/app/code/local</strong> directory. If you do not have local directory under /app/code/ then you can create new.

Place <strong>app/design/adminhtml/default/mimo</strong> directory into your <strong>magento_directory/app/design/adminhtml/default</strong> directory.

Place <strong>app/design/frontend/base/default/template/MimoPaymentModule</strong> directory into your <strong>magento_directory/app/design/frontend/base/default/template</strong> directory

Place <strong>app/etc/modules/Mimo_MimoPaymentModule.xml</strong> file into your <strong>magento_directory/app/etc/modules</strong> directory

Open <strong>app/etc/local.xml</strong> file and copy and paste below content in your <strong>magento_directory/app/etc/local.xml</strong> file before ending of <strong>&lt;/config&gt;</strong> tag.

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

<strong>Important: </strong>
Don't replace whole local.xml file.
Copy and paste only above content only.


## Configuration Guide:

Now you can set up your MiMo module. Login with your magento admin panel and go through below step for configure MiMo Module. 

<ol>
<li>Go to System --> Configuration --> Advance --> advance/Disable Modules Output/Mimo_MimoPaymentModule. Enable this module if it is not already enabled.</li>
<li>Go to System --> Configuration --> Sales --> checkout --> checkout options --> Enable Onepage Checkout. Select No from dropdown. So simply you just need to disable one page checkout.</li>
<li>Go to System --> Configuration --> Sales  --> Payment Methods --> MiMo. From here you need to configure MiMo Payment method related changes
<ul>
<li>Enabled : Select Yes</li>
<li>Payment Action : Authorize and Capture</li>
<li>Payment Method Name : Place the name which you want to display in Checkout Process</li>
<li>New Order Status : Select the Order status which are done using MiMo</li>
<li>API Key : Provide API key given by MiMo payment getway</li>
<li>API Secret : Provide API Secret given by MiMo</li>
<li>MiMo Payment Platform : Select Mode in which you want to Run MiMo Payment Gateway</li>
</ul>
</li>
</ol>

<strong>Note:</strong> If you are not getting above module configuration, then make sure you have cleared you magento cache.