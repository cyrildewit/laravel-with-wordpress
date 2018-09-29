<?php
 /*Template Name: IfSo Triggers
 */
 
get_header(); 

/* restrict this page to only authorized members (administartors) */

$isUserAdmin = current_user_can('administrator');

if (!$isUserAdmin) {
	?>

	<div id="primary">
    	<div id="content" role="main">
    	You are not authorized to view this page
    	</div>
    </div>

	<?php
	
	get_footer();

} else {

$post = get_post();

$data_versions = get_post_meta( $post->ID, 'ifso_trigger_version', false );
$data_default = get_post_meta( $post->ID, 'ifso_trigger_default', true );

function display_version_content_html($version_symbol, $version_content) {
?>
	<div class="version-content-wrapper">
		<div class="version-symbol" style="border-bottom: 1px solid #e1e1e1;border-top: 1px solid #e1e1e1;"><?php echo $version_symbol; ?></div>
		<div class="version-content" style="margin: 30px 0;"><?php echo $version_content; ?></div>
	</div>
<?php

} 

function display_version_content_with_symbol_html($version_index, $version_content) {
	$version_symbol = "Version ".chr(65 + $version_index);

	return display_version_content_html($version_symbol, $version_content);
}

?>

<div id="primary">
    <div id="content" role="main">
    	<div class="data-versions-content-wrapper">

    	<?php 
    		if (!empty($data_versions)) {
				foreach($data_versions as $index => $version_content) {
					display_version_content_with_symbol_html($index, $version_content);
    			}
	    	}

    		if (!empty($data_default)) {
				display_version_content_html("Default Version", $data_default);
    		}
    	?>

    	</div>
    </div>
</div>
<?php 
	get_footer();
	}
?>