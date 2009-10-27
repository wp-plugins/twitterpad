<?php if (isset($_POST['tp_action'])) { ?><div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);"><p><strong><?php echo $status; ?></strong></p></div><?php } ?>
<style type="text/css">
table
{
    background-color: #fff;
    border: 2px solid #ccc;
    -moz-border-radius: 10px;
    -webkit-border-radius: 10px;
    border-radius: 10px;
    width:100%;
    padding: 2px;
}
.form-table th {
	width:auto;
}
</style>
<div class="wrap">
  <h2>TwitterPad</h2>
  <h3>Add Feed</h3>
  <div class="gdsr">
<form method="post">
	<?php if ( function_exists('wp_nonce_field') )
			wp_nonce_field('twpad-1', 'twpad-main');
			?>
<input type="hidden" name="tp_action" value="add" />
<table class="form-table"><tbody>
    <tr>
      <th scope="row">Twitter RSS url:</th>
        <td align="left">
            <input type="text" name="tp_url" id="tp_url" style="width: 530px" /><br/>
			e.g. 'http://search.twitter.com/search.atom?q=from:mhawksey'
		</td>
    </tr>
    <tr>
      <th scope="row">Update Page:</th>
        <td align="left">
		 <select name="tp_page" id="tp_page">
						<option value="">-select a page-</option>
					<?php
					global $wpdb;
					$query = "SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'page' ORDER BY post_title ASC";
					$pages = $wpdb->get_results($query);
					foreach ($pages as $p) {
                    	echo '<option value="'.$p->ID.'"'.$selected.'>'.$p->post_title.'</option>';
						$pagename[$p->ID]=$p->post_title;
                    }
					?>
					 </select>
        </td>
    </tr>
</tbody></table>

<p class="submit"><input class="inputbutton" type="submit" value="Add Feed" name="saving"/></p>
</form>
</div>
<div class="gdsr">
<h3>Current Feeds</h3>
            <form method="post">
            	<?php wp_nonce_field('twpad-4' ); ?>
            <input type="hidden" name="tp_action" value="delete" />
  <table class="widefat">
    <thead>
      <tr>
        <th scope="col">Page</th>
        <th scope="col">Feed</th>
		<th scope="col">Last Item</th>
		<th scope="col">Last Feed Date</th>
		<th scope="col">Remove</th>
      </tr>
    </thead>
<?php
if(!(empty($options['tp_feeds']))) {
	foreach ($options['tp_feeds'] as $idx => $tps) {
		 	echo "<tr><td>".$pagename[$tps['page']]."</td><td>".$tps['url']."</td><td>".date("d M y  H:i:s", $tps['lastItemDate'])."</td><td>".date("d M y  H:i:s",$tps['refresh'])."</td><td align='center'><input name='tpfeed[]' type='checkbox' value='".$idx."'></td></tr>";
	  	}
	$showRemove = true;
	} else {
		echo "<tr><td colspan='5' align='center'><strong><em>None</em></strong></td></tr>";
	}
 ?>
 </table> 
 <?php if ($showRemove) echo "<p align=\"right\"><input class=\"inputbutton\" type=\"submit\" value=\"Remove\" name=\"saving\" /></p>"; ?>

</form>
 </div>
<div class="gdsr">
<h3>Options</h3>
<table class="form-table"><tbody>
    <tr><th scope="row">Item styling:</th>
        <td align="left">
            <form method="post">
            	<?php wp_nonce_field('twpad-5' ); ?>
            <input type="hidden" name="tp_action" value="stylenow" />
            <input name="tp_item_style" type="text" id="tp_item_style" style="width: 400px" value="<?php echo $options['tp_item_style']; ?>" />
                <input class="inputbutton" type="submit" value="Save Now" name="saving" /><br/>
				You can style each of the twitter items (e.g. 'border-bottom: 1px dashed #cccccc; padding: 10px 5px;')
            </form>
        </td>
    </tr>
    <tr><th scope="row">Read feeds:</th>
        <td align="left">
            <form method="post">
            	<?php wp_nonce_field('twpad-2' ); ?>
            <input type="hidden" name="tp_action" value="runow" />
                <input class="inputbutton" type="submit" value="Run Now" name="saving" />
            </form>
        </td>
    </tr>
    <tr><th scope="row">Reset settings:</th>
        <td align="left">
            <form method="post">
            	<?php wp_nonce_field('twpad-3' ); ?>

            <input type="hidden" name="tp_action" value="reset" />
                <input class="inputbutton" type="submit" value="Reset" name="saving" />
            </form>
        </td>
    </tr>
	<tr><th scope="row">Diagnostics:</th>
	<td align="left">Last Refresh:<?php echo date("d M y  H:i:s", $options["tp_last_refresh"]); ?>
		</td></tr>
</table>
</div>
</div>
