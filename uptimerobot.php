<?php
/**
 * @package Uptime Robot
 */
/*
Plugin Name: Uptime Robot
Plugin URI: http://mightyworker.com/
Description: Add a dashboard widget to monitor Uptime Robot 
Version: 0.1.3
Author: Scott Nellé
Author URI: http://mightyworker.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if (!class_exists("Uptime_Robot")) {

	class Uptime_Robot {

		/* create the dashboard widget */
		function render_dashboard_widget() {
			?>
			<p id="uptimerobot_loading">Loading Data</p>
			<script>
			// request data on load to keep the dashboard speedy
			jQuery(window).load(function(){
				var data = {
					action: 'uptimerobot_get_data'
				};
				jQuery.get(ajaxurl, data, function(response) {
					jQuery('#uptimerobot_dashboard_widget .inside').hide().append(response).slideDown('fast').find('#uptimerobot_loading').remove();
				});
			});
			</script>
			<?php
		}

		function uptimerobot_data() {
			// check for cached copy
			$cache = get_option( 'uptimerobot_cache' );

			if ($cache != '' && time() < $cache['timestamp'] + 600) { // cache is < 10 minutes old. use it.
				$json = json_decode($cache['data']);
			}
			else { // cache is stale
				// set up request 
				$api_key = get_option( 'uptimerobot_api_key' ); // My Settings > API Information > Monitor-specific API keys > Select a Monitor > Click to Create One
				$url = "http://api.uptimerobot.com/getMonitors?apiKey=" . $api_key . "&logs=1&showTimezone=1&format=json&noJsonCallback=1";

				// request via cURL
				$c = curl_init($url);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
				$responseJSON = curl_exec($c);
				curl_close($c);

				$json = json_decode($responseJSON);

				// don't cache if there's a failture
				if ($json !== NULL && $json->stat != 'fail') {
					// save to cached  option
					update_option('uptimerobot_cache', array ( 'data' => $responseJSON, 'timestamp' => time()));
				}
			}

			// parse request
			if ($json !== NULL && $json->stat != 'fail') {
				$num_log_items = (intval(get_option( 'uptimerobot_log_items' )) > 0) ? intval(get_option( 'uptimerobot_log_items' )) - 1 : 5;

				foreach ($json->monitors->monitor as $monitor) {
					echo '<h4>Monitor: <strong>'.$monitor->friendlyname.'</strong> ('.$monitor->alltimeuptimeratio.'% uptime)</h4>';
					echo '<ul style="margin-left:2em;">';
					// log events
					$i = 0;

					foreach ($monitor->log as $event) {
						switch ($event->type) {
							case 1:
								$prefix = '<span style="color:#800">Down at ';
								break;
							case 2:
								$prefix = '<span>Up at ';
								break;
							case 99:
								$prefix = '<span style="color:#bbb">Paused at ';
								break;
							case 98:
								$prefix = '<span>Started at ';
								break;
						}

						echo '<li class="log-event" style="list-style:disc;">'.$prefix.$event->datetime.'</span></li>';

						$i++;
						if ($i > $num_log_items) { break; }
					}
					echo '</ul>';
				}
			}
			else {
				echo 'No data available for your account. Please <a href="options-general.php?page=uptimerobot">check your API key</a> or wait for some monitoring data to become available.';
			}

			die(); // this is required to return a proper result
		}

		function add_dashboard_widget() {
			wp_add_dashboard_widget('uptimerobot_dashboard_widget', 'Uptime Robot', array( &$this, 'render_dashboard_widget' ) );
		}

		/* create the menu item */
		function create_menu() {
			add_options_page( 'Uptime Robot', 'Uptime Robot', 'manage_options', 'uptimerobot', array($this,'options_page'));
		}

		/* create the settings page */
		function options_page() {
			if (isset($_POST['submit_uptimerobot'])) {
				echo '<div id="message" class="updated"><p>Settings saved</p></div>';
			}

			echo '
				<div class="wrap uptime_robot">
					<h2>Uptime Robot Settings</h2>
		
					<form method="post" action="options-general.php?page=uptimerobot&savedata=true">

					<p><label for="uptimerobot_api_key">API Key</label> <input value="'.get_option( 'uptimerobot_api_key' ).'" type="text" name="uptimerobot_api_key" id="uptimerobot_api_key" /></p>
					<p><label for="uptimerobot_log_items">Log Items</label> <input value="'.get_option( 'uptimerobot_log_items' ).'" type="text" name="uptimerobot_log_items" id="uptimerobot_log_items" /> <small>(default 6)</small></p>
					<p><input type="submit" name="submit_uptimerobot" value="Save" /></p>
					</form>
					<p>Note: To keep the dashboard speedy and limit requests, this plugin will cache responses from uptime robot for 10 minutes.</p>
				</div>
				';
		}

		/* save options */
		function save_options($data) {
			if (isset($data['uptimerobot_api_key'])) { update_option('uptimerobot_api_key', $data['uptimerobot_api_key']); }
			else { delete_option('uptimerobot_api_key'); }

			if (isset($data['uptimerobot_log_items'])) { update_option('uptimerobot_log_items', $data['uptimerobot_log_items']); }
			else { delete_option('uptimerobot_log_items'); }

			delete_option( 'uptimerobot_cache' );
		}

	} // end class definition
} // end class check

$uptimerobot_plugin = new Uptime_Robot;
add_action('wp_dashboard_setup', array( $uptimerobot_plugin, 'add_dashboard_widget' ));
add_action('admin_menu', array($uptimerobot_plugin,'create_menu'));
add_action('wp_ajax_uptimerobot_get_data', array( $uptimerobot_plugin, 'uptimerobot_data' ));

// if submitted, process the data
if (isset($_POST['submit_uptimerobot'])) {
	$uptimerobot_plugin->save_options($_POST);
}
