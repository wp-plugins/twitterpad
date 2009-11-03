<?php if (isset($_POST['tp_action'])) { ?>
<div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);">
  <p><strong><?php echo $status; ?></strong></p>
</div>
<?php } ?>
<style type="text/css">
table, .box
{
    background-color: #fff;
    border: 2px solid #ccc;
    -moz-border-radius: 10px;
    -webkit-border-radius: 10px;
    border-radius: 10px;
    width:100%;
    padding: 2px;
}
table td, table th {
padding:4px;
vertical-align:top;
}

</style>
<div class="wrap">
  <h2>TwitterPad</h2>
  <div class="box">
    <h3>Instructions</h3>
    <ol>
      <li>In the 'Settings' for 'TwitterPad' enter a rss feed url for your query (i.e. Goto&nbsp;<a href="http://search.twitter.com">search.twitter.com</a>&nbsp;enter your search term then copy the 'Feed for this query' url)</li>
      <li>Paste the url into 'Twitter RSS url' and select the page you would like updates to be added to</li>
      <li>Click 'Add Feed'</li>
    </ol>
    <p><small>Tweet feature requests with the #twpadreq</small></p>
  </div>
  <h3>Add Feed</h3>
  <div class="gdsr">
    <form method="post">
      <?php if ( function_exists('wp_nonce_field') )
			wp_nonce_field('twpad-1', 'twpad-main');
			?>
      <input type="hidden" name="tp_action" value="add" />
      <table>
        <tbody>
          <tr>
            <th scope="row">Twitter RSS url:</th>
            <td align="left"><input type="text" name="tp_url" id="tp_url" style="width: 530px" />
              <br/>
              e.g. 'http://search.twitter.com/search.atom?q=from:mhawksey' </td>
          </tr>
          <tr>
            <th scope="row">Update Page:</th>
            <td align="left"><select name="tp_page" id="tp_page">
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
          <tr>
            <th scope="row"></th>
            <td><span class="submit">
              <input class="inputbutton" type="submit" value="Add Feed" name="saving"/>
              </span></td>
          </tr>
        </tbody>
      </table>
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
	if ($showRemove) echo "<tr><td colspan='5' align='right'><input class=\"inputbutton\" type=\"submit\" value=\"Remove\" name=\"saving\" /></td></tr>";
 ?>
      </table>
    </form>
  </div>
  <div class="gdsr">
    <h3>Options</h3>
    <form method="post">
      <table>
        <tbody>
          <tr>
            <th scope="row">Item styling:</th>
            <td align="left"><?php wp_nonce_field('twpad-5' ); ?>
              <input type="hidden" name="tp_action" value="stylenow" />
              <input name="tp_item_style" type="text" id="tp_item_style" style="width: 400px" value="<?php echo $options['tp_item_style']; ?>" />
              <br/>
              You can style each of the twitter items (e.g. 'border-bottom: 1px dashed #cccccc; padding: 10px 5px;') </td>
          </tr>
          <tr>
            <th scope="row">Update frequency:</th>
            <td align="left"><select style="width: 180px;" name="tp_refresh_period" id="tp_refresh_period">
                <option value="1"<?php echo $options["tp_refresh_period"] == '1' ? ' selected="selected"' : ''; ?>>1 hour</option>
                <option value="4"<?php echo $options["tp_refresh_period"] == '4' ? ' selected="selected"' : ''; ?>>4 hours</option>
                <option value="12"<?php echo $options["tp_refresh_period"] == '12' ? ' selected="selected"' : ''; ?>>12 hours</option>
                <option value="24"<?php echo $options["tp_refresh_period"] == '24' ? ' selected="selected"' : ''; ?>>24 hours</option>
              </select>
            </td>
          </tr>
          <tr>
            <th scope="row"></th>
            <td><span class="submit">
              <input class="inputbutton" type="submit" value="Save Now" name="saving" />
              </span></td>
          </tr>
        </tbody>
      </table>
    </form>
    <table>
      <tr>
        <td><form method="post">
            Manually update feeds now:
            <?php wp_nonce_field('twpad-2' ); ?>
            <input type="hidden" name="tp_action" value="runow" />
            <span class="submit">
            <input class="inputbutton" type="submit" value="Run Now" name="saving" />
            </span>
          </form></td>
      </tr>
      <tr>
        <td><form method="post">
            Reset (WARNING: this deletes all your exisiting feeds):
            <?php wp_nonce_field('twpad-3' ); ?>
            <input type="hidden" name="tp_action" value="reset" />
            <span class="submit">
            <input class="inputbutton" type="submit" value="Reset" name="saving" />
            </span>
          </form></td>
      </tr>
      <tr>
        <td><small>Diagnostics: Last Refresh - 
            <?php $pdate = $options["tp_last_refresh"]; echo date("d M y  H:i:s", $pdate); ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Next Update - 
            <?php $next = mktime(date("H", $pdate) + $options["tp_refresh_period"], date("i", $pdate), date("s", $pdate), date("m", $pdate) , date("d", $pdate), date("Y", $pdate)); echo date("d M y  H:i:s", $next); ?>
        </small></td>
      </tr>
    </table>
  </div>
</div>
