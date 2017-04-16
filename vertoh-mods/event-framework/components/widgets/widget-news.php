<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Register the Latest News Widget
 * 
 * @package Event Framework
 * @since 1.0.0
 */

/**
 * Ef_Footer_Text_Columns_Widget Widget Class.
 * 
 * 
 * @package Event Framework
 * @since 1.0.0
 */
class Ef_Latest_News_Widget extends WP_Widget {

    /**
     * Contact Widget setup.
     * 
     * @package Event Framework
     * @since 1.0.0
     */
    function Ef_Latest_News_Widget() {

        $widget_name = EF_Framework_Helper::get_widget_name();

        /* Widget settings. */
        $widget_ops = array('classname' => 'ef_latest_news', 'description' => __('Shows a section displaying the latest posts', 'dxef'));

        /* Create the widget. */
        $this->WP_Widget('ef_latest_news', $widget_name . __(' Latest News', 'dxef'), $widget_ops);
    }

    /**
     * Output of Widget Content
     * 
     * Handle to outputs the
     * content of the widget
     * 
     * @package Event Framework
     * @since 1.0.0
     */
    function widget($args, $instance) {

        $newstitle = isset($instance['newstitle']) ? $instance['newstitle'] : '';
        $newssubtitle = isset($instance['newssubtitle']) ? $instance['newssubtitle'] : '';
        $newsviewalltext = isset($instance['newsviewalltext']) ? $instance['newsviewalltext'] : '';
        $full_news_page = get_posts(array(
            'post_type' => 'page',
            'meta_key' => '_wp_page_template',
            'meta_value' => 'index.php'
        ));
        $news = get_posts(array(
            'posts_per_page' => -1,
        ));
        $blog_category_id = get_cat_ID('Blog');
        $categories = get_categories(array('type' => 'post'));

        if (empty($blog_category_id)) {
            $categories = get_categories(array('type' => 'post'));
            $blog_category_id = $categories[0];
        }
        $blog_category_link = get_category_link($blog_category_id);


        echo stripslashes($args['before_widget']);
        echo apply_filters('ef_widget_render', '', $this->id_base, array(
            'title' => $newstitle,
            'subtitle' => $newssubtitle,
            'full_news_page' => $full_news_page,
            'news' => $news,
            'blog_category_link' => $blog_category_link,
            'viewalltext' => $newsviewalltext));
        echo stripslashes($args['after_widget']);
    }

    /**
     * Update Widget Setting
     * 
     * Handle to updates the widget control options
     * for the particular instance of the widget
     * 
     * @package Event Framework
     * @since 1.0.0
     */
    function update($new_instance, $old_instance) {

        $instance = $old_instance;

        /* Set the instance to the new instance. */
        $instance = $new_instance;

        /* Input fields */
        $instance['newstitle'] = strip_tags($new_instance['newstitle']);
        $instance['newssubtitle'] = strip_tags($new_instance['newssubtitle']);
        $instance['newsviewalltext'] = strip_tags($new_instance['newsviewalltext']);

        return $instance;
    }

    /**
     * Display Widget Form
     * 
     * Displays the widget
     * form in the admin panel
     * 
     * @package Event Framework
     * @since 1.0.0
     */
    function form($instance) {

        $newstitle = isset($instance['newstitle']) ? $instance['newstitle'] : '';
        $newssubtitle = isset($instance['newssubtitle']) ? $instance['newssubtitle'] : '';
        $newsviewalltext = isset($instance['newsviewalltext']) ? $instance['newsviewalltext'] : '';
        ?>
        <em><?php _e('Title:', 'dxef'); ?></em><br />
        <input type="text" class="widefat" name=<?php echo $this->get_field_name('newstitle'); ?>"" value="<?php echo stripslashes($newstitle); ?>" />
        <br /><br />
        <em><?php _e('Subtitle:', 'dxef'); ?></em><br />
        <input type="text" class="widefat" name="<?php echo $this->get_field_name('newssubtitle'); ?>" value="<?php echo stripslashes($newssubtitle); ?>" />
        <br /><br />
        <em><?php _e('"View all news" Text:', 'dxef'); ?></em><br />
        <input type="text" class="widefat" name="<?php echo $this->get_field_name('newsviewalltext'); ?>" value="<?php echo stripslashes($newsviewalltext); ?>" />
        <br /><br />
        <input type="hidden" name="submitted" value="1" /><?php
    }

}

// Register Widget
register_widget('Ef_Latest_News_Widget');
