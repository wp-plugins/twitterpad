<?php
  /*
   Plugin Name: TwitterPad
   Plugin URI: http://www.rsc-ne-scotland.org.uk/mashe/twitterpad-plugin/
   Description: TwitterPad allows twitter users to automatically collect tweets using custom search strings which are added to a specified page or as a new post.
   Author: Martin Hawksey
   Author URI: http://www.rsc-ne-scotland.org.uk/mashe
   Version: 1.3.3
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
  if (!class_exists('SimplePie'))
      require_once(dirname(__FILE__) . '/simplepie.php');
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
			'tp_item_style' => 'border-bottom: 1px dashed #cccccc; padding: 10px 5px;', 
			'tp_refresh_period' => '24', 
			'tp_feeds' => array(), 
			'tp_post_days' => '12345', 
			'tp_post_time' => '16:00', 
			'tp_post_title' => 'Summary of tweets for %DATE%', 
			'tp_post_content' => '<p>Below is a summary of tweets from %DATE%</p>%TWPAD%', 
			'tp_post_status' => 'publish', 
			'tp_post_category' => '', 
			'tp_post_tags' => '', 
			'tp_item_image' => 1,
			'tp_item_css' => 1,
		  );
          function Twitterpad()
          {
              $this->plugin_path_url();
              $this->install_plugin();
              $this->actions_filters();
          }
          function plugin_path_url()
          {
              $this->plugin_url = WP_PLUGIN_URL . '/twitterpad/';
              $this->plugin_path = dirname(__FILE__) . '/';
              define('RSSEVERPOST_URL', $this->plugin_url);
              define('RSSEVERPOST_PATH', $this->plugin_path);
          }
          function install_plugin()
          {
              $this->o = get_option('twitterpad-options');
              if (!is_array($this->o)) {
                  update_option('twitterpad-options', $this->default_options);
                  $this->o = get_option('twitterpad-options');
              } else {
                  foreach ($this->default_options as $key => $value)
                      if (!isset($this->o[$key]))
                          $this->o[$key] = $value;
                  $this->o["tp_revision"] = $this->default_options["tp_revision"];
                  update_option('twitterpad-options', $this->o);
              }
          }
          function actions_filters()
          {
              add_action('init', array(&$this, 'init'));
              add_action('admin_menu', array(&$this, 'admin_menu'));
			  add_action('wp_print_scripts', array(&$this, 'twpadhead'));

          }
		  function twpadhead() {
			// Only load this if not on an admin page
			if (!is_admin() && $this->o["tp_item_css"] = 1 ) {
				echo '<link rel="stylesheet" type="text/css" href="'.WP_PLUGIN_URL . '/twitterpad/style.php" />';
				}
		  }
          function init()
          {
              if ($_POST['tp_action'] == 'runow') {
                  check_admin_referer('twpad-2');
                  $this->status .= "All feeds refreshed";
                  $this->generate_post();
              } else {
                  if ($_POST['tp_action'] == 'reset') {
                      check_admin_referer('twpad-3');
                      $this->status .= "Reset";
                      $this->o = $this->default_options;
                      update_option("twitterpad-options", $this->default_options);
                  } elseif ($_POST['tp_action'] == 'delete') {
                      check_admin_referer('twpad-4');
                      foreach ((array)$_POST['tpfeed'] as $post_id_del) {
                          unset($this->o["tp_feeds"][$post_id_del]);
                      }
                      $this->o["tp_feeds"] = array_values($this->o["tp_feeds"]);
                      update_option("twitterpad-options", $this->o);
                      $this->status = "Feed removed";
                  } elseif ($_POST['tp_action'] == 'stylenow') {
                      check_admin_referer('twpad-5');
                      $this->o["tp_item_style"] = $_POST['tp_item_style'];
                      $this->o["tp_item_image"] = isset($_POST['tp_item_image']) ? 1 : 0;
                      $this->o["tp_refresh_period"] = $_POST['tp_refresh_period'];
                      $this->status .= "Options updated.";
                      update_option("twitterpad-options", $this->o);
                  } elseif ($_POST['tp_action'] == 'post_template') {
                      check_admin_referer('twpad-6');
                      $this->o["tp_post_days"] = $_POST['d1'] . $_POST['d2'] . $_POST['d3'] . $_POST['d4'] . $_POST['d5'] . $_POST['d6'] . $_POST['d0'];
                      $this->o["tp_post_time"] = $_POST['tp_post_time'];
                      $this->o["tp_post_title"] = $_POST['tp_post_title'];
                      $this->o["tp_post_content"] = $_POST['tp_post_content'];
                      $this->o["tp_post_status"] = $_POST['tp_post_status'];
                      $this->o["tp_post_category"] = $_POST['tp_post_category'];
                      $this->o["tp_post_tags"] = $_POST['tp_post_tags'];
                      if ($this->o["tp_post_days"] == "") {
                          $this->status .= "OOPS. You need to select at least one day for the post template";
                      }
                      $this->status .= "Post template updated.<br>";
                      update_option("twitterpad-options", $this->o);
                  } elseif ($_POST['tp_action'] == 'add') {
                      check_admin_referer('twpad-1', 'twpad-main');
                      if (isset($_POST['tp_url']) && $_POST['tp_page'] != "") {
                          if ($this->valid_tp_feed($_POST['tp_url'])) {
                              $idx = sizeof($this->o["tp_feeds"]);
                              $this->o["tp_feeds"][$idx]['url'] = $_POST['tp_url'];
                              $this->o["tp_feeds"][$idx]['page'] = $_POST['tp_page'];
                              $this->o["tp_feeds"][$idx]['refresh'] = 0;
                              $this->o["tp_feeds"][$idx]["lastItemDate"] = 0;
                              $this->status = "Feed added.";
                          } else {
                              $this->status = "Feed is not valid.";
                          }
                      } else {
                          $this->status = "Please enter a valid url and page.";
                      }
                      update_option("twitterpad-options", $this->o);
                  }
                  $this->generate_post();
              }
          }
          function valid_tp_feed($url)
          {
              if ($url == "")
                  return;
              $rss_status = false;
              $rss = new SimplePie();
              $rss->set_feed_url($url);
              $rss->enable_cache(false);
              $rss->init();
              if ($rss->get_type() & SIMPLEPIE_TYPE_ALL) {
                  $rss_status = true;
              }
              return $rss_status;
          }
          function generate_post()
          {
              if (empty($this->o['tp_feeds']))
                  return;
              foreach ($this->o['tp_feeds'] as $key => $t) {
                  if ($this->o["tp_feeds"][$key]["refresh"] < mktime()) {
                      $rss = new SimplePie();
                      $rss->set_feed_url($t["url"]);
                      $rss->enable_cache(false);
                      $rss->enable_order_by_date(false);
                      $rss->init();
                      if ($t["page"] != "[New Post]") {
                          $page = get_post($t["page"]);
                          $content = $page->post_content;
						  $nextFeedDate = mktime(date("H") + $this->o["tp_refresh_period"], date("i"), date("s"), date("m"), date("d"), date("Y"));
                      } else {
					     $dayofWeek = date("w");
                         $i = 1;
						 while (($i <= 6) && ($foundit == false)) {
							if (($i + $dayofWeek) <= 6) {
								$weekidx = $i + $dayofWeek;
							} else {
								$weekidx = $i + $dayofWeek - 7;
							}
							if (strpos($this->o['tp_post_days'], strval($weekidx)) !== false) {
								$nextFeedDate = strtotime("next " . date("l", mktime(12, 0, 0, 1, $weekidx + 4, 1970)) . " " . $this->o['tp_post_time']);
								$foundit = true;
							}
							$i++;
						}
					  }
                      if ($rss->get_item()) {
                          $newestItem = strtotime($rss->get_item()->get_date());
                      } else {
                          $newestItem = $t["lastItemDate"];
                      }
					  $this->o["tp_feeds"][$key]["refresh"] = $nextFeedDate;
					  $this->o["tp_feeds"][$key]["lastItemDate"] = $newestItem;
					  update_option("twitterpad-options", $this->o); 
                      $new = "";
                      $rel = 'image';
                      foreach ($rss->get_items() as $item) {
                          $imgstr = "";
                          $itemDate = strtotime($item->get_date());
                          $img = $item->get_links($rel);
                          if (($itemDate - $t["lastItemDate"]) > 0) {
                              $author = $item->get_item_tags('', 'author');
                              if (isset($author[0]['data'])) {
                                  $author = $author[0]['data'];
                              } else {
                                  if ($author = $item->get_author()) {
                                      $author = $author->get_name();
                                  }
                              }
                              $name = preg_replace('` \([^\]]*\)`', '', $author);
                              if (isset($img[0]) && $this->o["tp_item_image"] == 1) {
                                  $imgstr = "<div class='twPadItmImg'><img src='" . $img[0] . "' alt='" . $name . " - Profile Pic' height='48px' width='48px' \></div>";
								  $heightOverride = " style='height:60px;' ";
                              }
                              $new .= "<div ".$heightOverride." class='twPadItm'>" . $imgstr . "<div class='twPadItmTxt'>@<a href='" . $item->get_link(0) . "'>" . $name . "</a>: " . $item->get_content() . " - <em>" . date("d M y  H:i", $itemDate) . "</em><br style='clear:both;' /></div></div>";
                          }
                      }
                      if ($new != "") {
                          $insertBegin = "<div class=\"twPad\">";
                          // Update page
                          if ($t["page"] != "[New Post]") {
                              $up_post = array();
                              $up_post['ID'] = $t["page"];
                              if (strpos($content, $insertBegin) !== false) {
                                  $up_post['post_content'] = str_replace($insertBegin, $insertBegin . $new, $content);
                              } else {
                                  $up_post['post_content'] = $content . $insertBegin . $new . "</div>";
                              }
                              wp_update_post($up_post);
                              //$nextFeedDate = mktime(date("H") + $this->o["tp_refresh_period"], date("i"), date("s"), date("m"), date("d"), date("Y"));
                          } else {
                              // New post
                              $my_post = array();
                              $dateString = date("F jS, Y");
                              $my_post['post_title'] = str_replace('%DATE%', $dateString, $this->o["tp_post_title"]);
                              $import = $insertBegin . $new . "</div>";
                              $import = str_replace('%TWPAD%', $import, $this->o["tp_post_content"]);
                              $import = str_replace('%DATE%', $dateString, $import);
                              $my_post['post_content'] = $import;
                              $my_post['post_status'] = $this->o["tp_post_status"];
                              $my_post['post_author'] = 1;
                              $my_post['post_category'] = array($this->o["tp_post_category"]);
                              $my_post['tags_input'] = $this->o["tp_post_tags"];
                              // Insert the post into the database
                              wp_insert_post($my_post);
                          }
                      }
                  }
              }
              $this->o["tp_last_refresh"] = mktime();
              update_option("twitterpad-options", $this->o);
          }
          function admin_menu()
          {
              add_submenu_page('options-general.php', 'TwitterPad', 'TwitterPad', 9, __FILE__, array($this, 'options_panel'));
          }
          function options_panel()
          {
              $options = $this->o;
              $status = $this->status;
              include($this->plugin_path . 'twpad-panel.php');
          }
      }
      $rssShare = new Twitterpad();
  }
?>