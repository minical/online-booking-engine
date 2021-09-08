<div class="settings">
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-ribbon text-success"></i>
            </div>
            <?php echo l('Online Booking Engine'); ?>
        </div>
    </div>
</div>

<div class="main-card card">
    <div class="card-body">

	<div class="panel panel-default">
	  <div class="panel-heading">
	    <h3 class="panel-title"><?php echo l('online_booking_engine_widget_code'); ?>:</h3>
	  </div>
	  <div class="panel-body">
	    <textarea id="widget-code" class="form-control" rows="7">
	    	<div class="minical-booking-widget" name="<?php echo $company_data['company_id']; ?>" style="width:250px;"></div>
	    	<script>
			    window.miniCal = window.miniCal || {};
			    window.miniCal.companyId = '<?php echo $company_data['company_id']; ?>';
			    window.miniCal.projectUrl = '<?php echo base_url();?>';
			</script>
		<script src="<?php echo base_url();?>js/widget.js?v=2.0" type="text/javascript"></script></textarea>
	  </div>
	</div>

	<div class="form-group">
		<label for="widget-code" class="col-sm-4 control-label">
			
		</label>
		<div class="col-sm-8">
			<div class="input-group">
				
			</div>
		</div>
	</div>


	<div class="panel panel-default">
	  <div class="panel-heading">
	    <h3 class="panel-title"><?php echo l('online_reservation_work_properly'); ?>:</h3>
	  </div>
	  <div class="panel-body">
	    <ul class="bullet-points">
			<li>
				<?php echo l('Create', true); ?> <a href="<?php echo base_url();?>settings/rates/rate_plans"><?php echo l('Rate Plans', true); ?></a>, <?php echo l('and set availability & rates.', true); ?>
			</li>
			<li>
				<?php echo l('Update your property information in', true); ?> <a href="<?php echo base_url();?>settings/company/general"><?php echo l('Property Settings', true); ?></a>
			</li>
			<li>
				<?php echo l('Set the appropriate rooms in', true); ?> <a href="<?php echo base_url();?>settings/room_inventory/rooms"><?php echo l('Room Settings', true); ?></a> <?php echo l("to 'can be sold online'.", true); ?>
			</li>
		</ul>
	  </div>
	</div>

    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><?php echo l('Online booking engine link'); ?>:</h3>
      </div>
      <div class="panel-body">
        <a href="<?php echo base_url()."online_reservation/select_dates_and_rooms/".$company_data['company_id']; ?>" target="_blank"><?php echo base_url()."online_reservation/select_dates_and_rooms/".$company_data['company_id']; ?></a>
      </div>
    </div>

    <div class="panel panel-default">
      	<div class="panel-heading">
        	<h3 class="panel-title"><?php echo l('Online booking engine fields'); ?>:</h3>
      	</div>
      	<div class="panel-body">
        	<table id="booking-engine-fields" class="table">
				<tr>
					<th><?php echo l('Field name', true); ?></th>
					<th class="text-center"><?php echo l('Show on booking form', true); ?></th>
		        	<th class="text-center"><?php echo l('Is a required field', true); ?></th>
				</tr>

			<?php if(isset($booking_engine_fields)): 
				foreach($booking_engine_fields as $booking_field) : ?>		
				<tr class="booking-field-tr" id="<?php echo $booking_field['id']; ?>">
					<td>
						<input name="name" class="form-control" type="text" value="<?php echo l($booking_field['field_name'], true); ?>" maxlength="45" style="width:250px" disabled/>
					</td>
					<td class="text-center">
	                    <div class="checkbox">
	                        <label>
	                            <input type="checkbox" name="show_on_booking_form" autocomplete="off"
	                                <?php
	                                if ($booking_field['show_on_booking_form'] == 1) {
	                                    echo 'checked="checked"';
	                                }
	                                if ($booking_field['id'] == BOOKING_FIELD_NAME) {
										echo 'disabled checked="checked"';
									}
	                                ?>
	                            />
	                        </label>
	                    </div>
	                </td>
	                <td class="text-center">
	                    <div class="checkbox">
	                        <label>
	                            <input type="checkbox" name="is_required" autocomplete="off"
	                                <?php
                                        if ($booking_field['id'] == BOOKING_FIELD_NAME) {
											echo 'disabled checked="checked"';
										}    
                                        if ($booking_field['is_required'] == 1) {
											echo 'checked="checked"';
										}
									?>
	                            />
	                        </label>
	                    </div>
	                </td>
				</tr>
			<?php endforeach; else : ?>	
				<h3><?php echo l('No booking field have been found.', true); ?></h3>
				<?php endif; ?>
			</table>
			<br />
			<button id="save-all-booking-fields-button" class="btn btn-primary"><?php echo l('save_all'); ?></button>
  		</div>
	</div>


	<form class="form-horizontal" method="post" action="<?php echo base_url();?>integrations/booking_engine" autocomplete="off">		
				
		<div class="form-group">
			<label for="website_uri" class="col-sm-4 control-label">
				<?php echo l('allow_same_day_checkins'); ?>
				<p class="help-block h6"><?php echo l("If this feature is disabled, online reservations' check-in date must be tomorrow or later", true); ?></p>
			</label>
			<div class="col-sm-8">
				<select name="allow_same_day_check_in" class="form-control">
					<option value="1" <?php echo ($company_data['allow_same_day_check_in'] == '1')?'SELECTED=SELECTED':''; ?> >
						<?php echo l('Enabled', true); ?>
					</option>
					<option value="0" <?php echo ($company_data['allow_same_day_check_in'] == '0')?'SELECTED=SELECTED':''; ?> >
						<?php echo l('Disabled', true); ?>
					</option>
				</select>
			</div>
		</div>
        <div class="form-group">
            <label for="website_uri" class="col-sm-4 control-label">
                <?php echo l('Require credit card information'); ?>
                <p class="help-block h6"></p>
            </label>

             <div class="col-sm-8">
                <select name="store_cc_in_booking_engine" class="form-control" <?php echo (!$are_gateway_credentials_filled) ? 'disabled"' : '' ?>>
                    <option value="1" <?php echo ($store_cc_in_booking_engine and $are_gateway_credentials_filled) ? 'SELECTED=SELECTED' : ''; ?> >
                        <?php echo l('Enabled', true); ?>
                    </option>
                    <option value="0" <?php echo (!$store_cc_in_booking_engine or !$are_gateway_credentials_filled) ? 'SELECTED=SELECTED' : ''; ?> >
                        <?php echo l('Disabled', true); ?>
                    </option>
                </select>

            </div> 
        </div>
		<!--<div class="form-group">
			<label for="website_uri" class="col-sm-4 control-label">
				<?php echo l('take_payment_using_paypal'); ?>
				<p class="help-block h6"></p>
			</label>
			<div class="col-sm-8">
				<select name="require_paypal_payment" class="form-control">
					<option value="1" <?php echo ($company_data['require_paypal_payment'] == '1')?'SELECTED=SELECTED':''; ?> >
						<?php echo l('Enabled', true); ?>
					</option>
					<option value="0" <?php echo ($company_data['require_paypal_payment'] == '0')?'SELECTED=SELECTED':''; ?> >
						<?php echo l('Disabled', true); ?>
					</option>
				</select>

			</div>
		</div> -->
		<!-- <div class="form-group">
			<label for="website_uri" class="col-sm-4 control-label">
				<?php echo l('paypal_account_email'); ?>: 
			</label>
			<div class="col-sm-8">
				<input type="text" name="paypal_account" class="form-control"
					value="<?php echo $company_data['paypal_account']; ?>"
					<?php if (!$company_data['require_paypal_payment'])
						{
							echo "readonly";
						}
					?>
				/> 
			</div>
		</div>

		<div class="form-group">
			<label for="website_uri" class="col-sm-4 control-label">
				<?php echo l('required_deposit'); ?>:
				<p class="help-block h6">
					<?php echo l('Guests must pay the deposit in order to confirm their reservations', true); ?>
				</p>
			</label>
			<div class="col-sm-8">
				<div class="input-group">
	     			<input type="text" name="percentage_of_required_paypal_payment"  class="form-control"
						value="<?php echo $company_data['percentage_of_required_paypal_payment']; ?>" 
						<?php if (!$company_data['require_paypal_payment'])
							{	
								echo "readonly";
							}
						?>
					/><div class="input-group-addon">%</div>
					
				</div>	
			</div>
		</div> -->
        
        <div class="form-group">
			<label for="mark-reservation" class="col-sm-4 control-label">
				<?php echo l('mark_reservations_from_booking_engine'); ?>
				<p class="help-block h6"></p>
			</label>
			<div class="col-sm-8">
				<select name="booking_engine_booking_status" class="form-control">
					<option value="1" <?php echo ($company_data['booking_engine_booking_status'] == '1')?'SELECTED=SELECTED':''; ?> >
						<?php echo l('Yes', true); ?>
					</option>
					<option value="0" <?php echo ($company_data['booking_engine_booking_status'] == '0')?'SELECTED=SELECTED':''; ?> >
						<?php echo l('No', true); ?>
					</option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="mark-reservation" class="col-sm-4 control-label">
				<?php echo l('Do Not Send Email Confirmation For Online Booking Engine'); ?>
				<p class="help-block h6"></p>
			</label>
			<div class="col-sm-8">
				<input type="checkbox" name="email_confirmation_for_booking_engine" <?=$company_data['email_confirmation_for_booking_engine'] == 1 ? 'checked=checked' : '';?> value="1" style="margin: 10px 0px;" />
			</div>
		</div>

		<div class="form-group">
			<label for="mark-reservation" class="col-sm-4 control-label">
				<?php echo l('Tracking/Analytics code', true); ?>
				<p class="help-block h6">
					<?php echo l('It will be embedded inside head tag on booking engine pages', true); ?>
				</p>
			</label>
			<div class="col-sm-8">
				<textarea class="form-control" rows="5" name="booking_engine_tracking_code"><?php echo html_entity_decode($company_data['booking_engine_tracking_code']); ?>
				</textarea>
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-12 text-center">
				<input class="btn btn-light" type="submit" value="<?php echo l('Update', true); ?>" />
			</div>
		</div>
			
	</form>

</div>
</div></div>