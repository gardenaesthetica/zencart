<?php
// -----
// Part of the One-Page Checkout plugin, provided under GPL 2.0 license by lat9 (cindy@vinosdefrutastropicales.com).
// Copyright (C) 2017-2022, Vinos de Frutas Tropicales.  All rights reserved.
//
// Last updated: OPC v2.4.2/Bootstrap v3.4.0
//
?>
<div class="centerColumn" id="loginOpcDefault">
    <h1 id="loginDefaultHeading"><?php echo HEADING_TITLE; ?></h1>
<?php 
if ($messageStack->size('login') > 0) {
    echo $messageStack->output('login');
}

// -----
// The 'presumed' name of the login-form has changed in zc157 and is used by the login-page's
// onload javascript processing.  Determine the name to use for that form, based on the
// site's current Zen Cart version.
//
$login_formname = (PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR >= '1.5.7') ? 'loginForm' : 'login';

//$block_class = 'opc-block-' . $num_columns;
$bs_column_width = ($num_columns > 0) ? 12 / $num_columns : 1;
$block_class = 'col-md-' . $bs_column_width;
?>
    <div class="row">
<?php
foreach ($column_blocks as $display_blocks) {
    if (count($display_blocks) > 0) {
?>
        <div class="opc-block <?php echo $block_class; ?>">
<?php
        foreach ($display_blocks as $current_block) {
            switch ($current_block) {
                // -----
                // Existing customer login
                //
                case 'L':
?>
            <div class="card mb-2">
                <h4 class="card-header"><?php echo HEADING_RETURNING_CUSTOMER_OPC; ?></h4>
                <div class="card-body">
                    <div class="card-text"><?php echo TEXT_RETURNING_CUSTOMER_OPC; ?>
<?php 
                    echo zen_draw_form($login_formname, zen_href_link(FILENAME_LOGIN, 'action=process' . (isset($_GET['gv_no']) ? '&gv_no=' . preg_replace('/[^0-9.,%]/', '', $_GET['gv_no']) : ''), 'SSL'), 'post', 'id="loginForm"'); 
?>
                    <div class="opc-label"><?php echo ENTRY_EMAIL_ADDRESS; ?></div>
<?php 
                    echo zen_draw_input_field('email_address', '', 'size="18" id="login-email-address" autofocus placeholder="' . ENTRY_EMAIL_ADDRESS_TEXT . '"' . ((int)ENTRY_EMAIL_ADDRESS_MIN_LENGTH > 0 ? ' required' : ''), 'email'); 
?>

                        <div class="opc-label"><?php echo ENTRY_PASSWORD; ?></div>
<?php 
                    echo zen_draw_password_field('password', '', 'size="18" id="login-password" autocomplete="off" placeholder="' . ENTRY_REQUIRED_SYMBOL . '"' . ((int)ENTRY_PASSWORD_MIN_LENGTH > 0 ? ' required' : '')); 
?>
                    <div id="opc-pwf"><?php echo '<a href="' . zen_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN . '</a>'; ?></div>
                    <div class="text-right"><?php echo zen_image_submit(BUTTON_IMAGE_LOGIN, BUTTON_LOGIN_ALT); ?></div>
<?php
                    echo '</form>';
?>
                    </div>
                </div>
            </div>
<?php
                    break;

                // -----
                // PayPal Express Checkout Shortcut Button.
                //
                // Note: OPC v2.4.1 introduces a flag that indicates whether the 'divider' should be displayed
                // before or after the PPEC button.  The 'legacy' default is 'next' (i.e. after the button).
                //
                case 'P':
                    if (!isset($ppec_divider_location)) {
                        $ppec_divider_location = 'next';
                    }
                    if ($ppec_divider_location === 'prev') {
?>
            <hr>
<?php
                        echo TEXT_NEW_CUSTOMER_POST_INTRODUCTION_DIVIDER;
                    }
?>
            <div class="information"><?php echo TEXT_NEW_CUSTOMER_INTRODUCTION_SPLIT; ?></div>
            <div class="center"><?php require DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/tpl_ec_button.php'; ?></div>
<?php
                    if ($ppec_divider_location === 'next') {
?>
            <hr>
<?php
                        echo TEXT_NEW_CUSTOMER_POST_INTRODUCTION_DIVIDER;
                    }
                    break;

                // -----
                // Guest-checkout link
                //
                case 'G':
?>
            <div class="card mb-2">
                <h4 class="card-header"><?php echo HEADING_GUEST_OPC; ?></h4>
                <div class="card-body">
                    <div class="card-text"><?php echo TEXT_GUEST_OPC; ?>
<?php
                    if (!$guest_active) {
                        echo zen_draw_form('guest', zen_href_link(FILENAME_CHECKOUT_ONE, '', 'SSL'), 'post') . zen_draw_hidden_field('guest_checkout', 1);
?>
                    <div class="text-right"><?php echo zen_image_submit(BUTTON_IMAGE_CHECKOUT, BUTTON_CHECKOUT_ALT); ?></div>
<?php
                        echo '</form>';
                    } else {
?>
                    <div class="text-right"><a href="<?php echo zen_href_link(FILENAME_CHECKOUT_ONE, '', 'SSL'); ?>"><?php echo zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_GUEST_CHECKOUT_CONTINUE); ?></a></div>
<?php
                    }
?>
                    </div>
                </div>
            </div>
<?php
                    break;

                // -----
                // Create/register account link.
                //
                case 'C':
?>
            <div class="card mb-2">
                <h4 class="card-header"><?php echo HEADING_NEW_CUSTOMER_OPC; ?></h4>
                <div class="card-body">
                    <div class="card-text"><?php echo TEXT_NEW_CUSTOMER_OPC; ?>
<?php 
                    echo zen_draw_form('create', zen_href_link(FILENAME_CREATE_ACCOUNT, (isset($_GET['gv_no']) ? '&gv_no=' . preg_replace('/[^0-9.,%]/', '', $_GET['gv_no']) : ''), 'SSL'), 'post');
?>
                        <div class="text-right"><?php echo zen_image_submit(BUTTON_IMAGE_CREATE_ACCOUNT, BUTTON_CREATE_ACCOUNT_ALT); ?></div>
<?php
                    echo '</form>';
?>
                    </div>
                </div>
            </div>
<?php
                    break;

                // -----
                // Account benefits display
                //
                case 'B':
?>
            <div class="card mb-2">
                <h4 class="card-header"><?php echo HEADING_ACCOUNT_BENEFITS_OPC; ?></h4>
                <div class="card-body">
                    <div class="card-text"><?php echo TEXT_ACCOUNT_BENEFITS_OPC; ?></div>
<?php
                    for ($i = 1; $i < 5; $i++) {
                        $benefit_heading = "HEADING_BENEFIT_$i";
                        $benefit_text = "TEXT_BENEFIT_$i";
                        if (defined($benefit_heading) && constant($benefit_heading) != '' && defined($benefit_text) && constant($benefit_text) != '') {
?>
                    <div class="card mt-2">
                        <div class="card-header"><?php echo constant($benefit_heading); ?></div>
                        <div class="card-body"><?php echo constant($benefit_text); ?></div>
                    </div>
<?php
                        }
                    }
?>
                </div>
            </div>
<?php
                    break;

                // -----
                // Anything else, nothing to do.
                //
                default:
                    break;
            }
        }
?>
        </div>
<?php
    }
}
?>
        <div class="clearBoth"></div>
    </div>
</div>
