<div class="modal fade"  id="show_rate_details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index:11111;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo l('Rate Description'); ?></h4>
            </div>
            <div class="modal-body form-horizontal">
                <div class="form-group" id="option-to-add-multiple-payments">
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover tax-price-brackets-table" >
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">Title</th>
                                        <th><?php echo l('Description', true); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <?php echo l('Standard Model (Base Price)', true); ?>
                                        </td>
                                        <td>
                                            <?php echo l('Under the standard model, you must specify two prices for every combination of room (except single rooms) and date', true); ?>:
                                            <br/><br/> 
                                            1. <?php echo l('A default price for the maximum number of adult guests.', true); ?>
                                            <br/>
                                            2. <?php echo l('A price for a single adult guest.', true); ?>
                                            <br/><br/>
                                            <?php echo l('For single rooms, where single occupancy and maximum occupancy are the same, you only specify a price for maximum occupancy.', true); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <?php echo l('Derived pricing model', true); ?>
                                        </td>
                                        <td>
                                            <?php echo l('Under the derived pricing model, you specify a price for a standard number of occupants (base occupancy), and one or more "offsets" (rate differences) to derive prices for other numbers of occupants.
                                            When the number of occupants is different than the standard/base number, we add or subtract the offset/difference from the standard price.
                                            An offset or rate difference is an amount.', true); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <?php echo l('Occupancy-based pricing (OBP) model', true); ?>
                                            <br/>
                                            <b>(<?php echo l('Recommended', true); ?>)</b>
                                        </td>
                                        <td>
                                            <?php echo l('Under the occupancy-based pricing (OBP) model, you specify a price for every combination of room type, date, and number of occupants.', true); ?>
                                            <br/>
                                            <?php echo l('Unlike with the derived pricing model, you do not specify offsets or rate difference based on a standard/base price.', true); ?>
                                            <br/>
                                            <?php echo l('Instead, you specify an absolute amount for each number of occupants, you can set these pricing under Settings > Rate Plans > Modify Rates, and set rates for each occupancy.', true); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="modal-footer">
                <span class="daily_charge_msg" style="color: green;display: none;margin: 32px;">Details Saved</span>
                <button type="button" class="btn btn-success" id="add_save_daily_charge_button">
                    <?php echo l('Ok'); ?>
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php echo l('close'); ?>
                </button>
            </div> -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="page-header integrations" integrations_enabled="<?=$integrations_enabled;?>">
    <h2>
        <?php echo l("Rooms and Rate Plan Mapping");?>
    </h2>
</div>
<!--<div class="col-md-12">
    <?php if($ota_id == SOURCE_AGODA): 
        echo "<h3>".l("Notice: Minical rate plan's rate should be between 70 and 9999")."<h3>";
        endif;
    ?>
</div>-->
<div class="col-md-12">
	<?php if($ota_id != SOURCE_SITEMINDER): ?>
		<button type="button" class='btn btn-success save-all'><?php echo l("Save All");?></button>
		<a class="btn btn-default" href="<?php echo base_url(); ?>integrations/roomsy_channel_manager"><?php echo l("Back");?></a>

        <?php if($ota_id == SOURCE_EXPEDIA): ?>
			<div class="expedia-pricing-model pull-right">
				<label class="control-label pull-left" style="line-height: 34px;"><?php echo l("Pricing Model");?></label>
				<div class="col-lg-7">
					<select class="form-control" name="expedia_pricing_model">
						<option value=""><?php echo l("Select Pricing Model");?></option>
						<option <?php if($pricing_model == "OBP"): echo 'selected'; else: echo ''; endif; ?> value="OBP"><?php echo l("Occupancy based pricing (OBP)");?></option>
						<option <?php if($pricing_model == "PDP"): echo 'selected'; else: echo ""; endif; ?> value="PDP"><?php echo l("Per-day pricing (PDP)");?></option>
					</select>
				</div>
                <button type="button" class='btn btn-success full-sync-siteminder pull-right' ota_id="<?=SOURCE_EXPEDIA?>"><?php echo l("Full Sync");?></button>
			</div>
		<?php endif; ?>

    <?php endif; if($ota_id == SOURCE_BOOKING_DOT_COM): ?>
        <br/><br/>
        <div class="booking-dot-com-rate-type">
            <div class="clearfix">
                <div class="">
                    <label class="control-label pull-left" style="line-height: 34px;"><?php echo l("Rate update type");?></label>
                    <div class="col-lg-2">
                        <select class="form-control" name="booking_dot_rate_type" data-old-pricing_model="<?=$company_data['booking_dot_com_rate_type']?>">
                            <option <?php if($company_data['booking_dot_com_rate_type'] == "base_rate"): echo 'selected'; else: echo ''; endif; ?> value="base_rate"><?php echo l("Standard Model (Base Price)");?></option>
                            <option <?php if($company_data['booking_dot_com_rate_type'] == "derived_rate"): echo 'selected'; else: echo ""; endif; ?> value="derived_rate"><?php echo l("Derived pricing model");?></option>
                            <option <?php if($company_data['booking_dot_com_rate_type'] == "occupancy_rate"): echo 'selected'; else: echo ""; endif; ?> value="occupancy_rate"><?php echo l("Occupancy-based pricing (OBP) model");?></option>
                        </select>
                    </div>
                </div>
                <div id="extra-derived-fields" style="display: initial;" class="<?=($company_data['booking_dot_com_rate_type'] == "derived_rate") ? "hidden" : "hidden";?>">
                    <div class="">
                        <label class="control-label pull-left" style="line-height: 34px;" for="common-additional-adult-rate"><?php echo l("Common additional adult rate");?></label>
                        <div class="col-lg-2">
                            <input class="form-control" id="common-additional-adult-rate" type="text" name="common_additional_adult_rate" value="<?php echo $company_data['common_additional_adult_rate']; ?>" />
                        </div>
                    </div>
                    <div style="display: inline-block;">
                        <button style="margin-right: 15px; width: 142px;" id="sync-occupancy-button" class="btn btn-success "><?php echo l("Sync");?></button>
                    </div>
                </div>
<!--                <div style="display: inline-block;min-height: 34px;padding-top: 6px;">
                    <a class="popover-help-icon booking-dot-com-popover-help" href="#" data-toggle="popover">?</a>
                </div>-->
                <span style="font-size: 21px;" data-toggle="modal" data-target="#show_rate_details">
                    <i class="fa fa-question-circle" aria-hidden="true"></i>
                </span>
                <button type="button" style="margin: -50px 0px;" class='btn btn-success full-sync-siteminder pull-right' ota_id="<?=SOURCE_BOOKING_DOT_COM?>"><?php echo l("Full Sync");?></button>
            </div>
            
        </div>

    <?php endif;  if($ota_id == SOURCE_SITEMINDER): ?>
        <button type="button" class='btn btn-success save-all-siteminder' ota_id="<?=SOURCE_SITEMINDER?>"><?php echo l("Save All");?></button>
        <a class="btn btn-default" href="<?php echo base_url(); ?>integrations/roomsy_channel_manager"><?php echo l("Back");?></a>
       <div class="col-md-12 col-lg-12 col-xs-12 col-sm-12 siteminder-hotel-region" style="margin-top: 20px;">
            <div class="linker-wrap" data-ota_id="<?=$ota_id;?>" >
                <div class="">
                    <label class="control-label pull-left" style="line-height: 34px;"><?php echo l("Hotel Region");?></label>
                    <div class="col-lg-2">
                        <select class="form-control" name="siteminder_hotel_region">
                            <option value=""><?php echo l("Select Region");?></option>
                            <option <?php if($hotel_region == "APAC"): echo 'selected'; else: echo ''; endif; ?> value="APAC"><?php echo l("APAC");?></option>
                            <option <?php if($hotel_region == "EMEA"): echo 'selected'; else: echo ""; endif; ?> value="EMEA"><?php echo l("EMEA");?></option>
                        </select>
                    </div>
                </div>
                <table style="border: 1px solid #aaa; width: 100%;">
                    <?php if(isset($pms_room_types) && $pms_room_types):
                        
                        $newRoomArray = $newRateArray = array();
                        if($pms_room_type_array)
                        {
                            foreach($pms_room_type_array as $key => $value){
                                $newRoomArray[$pms_room_type_array[$key]['pms_room_type_id']] = $pms_room_type_array[$key] ;
                            }
                        }
                        if($pms_rate_plan_array)
                        {
                            foreach($pms_rate_plan_array as $key => $value){
                                $newRateArray[$pms_rate_plan_array[$key]['pms_rate_plan_id']] = $pms_rate_plan_array[$key] ;
                            }
                        }
                    foreach($pms_room_types as $key => $room_type): ?>
                        <tr>
                            <td class="room_types" style="border-top: 1px solid #bbb;padding: 15px;">
                                <span style="font-weight: 600; font-size: 15px;" class="pms-room-type" data-id="<?php echo $room_type['id']; ?>"><?php echo $room_type['name']." (".$room_type['id'].") "; ?>
                                </span>
                                <span style="float: right;margin: 0px 5px;" class=""><input type="checkbox" <?php if(isset($newRoomArray[$room_type['id']]) && $newRoomArray[$room_type['id']]['pms_room_type_id'] == $room_type['id']) echo 'checked'; ?> class="room-check" name="room-check" value="<?php echo $room_type['id']; ?>">
                                    <?php echo l("Send availability");?>
                                </span>
                            </td>
                        </tr>
                        
						<?php if(isset($pms_rate_plans) && $pms_rate_plans && $pms_rate_plans[$key]):
							foreach($pms_rate_plans[$key] as $rate_plan): ?>
								<tr >
									<td style="padding: 10px;border-top: 1px solid #ddd;">
										<span style="margin: 0px 30px;" class="pms-rate-plan" data-id="<?php echo $rate_plan['rate_plan_id']; ?>"><?php echo $rate_plan['rate_plan_name']." (".$rate_plan['rate_plan_id'].") "; ?>
										</span>
										<span style="float: right;margin: 0px 5px;" class="">
											<input type="checkbox" 
											<?php if(isset($newRateArray[$rate_plan['rate_plan_id']]) && $newRateArray[$rate_plan['rate_plan_id']]['pms_rate_plan_id'] == $rate_plan['rate_plan_id']) { echo 'checked';} ?> 
											<?php if(!(isset($newRoomArray[$room_type['id']]) && $newRoomArray[$room_type['id']]['pms_room_type_id'] == $room_type['id'])) echo 'disabled'; ?> 
											name="rate-check" class="rate-check rate-checked-<?php echo $room_type['id']; ?> rate-select-<?php echo $rate_plan['rate_plan_id']; ?>"  
											value="<?php echo $rate_plan['rate_plan_id']; ?>"
											>
											<?php echo l("Send rates");?>
										</span>
									</td>
								</tr>
							<?php endforeach; endif; ?>
                    <?php endforeach; endif; ?>
                </table>
                
            </div>
        </div>
        <button type="button" style="position: absolute; right: 30px;" class='btn btn-success full-sync-siteminder' ota_id="<?=SOURCE_SITEMINDER?>"><?php echo l("Full Sync");?></button>
    <?php endif; ?>
</div>
<?php if($ota_id != SOURCE_SITEMINDER): ?>
<div class="col-md-12 col-lg-12 col-xs-12 col-sm-12" style="margin-top: 20px;">
    <div class="linker-wrap" data-ota_id="<?=$ota_id;?>"  
        <?php  if($ota_id == SOURCE_AGODA){ ?>
            data-roomsy-currency-code="<?php echo $roomsy_company_currency_code; ?>"
            data-ota-currency-code="<?php  echo $ota_company_curreny_code; ?>"   
        <?php } ?>
    >
        <?php
        if ($ota_room_types_and_rate_plans && count($ota_room_types_and_rate_plans) > 0 && isset($ota_room_types_and_rate_plans['error'])):
                echo "<b>".$ota_room_types_and_rate_plans['error']."</b>";
        elseif ($ota_room_types_and_rate_plans && count($ota_room_types_and_rate_plans) > 0):
                foreach ($ota_room_types_and_rate_plans as $key => $ota_room_type):
        ?>
                    <!-- Room Type panel starts here -->
                    <div class="panel panel-default room-type-panel">
                        <div class="panel-heading">
                            <span class="ota-room-type" data-id="<?php echo $ota_room_type['room_type_id']; ?>" 
                                <?php if($ota_id == SOURCE_AGODA): ?> data-ota-room-type-max-occupancy = "<?php echo $ota_room_type['max_occupency']; ?>" <?php endif;?>
                            >
                                <?php echo $ota_room_type['room_type_name']." (".$ota_room_type['room_type_id'].") "; ?>
                            </span>
                            <span class="pms-room-type"></span>
                            <?php if($ota_id == SOURCE_BOOKING_DOT_COM): ?>
                                <span style="float: right;" class="show_occupancy <?=($company_data['booking_dot_com_rate_type'] == "derived_rate") ? "" : "hidden";?>">
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <span>
                                        <span><?php echo l('Max Occupancy', true); ?>: </span>
                                        <span><?php echo (isset($ota_room_type['rate_plans']) && isset($ota_room_type['rate_plans'][0]) && isset($ota_room_type['rate_plans'][0]['ota_room_maximum_occupancy']) && $ota_room_type['rate_plans'][0]['ota_room_maximum_occupancy']) ? $ota_room_type['rate_plans'][0]['ota_room_maximum_occupancy'] : 0; ?></span>
                                    </span>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <span>
                                        <span><?php echo l('Lead Occupancy', true); ?>: </span>
                                        <span><?php echo (isset($ota_room_type['rate_plans']) && isset($ota_room_type['rate_plans'][0]) && isset($ota_room_type['rate_plans'][0]['ota_lead_occupancy']) && $ota_room_type['rate_plans'][0]['ota_lead_occupancy']) ? $ota_room_type['rate_plans'][0]['ota_lead_occupancy'] : 0; ?></span>
                                    </span>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="panel-body">
                            <?php
                                $ota_rate_plans = $ota_room_type['rate_plans'];
                                foreach ($ota_rate_plans as $ota_rate_plan):
                            ?>
                                    <!-- Rate Plans -->
                                    <div class="rate-plan">
                                        <span <?php if ($ota_id == SOURCE_BOOKING_DOT_COM): echo 'data-ota-room-max-occupancy="' . $ota_rate_plan['max_adult'] . '" data-ota-lead-occupancy="' . $ota_rate_plan['lead_occupancy'] . '"' . 'data-adult1_adult2_rate_diff="' . $ota_rate_plan['adult1_adult2_rate_diff'] . '"' . 'data-adult2_adult3_rate_diff="' . $ota_rate_plan['adult2_adult3_rate_diff'] . '"' . 'data-adult3_adult4_rate_diff="' . $ota_rate_plan['adult3_adult4_rate_diff'] . '"'; endif;
                                        if ($ota_id == SOURCE_EXPEDIA): echo '" data-ota-lead-occupancy="' . $ota_rate_plan['rate_plan_base_rate'] . '"'; endif; ?> class='ota-rate-plan'
                                                                                                                                                                    data-id="<?php echo $ota_rate_plan['rate_plan_id']; ?>">
                                            <?php echo $ota_rate_plan['rate_plan_name'] . " (" . $ota_rate_plan['rate_plan_id'] . ") "; ?>
                                        </span>
                                        <span class='pms-rate-plan'></span>

                                        <?php if($ota_id == SOURCE_BOOKING_DOT_COM): ?>
                                            <span class="adult_diff <?=($company_data['booking_dot_com_rate_type'] == "derived_rate") ? "" : "hidden";?>">
                                                <span>
                                                    <span><?php echo l('Adult 1 - 2 rate diff', true); ?></span>
                                                    <input type="number" min="0" class="adult1_adult2_rate_diff" style="width: 65px;"
                                                           name="adult1_adult2_rate_diff"
                                                           oninput="validity.valid||(value='0');" step="any"
                                                           value="<?php echo (isset($ota_rate_plan['adult1_adult2_rate_diff']) && $ota_rate_plan['adult1_adult2_rate_diff']) ? $ota_rate_plan['adult1_adult2_rate_diff'] : $company_data['common_additional_adult_rate']; ?>">
                                                </span>
                                                &nbsp;&nbsp;&nbsp;
                                                <span>
                                                    <span><?php echo l('Adult 2 - 3 rate diff', true); ?></span>
                                                    <input type="number" min="0" class="adult2_adult3_rate_diff" style="width: 65px;"
                                                           name="adult2_adult3_rate_diff"
                                                           oninput="validity.valid||(value='0');" step="any"
                                                           value="<?php echo (isset($ota_rate_plan['adult2_adult3_rate_diff']) && $ota_rate_plan['adult2_adult3_rate_diff']) ? $ota_rate_plan['adult2_adult3_rate_diff'] : $company_data['common_additional_adult_rate']; ?>">
                                                </span>
                                                &nbsp;&nbsp;&nbsp;
                                                <span>
                                                    <span><?php echo l('Adult 3 - 4 rate diff', true); ?></span>
                                                    <input type="number" min="0" class="adult3_adult4_rate_diff" style="width: 65px;"
                                                           name="adult3_adult4_rate_diff"
                                                           oninput="validity.valid||(value='0');" step="any"
                                                           value="<?php echo (isset($ota_rate_plan['adult3_adult4_rate_diff']) && $ota_rate_plan['adult3_adult4_rate_diff']) ? $ota_rate_plan['adult3_adult4_rate_diff'] : $company_data['common_additional_adult_rate']; ?>">
                                                </span>
                                            </span>
                                        <?php endif; ?>
                                    </div> <!-- /Rate Plans -->
                            <?php
                                endforeach;
                            ?>
                        </div>
                    </div>
            <?php
                endforeach;

            ?>
        <?php
            else:
                echo "<b>".l("Rooms not found on ".$ota_name."!")."</b>";
            endif;
            if($ota_id == SOURCE_AGODA):
        ?>
        <button type="button" style="margin: -50px 0px;" class='btn btn-success full-sync-siteminder pull-right' ota_id="<?=SOURCE_AGODA?>"><?php echo l("Full Sync");?></button>
    <?php endif; ?>
    </div>
</div>
<?php endif; ?>