<?php
	if ( ! defined( 'ABSPATH' ) ) exit;

	require_once plugin_dir_path( dirname( __FILE__ ) ) . "../services/class-if-so-geo-service.php";

	$license = get_option( 'edd_ifso_license_key' );
	$status  = get_option( 'edd_ifso_license_status' );
	$expires = get_option( 'edd_ifso_license_expires' );
	// $item_name = get_option( 'edd_ifso_license_item_name' );

	function is_license_valid($status) {
		return ( $status !== false && $status == 'valid' );
	}

	// function get_product_name($license, $status) {
	// 	if ( is_license_valid( $status ) ) {

	// 		return "Pro";

	// 		// if ( $item_name ) 
	// 		// 	return $item_name;
	// 		// else if ( $expires == 'lifetime' )
	// 		// 	return "Pro";
	// 		// else
	// 		// 	return "Pro";
	// 	}
		
	// 	return 'Free';
	// }

	function is_plusgeo_license_exist($geoData) {
		return ( isset($geoData['has_plusgeo_key']) && $geoData['has_plusgeo_key'] == true );
	}

	function is_pro_license_exist($geoData) {
		return ( isset($geoData['has_pro_key']) && $geoData['has_pro_key'] == true );
	}

	function get_subscription($geoData) {
		$subscription = '';

		if ( is_pro_license_exist($geoData) )
			$subscription = "Pro";
		else
			$subscription = "Free";
		

		if ( is_plusgeo_license_exist($geoData) )
			$subscription .= " +Geolocation";
		

		return $subscription;
	}

	function is_geo_data_valid($geoData) {
		return ( isset($geoData['success']) && $geoData['success'] == true );
	}

	function get_queries_left($geoData) {
		if ( is_geo_data_valid($geoData) ) {
			return intval($geoData['realizations']);
		}

		return 0;
	}

	function get_monthly_queries($geoData) {
		if ( is_geo_data_valid($geoData) ) {
			return $geoData['bank'];
		}

		return 0;
	}

	function get_key($geoData, $key) {
		if ( isset( $geoData[$key] ) )
			return $geoData[$key];
		else
			return false;
	}

	function get_date_i18n($date) {
		return date_i18n( 'F j, Y', strtotime( $date, current_time( 'timestamp' ) ) );
	}

	function get_pro_purchase_date($geoData) {
		return get_key($geoData, 'pro_purchase_date');
	}

	function get_pro_renewal_date($geoData) {
		return get_key($geoData, 'pro_renewal_date');
	}

	function get_plusgeo_purchase_date($geoData) {
		return get_key($geoData, 'plusgeo_purchase_date');
	}

	function get_plusgeo_renewal_date($geoData) {
		return get_key($geoData, 'plusgeo_renewal_date');
	}


	// General
	// $ifso_version = IFSO_WP_VERSION;
	// $ifso_product = get_product_name($license, $status);

	// Geolocation

	$geoData = If_So_Geo_Service::getInstance()->get_status($license);

	$geo_subscription = get_subscription($geoData, $license, $status);
	$geo_monthly_queries = number_format(get_monthly_queries($geoData));
	$geo_queries_left = number_format(get_queries_left($geoData));

	// $selectedTab = ( isset( $_GET['method'] ) && $_GET['method'] == 'license' ) ?
				   // 'license' : 'info';
	$selectedTab = 'license';

	$licenseTabHeaderExtraClasses = ( $selectedTab == 'license' ) ?
									'selected-tab' : '';
	$licenseTabExtraStyles = ( $selectedTab == 'license' ) ?
						  '' : 'display:none;';

	$infoTabHeaderExtraClasses = ( $selectedTab != 'license' ) ?
									'selected-tab' : '';
	$infoTabExtraStyles = ( $selectedTab != 'license' ) ?
						  '' : 'display:none;';


   /* determine the appropriate no-license message */


   if ( true == get_option( 'edd_ifso_user_deactivated_license' ) ) {

	   $noLicenseMessageBox = '<div class="no_license_message">Your license is inactive. <a style="color:#fff;font-weight: 600;" href="https://www.if-so.com/free-license?utm_source=Plugin&utm_medium=FreeTrial&utm_campaign=wordpessorg&utm_term=LicensePage" target="_blank">Click here to get a new license if you do not have one.</a></div>';

   } else if ( false == get_option('edd_ifso_had_license') ) {

	   $noLicenseMessageBox = '<div class="no_license_message">Enter your license key to activate all features. If you do not have a license key, <a style="color:#fff;font-weight: 600;" href="https://www.if-so.com/free-license?utm_source=Plugin&utm_medium=FreeTrial&utm_campaign=wordpessorg&utm_term=LicensePage" target="_blank">click here</a>.</div>';

	} else {
		
	   $noLicenseMessageBox = '<div class="no_license_message">Your trial license key has expired. <a style="color:#fff;font-weight: 600;" href="https://www.if-so.com/free-license?utm_source=Plugin&utm_medium=FreeTrial&utm_campaign=wordpessorg&utm_term=LicensePage" target="_blank">Click here to get a Pro license key</a>.</div>';		
	}

