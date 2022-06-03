<!-- Help Modal -->
<div class="modal fade" id="help-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?php echo l('About Products', true); ?></h4>
      </div>
      <div class="modal-body">
        <p>
            <?php echo l('Products are a great way to apply charges to invoices that are re-occuring or date sensitive.
            For all other charges, we recommend adding them directly to the invoice.', true); ?>
        </p>
        <p>
            <strong><?php echo l('Products have two types', true); ?></strong>: <?php echo l('items and rentals. They differ in the way they apply charges to invoices.', true); ?>
        
            <?php echo l('Rentals, such extra beds, are charged the same way rooms are charged -- by night.
            They are charged based on the number of nights between the start and end date.
            For example, if the start date is 2012-06-20 and the end date 2012-06-22, the invoice will be charged 2 times;
            once when the selling date changes from 2012-06-20 to 2012-06-21 (for 2012-06-20),
            and once when the selling date changes from 2012-06-21 to 2012-06-22 (for 2012-06-21).', true); ?>
        
            <?php echo l('Items, such as tickets and food, are charged by day.
            They are charged based on the number of days that include the start and end date.
            For example, if the start date is 2012-06-20 and the end date 2012-06-22, the invoice will be charged 3 times;
            once when the selling date changes from 2012-06-20 to 2012-06-21 (for 2012-06-20),
            once when the selling date changes from 2012-06-21 to 2012-06-22 (for 2012-06-21),
            and once when the selling date changes from 2012-06-22 to 2012-06-23 (for 2012-06-22).
            NOTE: If the booking check-out date and the extra end date are the same for an item, 
            the charge will NOT be automatically applied to the invoice. 
            In this case, you will need to add a charge for the end date directly to the invoice.', true); ?>
        </p>
        <br />
        <p>
            <?php echo l('Products also have different', true); ?> <strong><?php echo l('charging schemes.', true); ?></strong> 
            <?php echo l('These are used to choose when and how you charge Products.', true); ?>
        </p>
        <br />
        <p>
            <?php echo l("Selecting 'on start date' charges the rate to the invoice only once on the the start date,
            but allows you to record the start and end date that the extra applies to.
            This can be useful for charging upfront fees or deposits for a rental.", true); ?>
        </p>
        <br />
        <p>
            <?php echo l("Selecting 'once a day' charges the rate on a re-occuring basis.
            Reoccuring charges operate as described in the rental and item descriptions above.", true); ?>
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo l('Close', true); ?></button>
      </div>
    </div>
  </div>
</div>

<?php echo validation_errors('<div class="error">', '</div>'); ?>
<style>
    body {
        background: #fbfafa;
    }
</style>
<div class="container">
	<?php if (isset($view_data['available_rate_plans'])) { ?>	

	<div class="col-md-4" style="background: white;box-shadow: 0px 0px 3px rgba(200, 206, 210, 0.5);">
		<div class="page-header">
			<h3>
				<?php echo l('Booking Information'); ?>
				<a href="<?php echo base_url() . 'online_reservation/select_dates_and_rooms/'.$this->uri->segment(3); ?>"
					class="btn btn-default btn-sm pull-right" />
					<?php echo l('Start Over'); ?>
				</a>
			</h3>

		</div>

		<dl class="dl-horizontal">
			<dt>
				<?php echo l('check_in_date'); ?>:
			</dt>
			<dd>
				<?php echo get_local_formatted_date($view_data['check_in_date'], $company_data['date_format']); ?>
			</dd>

			<dt>
				<?php echo l('Check-out Date'); ?>:
			</dt>
			<dd>
				<?php echo get_local_formatted_date($view_data['check_out_date'], $company_data['date_format']); ?>
			</dd>

            <dt>
                <?php echo l('Number of Days', true); ?>:
            </dt>
            <dd>
                <?php  echo (strtotime($view_data['check_out_date']) - strtotime($view_data['check_in_date']))/(3600*24); ?>
            </dd>

			<dt>
				<?php echo l('Adults Count'); ?>:
			</dt>
			<dd>
				<?php echo isset($view_data['adult_count'][0])?$view_data['adult_count'][0]:1; ?>
			</dd>

			<dt>
				<?php echo l('Children Count'); ?>:
			</dt>
			<dd>
				<?php echo isset($view_data['children_count'][0])?$view_data['children_count'][0]:0; ?>
			</dd>

			<dt>
				<?php echo l('Currency'); ?>:
			</dt>
			<dd>
				<?php echo $view_data['default_currency']['currency_name']; ?>
			</dd>

		</dl>
	</div>

	<div class="col-md-8">

		<?php 
			$is_rooms_available = false;
			foreach ($view_data['available_rate_plans'] as $key => $rate_plan) :
				$is_room_type_unavailable = false;

                $is_room_bookable = true;

				$rate_plan_id = $rate_plan['rate_plan_id'];
				$rate_plan_selected_count = 0; 
				if(isset($view_data['rate_plan_selected_ids'])) :
					foreach ($view_data['rate_plan_selected_ids'] as $rate_plan_selected):
						if ($rate_plan_selected == $rate_plan_id)
						{
							$rate_plan_selected_count++;
						}
					endforeach;
				endif ?>
			
                <?php
                if(isset($view_data['unavailable_room_types']) && $view_data['unavailable_room_types']){
                    foreach($view_data['unavailable_room_types'] as $key1 => $unavailable_room_type)
                    {
                		if($unavailable_room_type['id'] == $rate_plan['room_type_id'])
                		{
                			$is_room_type_unavailable = true;
                            $is_room_bookable = false;
                		}
                    }
                }
                if($rate_plan['average_daily_rate'] != 0 || ($company_data['allow_free_bookings'] && (!$rate_plan['charge_type_id'] || $rate_plan['charge_type_id'] == '0')))
                { 
					$is_rooms_available = true;
                ?>                
				<div class="panel-rate-plan-listing panel panel-<?php echo $is_room_type_unavailable ? 'default' : 'success' ?>	">
					<div class="panel-body" style="padding-bottom: 0;padding-right: 0;">
						<form action="<?php echo base_url() . 'online_reservation/show_reservations/'.$this->uri->segment(3).''; ?>" method="post">
							
							<!-- keeps the original check-in, check-out date, and # of rooms populated between pages-->
							<!-- even though they aren't used in the program in any other way -->
							<input type="hidden" value="<?php echo $this->uri->segment(3); ?>" name="company-id">
							<input type="hidden" value="<?php echo $view_data['check_in_date']; ?>" name="check-in-date">
							<input type="hidden" value="<?php echo $view_data['check_out_date']; ?>" name="check-out-date">			
							<input type="hidden" value="<?php echo $view_data['adult_count']; ?>" name="adult_count">
							<input type="hidden" value="<?php echo $view_data['children_count']; ?>" name="children_count">			
							<input type="hidden" value="<?php echo $rate_plan_id; ?>" name="rate-plan-selected-ids[]">
							
							<?php if(isset($view_data['rate_plan_selected_ids'])) : ?>
								<?php foreach ($view_data['rate_plan_selected_ids'] as $rate_plan_selected_id) : ?>
									<input type="hidden" value="<?php echo $rate_plan_selected_id; ?>" name="rate-plan-selected-ids[]">
								<?php endforeach ?>
							<?php endif; ?>

							
							<!-- Picture will go here -->
							<div class="col-md-3" style="padding: 0;">
										
								<?php
									// Display room type image
									if (isset($rate_plan['images'][0])):
										foreach ($rate_plan['images'] as $image_index => $image):

								?>
											
												<a href="<?php echo $this->image_url . $company_data['company_id']."/".$image['filename']; ?>"
												class=" <?php 
															if ($image_index === 0) 
																echo "col-md-12"; 
															else 
																echo "col-md-4 hidden-xs"; 
														?> thumbnail"  data-lightbox="<?php echo $rate_plan_id; ?>" >
													<img src="<?php echo $this->image_url . $company_data['company_id']."/".$image['filename']; ?>" />
												</a>
										
								<?php
										endforeach;
									else:
								?>
										<div class="panel panel-default text-center">
											<div class="h4 text-muted"><?php echo l('Photo not available'); ?></div>
										</div>
																							
								<?php
									endif;
								?>
							
							</div>
                            <div class="col-md-9">
                                <div class="col-md-7">
                                    <h3 class="panel-title">
                                        <div class="h4" style="color: #145291; font-size: 22px; margin-top: 0;"><?php echo $rate_plan['room_type_name']; ?></div>
                                        <div><?php echo $rate_plan['rate_plan_name']; ?></div>
                                        <div style="font-size: 12px; color: gray; margin-top: 15px;">
                                            <?php if (isset($rate_plan['max_adults']) && $rate_plan['max_adults'] > 0) {
                                                for($i = 0; $i < $rate_plan['max_adults']; $i++) {
                                                    echo '<i class="glyphicon glyphicon-user" aria-hidden="true" style="margin-right: 3px;"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </h3>
                                    <br/>
                                </div>
                                <div class="col-md-5" style="padding: 0;">
                                    <div class="text-right" style="margin-bottom: 25px;padding: 0 20px;">
                                        <?php
                                        $average_daily_rate = $rate_plan['average_daily_rate'];
                                        if ($company_data['allow_free_bookings'] && $average_daily_rate == 0){
                                            // do not show rate if it's 0
                                        } else { ?>
                                            <div class="daily-rate" style="font-size: 32px;">
                                                <?=number_format($average_daily_rate, 2, ".", ","); ?><span style="font-size: 16px;color: gray;padding-left: 3px;"><?php echo $rate_plan['currency_code']; ?></span>
                                            </div>
                                            <small style="color: gray;"><?php echo l('Average rate per night', true); ?></small>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="col-md-12" style="min-height: 55px;">
                                    <?php echo $rate_plan['description'] ? str_replace(PHP_EOL, '<br/>', $rate_plan['description']) : ''; ?>
                                </div>
                                <div class="col-md-12">
                                    <div class="text-left">
                                        <input type="hidden" name="rate_plan_extra" class="rate_plan_extra" />
                                        <?php if(isset($rate_plan['extras']) && $rate_plan['extras']): ?>
                                        <div style="font-size: 15px;font-weight: 500;margin: 15px 0px 10px;">
                                            <?php echo l('Additional Products and Services', true); ?>
                                            <a href=#
                                                class="text-primary h4" 
                                                aria-hidden="true" 
                                                data-toggle="modal" 
                                                data-target="#help-modal"
                                            >(<?php echo l('About Products'); ?>)</a>
                                        </div>

                                        <table id="extras-fields" class="table">
                                            <thead>
                                                <tr class="table-header">
                                                    <th></th>
                                                    <th>Service</th>
                                                    <th>Charge Scheme</th>
                                                    <th>Price</th>
                                                    <th class="head-qty hidden">Quantity</th>
                                                </tr>
                                            </thead>
                                                <?php
                                                $extras = $rate_plan['extras'];
                                                // prx($extras);
                                                foreach($extras as $extra) : ?>
                                                    <tr for="extra-check-<?php echo $extra['extra_id']; ?>" class="extra-field-tr" id="<?php echo $extra['extra_id']; ?>" data-charge_type_id="<?php echo $extra['charge_type_id']; ?>" data-rate_plan_id="<?php echo $rate_plan_id; ?>">
                                                        <td style="width: 30px;">
                                                            <input class="extra-check" name="extra-check-<?php echo $extra['extra_id']; ?>" type="checkbox" value="1" />
                                                        </td>
                                                        <td>
                                                            <div class="name-rate-div-<?php echo $extra['extra_id']; ?>">
                                                                <span class="name-span-<?php echo $extra['extra_id']; ?>"><?php echo $extra['extra_name']; ?></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php if($extra['charging_scheme'] == 'on_start_date'){
                                                                echo 'On start date';
                                                            } else if($extra['charging_scheme'] == 'once_a_day' && $extra['extra_type'] == 'item'){
                                                                echo 'Once a day (end date inclusive)';
                                                            } else if($extra['charging_scheme'] == 'once_a_day' && $extra['extra_type'] == 'rental'){
                                                                echo 'Once a day (end date exclusive)';
                                                            } ?>
                                                        </td>
                                                        <td>
                                                            <div class="name-rate-div-<?php echo $extra['extra_id']; ?>">
                                                                <span class="rate-span-<?php echo $extra['extra_id']; ?>"><?php echo number_format($extra['rate'], 2, ".", ""); ?></span>
                                                            </div>
                                                            <div class="hidden name-rate-div-<?php echo $extra['extra_id']; ?>">
                                                                <span class="charging-scheme-span-<?php echo $extra['extra_id']; ?>"><?php echo $extra['charging_scheme']; ?></span>
                                                            </div>
                                                            <div class="hidden name-rate-div-<?php echo $extra['extra_id']; ?>">
                                                                <span class="extra-type-span-<?php echo $extra['extra_id']; ?>"><?php echo $extra['extra_type']; ?></span>
                                                            </div>
                                                            <div class="hidden name-rate-div-<?php echo $extra['extra_id']; ?>">
                                                                <span class="extra-rate-span-<?php echo $extra['extra_id']; ?>"><?php echo $extra['rate']; ?></span>
                                                            </div>
                                                        </td>
                                                        <td style="padding: 4px;">
                                                            <div class="pull-right hidden form-inline qty-div-<?php echo $extra['extra_id']; ?>-<?php echo $rate_plan_id; ?>">
                                                                <!-- <small><?php echo l('Qty', true); ?>: </small> -->
                                                                <div class="input-group">
                                                                    <input style="width: 70px;height: 30px;" type="number" name="extra_qty" min="1" value="1" class="form-control extra_qty_<?php echo $extra['extra_id']; ?>">
                                                                    <div class="input-group-btn">
                                                                        <button style="padding: 4px 6px;" type="button" class="btn btn-default qty_plus" id="<?php echo $extra['extra_id']; ?>">
                                                                            <i class="fa fa-plus"></i>
                                                                        </button>
                                                                        <button style="padding: 4px 6px;" type="button" class="btn btn-default qty_minus" id="<?php echo $extra['extra_id']; ?>">
                                                                            <i class="fa fa-minus"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>&nbsp;
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                        </table>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if($is_room_type_unavailable){
                                    echo '<div class="col-md-12" style="font-size: 14px;color: red;">'.l('This room type is unavailable for the given dates', true).'</div>';
                                }
                                ?>
                                <?php if(isset($rate_plan['min_length'])){
                                    $is_room_bookable = false;
                                    echo '<div class="col-md-12" style="font-size: 14px;color: red;">'.l($rate_plan['min_length'], true).'</div>';
                                }
                                ?>
                                <?php if(isset($rate_plan['max_length'])){
                                    $is_room_bookable = false;
                                    echo '<div class="col-md-12" style="font-size: 14px;color: red;">'.l($rate_plan['max_length'], true).'</div>';
                                }
                                ?>
                                <?php if(isset($rate_plan['arrival'])){
                                    $is_room_bookable = false;
                                    echo '<div class="col-md-12" style="font-size: 14px;color: red;">'.l($rate_plan['arrival'], true).'</div>';
                                }
                                ?>
                                <?php if(isset($rate_plan['departure'])){
                                    $is_room_bookable = false;
                                    echo '<div class="col-md-12" style="font-size: 14px;color: red;">'.l($rate_plan['departure'], true).'</div>';
                                }
                                ?>
                            </div>
                            <input type="submit" name="submit" value="<?php echo l('Book', 1); ?>" class="btn btn-<?= $is_room_bookable ? 'primary' : 'default';?> btn-lg" <?=$is_room_bookable ? '' : 'disabled';?> style="width: 200px;float: right; border-radius: 0; padding: 7px;" />
                        </form>
					</div>
				</div>
                <?php
                }
                endforeach;
				
                if(!$is_rooms_available):
				?>
                    <div class="container-fluid">
                        <div
                            class="alert alert-danger alert-dismissible" role="alert"
                            style="
                                position:fixed; 
                                z-index:1000; 
                                top:20%; 
                                left:50%;
                                width: 70%;
                                margin-left: -35%;
                                "
                        >
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                            <span class="sr-only">Error:</span>
                            <strong>Please correct the below error(s):</strong>
                            <?php echo l('<br/>There are no rooms available for the dates you have selected. Please select another date to check availability.', true); ?>
                        </div>
                    </div>
                <?php  endif; ?>		
		<?php } ?>
	</div>
	
		
</div>
