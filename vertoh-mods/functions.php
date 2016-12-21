<?php
// ******************* Add Libraries ****************** //
require_once('event-framework/lib/facebook/facebook.php');
require_once('event-framework/lib/twitter.php');
require_once('event-framework/lib/instagram.php');
require_once('event-framework/lib/geocode.php');
require_once('event-framework/lib/recaptchalib.php');
require_once('event-framework/lib/api/functions.php');

include 'event-framework/event-framework.php';

add_filter('ef_theme_options_logo', 'vertoh_set_theme_options_logo');

function vertoh_set_theme_options_logo() {
    return get_template_directory_uri() . '/images/logo.png';
}

function vertoh_set_theme_logo() {
    $ef_options = EF_Event_Options::get_theme_options();
    if (!empty($ef_options['ef_logo']) && $ef_options['ef_logo'] != 'http://') {
        $logo_url = $ef_options['ef_logo'];
    } else {
        $logo_url = '';
    }
    return $logo_url;
}

function vertoh_setup_social_networks() {
    global $twitter, $facebook, $instagram;

    $facebookAppID  = get_option('ef_facebook_rsvp_widget_appid');
    $facebookSecret = get_option('ef_facebook_rsvp_widget_secret');

    if (!empty($facebookAppID) && !empty($facebookSecret))
        $facebook = new Facebook(array(
            'appId'  => $facebookAppID,
            'secret' => $facebookSecret,
        ));

    $twitterAccessToken       = get_option('ef_twitter_widget_accesstoken');
    $twitterAccessTokenSecret = get_option('ef_twitter_widget_accesstokensecret');
    $twitterConsumerKey       = get_option('ef_twitter_widget_consumerkey');
    $twitterConsumerSecret    = get_option('ef_twitter_widget_consumersecret');

    if (!empty($twitterAccessToken) && !empty($twitterAccessTokenSecret) && !empty($twitterConsumerKey) && !empty($twitterConsumerSecret)) {
        $twitter = new TwitterAPIExchange(array(
            'oauth_access_token'        => $twitterAccessToken,
            'oauth_access_token_secret' => $twitterAccessTokenSecret,
            'consumer_key'              => $twitterConsumerKey,
            'consumer_secret'           => $twitterConsumerSecret
        ));
    }

    $instagramNewApiMode   = get_option('ef_instagram_widget_newapimode');
    $instagramClientID     = get_option('ef_instagram_widget_clientid');
    $instagramClientSecret = get_option('ef_instagram_widget_clientsecret');
    $instagramRedirectURI  = admin_url('widgets.php');

    if (!empty($instagramClientID) && !empty($instagramClientSecret)) {
        $instagramAccessToken = get_option('vertoh_instagram_token');
        if (!empty($instagramNewApiMode)) {
            $instagram = new InstagramAPI(array(
                'apiKey'      => $instagramClientID,
                'apiSecret'   => $instagramClientSecret,
                'apiCallback' => $instagramRedirectURI
            ));
            if (!empty($instagramAccessToken)) {
                $instagram->setAccessToken($instagramAccessToken);
            }
        } else {
            $instagram = new InstagramAPI($instagramClientID);
        }
    }
}

add_action('init', 'vertoh_setup_social_networks');

function vertoh_get_instagram_token() {
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        global $instagram;
        $page  = filter_input(INPUT_GET, 'page');
        $code  = filter_input(INPUT_GET, 'code');
        $token = '';
        if ($page == 'widgets.php' && !empty($code)) {
            if (!current_user_can('manage_options')) {
                wp_die();
            }
            $token                       = $instagram->getOAuthToken($code, false);
            $_SESSION['instagram_token'] = $token;
            if (!empty($token->access_token)) {
                $instagram->setAccessToken($token->access_token);
                update_option('vertoh_instagram_token', $token->access_token, true);
            } else {
                
            }
        }
    }
}

add_action('admin_init', 'vertoh_get_instagram_token');

add_action('after_setup_theme', 'vertoh_after_theme_setup');

function vertoh_after_theme_setup() {

// ******************* Localizations ****************** //
    load_theme_textdomain('vertoh', get_template_directory() . '/languages/');

// ******************* Add Custom Menus ****************** //    
    add_theme_support('menus');

// ******************* Add Post Thumbnails ****************** //
    add_theme_support('post-thumbnails');
    add_image_size('vertoh-speaker', 262, 272, true);
    add_image_size('vertoh-exhibitor', 360, 303);
    add_image_size('vertoh-media', 850, 460);
    add_image_size('vertoh-media-thumbnail', 117, 75, true);
    add_image_size('vertoh-blog-home', 245, 254, true);
    add_image_size('vertoh-blog-sidebar-first', 750, 305, true);
    add_image_size('vertoh-blog-sidebar-other', 360, 240, true);
    add_image_size('vertoh-blog-full-first', 555, 308, true);
    add_image_size('vertoh-blog-full-other', 263, 199, true);

// ******************* Add Navigation Menu ****************** //    
    register_nav_menu('primary', __('Navigation Menu', 'vertoh'));
}

// ******************* Scripts and Styles ****************** //
add_action('wp_enqueue_scripts', 'vertoh_enqueue_scripts');

