<!-- pass tomorrows_date to javascript -->
<!-- this is used in the date selector --> 
<span id="tomorrows-date" style="display: none;"><?php echo $view_data['tomorrows_date']; ?></span>

<div class="container">
	<div class="col-md-12 text-center">
		<?php if (isset($view_data['no_rooms_available'])) : ?>
			<h3><?php echo l('No rooms available for your search. Contact us for openings that may not be listed'); ?></h3>
		<?php endif; ?>
		<br/>
	</div>

    <div class="col-md-2">

    </div>
	<div class="col-md-8">
		<!-- This form needs to be a post for codeigniter form_validation to work -->
		<!-- because get variables are automatically cleared by codeigniter -->
		<form 
			action="<?php echo base_url() . 'online_reservation/select_dates_and_rooms/'.$this->uri->segment(3); ?>"
			method="post"
			class="form-horizontal"
		>
            <input name="company_id" value="<?php echo $this->uri->segment(3); ?>" hidden="hidden" />
			<input name="number-of-rooms" value="1" hidden="hidden" />
			<input name="companyDateFormat" id="companyDateFormat" value="<?php echo $company_data['date_format']; ?>" type="hidden" />
			<!--
			<label for="number-of-rooms"># of Rooms</label>
			<select name="number-of-rooms">
				<option value="1" <?php echo set_select('number-of-rooms', '1', TRUE); ?>>1</option>
				<option value="2" <?php echo set_select('number-of-rooms', '2'); ?>>2</option>
				<option value="3" <?php echo set_select('number-of-rooms', '3'); ?>>3</option>
				<option value="4" <?php echo set_select('number-of-rooms', '4'); ?>>4</option>
			</select>
			-->
			<div class="calendar_process"></div>
			<div class="form-group hotel-calendar-wrapper">
				<input type="hidden" name="hotel-calendar-date-range" id="hotel-calendar-date-range" value="" />
			</div>
			
			
			<div class="form-group hidden">
	  			<label for="check-in-date" class="col-sm-3 control-label"><?php echo l('check_in_date'); ?></label>
				<div class="col-sm-9">
    				<input id="check-in-date" class="form-control" name="check-in-date" type="text" value="<?php //echo set_value('check-in-date', $view_data['today']); ?>" />
    			</div>
			</div>
			<div class="form-group hidden">
	  			<label for="check-out-date" class="col-sm-3 control-label"><?php echo l('Check-out Date'); ?></label>
				<div class="col-sm-9">
    				<input id="check-out-date" class="form-control" name="check-out-date" type="text" value="<?php //echo set_value('check-out-date', $view_data['tomorrows_date']); ?>" />
    			</div>
			</div>
				
				
			<div class="form-group">
				<div class="clearfix" style="margin: auto; max-width: 590px;">
					<!--<label class="col-sm-8 control-label"># <?php echo l('of Adults'); ?></label>-->
					<div class="col-sm-6 mr-b-10">
					<label for="adult_counts" style="font-size:12px">	<?php echo l('Adult Count',true); ?>:</label>
						<select name="adult_count" class="form-control adult_count" id="adult_counts">
							<option value="1" <?php echo set_select('adult_count', '1', TRUE); ?>>1 <?php echo l('Adult', true); ?></option>
							<option value="2" <?php echo set_select('adult_count', '2'); ?>>2 <?php echo l('Adults', true); ?></option>
							<option value="3" <?php echo set_select('adult_count', '3'); ?>>3 <?php echo l('Adults', true); ?></option>
							<option value="4" <?php echo set_select('adult_count', '4'); ?>>4 <?php echo l('Adults', true); ?></option>
							<option value="5" <?php echo set_select('adult_count', '5'); ?>>5 <?php echo l('Adults', true); ?></option>
							<option value="6" <?php echo set_select('adult_count', '6'); ?>>6 <?php echo l('Adults', true); ?></option>
							<?php if($max_occupancy > 6){ 
								for($i = 7; $i <= $max_occupancy; $i++) {?>
								<option value="<?php echo $i; ?>" <?php echo set_select('adult_count', "<?php echo $i; ?>"); ?>><?php echo $i; ?> <?php echo l('Adults', true); ?></option>
							<?php } } ?>
						</select>
					</div>

					<!--<label class="col-sm-7 control-label"># <?php echo l('of Children'); ?></label>-->
					<div class="col-sm-6">
					<label for="children_counts" style="font-size:12px"> <?php echo l('Childern Count',true); ?>:</label>
						<select name="children_count" class="form-control children_count" id="children_counts">
							<option value="0" <?php echo set_select('children_count', '0', TRUE); ?>>0 <?php echo l('Child', true); ?></option>
							<option value="1" <?php echo set_select('children_count', '1'); ?>>1 <?php echo l('Child', true); ?></option>
							<option value="2" <?php echo set_select('children_count', '2'); ?>>2 <?php echo l('Children', true); ?></option>
							<option value="3" <?php echo set_select('children_count', '3'); ?>>3 <?php echo l('Children', true); ?></option>
							<option value="4" <?php echo set_select('children_count', '4'); ?>>4 <?php echo l('Children', true); ?></option>
							<?php if($max_occupancy > 6){ 
								for($i = 7; $i <= $max_occupancy; $i++) {?>
								<option value="<?php echo $i; ?>" <?php echo set_select('children_count', "<?php echo $i; ?>"); ?>><?php echo $i; ?> <?php echo l('Children', true); ?></option>
							<?php } } ?>
						</select>
					</div>	
					<div class="col-sm-12 mr-t-20">
						<div class="col-sm-6 availability_process mr-b-10">
							<?php if($show_error){ 
								echo "<span style='color:red;'>".l('No rooms available on the selected dates. Please try changing the dates', true).".</span>";
							} ?>
						</div>
						<div class="col-sm-6 tx-al-center">
							<input type="button" name="submit" value="<?php echo l('Check Availability', 1); ?>" class="pull-right btn-lg btn-success check_availability"/>
						</div>
					</div>	
				</div>	
			</div>
		</form>
	</div>
	<div class="col-md-2">
	</div>

</div>
<input type="hidden" name="selling_date" value="<?php echo $selling_date; ?>" />
<input type="hidden" name="subscription_level" value="<?php echo $company_data['subscription_level']; ?>" />

