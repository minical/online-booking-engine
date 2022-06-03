<?php $whitelabelinfo = $this->session->userdata('white_label_information');
// Set the partner name
$partner_name =  isset($whitelabelinfo['name']) ? ucfirst($whitelabelinfo['name']) : $this->config->item('branding_name');
?>
<!-- Button trigger modal -->
<!-- Modal -->
<div class="modal fade" id="policy_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <h3>Minical Inc.'s Terms and conditions</h3>

                <div class="text">
                    <p>These terms and conditions, as may be amended from time to time, apply to all our services directly or indirectly (through distributors) made available online, through any mobile device, by email or by telephone. By accessing, browsing and using our website or any of our
                        applications through whatever platform (hereafter collectively referred to as the "website") and/or by completing a reservation, you acknowledge and agree to have read, understood and agreed to the terms and conditions set out below (including the privacy statement).</p>

                    <p>These pages, the content and infrastructure of these pages, and the online hotel reservation service provided on these pages and through the website (the "service") are owned, operated and provided by Minical Inc. ("Minical Inc.", "us", "we" or "our") and are
                        provided for your personal, non-commercial use only, subject to the terms and conditions set out below.</p>
                    <h4>1. Scope of our Service</h4>

                    <p>Through the website we (Minical Inc. and its affiliate (distribution) partners) provide an online platform through which hotels (including all types of temporary accommodation) can advertise their rooms for reservation, and through which visitors to the website can
                        make such reservations. By making a reservation through Minical Inc., you enter into a direct (legally binding) contractual relationship with the hotel at which you book. From the point at which you make your reservation, we act solely as an intermediary between you
                        and the hotel, transmitting the details of your reservation to the relevant hotel and sending you a confirmation email for and on behalf of the hotel.</p>

                    <p>When rendering our services, the information that we disclose is based on the information provided to us by the hotels. As such, the hotels are given access to an extranet through which they are fully responsible for updating all rates, availability and other information which
                        is displayed on our website. Although we will use reasonable skill and care in performing our services we will not verify if, and cannot guarantee that, all information is accurate, complete or correct, nor can we be held responsible for any errors (including manifest and
                        typographical errors), any interruptions (whether due to any (temporary and/or partial) breakdown, repair, upgrade or maintenance of our website or otherwise), inaccurate, misleading or untrue information or non-delivery of information. Each hotel remains responsible at all
                        times for the accuracy, completeness and correctness of the (descriptive) information (including the rates and availability) displayed on our website. Our website does not constitute and should not be regarded as a recommendation or endorsement of the quality, service level
                        or rating of any hotel made available.</p>

                    <p>Our services are made available for personal and non-commercial use only. Therefore, you are not allowed to re-sell, deep-link, use, copy, monitor (e.g. spider, scrape), display, download or reproduce any content or information, software, products or services available on our
                        website for any commercial or competitive activity or purpose.</p>

                    <h4>2. Prices</h4>

                    <p>The prices on our site are highly competitive. All prices on the Minical Inc. website are per room for your entire stay and are displayed NOT including VAT or any other taxes (subject to change of such taxes), unless stated differently on our website or the
                        confirmation email.</p>

                    <p>Sometimes cheaper rates are available at our website for a specific stay at a hotel, however, these rates made by hotels may carry special restrictions and conditions, for example in respect to cancellation and refund. Please check the room and rate details thoroughly for any
                        such conditions prior to making your reservation.</p>
                    <h4>3. Privacy</h4>

                    <p>Minical Inc. uses high ethical standards and respects your privacy. Save for disclosures required by law in any relevant jurisdiction and the disclosure of your name, email address and your credit card details for completing your booking with the relevant hotel of your
                        choice, we will not disclose your personal information to third parties without your consent. However, we reserve the right to disclose your personal information to our affiliated (group) companies (in and outside the European Union), including our and our affiliated (group)
                        companies' employees and our trusted agents and representatives who have access to this information with our permission and who need to know or have access to this information to perform our service (including customer services and internal (audit/compliance) investigation)
                        to and for the benefit of you. </p>
                    <h4>4. Free of charge</h4>

                    <p>Our service is free of charge. Unlike many other parties, we will not charge you for our service or add any additional (reservation) fees to the room rate. We will not charge your credit card, as you will pay the hotel directly for your stay.</p>
                    <h4>5. Cancellation</h4>

                    <p>By making a reservation with a hotel, you accept and agree to the relevant cancellation and no-show policy of that hotel, and to any additional (delivery) terms and conditions of the hotel that may apply to your reservation or during your stay, including for services rendered
                        and/or products offered by the hotel (the delivery terms and conditions of a hotel can be obtained with the relevant hotel). The general cancellation and no-show policy of each hotel is made available on our website at the hotel information pages, during the reservation
                        procedure and in the confirmation email. Please note that certain rates or special offers are not eligible for cancellation or change. Please check the room details thoroughly for any such conditions prior to making your reservation.</p>

                    <p>If you wish to review, adjust or cancel your reservation, please revert to the confirmation email and follow the instructions therein. Please note that you may be charged for your cancellation in accordance with the hotel's cancellation and no-show policy. We recommend that
                        you read the cancellation and no-show policy of the hotel carefully prior to making your reservation.</p>
                    <h4>6. Further correspondence</h4>

                    <p>By completing a booking, you agree to receive (i) an email which we may send you shortly prior to your arrival date, giving you information on your destination and providing you with certain information and offers (including third party offers to the extent that you have
                        actively opted in for this information) relevant to your booking and destination, and (ii) an email which we may send to you promptly after your stay at the hotel inviting you to complete our guest review form. Other than the email confirmation providing for the confirmation
                        of the booking, the guest review invitation and the emails for which you may have actively opted in, we shall not send you any further (solicited or unsolicited) notices, emails or correspondence, unless you specifically agree otherwise.</p>
                    <h4>7. Disclaimer</h4>

                    <p>Subject to the limitations set out in these terms and conditions and to the extent permitted by law, we shall only be liable for direct damages actually suffered, paid or incurred by you due to an attributable shortcoming of our obligations in respect to our services, up to an
                        aggregate amount of the aggregate cost of your reservation as set out in the confirmation email (whether for one event or series of connected events).</p>

                    <p>However and to the extent permitted by law, neither we nor any of our officers, directors, employees, representatives, subsidiaries, affiliated companies, distributors, affiliate (distribution) partners, licensees, agents or others involved in creating, sponsoring, promoting,
                        or otherwise making available the site and its contents shall be liable for (i) any punitive, special, indirect or consequential loss or damages, any loss of production, loss of profit, loss of revenue, loss of contract, loss of or damage to goodwill or reputation, loss of
                        claim, (ii) any inaccuracy relating to the (descriptive) information (including rates, availability and ratings) of the hotel as made available on our website, (iii) the services rendered or the products offered by the hotel, (iv) any (direct, indirect, consequential or
                        punitive) damages, losses or costs suffered, incurred or paid by you, pursuant to, arising out of or in connection with the use, inability to use or delay of our website, or (v) for any (personal) injury, death, property damage, or other (direct, indirect, special,
                        consequential or punitive) damages, losses or costs suffered, incurred or paid by you, whether due to (legal) acts, errors, breaches, (gross) negligence, willful misconduct, omissions, non-performance, misrepresentations, tort or strict liability by or (wholly or partly)
                        attributable to the hotel (its employees, directors, officers, agents, representatives or affiliated companies), including any (partial) cancellation, overbooking, strike, force majeure or any other event beyond our control.</p>
                    <h4>8. Miscellaneous</h4>

                    <p>Unless stated otherwise, the software required for our services or available at or used by our website and the intellectual property rights (including the copyrights) of the contents and information of and material on our website are owned by Minical Inc., its
                        suppliers or providers.</p>

                    <p>To the extent permitted by law, these terms and conditions and the provision of our services shall be governed by and construed in accordance with Canadian law and any dispute arising out of these general terms and conditions and our services shall exclusively be submitted to
                        the competent courts in Alberta, Canada.</p>

                    <p>The original English version of these terms and conditions may have been translated into other languages. The translated version is a courtesy and office translation only and you cannot derive any rights from the translated version. In the event of a dispute about the contents
                        or interpretation of these terms and conditions or inconsistency or discrepancy between the English version and any other language version of these terms and conditions, the English language version to the extent permitted by law shall apply, prevail and be conclusive. The
                        English version is available on our website (by selecting the English language) or shall be sent to you upon your written request.</p>

                    <p>If any provision of these terms and conditions is or becomes invalid, unenforceable or non-binding, you shall remain bound by all other provisions hereof. In such event, such invalid provision shall nonetheless be enforced to the fullest extent permitted by applicable law, and
                        you will at least agree to accept a similar effect as the invalid, unenforceable or non-binding provision, given the contents and purpose of these terms and conditions.</p>
                    <h4>9. About Minical Inc.</h4>

                    <p>All of our services are rendered by Minical Inc., which is a corporation, incorporated under the Canadian law and having its offices at 2114 14ave, Wainwright Alberta</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

            </div>
        </div>
    </div>
