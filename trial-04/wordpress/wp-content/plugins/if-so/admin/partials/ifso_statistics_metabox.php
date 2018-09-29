<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Loading from DB

$data_default_metadata_json = 
                get_post_meta( $post->ID,
                              'ifso_trigger_default_metadata',
                               true );
if ( !empty($data_default_metadata_json) ) {
	$data_default_metadata = json_decode($data_default_metadata_json, true);
} else {
	$data_default_metadata = array(
		'statistics_count' => 0
	);
}

$data_rules_json = get_post_meta( $post->ID, 'ifso_trigger_rules', true );
$data_rules = json_decode($data_rules_json, true);

?>

<?php
	function display_statistics_html( $version_symbol,
									  $counter,
									  $reset_action_symbol,
									  $isNew ) {

	    ?>

	    <tr class="version_statistics">
	    	<td class="version"><?php echo $version_symbol; ?>
	    	</td>
	    	<td class="statistics">
	    		<?php echo $counter; ?>
	    	</td>
	    	<?php if (!$isNew): ?>

	    	<td class="rese_action">
	    		<a class="reset_version_action" data-version="<?php echo $reset_action_symbol; ?>">Reset</a>

	    	</td>

	    	<?php endif; ?>
	    </tr>

    <?php

	}

	function display_statistics($index, $rule) {
		$statisticsCounter = 0;
		if (isset($rule['statistics_counter']))
			$statisticsCounter = $rule['statistics_counter'];

	    $current_version_index = $index; //+1; // Removed the +1
	    $current_version_count_char = chr(64 + $current_version_index+1);

	    display_statistics_html( $current_version_count_char, 
	    						 $statisticsCounter,
	    						 $index,
	    						 false );
	}

?>

<div class="helper-metabox-container">
	<table class="statistics_wrapper">
		<tr class="version_statistics_title">
		    <th class="anaylticsTable;">Version</th>
		    <th>Views</th> 
		    <th></th>
		  </tr>

		<?php if(!empty($data_rules)): ?>
			<?php foreach($data_rules as $index => $rule): ?>
			    <?php display_statistics($index, $rule); ?>
			<?php endforeach; ?>
			<?php
				/* Display Default Statistics */
				display_statistics_html( "Default",
										 $data_default_metadata['statistics_count'],
										 "default",
										 false );
			?>
		<?php else: ?>
		<?php 
			 // New trigger?

			display_statistics_html( "A", 0, "0", true );
			display_statistics_html( "Default", 0, "default", true );
		?>

		<?php endif; ?>
	</table>

	<?php if(!empty($data_rules)): ?>

	<div class="analytics-reset-all-wrapper">
		<a class="reset-all-views-count">Reset All</a>	
	</div>

	<?php endif; ?>
</div>
