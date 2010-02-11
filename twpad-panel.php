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
      <li>Paste the url into 'Twitter RSS url' and select the page you would like updates to be added to (or results can be summarised in a new post) </li>
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
              e.g. 'http://search.twitter.com/search.atom?q=from:mhawksey' <br>
              <small>By default twitter limits search results to 20 items. If you expect more add '&amp;rpp=100' to the end of your url. It is also possible to use twitter feeds manipulated by other services like <a href="http://pipes.yahoo.com">Yahoo Pipes</a>. For example if you would like to post your tweets excluding @replies you can enter 
              'http://pipes.yahoo.com/mashe/noreplies?_render=rss&amp;uname=<span style="font-style: italic">yourtwitterusername'</span> (<a href="http://pipes.yahoo.com/mashe/noreplies?_render=rss&uname=mhawksey">example</a>). Here is<a href="http://pipes.yahoo.com/mashe/noreplies"> the</a></small><a href="http://pipes.yahoo.com/mashe/noreplies"> source pipe</a>. </td>
          </tr>
          <tr>
            <th scope="row">Update Page/ New Post:</th>
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
					<option value="[New Post]">[New Post]</option>
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
            <th scope="col">Next Feed Refresh</th>
            <th scope="col">Remove</th>
          </tr>
        </thead>
        <?php
