<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $Qaccount = osC_Account::getEntry();
?>

<?php echo osc_image(DIR_WS_IMAGES . $osC_Template->getPageImage(), $osC_Template->getPageTitle(), HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, 'id="pageIcon"'); ?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  if ($osC_MessageStack->size('account_edit') > 0) {
    echo $osC_MessageStack->get('account_edit');
  }
?>

<form name="account_edit" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'edit=save', 'SSL'); ?>" method="post" onsubmit="return check_form(account_edit);">

<div class="moduleBox">
  <em style="float: right; margin-top: 10px;"><?php echo $osC_Language->get('form_required_information'); ?></em>

  <h6><?php echo $osC_Language->get('my_account_title'); ?></h6>

  <div class="content">
    <ol>

<?php
  if (ACCOUNT_GENDER > -1) {
    $gender_array = array(array('id' => 'm', 'text' => $osC_Language->get('gender_male')),
                          array('id' => 'f', 'text' => $osC_Language->get('gender_female')));
?>

      <li><?php echo osc_draw_label($osC_Language->get('field_customer_gender'), 'fake', null, (ACCOUNT_GENDER > 0)) . osc_draw_radio_field('gender', $gender_array, $Qaccount->value('customers_gender')); ?></li>

<?php
  }
?>

      <li><?php echo osc_draw_label($osC_Language->get('field_customer_first_name'), 'firstname', null, true) . ' ' . osc_draw_input_field('firstname', $Qaccount->value('customers_firstname')); ?></li>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_last_name'), 'lastname', null, true) . ' ' . osc_draw_input_field('lastname', $Qaccount->value('customers_lastname')); ?></li>

<?php
  if (ACCOUNT_DATE_OF_BIRTH == '1') {
?>

      <li><?php echo osc_draw_label($osC_Language->get('field_customer_date_of_birth'), 'dob_days', null, true) . ' ' . osc_draw_date_pull_down_menu('dob', array('year' => $Qaccount->value('customers_dob_year'), 'month' => $Qaccount->value('customers_dob_month'), 'date' => $Qaccount->value('customers_dob_date')), false, null, null, date('Y')-1901, -5); ?></li>

<?php
  }
?>

      <li><?php echo osc_draw_label($osC_Language->get('field_customer_email_address'), 'email_address', null, true) . ' ' . osc_draw_input_field('email_address', $Qaccount->value('customers_email_address')); ?></li>
    </ol>
  </div>
</div>

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_draw_image_submit_button('button_continue.gif', $osC_Language->get('button_continue')); ?>
</div>

</form>
