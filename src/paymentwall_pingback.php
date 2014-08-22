<?php

/**
* Paymentwall pingback
*
* @package paymentMethod
* @copyright Copyright 2014 Paymentwall Inc.
* @version v1.0.0
*/

require 'paymentwall_api/lib/paymentwall.php';
require 'includes/application_top.php';

global $db;

Paymentwall_Base::setApiType(Paymentwall_Base::API_GOODS);
Paymentwall_Base::setAppKey(MODULE_PAYMENT_PAYMENTWALL_APP_KEY); // available in your Paymentwall merchant area
Paymentwall_Base::setSecretKey(MODULE_PAYMENT_PAYMENTWALL_SECRET_KEY); // available in your Paymentwall merchant area

$pingback = new Paymentwall_Pingback($_GET, $_SERVER['REMOTE_ADDR']);
if ($pingback->validate()) {
  $productId = $pingback->getProduct()->getId();
  $id = $db->Execute("select orders_id from " . TABLE_ORDERS . " where orders_id = " . $productId);

  if(!$id->EOF) {
    if ($pingback->isDeliverable()) {
      $status = MODULE_PAYMENT_PAYMENTWALL_ORDER_STATUS_ID_SUCCESS_PINGBACK;
    } else if ($pingback->isCancelable()) {
      $status = MODULE_PAYMENT_PAYMENTWALL_ORDER_STATUS_ID_CANCEL_PINGBACK;
    }

    $db->Execute("update " . TABLE_ORDERS . " set orders_status = " . $status . " where orders_id = " . intval($productId));
    echo 'OK'; // Paymentwall expects response to be OK, otherwise the pingback will be resent
  } else {
    echo 'Error, order not found';
  }
} else {
  echo $pingback->getErrorSummary();
}