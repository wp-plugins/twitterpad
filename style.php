<?php
require_once("../../../wp-load.php");
header('Content-Type: text/css; charset='.get_option('blog_charset').'');
$o = get_option('twitterpad-options');

echo '.twPadItmImg {
	float:left;
	padding-right:5px;
}

.twPadItm{
	'.$o["tp_item_style"].'
}'


?>
