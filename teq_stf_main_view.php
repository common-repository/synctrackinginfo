<?php
defined('ABSPATH') || die('Access Denied!');
?>

<style>
	.ot-note {background:#efefef;padding: 2px 10px;display:none}
	.ot-note p {font-size:10px}
	#custom_carrier_section {display:none}
</style>

	<div class="ot-note" id="ot-note">
		<p id="oss_p_status">Loading..</p>
	</div>
	
	<?php if (WC_Admin_Settings::get_option('teq_stf_tracking_app', 'synctrackinginfo') == 'synctrackinginfo') : ?>

		<p class="form-field form-field-wide">
			<label for="oss_tracking_number_input">Tracking Number:</label>
			<input type="text" name="oss_tracking_number_input" id="oss_tracking_number_input" value="" />
		</p>
		<p class="form-field form-field-wide">
			<label for="oss_shipping_carrier_select">Shipping Carrier:</label>
			<select id="oss_shipping_carrier_select" name="oss_shipping_carrier_select" style="width:95%">
				<option value="" disabled selected>Select Carrier</option>
				<option value="4PX">4PX</option>
				<option value="APC">APC</option>
				<option value="Amazon Logistics">Amazon Logistics</option>
				<option value="Australia Post">Australia Post</option>
				<option value="Bluedart">Bluedart</option>
				<option value="Canada Post">Canada Post</option>
				<option value="China Post">China Post</option>
				<option value="DHL Express">DHL Express</option>
				<option value="DHL eCommerce">DHL eCommerce</option>
				<option value="DHL eCommerce Asia">DHL eCommerce Asia</option>
				<option value="Delhivery">Delhivery</option>
				<option value="Eagle">Eagle</option>
				<option value="FedEx">FedEx</option>
				<option value="GLS">GLS</option>
				<option value="La Poste">La Poste</option>
				<option value="Royal Mail">Royal Mail</option>
				<option value="Singapore Post">Singapore Post</option>
				<option value="TNT">TNT</option>
				<option value="UPS">UPS</option>
				<option value="USPS">USPS</option>
				<option value="Other">Other</option>
			</select>
		</p>
		<p class="form-field form-field-wide" id="custom_carrier_section">
			<label for="oss_custom_carrier_input">Carrier Name:</label>
			<input type="text" name="oss_custom_carrier_input" id="oss_custom_carrier_input" value="" placeholder="Carrier Name" />
		</p>
		<p class="form-field form-field-wide">
			<label for="oss_tracking_link_input">Tracking Link:</label>
			<input type="text" name="oss_tracking_link_input" id="oss_tracking_link_input" value="" placeholder="https://" />
		</p>
		<p class="form-field form-field-wide">
			<label for="oss_tracking_completed_check">Mark Order As Completed:</label>
			<input type="checkbox" name="oss_tracking_completed_check" id="oss_tracking_completed_check" />
		</p>
		<?php if ($teq_stf_version) : ?>
		<!--
		<p class="form-field form-field-wide">
			<label for="oss_tracking_sync_check">Submit Tracking to Paypal:</label>
			<input type="checkbox" name="oss_tracking_sync_check" id="oss_tracking_sync_check" checked />
		</p>
		-->
		<?php else: ?>
		<p class="form-field form-field-wide">
			<label>
				<a href="<?php echo admin_url('admin.php?page=wc-settings&tab=synctrackinginfo') ?>">Paypal Sync is inactive (pro)</a>
			</label>
		</p>
		<!--
		<p class="form-field form-field-wide">
			<label for="oss_tracking_sync_check">Submit Tracking to Paypal (
				<a href="<?php echo admin_url('admin.php?page=wc-settings&tab=synctrackinginfo') ?>">pro
				</a>):
			</label>
			<input type="checkbox" name="oss_tracking_sync_check" id="oss_tracking_sync_check" disabled />
		</p>
		-->
		<?php endif; ?>
		<div style="border-top: 1px solid #ddd;">
			<input type="button" name="oss_stf_submit" id="oss_stf_submit" class="button button-primary" value="Submit" style="float:right; margin-top:10px">
		</div>
	<?php endif; ?>
	<div class="clear"></div>



<script>
/**
* OnClick event for submit button in the main widget
* It mainly send values to the API
* The API stores these values and Sync to Paypal if checked
*/
<?php if (WC_Admin_Settings::get_option('teq_stf_tracking_app', 'synctrackinginfo') == 'synctrackinginfo') : ?>
	document.getElementById('oss_stf_submit').onclick = async function(e) {
		e.preventDefault();
		this.disabled = true;

		// get fields' values
		var oss_shipping_carrier_select = document.getElementById('oss_shipping_carrier_select').value;
		var ascompleted_cbox = document.getElementById('oss_tracking_completed_check');
		var status_select = document.getElementById('order_status');
		var tracking_number = document.getElementById('oss_tracking_number_input').value;
		var shipping_carrier = oss_shipping_carrier_select == "Other" ? document.getElementById('oss_custom_carrier_input').value : oss_shipping_carrier_select;
		var tracking_link = document.getElementById('oss_tracking_link_input').value;
		var sync_cbox = document.getElementById('oss_tracking_sync_check');

		// mark order as completed before save
		if (ascompleted_cbox.checked) {
			status_select.value = 'wc-completed';
		}

		// send values to api
		var post_data = {
			"tracking_info": tracking_number,
			"order_id": <?php echo esc_js(get_the_ID()); ?>,
			"carrier_name": shipping_carrier,
			"tracking_link": tracking_link
		};

		try {
			var result = await send_post('<?php echo esc_js(TEQ_STF_SERVER); ?>/orders', post_data);
			var order = JSON.parse(result);

			document.getElementById('post').submit();
		}catch (e) {
			alert("Sync Error Occured. Please Try again!");
		}

	};
<?php endif; ?>


/**
* Perform a post request on url
*
* @param: String url
* @json: data
*/
function send_post(url, data) {

	return new Promise(function (resolve, reject) {
		var xhr = new XMLHttpRequest();
		xhr.open("POST", url, true);

		//Send the proper header information along with the request
		xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
		xhr.setRequestHeader("Serial-Key", "<?php echo esc_js(WC_Admin_Settings::get_option('oss_stf_activation_key', '')); ?>");
		xhr.setRequestHeader("Store-Url", window.location.hostname);

		xhr.onreadystatechange = function() {
			if (this.readyState === XMLHttpRequest.DONE) {
				if (this.status === 201) {
					resolve(this.responseText);
				}else {
					reject(this.responseText);
				}
			}
		}

		xhr.onerror = function() {
			reject(this.responseText);
		}

		xhr.send(JSON.stringify(data));

	});

}


/**
* Carrier drop down change action
* If selected carrier is Other, we show a field to input custom carrier name
*/
<?php if (WC_Admin_Settings::get_option('teq_stf_tracking_app', 'synctrackinginfo') == 'synctrackinginfo') : ?>
	document.getElementById('oss_shipping_carrier_select').addEventListener('change', function() {

		if (this.value == "Other") {
			document.getElementById('custom_carrier_section').style.display = "block";
		}else {
			document.getElementById('custom_carrier_section').style.display = "none";
		}

	});
<?php endif; ?>


/**
* On page load
* Gets order tracking info from the API and shows them on app widget
*/
function main() {
	var xhr = new XMLHttpRequest();

	xhr.addEventListener("load", function() {
		var p_status = document.getElementById('oss_p_status');
		document.getElementById('ot-note').style.display = "block";
		if (this.status == 200) {
			var order = JSON.parse(xhr.responseText);
			<?php if (WC_Admin_Settings::get_option('teq_stf_tracking_app', 'synctrackinginfo') == 'synctrackinginfo') : ?>
				var oss_shipping_carrier_select = document.getElementById('oss_shipping_carrier_select');

				document.getElementById('oss_tracking_number_input').value = order.tracking_info;
				oss_shipping_carrier_select.value = order.carrier_name;
				document.getElementById('oss_tracking_link_input').value = order.tracking_link;
				
				// Check custom carrier
				if (oss_shipping_carrier_select.selectedIndex == -1) { // custom carrier
					oss_shipping_carrier_select.value = "Other";
					document.getElementById('custom_carrier_section').style.display = "block";
					document.getElementById('oss_custom_carrier_input').value = order.carrier_name;
				}
			<?php endif; ?>

			if (order.sync_status == 'Processing') {
				p_status.innerHTML = "We are still processing this order, refresh page for updates..";
			}else if (order.sync_status == 'Processed') {
				p_status.innerHTML = "Order Processed but Sync not submitted. Upgrade or check your settings.";
			}else if (order.sync_status == 'Ignored') {
				p_status.innerHTML = "Sync Cancelled. No Paypal Transaction Found. This Order Wasn't Paid via Paypal.";
			}else if (order.sync_status == 'Synced') {
				p_status.innerHTML = "Synced On: " + order.created_at;
			}else {
				p_status.innerHTML = "This Order is Not Yet Processed, Waiting For Status.";
			}
		}else {
			p_status.innerHTML = "This Order is Not Yet Processed, Waiting For Status.";
		}
	});

	xhr.open("GET", "<?php echo esc_js(TEQ_STF_SERVER . '/orders/' . get_the_ID()); ?>");

	xhr.setRequestHeader("Serial-Key", "<?php echo esc_js(WC_Admin_Settings::get_option('oss_stf_activation_key', '')); ?>");
	xhr.setRequestHeader("Store-Url", window.location.hostname);
	xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");

	xhr.send();

}

main();

</script>