if(!(empty($options['tp_feeds']))) {
	foreach ($options['tp_feeds'] as $idx => $tps) {
		if ($tps['page'] != "[New Post]") {
			$pageText = "<a href='".get_permalink($tps['page'])."'>".$pagename[$tps['page']]."</a>";
		}else{
			$pageText = "[New Post]";
		}
		 	echo "<tr><td>".$pageText."</td><td>".$tps['url']."</td><td>".date("d M y  H:i:s", $tps['lastItemDate'])."</td><td>".date("d M y  H:i:s",$tps['refresh'])."</td><td align='center'><input name='tpfeed[]' type='checkbox' value='".$idx."'></td></tr>";
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
            <th scope="row">Item styling CSS </th>
            <td align="left"><input type="checkbox" name="tp_item_css" id="tp_item_css"<?php if ($options["tp_item_css"] == 1) echo " checked"; ?> />
            <label style="margin-left: 5px;" for="tp_item_images">Use item styling CSS  Enabling this allows you to control item styling using the 'Item style' below. </label></td>
          </tr>
          <tr>
            <th scope="row">Item styling:</th>
            <td align="left"><?php wp_nonce_field('twpad-5' ); ?>
              <input type="hidden" name="tp_action" value="stylenow" />
              <input name="tp_item_style" type="text" id="tp_item_style" style="width: 400px" value="<?php echo $options['tp_item_style']; ?>" />
              <br/>
              You can style each of the twitter items (e.g. 'border-bottom: 1px dashed #cccccc; padding: 10px 5px;') </td>
          </tr>
		  <tr>
		  	<th scope="row">Profile pictures:</th>
			<td align="left"><input type="checkbox" name="tp_item_image" id="tp_item_image"<?php if ($options["tp_item_image"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="tp_item_images">Show profile pictures next to tweets</label></td>
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
 </div>
 <div class="gdsr">
 <h3>New post template</h3>
   <form method="post">
      <table>
        <tbody>
          <tr>
            <th scope="row">Select day(s):</th>
            <td align="left"><?php wp_nonce_field('twpad-6' ); ?>
              <input type="hidden" name="tp_action" value="post_template" />
              <input name="d1" type="checkbox" id="d1" value="1" <?php if (strpos($options['tp_post_days'],"1")!== false) echo "checked"?>>
			  <label for="d1">Mon</label>
			  <input name="d2" type="checkbox" id="d2" value="2" <?php if (strpos($options['tp_post_days'],"2")!== false) echo "checked"?>>
			  <label for="d2">Tue</label>
			  <input name="d3" type="checkbox" id="d3" value="3" <?php if (strpos($options['tp_post_days'],"3")!== false) echo "checked"?>>
			  <label for="d3">Wed</label>
			  <input name="d4" type="checkbox" id="d4" value="4" <?php if (strpos($options['tp_post_days'],"4")!== false) echo "checked"?>>
			  <label for="d2">Thur</label>
			  <input name="d5" type="checkbox" id="d5" value="5" <?php if (strpos($options['tp_post_days'],"5")!== false) echo "checked"?>>
			  <label for="d5">Fri</label>
			  <input name="d6" type="checkbox" id="d6" value="6" <?php if (strpos($options['tp_post_days'],"6")!== false) echo "checked"?>>
			  <label for="d6">Sat</label>
			  <input name="d0" type="checkbox" id="d0" value="0" <?php if (strpos($options['tp_post_days'],"0")!== false) echo "checked"?>>
			  <label for="d0">Sun</label>
              </td>
          </tr>
          <tr>
            <th scope="row">Time:</th>
            <td align="left"><input name="tp_post_time" type="text" id="tp_post_time" value="<?php echo $options['tp_post_time']; ?>"> 
              [Format HH:MM (24h)] </td>
          </tr>
		  <tr>
            <th scope="row">Post Title:</th>
            <td align="left"><input name="tp_post_title" type="text" id="tp_post_title" value="<?php echo $options['tp_post_title']; ?>" size="70">
            <br> 
            Insert 
            <span style="font-weight: bold">%DATE%</span> if you wish to incorporate the post publish date as part of the title </td>
          </tr>
          <tr>
            <th scope="row">Post Content:</th>
            <td align="left"><textarea name="tp_post_content" cols="70" rows="6" id="tp_post_content"><?php echo $options['tp_post_content']; ?></textarea>
            <br>
            Insert <span style="font-weight: bold">%TWPAD% </span>to position the list of search results (you can also use <span style="font-weight: bold">%DATE%</span>)</td>
          </tr>
		  <tr>
		    <th scope="row">Post Status: </th>
		    <td align="left"><label>
		      <input type="radio" name="tp_post_status" value="draft" <?php if ($options["tp_post_status"] =="draft") echo "checked";?>>
Draft </label>
|
<label>
<input type="radio" name="tp_post_status" value="publish" <?php if ($options["tp_post_status"] =="publish") echo "checked";?>>
Publish</label>
|
<label>
<input type="radio" name="tp_post_status" value="private" <?php if ($options["tp_post_status"] =="private") echo "checked";?>>
Private</label>
| </td>
	      </tr>
		  <tr>
            <th scope="row">Post Category:</th>
            <td align="left"><?php 
                        $dropdown_options = array('show_option_all' => '', 'hide_empty' => 0, 'hierarchical' => 1,
                            'show_count' => 0, 'depth' => 0, 'orderby' => 'ID', 'selected' => $options["tp_post_category"], 'name' => 'tp_post_category');
                        wp_dropdown_categories($dropdown_options);
                    ?></td>
          </tr>		
		  <tr>
            <th scope="row">Post Tags:</th>
            <td align="left"><input name="tp_post_tags" type="text" id="tp_post_tags" value="<?php echo $options['tp_post_tags']; ?>" size="60"></td>
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

 </div>
 <div class="gdsr">
 <h3>Debug</h3>
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
            Reset (WARNING: this deletes all your existing feeds):
            <?php wp_nonce_field('twpad-3' ); ?>
            <input type="hidden" name="tp_action" value="reset" />
            <span class="submit">
            <input class="inputbutton" type="submit" value="Reset" name="saving" />
            </span>
          </form></td>
      </tr>
      <tr>
        <td><small>Diagnostics: Last Refresh - 
            <?php $pdate = $options["tp_last_refresh"]; echo date("d M y  H:i:s", $pdate); ?></small></td>
      </tr>
    </table>
  </div>
</div>
