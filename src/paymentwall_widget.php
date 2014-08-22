<?php

/**
* Paymentwall widget
*
* @package paymentMethod
* @copyright Copyright 2014 Paymentwall Inc.
* @version v1.0.0
*/

require 'includes/application_top.php';

if($_SESSION['order'] && $_SESSION['insert_id']) {
  require 'paymentwall_api/lib/paymentwall.php';

  Paymentwall_Base::setApiType(Paymentwall_Base::API_GOODS);
  Paymentwall_Base::setAppKey(MODULE_PAYMENT_PAYMENTWALL_APP_KEY);       // available in your Paymentwall merchant area
  Paymentwall_Base::setSecretKey(MODULE_PAYMENT_PAYMENTWALL_SECRET_KEY); // available in your Paymentwall merchant area

  $order = (array)unserialize(base64_decode($_SESSION['order']));

  $products_names = array();
  foreach ($order['products'] as $key => $value) {
    if(!in_array($value['name'], $products_names))
      array_push($products_names, $value['name']);
  }

  $widget = new Paymentwall_Widget(
    $order['customer']['email_address'],        // id of the end-user who's making the payment
    MODULE_PAYMENT_PAYMENTWALL_WIDGET_CODE,     // widget code, e.g. p1; can be picked inside of your merchant account
    array(                                      // product details for Flexible Widget Call. To let users select the product on Paymentwall's end, leave this array empty
      new Paymentwall_Product(
        (int)$_SESSION['insert_id'],            // id of the product in your system
        $order['info']['total'],                // price
        $order['info']['currency'],             // currency code
        implode(', ', $products_names),         // product name
        Paymentwall_Product::TYPE_FIXED         // this is a time-based product; for one-time products, use Paymentwall_Product::TYPE_FIXED and omit the following 3 array elements
      )
    ),
    array(
      'email' => $order['customer']['email_address'],
      'success_url' => strval(MODULE_PAYMENT_PAYMENTWALL_SUCCESS_URI),
      'test_mode' => ((MODULE_PAYMENT_PAYMENTWALL_TEST_MODE == 'True') ? 1 : 0)
    )                                           // additional parameters
  );

  echo $widget->getHtmlCode();
} else {
  zen_redirect('/');
}
