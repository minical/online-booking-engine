
<div class="container">
	<?php echo l('The online reservation has been requested to', true); ?> <?php echo $company_data['name']; ?>. 
	<?php if(isset($company_data['email_confirmation_for_booking_engine']) && !$company_data['email_confirmation_for_booking_engine']){
        	echo l('Please check your email inbox for a booking confirmation email.', true); 
        } ?>
	<br/><br/>
	<?php echo l('For inquiries, please contact us at', true); ?>: <?php echo $company_data['phone']; ?>
	<br/><br/>
	
	<?php
		if ($paypal_data['require_paypal_payment']):
	?>
			<?php echo $company_data['name']; ?> <?php echo l('requires a PayPal payment to secure your reservation.'); ?>
			
			<br/>
			<?php echo l('Please deposit'); ?> <b> <?php echo number_format($paypal_data['required_payment'], 2, ".", ",")." ".$paypal_data['currency_code']; ?> </b>
			(<?php echo $paypal_data['percentage_of_required_paypal_payment']; ?>% <?php echo l('of total cost'); ?>)
			
			<?php

				if (strtolower($_SERVER['HTTP_HOST']) != 'localhost')
				{
					$webscr_url = "https://www.paypal.com/cgi-bin/webscr";	
				}
				else
				{
					$webscr_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";	
				}
			?>
			<form action="<?php echo $webscr_url; ?>" method="post"> 
				<!-- Identify your business so that you can collect the payments. --> 
				<input type="hidden" name="business" value="<?php echo $paypal_data['paypal_account']; ?>"> 

				<!-- Specify a Buy Now button. --> 
				<input type="hidden" name="cmd" value="_xclick"> 
				<!-- set IPN url -->
                <input type="hidden" name="notify_url" value="<?php echo base_url(); ?>online_reservation/validate_ipn/<?php echo $paypal_data['company_id']; ?>">

				<!-- Specify details about the item that buyers will purchase. --> 
				<input type="hidden" name="item_name" value="<?php echo $paypal_data['item_name']; ?>"> 
				<input type="hidden" name="item_number" value="<?php echo $paypal_data['booking_id']; ?>"> 
				 
				<input type="hidden" name="amount" value="<?php echo number_format($paypal_data['required_payment'], 2, ".", ""); ?>">  
				<input type="hidden" name="currency_code" value="<?php echo $paypal_data['currency_code']; ?>"> 

				<!-- Display the payment button. --> 
				<?php echo l('by clicking here'); ?>: <input type="image" name="submit" border="0" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" alt="PayPal - The safer, easier way to pay online"
				style="vertical-align:text-top;"		
				> 
				<img alt="" border="0" width="1" height="1" 	src="https://www.paypal.com/en_US/i/scr/pixel.gif" > 
			</form>
	<?php
		endif;
	?>
	
</div>
<input name="company_id" value="<?php echo $this->uri->segment(3); ?>" hidden="hidden" />
<script>
	var isReservationSuccessPage = true;
	var reservationCheckInDate = "<?=$view_data['check_in_date']?>";
	var reservationCheckOutDate = "<?=$view_data['check_out_date']?>";
</script>