function vertoh_enqueue_scripts() {
// Get Theme Options
    $ef_options      = EF_Event_Options::get_theme_options();
    $google_maps_key = '';
    if (!empty($ef_options['efcb_googlemaps_key'])) {
        $google_maps_key = $ef_options['efcb_googlemaps_key'];
    }
    wp_enqueue_style('vertoh-font-lato', 'https://fonts.googleapis.com/css?family=Lato:300,400,900,400italic');
    wp_enqueue_style('vertoh-owltransitions', get_template_directory_uri() . '/css/owl.transitions.css');
    wp_enqueue_style('vertoh-owlcarousel', get_template_directory_uri() . '/css/owl.carousel.css');
    wp_enqueue_style('vertoh-owltheme', get_template_directory_uri() . '/css/owl.theme.css');
    wp_enqueue_style('vertoh-fontawesome', get_template_directory_uri() . '/css/font-awesome.min.css');
    wp_enqueue_style('vertoh-animations', get_template_directory_uri() . '/css/animations.css');
    wp_enqueue_style('vertoh-royalslider', get_template_directory_uri() . '/css/royalslider.css');
    wp_enqueue_style('vertoh-rsdefault', get_template_directory_uri() . '/css/rs-default.css');
    wp_enqueue_style('vertoh-normalize', get_template_directory_uri() . '/css/normalize.min.css');
    wp_enqueue_style('vertoh-bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css');
    if (is_child_theme()) {
        wp_enqueue_style('vertoh-parent-style', trailingslashit(get_template_directory_uri()) . 'style.css');
    }
    wp_enqueue_style('vertoh-layout', get_template_directory_uri() . '/css/layout.css');
    wp_enqueue_style('vertoh-layout-mobile', get_template_directory_uri() . '/css/layout-mobile.css');
    wp_enqueue_style('vertoh-fix', get_template_directory_uri() . '/css/fix.css');
    // inline styles for page load optimization
    wp_add_inline_style('vertoh-layout', '
        .time_circles {position: relative;width: 100%;height: 100%;}
        .time_circles > div {position: absolute;text-align: center;}
        .time_circles > div > h4 {margin: 0px;padding: 0px;text-align: center;text-transform: uppercase;font-family: \'Century Gothic\', Arial;}
        .time_circles > div > span {display: block;width: 100%;text-align: center;font-family: \'Century Gothic\', Arial;font-size: 300%;margin-top: 0.4em;font-weight: bold;}
    ');
    // Color Schemes
    $color_scheme = empty($ef_options['ef_color_palette']) ? 'basic' : $ef_options['ef_color_palette'];
    if (isset($color_scheme) && $color_scheme != 'basic') {
        wp_enqueue_style($color_scheme . '-scheme', get_template_directory_uri() . '/css/schemes/' . $color_scheme . '/layout.css');
    }
    wp_enqueue_style('vertoh-style', get_stylesheet_uri());
    // Scripts
    //wp_deregister_script('jquery');
    //wp_enqueue_script('jquery', get_template_directory_uri() . '/js/vendor/jquery-1.11.0.min.js', false, false, true);
    wp_enqueue_script('vertoh-jqueryui', get_template_directory_uri() . '/js/vendor/jquery-ui.min.js', array('jquery'), false, true);
    wp_enqueue_script('vertoh-modernizer', get_template_directory_uri() . '/js/vendor/modernizr-2.6.2.min.js', false, false, true);
    wp_enqueue_script('vertoh-owlcarousel', get_template_directory_uri() . '/js/vendor/owl.carousel.min.js', array('jquery'), false, true);
    wp_enqueue_script('vertoh-bootstrap', get_template_directory_uri() . '/js/vendor/bootstrap.min.js', array('jquery'), false, true);
    wp_enqueue_script('vertoh-stickem', get_template_directory_uri() . '/js/vendor/jquery.stickem.js', array('jquery'), false, true);
    wp_enqueue_script('vertoh-timecircles', get_template_directory_uri() . '/js/vendor/TimeCircles.js', array('jquery'), false, true);
    wp_enqueue_script('vertoh-iosorientationchangefix', get_template_directory_uri() . '/js/vendor/ios-orientationchange-fix.js', false, false, true);
    wp_enqueue_script('vertoh-googlemaps', 'https://maps.googleapis.com/maps/api/js?key=' . $google_maps_key, false, false, true);
    wp_enqueue_script('vertoh-gmaps', get_template_directory_uri() . '/js/vendor/gmaps.js', array('vertoh-googlemaps'), false, true);
    wp_enqueue_script('vertoh-maps', get_template_directory_uri() . '/js/map.js', array('jquery', 'vertoh-gmaps'), false, true);
    wp_enqueue_script('vertoh-textfit', get_template_directory_uri() . '/js/vendor/textFit.min.js', false, false, true);
    wp_enqueue_script('vertoh-royalslider', get_template_directory_uri() . '/js/vendor/jquery.royalslider.min.js', array('jquery'), false, true);
    wp_register_script('vertoh-main', get_template_directory_uri() . '/js/main.js', array('jquery'), false, true);
    wp_localize_script('vertoh-main', 'vertoh_timer_labels', array(
        'seconds' => __('Seconds', 'vertoh'),
        'minutes' => __('Minutes', 'vertoh'),
        'hours'   => __('Hours', 'vertoh'),
        'days'    => __('Days', 'vertoh'),
    ));
    wp_localize_script('vertoh-main', 'vertoh_timer_colors', array(
        'color' => veetoh_scheme_main_color()
    ));
    wp_enqueue_script('vertoh-main', get_template_directory_uri() . '/js/main.js');
    wp_enqueue_script('vertoh-stick', get_template_directory_uri() . '/js/stick.js', array('jquery'), false, true);
    wp_enqueue_script('vertoh-touchswipe', get_template_directory_uri() . '/js/vendor/jquery.touchSwipe.min.js', array('jquery'), false, true);

    if (get_page_template_slug() == 'twitter.php') {
        wp_enqueue_script('vertoh-tweet-machine', get_template_directory_uri() . '/js/vendor/tweetMachine.min.js', array('jquery'), false, true);
        wp_enqueue_script('vertoh-twitter', get_template_directory_uri() . '/js/twitter.js', array('vertoh-tweet-machine'), true);
    }

    if (get_page_template_slug() == 'instagram.php') {
        wp_enqueue_script('vertoh-jqueryinstagram', get_template_directory_uri() . '/js/vendor/instagram.min.js', array('jquery'), false, true);
        wp_enqueue_script('vertoh-instagram', get_template_directory_uri() . '/js/instagram.js', array('jquery', 'vertoh-jqueryinstagram'), true);
    }

    if (is_admin() && get_page_template_slug() == 'speakers.php') {
        wp_enqueue_script('ef-upload-media', EF_ASSETS_URL . 'js/upload-media.js', array('jquery'), false, true);
    }

    if (is_singular())
        wp_enqueue_script('comment-reply');

// session
    if (is_singular(array('session'))) {
        if (!empty($ef_options['ef_add_this_pubid'])) {
            wp_enqueue_script('addthis', "//s7.addthis.com/js/300/addthis_widget.js#pubid={$ef_options['ef_add_this_pubid']}");
        }
    }

// full schedule
    if (get_page_template_slug() == 'schedule.php') {
        wp_enqueue_script('vertoh-schedule', get_template_directory_uri() . '/js/schedule.js', array('jquery'), false, true);
    }

// exhibitors
    if (get_page_template_slug() == 'exhibitors.php') {
        wp_enqueue_script('vertoh-exhibitors', get_template_directory_uri() . '/js/exhibitors.js', array('jquery'), false, true);
    }
}

add_action('admin_enqueue_scripts', 'vertoh_admin_enqueue_scripts');

add_action('wp_head', 'vertoh_twitter_template_hash');

function vertoh_twitter_template_hash() {
    ?>
    <script type="text/javascript">
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    <?php if (get_page_template_slug() == 'twitter.php') { ?>
            var vertoh_twitter_hash = '<?php echo get_option('ef_twitter_widget_twitterhash'); ?>';
    <?php } ?>
    </script>
    <?php
}

function vertoh_admin_enqueue_scripts($hook) {
    global $post_type;

    if (in_array($hook, array('post.php', 'post-new.php'))) {
        if ($post_type == 'session') {
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style('jquery-ui-datepicker', get_template_directory_uri() . '/css/admin/jquery-ui-smoothness/jquery-ui-1.10.3.custom.min.css');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_style('vertoh-sortable', get_template_directory_uri() . '/css/admin/sortable.css');
        } else if (get_page_template_slug() == 'speakers.php') {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('vertoh-page-speakers-full-screen', get_template_directory_uri() . '/js/admin/page-speakers-full-screen.js', array('jquery-ui-sortable'));
            wp_enqueue_style('vertoh-sortable', get_template_directory_uri() . '/css/admin/sortable.css');
            wp_enqueue_style('jquery-ui-datepicker', get_template_directory_uri() . '/css/admin/jquery-ui-smoothness/jquery-ui-1.10.3.custom.min.css');
        }
    } else if ($hook == 'toplevel_page_ef-options') {
        wp_enqueue_style('vertoh-theme-options', get_template_directory_uri() . '/css/admin/themeoptions.css');
        wp_enqueue_script('vertoh-theme-options', get_template_directory_uri() . '/js/admin/themeoptions.js', array('jquery'), false, true);
    }
}

// ******************* Ajax ****************** //

add_action('wp_ajax_nopriv_get_tweets', 'vertoh_ajax_get_tweets');
add_action('wp_ajax_get_tweets', 'vertoh_ajax_get_tweets');

function vertoh_ajax_get_tweets() {
    global $twitter;
    $twitterhash = get_option('ef_twitter_widget_twitterhash');
    $tweets      = array();

    if (isset($twitter) && !empty($twitterhash)) {
        $url           = 'https://api.twitter.com/1.1/search/tweets.json';
        $getfield      = "?q={$_GET['queryParams']['q']}&count={$_GET['queryParams']['count']}";
        $requestMethod = 'GET';
        $store         = $twitter->setGetfield($getfield)
                ->buildOauth($url, $requestMethod)
                ->performRequest();
        $tweets        = json_decode($store);
    }

    echo json_encode($tweets->statuses);
    die;
}

add_action('wp_ajax_nopriv_get_instagrams', 'vertoh_ajax_get_instagrams');
add_action('wp_ajax_get_instagrams', 'vertoh_ajax_get_instagrams');

function vertoh_ajax_get_instagrams() {
    global $instagram;
    $instagramhash = get_option('ef_instagram_widget_instagramhash');
    $limit         = filter_input(INPUT_POST, 'limit');
    $limit         = !empty($limit) ? $limit : 4;
    $ret           = array();

    if (isset($instagram) && !empty($instagramhash)) {
        $instagrams = $instagram->getTagMedia($instagramhash, $limit);
        $ret        = $instagrams->data;
    }

    echo json_encode($ret);
    die;
}

add_action('wp_ajax_nopriv_get_schedule', array('EF_Session_Helper', 'ef_ajax_get_schedule'));
add_action('wp_ajax_get_schedule', array('EF_Session_Helper', 'ef_ajax_get_schedule'));

add_action('wp_ajax_nopriv_get_video_thumbnail', 'vertoh_ajax_get_video_thumbnail');
add_action('wp_ajax_get_video_thumbnail', 'vertoh_ajax_get_video_thumbnail');

function vertoh_ajax_get_video_thumbnail() {
    $ret = '';
    $url = filter_input(INPUT_POST, 'url');
    if (!empty($url)) {
        $ret = vertoh_get_video_thumbnail($url, array('youtube' => 'default', 'vimeo' => 'thumbnail_small'));
    }

    echo json_encode($ret);
    die;
}

// ******************* Misc ****************** //

add_filter('manage_edit-speaker_columns', 'edit_speaker_columns');

function edit_speaker_columns($columns) {
    $new_columns = array(
        'cb'         => $columns['cb'],
        'title'      => $columns['title'],
        'menu_order' => __('Order', 'vertoh'),
        'date'       => $columns['date'],
    );
    return $new_columns;
}

add_action('manage_posts_custom_column', 'edit_post_columns', 10, 2);

function edit_post_columns($column_name) {
    global $post;

    switch ($column_name) {
        case 'menu_order' :
            echo $post->menu_order;
            break;

        default:
    }
}

function getRelativeTime($date) {
    $diff = time() - strtotime($date);
    if ($diff < 60)
        return $diff . _n(' second', ' seconds', $diff, 'vertoh') . __(' ago', 'vertoh');
    $diff = round($diff / 60);
    if ($diff < 60)
        return $diff . _n(' minute', ' minutes', $diff, 'vertoh') . __(' ago', 'vertoh');
    $diff = round($diff / 60);
    if ($diff < 24)
        return $diff . _n(' hour', ' hours', $diff, 'vertoh') . __(' ago', 'vertoh');
    $diff = round($diff / 24);
    if ($diff < 7)
        return $diff . _n(' day', ' days', $diff, 'vertoh') . __(' ago', 'vertoh');
    $diff = round($diff / 7);
    if ($diff < 4)
        return $diff . _n(' week', ' weeks', $diff, 'vertoh') . __(' ago', 'vertoh');
    return __('on ', 'vertoh') . date("F j, Y", strtotime($date));
}

add_filter('wp_nav_menu_items', 'vertoh_wp_nav_menu_items', 10, 2);

function vertoh_wp_nav_menu_items($items, $args) {
    $widget_ef_registration = get_option('widget_ef_registration');

    if ($args->theme_location == 'primary' && is_active_widget(false, false, 'ef_registration') && is_array($widget_ef_registration)) {
        foreach ($widget_ef_registration as $key => $reg_widget) {
            if (empty($reg_widget)) {
                unset($widget_ef_registration[$key]);
                update_option('widget_ef_registration', $widget_ef_registration);
            } elseif (isset($reg_widget['registrationshowtopmenu']) && $reg_widget['registrationshowtopmenu'] == 1) {
                $registration_topmenu_url = !empty($reg_widget['registrationtopmenuurl']) ? $reg_widget['registrationtopmenuurl'] : '#tile_registration_anchor';
                $items .= '<li class="register"><a href="' . $registration_topmenu_url . '" class="section-button">' . stripslashes($reg_widget['registrationtopmenutext']) . '</a></li>';
                break;
            }
        }
    }

    return $items;
}

add_filter('wp_nav_menu_objects', 'vertoh_wp_nav_menu_objects', 10, 2);

function vertoh_wp_nav_menu_objects($sorted_menu_items, $args) {
    foreach ($sorted_menu_items as $menu_item) {
        
    }

    return $sorted_menu_items;
}

function vertoh_get_video_gallery_attribute($video_type, $video_code) {
    $ret = '';

    switch ($video_type) {
        case 'youtube':
            $ret = "type='text/html' href='https://www.youtube.com/watch?v=$video_code' data-youtube='$video_code'";
            break;
        case 'vimeo':
            $ret = "type='text/html' href='https://vimeo.com/$video_code' data-vimeo='$video_code'";
            break;
    }

    return $ret;
}

################################################################
/**
 * Retrieve adjacent post link.
 *
 * Can either be next or previous post link.
 *
 * Based on get_adjacent_post() from wp-includes/link-template.php
 *
 * @param array $r Arguments.
 * @param bool $previous Optional. Whether to retrieve previous post.
 * @return array of post objects.
 */

function vertoh_get_adjacent_post_plus($r, $previous = true) {
    global $post, $wpdb;

    extract($r, EXTR_SKIP);

    if (empty($post))
        return null;

//	Sanitize $order_by, since we are going to use it in the SQL query. Default to 'post_date'.
    if (in_array($order_by, array('post_date', 'post_title', 'post_excerpt', 'post_name', 'post_modified'))) {
        $order_format = '%s';
    } elseif (in_array($order_by, array('ID', 'post_author', 'post_parent', 'menu_order', 'comment_count'))) {
        $order_format = '%d';
    } elseif ($order_by == 'custom' && !empty($meta_key)) { // Don't allow a custom sort if meta_key is empty.
        $order_format = '%s';
    } elseif ($order_by == 'numeric' && !empty($meta_key)) {
        $order_format = '%d';
    } else {
        $order_by     = 'post_date';
        $order_format = '%s';
    }

//	Sanitize $order_2nd. Only columns containing unique values are allowed here. Default to 'post_date'.
    if (in_array($order_2nd, array('post_date', 'post_title', 'post_modified'))) {
        $order_format2 = '%s';
    } elseif (in_array($order_2nd, array('ID'))) {
        $order_format2 = '%d';
    } else {
        $order_2nd     = 'post_date';
        $order_format2 = '%s';
    }

//	Sanitize num_results (non-integer or negative values trigger SQL errors)
    $num_results = intval($num_results) < 2 ? 1 : intval($num_results);

//	Queries involving custom fields require an extra table join
    if ($order_by == 'custom' || $order_by == 'numeric') {
        $current_post = get_post_meta($post->ID, $meta_key, TRUE);
        $order_by     = ($order_by === 'numeric') ? 'm.meta_value+0' : 'm.meta_value';
        $meta_join    = $wpdb->prepare(" INNER JOIN $wpdb->postmeta AS m ON p.ID = m.post_id AND m.meta_key = %s", $meta_key);
    } elseif ($in_same_meta) {
        $current_post = $post->$order_by;
        $order_by     = 'p.' . $order_by;
        $meta_join    = $wpdb->prepare(" INNER JOIN $wpdb->postmeta AS m ON p.ID = m.post_id AND m.meta_key = %s", $in_same_meta);
    } else {
        $current_post = $post->$order_by;
        $order_by     = 'p.' . $order_by;
        $meta_join    = '';
    }

//	Get the current post value for the second sort column
    $current_post2 = $post->$order_2nd;
    $order_2nd     = 'p.' . $order_2nd;

//	Get the list of post types. Default to current post type
    if (empty($post_type))
        $post_type = "'$post->post_type'";

//	Put this section in a do-while loop to enable the loop-to-first-post option
    do {
        $join                = $meta_join;
        $excluded_categories = $ex_cats;
        $included_categories = $in_cats;
        $excluded_posts      = $ex_posts;
        $included_posts      = $in_posts;
        $in_same_term_sql    = $in_same_author_sql  = $in_same_meta_sql    = $ex_cats_sql         = $in_cats_sql         = $ex_posts_sql        = $in_posts_sql        = '';

//		Get the list of hierarchical taxonomies, including customs (don't assume taxonomy = 'category')
        $taxonomies = array_filter(get_post_taxonomies($post->ID), "is_taxonomy_hierarchical");

        if (($in_same_cat || $in_same_tax || $in_same_format || !empty($excluded_categories) || !empty($included_categories)) && !empty($taxonomies)) {
            $cat_array    = $tax_array    = $format_array = array();

            if ($in_same_cat) {
                $cat_array = wp_get_object_terms($post->ID, $taxonomies, array('fields' => 'ids'));
            }
            if ($in_same_tax && !$in_same_cat) {
                if ($in_same_tax === true) {
                    if ($taxonomies != array('category'))
                        $taxonomies = array_diff($taxonomies, array('category'));
                } else
                    $taxonomies = (array) $in_same_tax;
                $tax_array  = wp_get_object_terms($post->ID, $taxonomies, array('fields' => 'ids'));
            }
            if ($in_same_format) {
                $taxonomies[] = 'post_format';
                $format_array = wp_get_object_terms($post->ID, 'post_format', array('fields' => 'ids'));
            }

            $join .= " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy IN (\"" . implode('", "', $taxonomies) . "\")";

            $term_array       = array_unique(array_merge($cat_array, $tax_array, $format_array));
            if (!empty($term_array))
                $in_same_term_sql = "AND tt.term_id IN (" . implode(',', $term_array) . ")";

            if (!empty($excluded_categories)) {
//				Support for both (1 and 5 and 15) and (1, 5, 15) delimiter styles
                $delimiter           = ( strpos($excluded_categories, ',') !== false ) ? ',' : 'and';
                $excluded_categories = array_map('intval', explode($delimiter, $excluded_categories));
//				Three category exclusion methods are supported: 'strong', 'diff', and 'weak'.
//				Default is 'weak'. See the plugin documentation for more information.
                if ($ex_cats_method === 'strong') {
                    $taxonomies    = array_filter(get_post_taxonomies($post->ID), "is_taxonomy_hierarchical");
                    if (function_exists('get_post_format'))
                        $taxonomies[]  = 'post_format';
                    $ex_cats_posts = get_objects_in_term($excluded_categories, $taxonomies);
                    if (!empty($ex_cats_posts))
                        $ex_cats_sql   = "AND p.ID NOT IN (" . implode($ex_cats_posts, ',') . ")";
                } else {
                    if (!empty($term_array) && !in_array($ex_cats_method, array('diff', 'differential')))
                        $excluded_categories = array_diff($excluded_categories, $term_array);
                    if (!empty($excluded_categories))
                        $ex_cats_sql         = "AND tt.term_id NOT IN (" . implode($excluded_categories, ',') . ')';
                }
            }

            if (!empty($included_categories)) {
                $in_same_term_sql    = ''; // in_cats overrides in_same_cat
                $delimiter           = ( strpos($included_categories, ',') !== false ) ? ',' : 'and';
                $included_categories = array_map('intval', explode($delimiter, $included_categories));
                $in_cats_sql         = "AND tt.term_id IN (" . implode(',', $included_categories) . ")";
            }
        }

//		Optionally restrict next/previous links to same author		
        if ($in_same_author)
            $in_same_author_sql = $wpdb->prepare("AND p.post_author = %d", $post->post_author);

//		Optionally restrict next/previous links to same meta value
        if ($in_same_meta && $r['order_by'] != 'custom' && $r['order_by'] != 'numeric')
            $in_same_meta_sql = $wpdb->prepare("AND m.meta_value = %s", get_post_meta($post->ID, $in_same_meta, TRUE));

//		Optionally exclude individual post IDs
        if (!empty($excluded_posts)) {
            $excluded_posts = array_map('intval', explode(',', $excluded_posts));
            $ex_posts_sql   = " AND p.ID NOT IN (" . implode(',', $excluded_posts) . ")";
        }

//		Optionally include individual post IDs
        if (!empty($included_posts)) {
            $included_posts = array_map('intval', explode(',', $included_posts));
            $in_posts_sql   = " AND p.ID IN (" . implode(',', $included_posts) . ")";
        }

        $adjacent = $previous ? 'previous' : 'next';
        $order    = $previous ? 'DESC' : 'ASC';
        $op       = $previous ? '<' : '>';

//		Optionally get the first/last post. Disable looping and return only one result.
        if ($end_post) {
            $order       = $previous ? 'ASC' : 'DESC';
            $num_results = 1;
            $loop        = false;
            if ($end_post === 'fixed') // display the end post link even when it is the current post
                $op          = $previous ? '<=' : '>=';
        }

//		If there is no next/previous post, loop back around to the first/last post.		
        if ($loop && isset($result)) {
            $op   = $previous ? '>=' : '<=';
            $loop = false; // prevent an infinite loop if no first/last post is found
        }

        $join = apply_filters("get_{$adjacent}_post_plus_join", $join, $r);

//		In case the value in the $order_by column is not unique, select posts based on the $order_2nd column as well.
//		This prevents posts from being skipped when they have, for example, the same menu_order.
        $where = apply_filters("get_{$adjacent}_post_plus_where", $wpdb->prepare("WHERE ( $order_by $op $order_format OR $order_2nd $op $order_format2 AND $order_by = $order_format ) AND p.post_type IN ($post_type) AND p.post_status = 'publish' $in_same_term_sql $in_same_author_sql $in_same_meta_sql $ex_cats_sql $in_cats_sql $ex_posts_sql $in_posts_sql", $current_post, $current_post2, $current_post), $r);

        $sort = apply_filters("get_{$adjacent}_post_plus_sort", "ORDER BY $order_by $order, $order_2nd $order LIMIT $num_results", $r);

        $query     = "SELECT DISTINCT p.* FROM $wpdb->posts AS p $join $where $sort";
        $query_key = 'adjacent_post_' . md5($query);
        $result    = wp_cache_get($query_key);
        if (false !== $result)
            return $result;

//		echo $query . '<br />';
//		Use get_results instead of get_row, in order to retrieve multiple adjacent posts (when $num_results > 1)
//		Add DISTINCT keyword to prevent posts in multiple categories from appearing more than once
        $result = $wpdb->get_results("SELECT DISTINCT p.* FROM $wpdb->posts AS p $join $where $sort");
        if (null === $result)
            $result = '';
    } while (!$result && $loop);

    wp_cache_set($query_key, $result);
    return $result;
}

//Event Framwork Session Order By Session Date

/**
 * Display previous post link that is adjacent to the current post.
 *
 * Based on previous_post_link() from wp-includes/link-template.php
 *
 * @param array|string $args Optional. Override default arguments.
 * @return bool True if previous post link is found, otherwise false.
 */
function vertoh_previous_post_link_plus($args = '') {

    return vertoh_adjacent_post_link_plus($args, '&laquo; %link', true);
}

/**
 * Display next post link that is adjacent to the current post.
 *
 * Based on next_post_link() from wp-includes/link-template.php
 *
 * @param array|string $args Optional. Override default arguments.
 * @return bool True if next post link is found, otherwise false.
 */
function vertoh_next_post_link_plus($args = '') {

    return vertoh_adjacent_post_link_plus($args, '%link &raquo;', false);
}

/**
 * Display adjacent post link.
 *
 * Can be either next post link or previous.
 *
 * Based on adjacent_post_link() from wp-includes/link-template.php
 *
 * @param array|string $args Optional. Override default arguments.
 * @param bool $previous Optional, default is true. Whether display link to previous post.
 * @return bool True if next/previous post is found, otherwise false.
 */
function vertoh_adjacent_post_link_plus($args = '', $format = '%link &raquo;', $previous = true) {

    $defaults = array(
        'order_by'       => 'post_date', 'order_2nd'      => 'post_date', 'meta_key'       => '', 'post_type'      => '',
        'loop'           => false, 'end_post'       => false, 'thumb'          => false, 'max_length'     => 0,
        'format'         => '', 'link'           => '%title', 'date_format'    => '', 'tooltip'        => '%title',
        'in_same_cat'    => false, 'in_same_tax'    => false, 'in_same_format' => false,
        'in_same_author' => false, 'in_same_meta'   => false,
        'ex_cats'        => '', 'ex_cats_method' => 'weak', 'in_cats'        => '', 'ex_posts'       => '', 'in_posts'       => '',
        'before'         => '', 'after'          => '', 'num_results'    => 1, 'return'         => false, 'echo'           => true
    );

//If Post Types Order plugin is installed, default to sorting on menu_order
    if (function_exists('CPTOrderPosts')) {

        $defaults['order_by'] = 'menu_order';
    }

    $r = wp_parse_args($args, $defaults);
    if (empty($r['format'])) {
        $r['format'] = $format;
    }
    if (empty($r['date_format'])) {
        $r['date_format'] = get_option('date_format');
    }
    if (!function_exists('get_post_format')) {
        $r['in_same_format'] = false;
    }

    if ($previous && is_attachment()) {

        $posts   = array();
        $posts[] = & get_post($GLOBALS['post']->post_parent);
    } else {
        $posts = vertoh_get_adjacent_post_plus($r, $previous);
    }

//If there is no next/previous post, return false so themes may conditionally display inactive link text.
    if (!$posts) {
        return false;
    }

//If sorting by date, display posts in reverse chronological order. Otherwise display in alpha/numeric order.
    if (($previous && $r['order_by'] != 'post_date') || (!$previous && $r['order_by'] == 'post_date')) {
        $posts = array_reverse($posts, true);
    }

//Option to return something other than the formatted link		
    if ($r['return']) {

        if ($r['num_results'] == 1) {

            reset($posts);
            $post = current($posts);
            if ($r['return'] === 'id')
                return $post->ID;
            if ($r['return'] === 'href')
                return get_permalink($post);
            if ($r['return'] === 'object')
                return $post;
            if ($r['return'] === 'title')
                return $post->post_title;
            if ($r['return'] === 'date')
                return mysql2date($r['date_format'], $post->post_date);
        } elseif ($r['return'] === 'object') {

            return $posts;
        }
    }

    $output = $r['before'];

//When num_results > 1, multiple adjacent posts may be returned. Use foreach to display each adjacent post.
    foreach ($posts as $post) {

        $title = $post->post_title;
        if (empty($post->post_title)) {

            $title = $previous ? __('Previous Post', 'vertoh') : __('Next Post', 'vertoh');
        }

        $title  = apply_filters('the_title', $title, $post->ID);
        $date   = mysql2date($r['date_format'], $post->post_date);
        $author = get_the_author_meta('display_name', $post->post_author);

//Set anchor title attribute to long post title or custom tooltip text. Supports variable replacement in custom tooltip.
        if ($r['tooltip']) {
            $tooltip = str_replace('%title', $title, $r['tooltip']);
            $tooltip = str_replace('%date', $date, $tooltip);
            $tooltip = str_replace('%author', $author, $tooltip);
            $tooltip = ' title="' . esc_attr($tooltip) . '"';
        } else
            $tooltip = '';

//Truncate the link title to nearest whole word under the length specified.
        $max_length = intval($r['max_length']) < 1 ? 9999 : intval($r['max_length']);
        if (strlen($title) > $max_length)
            $title      = substr($title, 0, strrpos(substr($title, 0, $max_length), ' ')) . '...';

        $rel = $previous ? 'prev' : 'next';

        $anchor = '<a href="' . get_permalink($post) . '" rel="' . $rel . '"' . $tooltip . '>';
        $link   = str_replace('%title', $title, $r['link']);
        $link   = str_replace('%date', $date, $link);
        $link   = $anchor . $link . '</a>';

        $format = str_replace('%link', $link, $r['format']);
        $format = str_replace('%title', $title, $format);
        $format = str_replace('%date', $date, $format);
        $format = str_replace('%author', $author, $format);
        if (($r['order_by'] == 'custom' || $r['order_by'] == 'numeric') && !empty($r['meta_key'])) {
            $meta   = get_post_meta($post->ID, $r['meta_key'], true);
            $format = str_replace('%meta', $meta, $format);
        } elseif ($r['in_same_meta']) {
            $meta   = get_post_meta($post->ID, $r['in_same_meta'], true);
            $format = str_replace('%meta', $meta, $format);
        }

//Get the category list, including custom taxonomies (only if the %category variable has been used).
        if ((strpos($format, '%category') !== false) && version_compare(PHP_VERSION, '5.0.0', '>=')) {
            $term_list    = '';
            $taxonomies   = array_filter(get_post_taxonomies($post->ID), "is_taxonomy_hierarchical");
            if ($r['in_same_format'] && get_post_format($post->ID))
                $taxonomies[] = 'post_format';
            foreach ($taxonomies as &$taxonomy) {
//No, this is not a mistake. Yes, we are testing the result of the assignment ( = ).
//We are doing it this way to stop it from appending a comma when there is no next term.
                if ($next_term = get_the_term_list($post->ID, $taxonomy, '', ', ', '')) {
                    $term_list .= $next_term;
                    if (current($taxonomies))
                        $term_list .= ', ';
                }
            }
            $format = str_replace('%category', $term_list, $format);
        }

//Optionally add the post thumbnail to the link. Wrap the link in a span to aid CSS styling.
        if ($r['thumb'] && has_post_thumbnail($post->ID)) {
            if ($r['thumb'] === true) // use 'post-thumbnail' as the default size
                $r['thumb'] = 'post-thumbnail';
            $thumbnail  = '<a class="post-thumbnail" href="' . get_permalink($post) . '" rel="' . $rel . '"' . $tooltip . '>' . get_the_post_thumbnail($post->ID, $r['thumb']) . '</a>';
            $format     = $thumbnail . '<span class="post-link">' . $format . '</span>';
        }

//If more than one link is returned, wrap them in <li> tags		
        if (intval($r['num_results']) > 1)
            $format = '<li>' . $format . '</li>';

        $output .= $format;
    }

    $output .= $r['after'];

//If echo is false, don't display anything. Return the link as a PHP string.
    if (!$r['echo'] || $r['return'] === 'output')
        return $output;

    $adjacent = $previous ? 'previous' : 'next';
    echo apply_filters("{$adjacent}_post_link_plus", $output, $r);

    return true;
}

/**
 *
 * Woocommerce Integration
 *
 */
if (is_active_widget(false, false, 'ef_registration') && in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
    add_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 10);
    add_action('woocommerce_after_shop_loop_item', 'vertoh_woocommerce_after_shop_loop_item');
    add_action('wp_enqueue_scripts', 'vertoh_woocommerce_wp_enqueue_scripts');
}

add_action('after_setup_theme', 'vertoh_woocommerce_setup_theme');

function vertoh_woocommerce_after_shop_loop_item() {
    global $post;

    echo '<td class="description"><span class="short-description">' . $post->post_excerpt . '</span></td>';
}

function vertoh_woocommerce_setup_theme() {
    add_theme_support('woocommerce');
}

function vertoh_woocommerce_wp_enqueue_scripts() {
    wp_enqueue_script('vertoh-woocommerce', get_template_directory_uri() . '/js/woocommerce.js', array('jquery'), false, true);
}

// widgets

add_filter('ef_widget_render', 'vertoh_ef_widget_render', 10, 3);

function vertoh_ef_widget_render($content, $id_base, $args) {
    ob_start();
    include(locate_template("components/templates/widgets/$id_base.php"));
    return ob_get_clean();
}

add_filter('walker_nav_menu_start_el', 'vertoh_walker_nav_menu_start_el', 10, 4);

function vertoh_walker_nav_menu_start_el($item_output, $item, $depth, $args) {
    if (in_array('menu-item-has-children', $item->classes)) {
        $item_output = "<a href=\"$item->url\" class=\"menu-item-header\">$item->title</a> <i class=\"fa fa-chevron-down\"></i>";
    }

    return $item_output;
}

add_filter('dynamic_sidebar_params', 'vertoh_dynamic_sidebar_params', 10);

function vertoh_dynamic_sidebar_params($params) {
    if ($params[0]['id'] == 'footer') {
        $params[0]['before_widget'] = '';
        $params[0]['after_widget']  = '';
    }
    return $params;
}

add_action('widgets_init', 'vertoh_widgets_init');

function vertoh_widgets_init() {
    include_once(get_template_directory() . '/components/widgets/widget-exhibitors.php');
}

add_action('init', 'vertoh_components_init');

function vertoh_components_init() {
    include_once(get_template_directory() . '/components/cpts/exhibitor.php');
    include_once(get_template_directory() . '/components/taxonomies/exhibitor.php');
    include_once(get_template_directory() . '/components/metaboxes/speakers.php');
    include_once(get_template_directory() . '/components/metaboxes/schedule.php');
    include_once(get_template_directory() . '/components/metaboxes/exhibitor.php');
    include_once(get_template_directory() . '/components/metaboxes/exhibitors.php');

    new RW_Taxonomy_Meta(array(
        'id'         => 'sponsor-tier-metas',
        'taxonomies' => array('sponsor-tier'),
        'title'      => '',
        'fields'     =>
        array(
            array(
                'name'    => __('Type', 'vertoh'),
                'id'      => 'sponsor_tier_type',
                'type'    => 'select',
                'options' => array(
                    'large'  => __('Large', 'vertoh') . ' (748x281)',
                    'medium' => __('Medium', 'vertoh') . ' (356x302)',
                    'small'  => __('Small', 'vertoh') . ' (159x136)'
                )
            ),
            array(
                'name'  => __('Order', 'dxef'),
                'id'    => 'sponsor_tier_order',
                'style' => 'width:50px;',
                'type'  => 'text'
            )
        )
    ));
}

add_action('do_meta_boxes', 'veetoh_admin_components_init');

function veetoh_admin_components_init() {
    if (in_array(get_page_template_slug(), array('speakers.php', 'schedule.php', 'exhibitors.php'))) {
        remove_meta_box('postimagediv', 'page', 'side');
    }
}

add_filter('ef_schedule_speakers_thumbnail_size', 'vertoh_ef_schedule_speakers_thumbnail_size');

function vertoh_ef_schedule_speakers_thumbnail_size($size) {
    return 'vertoh-speaker';
}

add_filter('ef_schedule_speakers_thumbnail_class', 'vertoh_ef_schedule_speakers_thumbnail_class');

function vertoh_ef_schedule_speakers_thumbnail_class($class) {
    return 'image scalable-image';
}

add_filter('next_post_link', 'vertoh_next_post_link');
add_filter('next_post_link_plus', 'vertoh_next_post_link');

function vertoh_next_post_link($html) {
    $html = str_replace('<a', '<a class="next pull-right"', $html);
    return $html;
}

add_filter('previous_post_link', 'vertoh_previous_post_link');
add_filter('previous_post_link_plus', 'vertoh_previous_post_link');

function vertoh_previous_post_link($html) {
    $html = str_replace('<a', '<a class="prev pull-left"', $html);
    return $html;
}

function vertoh_include_home_header() {
    $ef_options  = EF_Event_Options::get_theme_options();
    $header_type = !empty($ef_options['ef_header_type']) ? $ef_options['ef_header_type'] : 'slider';
    include(locate_template("components/templates/headers/home/$header_type.php"));
}

function vertoh_include_page_header() {
    if (is_singular('post', 'page') || in_array(get_page_template_slug(), array('speakers.php', 'exhibitors.php', 'schedule.php'))) {
        include(locate_template("components/templates/headers/page/solid.php"));
    }
}

function vertoh_include_page_layout() {
    $ef_options  = EF_Event_Options::get_theme_options();
    $date_format = get_option('date_format');
    $blog_layout = 'blog-full-width';
    if (!empty($ef_options['ef_blog_layout']))
        $blog_layout = $ef_options['ef_blog_layout'];
    if (is_single())
        $blog_layout = "single-$blog_layout";
    include(locate_template("components/templates/pages/$blog_layout.php"));
}

function vertoh_exhibitor_letter_posts_where($where) {
    return $where . " AND `post_title` LIKE '" . trim($_REQUEST['letter']) . "%' ";
}

function vertoh_exhibitor_text_posts_where($where) {
    return $where . " AND `post_title` LIKE '%" . trim($_REQUEST['text']) . "%' ";
}

add_action('pre_get_posts', 'vertoh_pre_get_posts');

function vertoh_pre_get_posts($query) {
    if (class_exists('EF_Event_Options')) {
        $ef_options  = EF_Event_Options::get_theme_options();
        $blog_layout = 'blog-full-width';
        if (!empty($ef_options['ef_blog_layout']))
            $blog_layout = $ef_options['ef_blog_layout'];

        if (is_category()) {
            if ($blog_layout == 'blog-full-width')
                $query->set('posts_per_page', 11);
            else if ($blog_layout == 'blog-right-sidebar')
                $query->set('posts_per_page', 9);
        } else if (get_page_template_slug() == 'exhibitors.php') {
            $query->set('posts_per_page', 12);
        }
    }
}

function vertoh_comment_callback($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    extract($args, EXTR_SKIP);
    ?>
    <li class='comment'>
        <div class="pull-left">
            <?php if ($args['avatar_size'] != 0) echo get_avatar($comment, $args['avatar_size']); ?>
        </div>
        <div class="pull-right">
            <span class='comment-author'><a href="#"><?php comment_author(); ?></a>  <?php _e('says:', 'vertoh'); ?></span>
            <span class="comment-date"><?php printf(__('%1$s at %2$s', 'vertoh'), get_comment_date(), get_comment_time()); ?></span>
            <div class="comment-body">
                <?php if ($comment->comment_approved == '0') : ?>
                    <em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.', 'vertoh'); ?></em>
                    <br />
                <?php endif; ?>
                <?php comment_text(); ?>
            </div>
        </div>
        <?php
    }

    function vertoh_pagination($paged, $total) {
        global $wp;

        $current_url = remove_query_arg('paged', add_query_arg($wp->query_string, '', home_url($wp->request)));
        $pag_links   = paginate_links(array(
            /* 'base' => $current_url . '%_%',
              'format' => strstr($current_url, '?') === false ? '?paged=%#%' : '&paged=%#%', */
            'base'      => preg_replace('/\?.*/', '/', get_pagenum_link()) . '%_%',
            'current'   => $paged,
            'total'     => $total,
            'show_all'  => true,
            'prev_text' => __('PREV', 'vertoh'),
            'next_text' => __('NEXT', 'vertoh'),
            'type'      => 'array'
        ));
        ?>
        <?php if ($pag_links && count($pag_links) > 0) { ?>
            <section class="fullwidth back-to-top pages-navigation hidden-xs">
                <div class="container">
                    <?php
                    foreach ($pag_links as $pag_link) {
                        if (strstr($pag_link, 'next ') !== false) {
                            echo str_replace('<a class="', '<a class="next pull-right ', $pag_link);
                        }
                        if (strstr($pag_link, 'prev ') !== false) {
                            echo str_replace('<a class="', '<a class="prev pull-left ', $pag_link);
                        }
                    }
                    ?>
                    <ul class="pagination">
                        <?php
                        foreach ($pag_links as $pag_link) {
                            if (strstr($pag_link, 'next ') === false && strstr($pag_link, 'prev ') === false) {
                                ?>
                                <li>
                                    <?php echo $pag_link; ?>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                    <a href='#top' class="back-to-top-icon fa-stack">
                        <i class="fa fa-circle-thin fa-stack-2x"></i>
                        <i class="fa fa-chevron-up fa-stack-1x"></i>
                    </a>
                </div>
            </section>
        <?php } ?>
        <section class="fullwidth back-to-top pages-navigation visible-xs">
            <div class="container">
                <?php
                if ($pag_links && count($pag_links) > 0) {
                    foreach ($pag_links as $pag_link) {
                        if (strstr($pag_link, 'next ') !== false) {
                            echo str_replace('<a class="', '<a class="next pull-right ', $pag_link);
                        }
                        if (strstr($pag_link, 'prev ') !== false) {
                            echo str_replace('<a class="', '<a class="prev pull-left ', $pag_link);
                        }
                    }
                    ?>
                    <ul id="pagination-carousel"  class="pagination owl-carousel">
                        <?php
                        foreach ($pag_links as $pag_link) {
                            if (strstr($pag_link, 'next ') === false && strstr($pag_link, 'prev ') === false) {
                                ?>
                                <li>
                                    <?php echo $pag_link; ?>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                <?php } ?>
                <a href='#top' class="back-to-top-icon fa-stack">
                    <i class="fa fa-circle-thin fa-stack-2x"></i>
                    <i class="fa fa-chevron-up fa-stack-1x"></i>
                </a>
            </div>
        </section>
        <?php
    }

    add_filter('ef_widget_recommended_size_text', 'vertoh_ef_widget_recommended_size_text', 10, 3);

    function vertoh_ef_widget_recommended_size_text($content, $id_base, $element_id) {
        $ret = array(
            'ef_options'       => array(
                'ef_logo'                          => '136x26',
                'ef_header_logo'                   => '308x162',
                'ef_header_video_background_image' => '1920x720',
                'ef_header_gallery'                => '1920x820',
                'ef_media_gallery'                 => '850x460',
            ),
            'ef_calltoaction2' => array(
                'calltoaction2image' => '308x162'
            ),
            'ef_calltoaction'  => array(
                'calltoactionimage' => '1920x566'
            )
        );
        return __('Recommended size: ', 'vertoh') . $ret[$id_base][$element_id];
    }

    function vertoh_get_video_thumbnail($url, $sizes) {
        $ret = '';
        try {
            $image_url = parse_url($url);
            if ($image_url['host'] == 'www.youtube.com' || $image_url['host'] == 'youtube.com') {
                $array = explode("&", $image_url['query']);
                $ret   = "http://img.youtube.com/vi/" . substr($array[0], 2) . "/{$sizes['youtube']}.jpg";
            } else if ($image_url['host'] == 'www.vimeo.com' || $image_url['host'] == 'vimeo.com') {
                $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/" . substr($image_url['path'], 1) . ".php"));
                $ret  = $hash[0][$sizes['vimeo']];
            }
        } catch (Exception $e) {
            
        }

        return $ret;
    }

    function vertoh_parse_tweet_text($text) {
        $text = preg_replace('/(https?:\/\/[^\s"<>]+)/', '<a href="$1">$1</a>', $text);
        $text = preg_replace('/(^|[\n\s])@([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/$2">@$2</a>', $text);
        $text = preg_replace('/(^|[\n\s])#([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/search?q=%23$2">#$2</a>', $text);

        return $text;
    }

    function veetoh_scheme_main_color() {
        $ef_options = EF_Event_Options::get_theme_options();
        $colors     = array(
            'basic'     => '#bc9f60',
            'blue'      => '#3c81d5',
            'brick'     => '#b7220b',
            'bubblegum' => '#f49ac1',
            'coldgreen' => '#2ac278',
            'jade'      => '#97b9b0',
            'male'      => '#93b8d9',
            'mint'      => '#0ae2b7',
            'orange'    => '#f26522',
            'peach'     => '#f0af89',
            /*'petrolio'  => '#0076a3', */
            'pumpkin'   => '#f4b900',
            'sangria'   => '#af0e5e',
            'sea'       => '#3458ac',
            'silver'    => '#959595',
            'ucd'       => '#c99700'
        );
        $ret        = $colors['basic'];

        if (!empty($ef_options['ef_color_palette'])) {
            $ret = $colors[$ef_options['ef_color_palette']];
        }

        return $ret;
    }
    
