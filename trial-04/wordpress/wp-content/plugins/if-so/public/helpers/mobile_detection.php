<?php /* check if user using smaller mobile device */
function my_wp_is_mobile() {
include_once ( __DIR__ .'/IfSo_Mobile_Detect.php');
$detect = new IfSo_Mobile_Detect;
if( $detect->isMobile() && !$detect->isTablet() ) {
return true;
} else {
return false;
}
}

/* check if user using tablet device */
function my_wp_is_tablet() {
include_once ( __DIR__  . '/IfSo_Mobile_Detect.php');
$detect = new IfSo_Mobile_Detect;
if( $detect->isTablet() ) {
return true;
} else {
return false;
}
}

?>