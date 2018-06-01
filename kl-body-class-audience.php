<?php
/*
Plugin Name: KL Body Class Audience
Plugin URI: https://github.com/educate-sysadmin/kl-body-class-audience
Description: Adds role and group restrictions to body class
Version: 0.1
Author: b.cunningham@ucl.ac.uk
Author URI: https://educate.london
License: GPL2
*/
/* thanks https://css-tricks.com/snippets/wordpress/add-category-name-body_class/ */
add_filter('body_class','kl_add_audience');

/* helpers from KL Groups Restrict Categories Shortcode */
/* helper: return group ids restrictions for category */

function klbca_get_groups_restrict_categories($category) {
    global $wpdb;
    
    return $wpdb->get_results( 
        '
            SELECT meta_value 
            FROM '.$wpdb->prefix.'termmeta  
            LEFT JOIN '.$wpdb->prefix.'terms ON '.$wpdb->prefix.'termmeta.term_id = '.$wpdb->prefix.'terms.term_id 
            WHERE '.$wpdb->prefix.'termmeta.meta_key = "groups-read" 
            AND '.$wpdb->prefix.'terms.slug = "'.$category.'";'
    );
}

/* helper: get group name */
function klbca_get_group_name($group_id) {
    global $wpdb;
    if (class_exists('Groups_User')) {		
	    $sql = 'SELECT name FROM '.$wpdb->prefix .'groups_group WHERE group_id='.$group_id;
	    $row = $wpdb->get_row( $sql );
	    $group_name = $row->name;
	    return $group_name;
    } else {
	    return "";
    }		
}

function kl_add_audience($classes) {
    if (is_singular() ) {
        global $post;        
        // add roles
        if (function_exists('members_get_post_roles')) {
            $roles_allowed = members_get_post_roles( $post->ID );
            foreach ($roles_allowed as $role_allowed) {
                $classes[] = $role_allowed;
            }
        }

        // add category-restricted groups        
        foreach((get_the_category($post->ID)) as $category) {

            $groups_allowed = klbca_get_groups_restrict_categories($category->category_nicename);
            if ($groups_allowed) {
                foreach ($groups_allowed as $group_allowed) {
                    $classes[] = klbca_get_group_name((int)$group_allowed->meta_value).' ';
                }
            }
        }
    }
    // return the $classes array
    return $classes;
}
