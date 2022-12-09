<?php
add_action('wp_enqueue_scripts', 'enqueue_parent_styles');

function enqueue_parent_styles() {
    wp_enqueue_style(  'parent-style', get_template_directory_uri() . '/style.css');
}

add_action('rest_api_init', 'register_my_route');

function register_my_route() {
    
    register_rest_route(
    'twentytwentyone-child/v1',
    '/latest-posts/(?P<category_id>\d+)',
    array(
        'methods' => 'GET',
        'callback' => 'get_latest_posts_by_category'
    )
    );
}

function get_latest_posts_by_category($request) {
    $args = array(
        'category' => $request['category_id']
    );
    $posts = get_posts( $args );

    if( empty($posts) ) {
        return new WP_Error('empty_category', 'There are no posts to display', array('status' => 404) );
    }
    $response = new WP_REST_Response($posts);
    $response -> set_status(200); //okay response code

    return $response;
}
// new custom post types
function custom_post_types() {
   register_post_type(
    'contact',
    array(
        'labels' => array (
            'name' => __('Contacts', 'textdomain'),
            'singular_name' => __('Contacts', 'textdomain')
        ),
        'public' => true,
        'has_archive' => true
    )
   ); 
}
add_action('init', 'custom_post_types');

// new custom post types
function custom_post_types2() {
   register_post_type(
    'User_Emails',
    array(
        'labels' => array (
            'name' => __('User_Emails', 'textdomain'),
            'singular_name' => __('User_Emails', 'textdomain')
        ),
        'public' => true,
        'has_archive' => true
    )
   ); 
}
add_action('init', 'custom_post_types2');


// new custom post types
function custom_post_types3() {
   register_post_type(
    'User_Numbers',
    array(
        'labels' => array (
            'name' => __('User_Numbers', 'textdomain'),
            'singular_name' => __('User_Numbers', 'textdomain')
        ),
        'public' => true,
        'has_archive' => true
    )
   ); 
}
add_action('init', 'custom_post_types3');


//tell wp api to reg a new REST url endpoint

add_action('rest_api_init', 'register_my_route2');
function register_my_route2() {
    
    register_rest_route(
    'twentytwentyone-child/v1',
    '/special',
    array(
        'methods' => 'GET',
        'callback' => 'get_posts_via_sql'
    )
    );
}
function get_posts_via_sql() {
    global $wpdb;

    $pre = $wpdb -> prefix;
    //difine sql query string that will use join to merge results
    
    $query = "SELECT " .$pre . "posts.ID, ";
    $query .= $pre . "posts.post_title, ";
    $query .= $pre . "posts.post_content, ";
    $query .= $pre ."users.user_login ";
    $query .= "FROM " . $pre . "posts ";
    $query .= "INNER JOIN " . $pre . "users ";
    $query .= "ON " .$pre . "posts.post_author = " . $pre . "users.ID ";
    $query .= "WHERE " .$pre . "posts.post_status = 'publish';";
	$query .= "AND " .$pre . "posts.post_type = 'post';";
   
   /* $query  = "SELECT wp_posts.ID, ";
    $query .= "GROUP_CONCAT( wp_postmeta.meta_key, ':', REPLACE(REPLACE(wp_postmeta.meta_value,',',''),':','') )AS acf_fields ";
    $query .= "FROM wp_posts ";
    $query .= "INNER JOIN wp_postmeta ";
    $query .= "ON wp_posts.ID = wp_postmeta.post_id ";
    $query .= "WHERE wp_posts.post_status = 'publish' ";
    $query .= "AND wp_posts.post_type = 'post' ";
    $query .= "AND wp_postmeta.meta_key NOT LIKE '\_%' ";
    $query .= "GROUP BY wp_posts.ID";
	*/

    



    $results = $wpdb -> get_results($query);
    //send back data for what was found
    $response = new WP_REST_Response($results);
    $response -> set_status(200);

    return $response;
}

?>