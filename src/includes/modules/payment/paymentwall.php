<?php

/**
* Paymentwall payment method class
*
* @package paymentMethod
* @copyright Copyright 2014 Paymentwall Inc.
* @version v1.0.0
*/

class paymentwall {

  /**
   * Class constructor
   */
  function paymentwall() {
    global $order;

    $this->title = MODULE_PAYMENT_PAYMENTWALL_TEXT_TITLE;
    $this->code = 'paymentwall';
    $this->codeVersion = '1.0.0';
    $this->description = MODULE_PAYMENT_PAYMENTWALLTEXT_DESCRIPTION;
    $this->sort_order = 0;
    $this->enabled = ((MODULE_PAYMENT_PAYMENTWALL_STATUS == 'True') ? true : false);
    $this->paymentMethod = 'Paymentwall';

    if ((int)MODULE_PAYMENT_PAYMENTWALL_ORDER_STATUS_ID_BEFORE_PINGBACK > 0) {
      $this->order_status = MODULE_PAYMENT_PAYMENTWALL_ORDER_STATUS_ID_BEFORE_PINGBACK;
    }

    if (is_object($order))
      $this->update_status();

    // $this->form_action_url = '/paymentwall_widget.php';
  }

  function update_status() {
    global $order, $db;

      if ($this->enabled && (int)MODULE_PAYMENT_PAYMENTWALL_ZONE > 0 && isset($order->billing['country']['id'])) {
        $check_flag = false;
        $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYMENTWALL_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
        while (!$check->EOF) {
          if ($check->fields['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
          $check->MoveNext();
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
  }

  function javascript_validation() {
    return false;
  }

  function selection() {
    return array('id' => $this->code,
                 'module' => $this->title);
  }

  function pre_confirmation_check() {
    return false;
  }

  function confirmation() {
    return array('title' => MODULE_PAYMENT_PAYMENTWALLTEXT_DESCRIPTION);
  }

  function process_button() {
    return false;
  }

  function before_process() {
    return false;
  }

  function after_process() {
    global $db, $order, $insert_id;

    // unpaid order
    $db->Execute("update " . TABLE_ORDERS . " set orders_status = 2 where orders_id = " . intval($insert_id));

    $order->info['payment_method'] = 'Paymentwall';
    $order->info['payment_module_code'] = 'paymentwall';

    $_SESSION['order'] = base64_encode(serialize($order));
    $_SESSION['insert_id'] = $insert_id;

    zen_redirect('paymentwall_widget.php');
  }

  function get_error() {
    return false;
  }

  function check() {
    global $db;

    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYMENTWALL_STATUS'");
      $this->_check = $check_query->RecordCount();
    }

    return $this->_check;
  }

  /**
   * Install the payment module and set base settings
   */
  function install() {
    global $db, $messageStack;

    if (defined('MODULE_PAYMENT_PAYMENTWALL_STATUS')) {
      $messageStack->add_session('Paymentwall module already installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=paymentwall', 'NONSSL'));
      return 'failed';
    }

    $db->Execute("insert into " . TABLE_CONFIGURATION
                  . " (configuration_title, configuration_key, configuration_value, 
                      configuration_description, configuration_group_id, sort_order, set_function, date_added)
                  values ('Enable Paymentwall module', 'MODULE_PAYMENT_PAYMENTWALL_STATUS', 'True',
                          'Do you want to accept payments via Paymentwall?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION
                  . " (configuration_title, configuration_key, configuration_value, 
                      configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) 
                  values ('Payment Zone', 'MODULE_PAYMENT_PAYMENTWALL_ZONE', '0', '
                    If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");


    $db->Execute("insert into " . TABLE_CONFIGURATION
                  . " (configuration_title, configuration_key, configuration_value, 
                      configuration_description, configuration_group_id, sort_order, date_added)
                  values ('Application key', 'MODULE_PAYMENT_PAYMENTWALL_APP_KEY', '0000000',
                          'You can get it here <a href=\'https://api.paymentwall.com/developers/applications\'>https://api.paymentwall.com/developers/applications</a>', '6', '0', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION
                  . " (configuration_title, configuration_key, configuration_value, 
                      configuration_description, configuration_group_id, sort_order, date_added)
                  values ('Secret key', 'MODULE_PAYMENT_PAYMENTWALL_SECRET_KEY', '0000000',
                          'You can get it here <a href=\'https://api.paymentwall.com/developers/applications\'>https://api.paymentwall.com/developers/applications</a>', '6', '0', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION
                  . " (configuration_title, configuration_key, configuration_value, 
                      configuration_description, configuration_group_id, sort_order, date_added)
                  values ('Widget code', 'MODULE_PAYMENT_PAYMENTWALL_WIDGET_CODE', '0000000',
                          'You can get it here <a href=\'https://api.paymentwall.com/developers/applications\'>https://api.paymentwall.com/developers/applications</a>', '6', '0', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION 
                  . " (configuration_title, configuration_key, configuration_value, 
                    configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) 
                  values ('Set Order Status before pingback', 'MODULE_PAYMENT_PAYMENTWALL_ORDER_STATUS_ID_BEFORE_PINGBACK', '0', 
                          'Set the status of orders before pingback (recommend use Processing[2])', '6', '2', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION 
                  . " (configuration_title, configuration_key, configuration_value, 
                    configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) 
                  values ('Set Order Status if pingback successful', 'MODULE_PAYMENT_PAYMENTWALL_ORDER_STATUS_ID_SUCCESS_PINGBACK', '0', 
                          'Set the status of orders if pingback successful (recommend use Pending[1])', '6', '1', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION 
                  . " (configuration_title, configuration_key, configuration_value, 
                    configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) 
                  values ('Set Order Status if pingback canceled', 'MODULE_PAYMENT_PAYMENTWALL_ORDER_STATUS_ID_CANCEL_PINGBACK', '0', 
                          'Set the status of orders if pingback canceled (recommend use Update[4])', '6', '4', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION
                  . " (configuration_title, configuration_key, configuration_value, 
                      configuration_description, configuration_group_id, sort_order, date_added)
                  values ('Successful URL', 'MODULE_PAYMENT_PAYMENTWALL_SUCCESS_URI', '',
                          'URL if payment was successful (for example it may be \'thank you\' page', '6', '0', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION
                  . " (configuration_title, configuration_key, configuration_value, 
                      configuration_description, configuration_group_id, sort_order, set_function, date_added)
                  values ('Enable Paymentwall test_mode', 'MODULE_PAYMENT_PAYMENTWALL_TEST_MODE', 'False',
                          'Do you want enable test mode?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

  }

  /**
   * Remove the module
   */
  function remove() {
    global $db;

    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE\_PAYMENT\_PAYMENTWALL\_%'");
  }

  function keys() {
    return array(
      'MODULE_PAYMENT_PAYMENTWALL_STATUS',
      'MODULE_PAYMENT_PAYMENTWALL_APP_KEY',
      'MODULE_PAYMENT_PAYMENTWALL_SECRET_KEY',
      'MODULE_PAYMENT_PAYMENTWALL_WIDGET_CODE',
      'MODULE_PAYMENT_PAYMENTWALL_ORDER_STATUS_ID_BEFORE_PINGBACK',
      'MODULE_PAYMENT_PAYMENTWALL_ORDER_STATUS_ID_SUCCESS_PINGBACK',
      'MODULE_PAYMENT_PAYMENTWALL_ORDER_STATUS_ID_CANCEL_PINGBACK',
      'MODULE_PAYMENT_PAYMENTWALL_SUCCESS_URI',
      'MODULE_PAYMENT_PAYMENTWALL_TEST_MODE'
    );
  }
}