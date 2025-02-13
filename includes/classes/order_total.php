<?php
/**
 * File contains the order-totals-processing class ("order-total")
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Jul 03 Modified in v1.5.8-alpha $
 */
/**
 * order-total class
 *
 * Handles all order-total processing functions
 *
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
class order_total extends base {
  var $modules = array();

  // class constructor
  function __construct() {
    global $messageStack, $languageLoader;
    if (defined('MODULE_ORDER_TOTAL_INSTALLED') && zen_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
      $module_list = explode(';', MODULE_ORDER_TOTAL_INSTALLED);

      foreach($module_list as $value) {
        $lang_file = null;
        $module_file = DIR_WS_MODULES . 'order_total/' . $value;
        if (IS_ADMIN_FLAG === true) {
          $lang_file = zen_get_file_directory(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/', $value, 'false');
          $module_file = DIR_FS_CATALOG . $module_file;
        } else {
          $lang_file = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/', $value, 'false');
        }
          if ($languageLoader->hasLanguageFile(DIR_FS_CATALOG . DIR_WS_LANGUAGES,  $_SESSION['language'], $value, '/modules/order_total')) {
              $languageLoader->loadExtraLanguageFiles(DIR_FS_CATALOG . DIR_WS_LANGUAGES,  $_SESSION['language'], $value, '/modules/order_total');
        } else {
          if (IS_ADMIN_FLAG === false && is_object($messageStack)) {
            $messageStack->add('header', WARNING_COULD_NOT_LOCATE_LANG_FILE . $lang_file, 'caution');
          } else {
            $messageStack->add_session(WARNING_COULD_NOT_LOCATE_LANG_FILE . $lang_file, 'caution');
          }
        }
        if (@file_exists($module_file)) {
          include_once($module_file);
          $class = substr($value, 0, strrpos($value, '.'));
          $GLOBALS[$class] = new $class;
          $this->modules[] = $value;
        }
      }
    }
  }

  function process() {
    global $order;
    $order_total_array = array();
    if (is_array($this->modules)) {
      foreach($this->modules as $value) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (!isset($GLOBALS[$class])) continue;
        $GLOBALS[$class]->process();
        if (empty($GLOBALS[$class]->output)) {
           continue; 
        }
        for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
          if (zen_not_null($GLOBALS[$class]->output[$i]['title']) && zen_not_null($GLOBALS[$class]->output[$i]['text'])) {
            $order_total_array[] = array('code' => $GLOBALS[$class]->code,
                                         'title' => $GLOBALS[$class]->output[$i]['title'],
                                         'text' => $GLOBALS[$class]->output[$i]['text'],
                                         'value' => $GLOBALS[$class]->output[$i]['value'],
                                         'sort_order' => $GLOBALS[$class]->sort_order);
          }
        }
      }
    }

    return $order_total_array;
  }

  function output($return_html=false) {
    global $template, $current_page_base;
    $output_string = '';
    if (is_array($this->modules)) {
      foreach($this->modules as $value) {
        $class = substr($value, 0, strrpos($value, '.'));
        $size = sizeof($GLOBALS[$class]->output);

        // ideally, the first part of this IF statement should be dropped, and the ELSE portion is all that should be kept
        if ($return_html == true) {
          for ($i=0; $i<$size; $i++) {
            $output_string .= '              <tr>' . "\n" .
            '                <td align="right" class="' . str_replace('_', '-', $GLOBALS[$class]->code) . '-Text">' . $GLOBALS[$class]->output[$i]['title'] . '</td>' . "\n" .
            '                <td align="right" class="' . str_replace('_', '-', $GLOBALS[$class]->code) . '-Amount">' . $GLOBALS[$class]->output[$i]['text'] . '</td>' . "\n" .
            '              </tr>';
          }
        } else {
          // use a template file for output instead of hard-coded HTML
          require($template->get_template_dir('tpl_modules_order_totals.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_order_totals.php');
        }
      }
    }
    return $output_string;
  }
  //
  // This function is called in checkout payment after display of payment methods. It actually calls
  // two credit class functions.
  //
  // use_credit_amount() is normally a checkbox used to decide whether the credit amount should be applied to reduce
  // the order total. Whether this is a Gift Voucher, or discount coupon or reward points etc.
  //
  // The second function called is credit_selection(). This in the credit classes already made is usually a redeem box.
  // for entering a Gift Voucher number. Note credit classes can decide whether this part is displayed depending on
  // E.g. a setting in the admin section.
  //
  function credit_selection() {
    $selection_array = array();
    if (is_array($this->modules)) {
      foreach($this->modules as $value) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (isset($GLOBALS[$class]->credit_class) && $GLOBALS[$class]->credit_class == true ) {
          $selection = $GLOBALS[$class]->credit_selection();
          if (is_array($selection)) $selection_array[] = $selection;
        }
      }
    }
    return $selection_array;
  }


  // update_credit_account is called in checkout process on a per product basis. Its purpose
  // is to decide whether each product in the cart should add something to a credit account.
  // e.g. for the Gift Voucher it checks whether the product is a Gift voucher and then adds the amount
  // to the Gift Voucher account.
  // Another use would be to check if the product would give reward points and add these to the points/reward account.
  //
  function update_credit_account($i) {
    if (MODULE_ORDER_TOTAL_INSTALLED) {
      foreach($this->modules as $value) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (isset($GLOBALS[$class]->credit_class) && $GLOBALS[$class]->credit_class == true ) {
          $GLOBALS[$class]->update_credit_account($i);
        }
      }
    }
  }


  // This function is called in checkout confirmation.
  // Its main use is for credit classes that use the credit_selection() method. This is usually for
  // entering redeem codes(Gift Vouchers/Discount Coupons). This function is used to validate these codes.
  // If they are valid then the necessary actions are taken, if not valid we are returned to checkout payment
  // with an error

  function collect_posts() {
    if (MODULE_ORDER_TOTAL_INSTALLED) {
      foreach($this->modules as $value) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (isset($GLOBALS[$class]->credit_class) && $GLOBALS[$class]->credit_class == true ) {
          $post_var = 'c' . $GLOBALS[$class]->code;
          if (isset($_POST[$post_var]) && $_POST[$post_var]) $_SESSION[$post_var] = $_POST[$post_var];
          $GLOBALS[$class]->collect_posts();
        }
      }
    }
  }
  // pre_confirmation_check is called on checkout confirmation. Its function is to decide whether the
  // credits available are greater than the order total. If they are then a variable (credit_covers) is set to
  // true. This is used to bypass the payment method. In other words if the Gift Voucher is more than the order
  // total, we don't want to go to paypal etc.
  //
  function pre_confirmation_check($returnOrderTotalOnly = false) {
    global $order, $credit_covers;
    if (MODULE_ORDER_TOTAL_INSTALLED) {
      $orderInfoSaved = $order->info;
      foreach($this->modules as $value) {
        $class = substr($value, 0, strrpos($value, '.'));
        $GLOBALS[$class]->process();
        $GLOBALS[$class]->output = array();
      }
      $reCalculatedOrderTotal = $order->info['total'];
      if ($reCalculatedOrderTotal <= 0.009 && !(isset($_SESSION['payment']) && $_SESSION['payment'] === 'freecharger')) {
        $credit_covers = true;
      }
      $order->info = $orderInfoSaved;
      if ($returnOrderTotalOnly === true) return $reCalculatedOrderTotal;
    }
  }
  // this function is called in checkout process. it tests whether a decision was made at checkout payment to use
  // the credit amount be applied aginst the order. If so some action is taken. E.g. for a Gift voucher the account
  // is reduced the order total amount.
  //
  function apply_credit() {
    if (MODULE_ORDER_TOTAL_INSTALLED) {
      foreach($this->modules as $value) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (isset($GLOBALS[$class]->credit_class) && $GLOBALS[$class]->credit_class == true ) {
          $GLOBALS[$class]->apply_credit();
        }
      }
    }
  }

  // Called in checkout process to clear session variables created by each credit class module.
  //
  function clear_posts() {
    if (MODULE_ORDER_TOTAL_INSTALLED) {
      foreach($this->modules as $value) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (!empty($GLOBALS[$class]->credit_class) && method_exists($GLOBALS[$class], 'clear_posts')) {
          $GLOBALS[$class]->clear_posts();
        }
      }
    }
  }
  // Called at various times. This function calulates the total value of the order that the
  // credit will be applied against. This varies depending on whether the credit class applies
  // to shipping & tax
  //
  function get_order_total_main($class, $order_total) {
    global $credit, $order;
    //      if ($GLOBALS[$class]->include_tax == 'false') $order_total=$order_total-$order->info['tax'];
    //      if ($GLOBALS[$class]->include_shipping == 'false') $order_total=$order_total-$order->info['shipping_cost'];
    return $order_total;
  }
}
