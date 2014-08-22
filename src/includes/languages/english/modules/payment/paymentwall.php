<?php

/**
* Paymentwall language class
*
* @package paymentMethod
* @copyright Copyright 2014 Paymentwall Inc.
* @version v1.0.0
*/

define('MODULE_PAYMENT_PAYMENTWALL_TEXT_TITLE', 'Paymentwall');
if (IS_ADMIN_FLAG === true) {
  define('MODULE_PAYMENT_PAYMENTWALLTEXT_DESCRIPTION', '<strong>Paymentwall</strong><br /><a href="https://www.paymentwall.com" target="_blank">Manage your Paymentwall account.</a><br /><br />Configuration Instructions:<br />1. <a href="http://www.paymentwall.com/pwaccount/singin" target="_blank">Sign up for your Paymentwall account</a><br />2. In your Paymentwall account",<ul><li>set your pingback URL in <strong>application settings</strong>  to:<br /><nobr><pre>'.str_replace('index.php?main_page=index','paymentwall_pingback.php',zen_catalog_href_link(FILENAME_DEFAULT, '', 'SSL')) . '</pre></nobr><br />' );
 } else {
    define('MODULE_PAYMENT_PAYMENTWALLTEXT_DESCRIPTION', '<strong>Paymentwall</strong>');
  }
?>