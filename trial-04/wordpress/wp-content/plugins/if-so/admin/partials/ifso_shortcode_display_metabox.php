<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
/*
 * @link       https://icanwp.com/plugins/portfolio-gallery/
 * @since      1.0.0
 *
 * @package    WP_Post_Ticker_Pro
 * @subpackage WP_Post_Ticker_Pro/admin/partials
 */
 ?>
<?php
$current_post_id = get_the_ID();
if (get_post_status( $current_post_id ) == 'publish' ):
?>
<h4 style="margin-bottom:8px;font-weight:normal;"><?php _e('Paste this shortcode into your website', 'if-so'); ?></h4>
<?php $shortcode = sprintf( '[ifso id="%1$d"]', $current_post_id); ?>
<span class="shortcode"><input type="text" onfocus="this.select();" readonly="readonly" value='<?php echo $shortcode; ?>' class="large-text code"></span>
<!--<p style="text-align: center; margin: 5px auto;">-- <?php _e('Or', 'if-so'); ?> --</p>
<h4 style="margin-top:0; margin-bottom:0;"><?php _e('PHP code to paste in your template', 'if-so'); ?></h4>-->
<p class="php-shortcode-toggle-link">> <?php _e('PHP Code (for developers)', 'if-so'); ?></p>

<div class="php-shortcode-toggle-wrap">
	<?php $php_code = sprintf( '<?php ifso(%1$d); ?>', $current_post_id); ?>
	<span class="shortcode"><input type="text" onfocus="this.select();" readonly="readonly" value='<?php echo $php_code; ?>' class="large-text code"></span>
</div>

<?php else: ?>
<?php if(false): /*:$post_status != 'publish'):*/ ?>
					<p class="initial-instructions"><b><?php _e('', 'if-so'); ?>3</b> <?php _e('Paste this shortcode on to your website', 'if-so'); ?></p>
									<?php endif; ?>
<p><?php _e('The shortcode will be available after publishing', 'if-so'); ?></p>
<?php endif; ?>