<?php
/*
Plugin Name: WP XMLRPC Plus
Plugin URI: http://github.com/reschkevito/WP-XMLRPC-Plus
Description: A Wordpress Plugin which extends the XML RPC Server
Version: 0.1
Author: Marcel Reschke
Author URI: http://github.com/reschkevito/
License: GPL2

Copyright 2011  Marcel Reschke  (email : marcel.reschke@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_filter('xmlrpc_methods', 'add_xmlrpc_methods');

function add_xmlrpc_methods($methods)
{
	$methods['wp_xmlrpc_plus.getModifiedPosts'] = 'wp_xmlrpc_plus_get_modified_posts';
	return $methods;
}

function wp_xmlrpc_plus_get_modified_posts($args) {
	
	global $wp_xmlrpc_server;
	
	$wp_xmlrpc_server->escape($args);

	// $args[0] = appkey - ignored
	$blog_ID    = (int) $args[1]; /* though we don't use it yet */
	$username = $args[2];
	$password  = $args[3];
	$modifiedDate = $args[4];
	


	if ( !$user = $wp_xmlrpc_server->login($username, $password) ) return $wp_xmlrpc_server->error;	

		$query = array('numberposts' => -1,);
		$posts_list = wp_get_recent_posts($query);

		if ( !$posts_list ) {
			$wp_xmlrpc_server->error = new IXR_Error(500, __('Either there are no posts, or something went wrong.'));
			return $wp_xmlrpc_server->error;
		}
		
		foreach ($posts_list as $entry) {
			if ( !current_user_can( 'edit_post', $entry['ID'] ) ) continue;
		
			$posts_modified_date = new IXR_Date(mysql2date('Ymd\TH:i:s', $entry['post_modified'], false));
			if ( $posts_modified_date < $modifiedDate) continue;
			$struct[] = array(
				'dateModified' => $posts_modified_date,
				'postid' => (string) $entry['ID'],
			);
		}
	
		$modified_posts = array();
		for ( $j=0; $j<count($struct); $j++ ) {
			array_push($modified_posts, $struct[$j]);
		}

		return $modified_posts;
}

?>