</div>


<div class="container">


    <div class="col-md-4">
        <div class="page-header h3">
            <?php echo l('Booking Information'); ?>
            <a href="<?php echo base_url().'online_reservation/select_dates_and_rooms/'.$this->uri->segment(3); ?>"
               class="btn btn-default btn-sm pull-right">
                <?php echo l('Start Over'); ?>
            </a>

        </div>

        <dl class="dl-horizontal">
            <dt>
                <?php echo l('check_in_date'); ?>::
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
                <?php echo $view_data['adult_count']; ?>
            </dd>

            <dt>
                <?php echo l('Children Count'); ?>:
            </dt>
            <dd>
                <?php echo $view_data['children_count']; ?>
            </dd>

            <dt>
                <?php echo l('Currency'); ?>:
            </dt>
            <dd>
                <?php echo $view_data['default_currency']['currency_name']; ?>
            </dd>
        </dl>

        <?php
        foreach ($view_data['rate_plan_selected_ids'] as $rate_plan_selected_index => $rate_plan_selected_id) :
            foreach ($view_data['available_rate_plans'] as $rate_plan_available) :
                $rate_plan_id = $rate_plan_available['rate_plan_id'];
                if ($rate_plan_selected_id == $rate_plan_id) :
                    ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php echo $rate_plan_available['rate_plan_name']; ?>
                        </div>
                        <div class="panel-body">
                            <dl class="dl-horizontal">
                                <dt>
                                    <?php echo l('Room'); ?>:
                                </dt>
                                <dd>
                                    <?php echo $rate_plan_available['room_type_name']; ?>
                                </dd>
                                <?php if ($company_data['allow_free_bookings'] && $rate_plan_available['average_daily_rate'] == 0) {
                                    //// do not show rate if it's 0
                                } else { ?>
                                    <dt>
                                        <?php echo l('Average Daily Rate'); ?>:
                                    </dt>
                                    <dd>
                                        <?php echo number_format($rate_plan_available['average_daily_rate'], 2, ".", ","); ?>
                                    </dd>
                                <?php } ?>

                            </dl>
                        </div>
                    </div>

                    <?php if(isset($view_data['rate_plan_extra']) && $view_data['rate_plan_extra']): ?>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <?php echo l('Product Items', true); ?>
                            </div>
                            <?php $new_array = array();

                                foreach($view_data['rate_plan_extra'] as $key =>$value) {
                                    if(!array_key_exists($value['extra_id'], $new_array)){
                                        $new_array[$value['extra_id']] = 0;
                                    }
                                    $new_array[$value['extra_id']] = $new_array[$value['extra_id']] + 1;
                                } ?>
                            <div class="panel-body">
                                <table>
                                    <tr>
                                        <th><?php echo l('Name', true); ?></th>
                                        <th style="float: right; margin-left: 50px;"><?php echo l('Amount', true); ?></th>
                                        <th style="float: right; margin-left: 50px;"><?php echo l('Qty', true); ?></th>
                                    </tr>
                                    <?php $prev_extras = array();
                                    
                                    foreach ($view_data['rate_plan_extra'] as $key => $extra): 
                                        if(!in_array($extra['extra_id'], $prev_extras)): ?>
                                    <tr>
                                        <td><?php echo $extra['extra_name']; ?></td>
                                        <td style="float: right;"><?php echo $extra['amount']; ?></td>
                                        <td style="float: left;margin-left: 57px;">
                                            <?php echo $extra['quantity']; ?>
                                        </td>
                                    </tr>
                                    <?php $prev_extras[] = $extra['extra_id']; endif; endforeach; ?>
                                    <tr>
                                        <th><br/><?php echo l('Total', true); ?>: <?php echo $view_data['grand_total']; ?></th>
                                    </tr>
                                </table>
                            </div>
                        </div>
                <?php endif;
                endif;
            endforeach;
        endforeach;
        ?>

        <?php if ($company_data['allow_free_bookings'] && $view_data['sub_total'] == 0) {
            //// do not show rate if it's 0
        } else { ?>
        <dl class="h3 dl-horizontal">
            <dt>
                <?php echo l('Total Charge'); ?>:
            </dt>
            <dd class="text-right text-muted">
                <?php echo number_format($view_data['sub_total'], 2, ".", ","); ?>
            </dd>

            <?php if ($view_data['tax_amount'] > 0): ?>
                <dt>
                    <?php echo l('Tax'); ?>:
                </dt>
                <dd class="text-right text-muted">
                    <?php echo number_format($view_data['tax_amount'], 2, ".", ","); ?>
                </dd>
            <?php endif; ?>

            <dt>
                <?php echo l('Total'); ?>:
            </dt>
            <dd class="text-right text-muted">
                <?php echo number_format($view_data['total'], 2, ".", ","); ?>
            </dd>
        </dl>
        <?php } ?>

    </div>

    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php echo l('Please enter your information'); ?>
            </div>
            <div class="panel-body">
                <form
                    id="guest-information-form"
                    action="<?php echo base_url().'online_reservation/book_reservation/'.$this->uri->segment(3); ?>"
                    method="post"
                    class="form-horizontal"
                    >

                    <?php if(count($booking_engine_fields) > 0):
                        foreach ($booking_engine_fields as $key => $value):
                        if($value['id'] == BOOKING_FIELD_NAME){
                            $name = 'customer-name';
                            $is_required = $value['is_required'] ? 'required' : '';
                            $show = $value['show_on_booking_form'] ? '' : 'hidden';
                        } else if($value['id'] == BOOKING_FIELD_EMAIL){
                            $name = 'customer-email';
                            $is_required = $value['is_required'] ? 'required' : '';
                            $show = $value['show_on_booking_form'] ? '' : 'hidden';
                        } else if($value['id'] == BOOKING_FIELD_PHONE){
                            $name = 'phone';
                            $is_required = $value['is_required'] ? 'required' : '';
                            $show = $value['show_on_booking_form'] ? '' : 'hidden';
                        } else if($value['id'] == BOOKING_FIELD_ADDRESS){
                            $name = 'address';
                            $is_required = $value['is_required'] ? 'required' : '';
                            $show = $value['show_on_booking_form'] ? '' : 'hidden';
                        } else if($value['id'] == BOOKING_FIELD_CITY){
                            $name = 'city';
                            $is_required = $value['is_required'] ? 'required' : '';
                            $show = $value['show_on_booking_form'] ? '' : 'hidden';
                        } else if($value['id'] == BOOKING_FIELD_REGION){
                            $name = 'region';
                            $is_required = $value['is_required'] ? 'required' : '';
                            $show = $value['show_on_booking_form'] ? '' : 'hidden';
                        } else if($value['id'] == BOOKING_FIELD_COUNTRY){
                            $name = 'country';
                            $is_required = $value['is_required'] ? 'required' : '';
                            $show = $value['show_on_booking_form'] ? '' : 'hidden';
                        } else if($value['id'] == BOOKING_FIELD_POSTAL_CODE){
                            $name = 'postal-code';
                            $is_required = $value['is_required'] ? 'required' : '';
                            $show = $value['show_on_booking_form'] ? '' : 'hidden';
                        } else if($value['id'] == BOOKING_FIELD_SPECIAL_REQUEST){
                            $name = 'special-requests';
                            $is_required = $value['is_required'] ? 'required' : '';
                            $show = $value['show_on_booking_form'] ? '' : 'hidden';
                        }
                        ?>
                        <?php if($value['id'] == BOOKING_FIELD_SPECIAL_REQUEST){ ?>
                            <div class="form-group <?php echo $show; ?>">
                                <label for="customer-name" class="col-sm-3 control-label"><?php echo l(ucfirst($value['field_name']), true); ?>
                                    <?php if($is_required == 'required'): ?>
                                        <span style="color:red;">*</span>
                                    <?php endif; ?>
                                </label>

                                <div class="col-sm-9">
                                    <textarea 
                                        name="<?php echo $name; ?>"
                                        class="form-control"
                                        data-error= "<?php echo l('Please enter your', true).' '.l(ucfirst($value['field_name']), true); ?>" <?php echo $is_required; ?>
                                        rows = '5'
                                    ><?php echo set_value($name); ?></textarea>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        <?php } else { ?>
                         <div class="form-group <?php echo $show; ?>">
                            <label for="customer-name" class="col-sm-3 control-label" <?php if($value['id'] == BOOKING_FIELD_REGION) { ?> style="padding: 5px 12px;" <?php } ?>><?php echo l(ucfirst($value['field_name']), true); ?>
                                <?php if($is_required == 'required'): ?>
                                    <span style="color:red;">*</span>
                                <?php endif; ?>
                            </label>

                            <div class="col-sm-9">
                                <input
                                    name="<?php echo $name; ?>"
                                    type="text"
                                    class="form-control"
                                    value="<?php echo set_value($name); ?>"
                                    data-error= "<?php echo l('Please enter your', true).' '.l(ucfirst($value['field_name']), true); ?>" <?php echo $is_required; ?>
                                    />

                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        <?php } endforeach; endif;?>

                        <input type="hidden" name="store_cc_in_booking_engine" id="store_cc_in_booking_engine" value="<?php echo $store_cc_in_booking_engine; ?>">
                        <input type="hidden" name="are_gateway_credentials_filled" id="are_gateway_credentials_filled" value="<?php echo $are_gateway_credentials_filled; ?>">

                    <?php if ($store_cc_in_booking_engine and $are_gateway_credentials_filled): ?>
                        
                        <div class="form-group cc_details">
                            <label for="birthday" class=" col-md-3 control-label"><?php echo l('Credit card'); ?>
                                <span style="color:red;">*</span>
                            </label>

                            <div class="form-group form-group-inner col-md-6" style="margin:0;">
                                <input 
                                    class="form-control" 
                                    name="cc_number" 
                                    type="text"
                                    value="<?php echo set_value('cc_number'); ?>"
                                    placeholder="•••• •••• •••• ••••"
                                    data-error= "<?php echo l('Please enter CC number', true); ?>" <?php echo 'required'; ?>
                                />
                                <div class="help-block with-errors"></div>
                            </div>

                            <div class="form-group col-md-3" style="margin:0;">
                                <input class="form-control"
                                       name="cc_expiry"
                                       placeholder="MM / YY"
                                       data-expiry="expiry"
                                       maxlength="7"
                                       autocomplete="false"
                                    >

                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        <div class="form-group cc_details cvc_code">
                            <label for="cvc" class="col-lg-3 control-label"><?php echo l('CVC'); ?>
                                <span style="color:red;">*</span>
                            </label>

                            <div class="col-sm-3">
                                
                                <!--                            
                                a workaround to disable autocomplete for email and cvv
                                browser check if password field is hidden than don't auto populate user and password field that is email and cvv.
                                -->
                                <input class="hidden" type="password" />
                                <input type="password"
                                       class="form-control"
                                       name="cc_cvc"
                                       data-cvc="cvc"
                                       placeholder="***"
                                       maxlength="4"
                                       autocomplete="false"
                                    >
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-9">
                            <h3><?php echo $company_data['name']; ?> <?php echo l('Reservation Policy'); ?></h3>
                            <?php //echo str_replace("\n", "<br/>", str_replace(PHP_EOL, '<br/>', $view_data['reservation_policy'])); ?>
                            <?php echo $view_data['reservation_policy']; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-9">
                            <?php echo l("By clicking 'Book Now' below, I agree with the"); ?> <?php echo $company_data['name']; ?> <?php echo l("'s Reservation Policy and"); ?>
                            <?php  if(empty($whitelabelinfo) || (isset($whitelabelinfo['name']) && $whitelabelinfo['name'] == 'Minical')) {  ?>
                                <a href="#" data-toggle="modal" data-target="#policy_modal">
                                    <?php echo l($partner_name."'s Terms and Conditions"); ?><br/>
                                </a>
                            <?php } else {
                                echo $partner_name."'s";
                            ?>
                                <a <?php echo !empty($whitelabelinfo['terms_of_service']) ? 'target="_blank" href="'.$whitelabelinfo['terms_of_service'].'"' : 'href="#"' ?>>
                                    <?php echo l("Terms and Conditions"); ?>
                                </a>
                                <?php echo l("&"); ?>
                                <a <?php echo !empty($whitelabelinfo['privacy_policy']) ? 'target="_blank" href="'.$whitelabelinfo['privacy_policy'].'"' : 'href="#"' ?>>
                                    <?php echo l("Privacy Policy"); ?><br/>
                                </a>
                            <?php } ?>


                        </div>
                    </div>
                    
                    <?php 
                     if(isset($this->module_assets_files['nexio_integration']) && $this->company_data['selected_payment_gateway'] =='nexio'){?>
                        <input type="hidden" value='0' name='nexio_active' id='nexio_active' class='nexio_active'>
                        <input type="button" value="<?php echo l('Book Now', 1); ?>" class="btn btn-success btn-lg pull-right" id="booking_engine_form"/>
                    <?php }else{?>
                        <input type="submit" value="<?php echo l('Book Now', 1); ?>" class="btn btn-success btn-lg pull-right" />

                    <?php }?>
                </form>
            </div>
        </div>
    </div>
</div>