?>
<div class="wrap">

	<h2>
	
		<?php 
			// _e('If>So License'); 
			_e('If>So Dynamic Content License');
		?>
		
	</h2>

	<div class="ifso-settings-wrapper">
			<ul class="ifso-settings-tabs-header">
				
				<li class="ifso-tab <?php echo $licenseTabHeaderExtraClasses ?>" 
					data-tab="license-tab-wrapper">
					License
				</li>
				<li class="ifso-tab <?php echo $infoTabHeaderExtraClasses; ?>" 					data-tab="ifso-info-tab-wrapper">					Geolocation				</li>
			</ul>

			<div class="ifso-settings-tabs-wrapper">

				<div class="ifso-info-tab-wrapper"
					 style="<?php echo $infoTabExtraStyles; ?>">

					<!--<div class="ifso-general-info-wrapper">
						<h1 class="ifso-info-title">General</h1>
						<div class="ifso-info-content-wrapper">
							<div class="ifso-info-content">
								<span class="ifso-content-head">Version:</span>
								<span class="ifso-content-body"><?php echo $ifso_version; ?><span>
							</div>
							<div class="ifso-info-content">
								<span class="ifso-content-head">Product:</span>
								<span class="ifso-content-body"><?php echo $ifso_product; ?><span>
							</div>
						</div>
					</div>-->
					<div class="geolocation-info-wrapper">	
							
					<p class="ifso-settings_paragraph">Geolocation is limited to 250 monthly <span title="A session is defined as beginning when a visitor first visits a page with a geolocation trigger and ends when a visitor closes the browser or after 25 minutes." class="tm-tip ifso_tooltip line-tooltip">sessions</span>  with the free version and 3,000 monthly sessions for the duration of one year with the pro version.  <a class="buy-more-credits-link" href="https://www.if-so.com/plans/geolocation-plans/?utm_source=Plugin&utm_medium=geo_utilization" target="_blank">Click here</a> for additional options if your website handles a larger amount.</p>
							<p class="ifso-settings_paragraph">
								<span class="ifso-content-body">Sessions used this month:</span>
								<span class="ifso-content-head">
									<?php echo $geo_queries_left."/".$geo_monthly_queries; ?></span>
									 
									
								
							</p>
							
							
						<h1 class="ifso-info-title">Account Info</h1>
						<div class="ifso-info-content">													
							<div class="ifso-info-content">
								<span class="ifso-content-head">Plan:</span>
								<span class="ifso-content-body">
									<span class="geo-subscription">
										<?php echo $geo_subscription; ?>
									</span>
									<span class="geo-monthly-queries">
										- <?php echo $geo_monthly_queries; ?> 
										monthly sessions 
										
									</span>
								<span>
							</div>
							<?php if ( is_pro_license_exist($geoData) ): ?>

							<div class="ifso-info-content">
								<span class="ifso-content-head">Purchase date:</span>
								<span class="ifso-content-body">
									<?php echo get_date_i18n(get_pro_purchase_date($geoData)); ?>
								<span>
							</div>

							
							<?php endif; ?>
							<div class="ifso-info-content">
								<span class="ifso-content-head">Utilization:</span>
								<span class="ifso-content-body">
									<?php echo $geo_queries_left."/".$geo_monthly_queries; ?>
									 <a class="buy-more-credits-link" href="https://www.if-so.com/geolocation-plans?ifso=geo&utm_source=Plugin&utm_medium=Geolocation&utm_campaign=geo_utilization" target="_blank">
										 	(Upgrade)
										 </a>
								<span>
							</div>

							<?php if ( is_pro_license_exist($geoData) ): ?>

							
							<div class="ifso-info-content">
								<span class="ifso-content-head">Sessions renewal date:</span>
								<span class="ifso-content-body">
									<?php echo get_date_i18n(get_pro_renewal_date($geoData)); ?>
								<span>
							</div>

							<?php endif; ?>

							<?php if ( is_plusgeo_license_exist($geoData) ): ?>

							<div class="ifso-info-content">
								<span class="ifso-content-head">PlusGeo Purchase date:</span>
								<span class="ifso-content-body">
									<?php echo get_date_i18n(get_plusgeo_purchase_date($geoData)); ?>
								<span>
							</div>

							<div class="ifso-info-content">
								<span class="ifso-content-head">PlusGeo Queries renewal date:</span>
								<span class="ifso-content-body">
									<?php echo get_date_i18n(get_plusgeo_renewal_date($geoData)); ?>
								<span>
							</div>

							<?php endif; ?>

						</div>
					</div>
				</div>

				<div class="license-tab-wrapper" 
					 style="<?php echo $licenseTabExtraStyles; ?>">

				<?php if (!is_license_valid( $status )): ?>

					<?php echo $noLicenseMessageBox; ?>

				<?php endif; ?>

				<form method="post" action="options.php" class="license-form">

					<?php settings_fields('edd_ifso_license'); ?>


					<table class="form-table license-tbl">
						<tbody>
							<tr valign="top">
								<th class="licenseTable" scope="row" valign="top">
									<?php _e('License Key'); ?>
								</th>
								<td>
									<input id="edd_ifso_license_key" <?php echo ( is_license_valid( $status ) ) ? "readonly":""; ?> name="edd_ifso_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />

									<?php
										
										if ( $this->edd_ifso_is_in_activations_process() ) {
											// in activations process
											
											$error_message = $this->edd_ifso_get_error_message();

											if ( $error_message ) {
												?>

												<span class="description license-error-message">
													<?php echo $error_message; ?>
												</span>

												<?php
											}

										} else {
									?>

										<label class="description" for="edd_ifso_license_key"><?php _e('Enter your license key'); ?></label>

									<?php
										}
									?>

								</td>
							</tr>
							<tr valign="top">
								<th class="licenseTable" scope="row" valign="top">
									<?php _e('Activate License'); ?>
								</th>
								<td>
									<?php if( $status !== false && $status == 'valid' ) { ?>
										<?php wp_nonce_field( 'edd_ifso_nonce', 'edd_ifso_nonce' ); ?>
										<input type="submit" class="button-secondary" name="edd_ifso_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
									<?php } else {
										wp_nonce_field( 'edd_ifso_nonce', 'edd_ifso_nonce' ); ?>
										<input type="submit" class="button-secondary" name="edd_ifso_license_activate" value="<?php _e('Activate License'); ?>"/>
									<?php } ?>
								</td>
							</tr>
						</tbody>
					</table>

					<!-- License key expiratiaton date -->
					<?php if ($status == 'valid' && $expires == 'lifetime') { ?>
					<div class="license_expires_message">Your license is Lifetime.</span></div>				
					<?php } else if ( $status == 'valid' && $expires !== false ) { ?>
					<div class="license_expires_message">Your license key expires on <span class="expire_date"><?php echo date_i18n( 'F j, Y', strtotime( $expires, current_time( 'timestamp' ) ) ); ?>.</span></div>
					<?php } ?>

					<?php //submit_button(); ?>

				</form>

				<?php if ($status !== false && $status == 'valid' ): ?>
					<div class="approved_license_message">
						<strong>Thank you for using If>So Dynamic Content!</strong> Please feel free to contact our team with any issues you may have.
					</div>
				<?php endif; ?>

			</div> <!-- end of license-tab-wrapper -->
		</div> <!-- end of ifso-settings-tabs-wrapper -->

	</div>

</div>