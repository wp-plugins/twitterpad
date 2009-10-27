<?php

/*
Plugin Name: TwitterPad
Plugin URI: http://www.rsc-ne-scotland.org.uk/mashe/twitterpad-plugin/
Description: TwitterPad allows twitter users to automatically collect tweets using custom search strings which are added to a specified page.  
Author: Martin Hawksey
Author URI: http://www.rsc-ne-scotland.org.uk/mashe
Version: 0.1
*/


/*  Copyright 2009  Martin Hawksey  (email : martin.hawksey@gmail.com)
	The code used in TwitterPad is based on:
		Plugin Name: Shared Items Post
		Plugin URI: http://www.googletutor.com/shared-items-post/
		Description: Scheduled automatic posting of Google Reader Shared Items.
		Version: 1.3.0
		Author:  Craig Fifield, Google Tutor
		Author URI: http://www.googletutor.com/ 

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(! class_exists('SimplePie'))
  require_once(dirname(__FILE__).'/simplepie.php');

if (!class_exists('Twitterpad')) {
    class Twitterpad
    { 
        var $plugin_url;
        var $plugin_path;
        var $status = "";

        var $o;

        var $default_options = array(
            'tp_revision' => 1,
            'tp_last_refresh' => 0,
			'tp_item_style' =>'border-bottom: 1px dashed #cccccc; padding: 10px 5px;',
			'tp_feeds' => array()
        );
        
        function Twitterpad() {
            $this->plugin_path_url();
            $this->install_plugin();
            $this->actions_filters();
        }


        function plugin_path_url() {
            $this->plugin_url = WP_PLUGIN_URL.'/twitterpad/';
            $this->plugin_path = dirname(__FILE__).'/';
            define('RSSEVERPOST_URL', $this->plugin_url);
            define('RSSEVERPOST_PATH', $this->plugin_path);
        }

        function install_plugin() {
            $this->o = get_option('twitterpad-options');
            
            if (!is_array($this->o)) {
                update_option('twitterpad-options', $this->default_options);
                $this->o = get_option('twitterpad-options');
            }
            else {
                foreach ($this->default_options as $key => $value)
                    if (!isset($this->o[$key])) $this->o[$key] = $value;
                $this->o["tp_revision"] = $this->default_options["tp_revision"];
                update_option('twitterpad-options', $this->o);
            }
        }

        function actions_filters() {
            add_action('init', array(&$this, 'init'));
            add_action('admin_menu', array(&$this, 'admin_menu'));
        }
		      
        function init() {
            if ($_POST['tp_action'] == 'runow') {
	            	check_admin_referer('twpad-2');
	            $this->status .= "All feeds refreshed";
                $this->generate_post();
            }
            else {
                if ($_POST['tp_action'] == 'reset') {
                		check_admin_referer('twpad-3');
                	$this->status .= "Reset";
                    $this->o = $this->default_options;
                    update_option("twitterpad-options", $this->default_options);
                } elseif ($_POST['tp_action'] == 'delete') {
                		check_admin_referer('twpad-4');
					foreach( (array) $_POST['tpfeed'] as $post_id_del ) {
						unset($this->o["tp_feeds"][$post_id_del]);
					}
					$this->o["tp_feeds"] = array_values($this->o["tp_feeds"]);
					update_option("twitterpad-options", $this->o);
					$this->status = "Feed removed";
                } elseif ($_POST['tp_action'] == 'stylenow') {
                		check_admin_referer('twpad-5');
					$this->o["tp_item_style"]=$_POST['tp_item_style'];
					$this->status .= "Style updated.";
					update_option("twitterpad-options", $this->o);
                } elseif ($_POST['tp_action'] == 'add') {
                		check_admin_referer('twpad-1', 'twpad-main');
                	if (isset($_POST['tp_url']) && $_POST['tp_page']!=""){
						if ($this->valid_tp_feed($_POST['tp_url'])){
							$idx=sizeof($this->o["tp_feeds"]);
							$this->o["tp_feeds"][$idx]['url']=$_POST['tp_url'];
							$this->o["tp_feeds"][$idx]['page']=$_POST['tp_page'];
							$this->o["tp_feeds"][$idx]['refresh']=0;
							$this->o["tp_feeds"][$idx]["lastItemDate"]=0;
							$this->status = "Feed added.";
						} else {
							$this->status = "Feed is not valid.";
						}
					} else {
						 $this->status = "Please enter a valid url and page.";
					}
                    update_option("twitterpad-options", $this->o);
                }

                if ($this->check_refresh()) 
                    $this->generate_post();
            }
        }
		
		
		function valid_tp_feed($url) {
                if ($url == "") return;
			$rss_status = false;
            $rss = new SimplePie();
            $rss->set_feed_url($url);
            $rss->enable_cache(false);
            $rss->init();
			if ($rss->get_type() & SIMPLEPIE_TYPE_ALL){
				$rss_status = true;
			}
			return $rss_status;
		}
		
		
        function generate_post() {
        	 	if(empty($this->o['tp_feeds']))  return;
			foreach ($this->o['tp_feeds'] as $key => $t) { 
				$rss = new SimplePie();
				$rss->set_feed_url($t["url"]);
				$rss->enable_cache(false);
				$rss->enable_order_by_date(false);
				$rss->init();
				
				$page = get_post($t["page"]);
				$content = $page->post_content;
				
				$updated = $rss->get_channel_tags('http://www.w3.org/2005/Atom', 'updated');
				$feedDate = strtotime(str_replace(array("T", "Z"), " ", $updated[0]["data"]));
				if ($rss->get_item()){
					$newestItem = strtotime(str_replace(array("T", "Z"), " ", $rss->get_item()->get_date()));
				}
				$new = "";
				$rel='image';
				foreach ($rss->get_items() as $item) {
					$itemDate = strtotime(str_replace(array("T", "Z"), " ", $item->get_date())); 
					//$this->status .="<br>".($itemDate-$t["lastItemDate"]);
					if(($itemDate-$t["lastItemDate"]) > 0 ){
						// new content
						//$this->status .="<br>".date("d M y  H:i:s", $itemDate). " ".date("d M y  H:i:s", $t["lastItemDate"]);
						$img = $item->get_links($rel);
						$name = preg_replace('` \([^\]]*\)`', '', $item->get_author()->get_name());
						$new .= "<div style='".$this->o["tp_item_style"]."' class='twPadItm'><div style='float:left; padding-right:5px;' class='twPadItmImg'><img src='".$img[0]."' alt='".$name." - Profile Pic' height='48px' width='48px' \></div><div class='twPadItmTxt'>@<a href='".$item->get_link(0)."'>".$name."</a>: ".$item->get_content(). " - <em>".date("d M y  H:i", $itemDate)."</em><br style='clear:both;' /></div></div>"; 
					}
				}
				if ($new !=""){
					$up_post = array();
					$up_post['ID'] = $t["page"];
					$insertBegin = "<div class=\"twPad\">";
					if (strpos($content, $insertBegin) !== false){
						$up_post['post_content'] = str_replace($insertBegin, $insertBegin.$new, $content);
					} else {
						$up_post['post_content'] = $content.$insertBegin.$new."</div>";
					}
						
					wp_update_post( $up_post );
				}
					
				$this->o["tp_feeds"][$key]["lastItemDate"] = $newestItem;
				$this->o["tp_feeds"][$key]["refresh"] = $feedDate; 
				update_option("twitterpad-options", $this->o); 
				
			}
			$this->o["tp_last_refresh"] = mktime();
			update_option("twitterpad-options", $this->o);
        }
        
        function check_refresh() {
		if(!(empty($this->o["tp_feeds"]))) {
                if ($this->o["tp_last_refresh"] == 0) return true;
                $pdate = $this->o["tp_last_refresh"];
                $timeparts = $this->convert_time($this->o["tp_refresh_time"]);
				$next = mktime(0 + $timeparts[0], 0 + $timeparts[1], 0, date("m", $pdate), date("d", $pdate) + 1, date("Y", $pdate));
                
                if (mktime() >= $next) return true;
                else return false;
            }
            else return false;
        }
        
        function convert_time($timer) {
            $tp = split(" ", $timer);
            if (count($tp) == 2) {
                if ($tp[1] == "PM") {
                    $tt = split(":", $tp[0]);
                    $tt[0] = $tt[0] + 12;
                    return $tt;
                }
                return split(":", $tp[0]);
            }
            else return split(":", $timer);
        }

        function admin_menu() {
            add_submenu_page('options-general.php','TwitterPad', 'TwitterPad', 9, __FILE__, array($this, 'options_panel'));
        }
       
        function options_panel() {
            $options = $this->o;
            $status = $this->status;
            include($this->plugin_path.'twpad-panel.php');
        }
    }
    
    $rssShare = new Twitterpad();
}

?>