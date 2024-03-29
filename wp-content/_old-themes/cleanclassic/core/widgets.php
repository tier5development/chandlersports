<?php

function art_get_vmenu($id='', $class='' , $theme_location = 'secondary-menu', $title = 'Navigation Menu')
{
    global $art_config;
	$caption = __($art_config['vmenu']['source'], 'kubrick');
	if (function_exists('wp_nav_menu') && has_nav_menu( $theme_location ) ) {
        $caption = __($title);
	} 
    $content = '<ul class="art-vmenu">' . art_get_menu_auto($theme_location, $art_config['vmenu']['source'], $art_config['vmenu']['showSubitems']) . '</ul>';
    return art_get_block($caption, $content, $id, $class, 'vmenu');
}



function widget_verticalmenu($args) {
	extract($args);
    $id = art_get_widget_id($before_widget);
    $class = art_get_widget_class($before_widget);
    echo art_get_vmenu($id, $class) . $after_widget;
}

if (class_exists('WP_Widget')){

    class LegacyVMenuWidget extends WP_Widget {

        function LegacyVMenuWidget() {
            $widget_ops = array( 'description' => __('Use this widget to add custom vertical menus.') );
            parent::WP_Widget( 'vmenu', __('Navigation Menu'), $widget_ops );
        }

        function widget($args, $instance) {
            extract($args);
            global $art_config;
            $depth = (!$art_config['vmenu']['showSubitems'] ? 1 : 0);
            $id = art_get_widget_id($before_widget);
            $class = art_get_widget_class($before_widget);
            $caption = $instance['title'];
            $content = '<ul class="art-vmenu">' . art_get_menu($instance['source'], $depth, null) . '</ul>';
            echo art_get_block($caption, $content, $id, $class, 'vmenu') .$after_widget;
        }

        function update( $new_instance, $old_instance ) {
            $instance['title'] = strip_tags( stripslashes($new_instance['title']) );
            $instance['source'] = $new_instance['source'];
            return $instance;
        }

        function form( $instance ) {
            $title = isset( $instance['title'] ) ? $instance['title'] : '';
            $source = isset( $instance['source'] ) ? $instance['source'] : '';
            $sources = array('Pages', 'Categories');
            ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('source'); ?>"><?php _e('Source:') ?></label>
                <select class="widefat" id="<?php echo $this->get_field_id('source'); ?>" name="<?php echo $this->get_field_name('source'); ?>">
            <?php
                
                foreach ( $sources as $s ) {
                    $selected = ($source == $s ? ' selected="selected"' : '');
                    echo '<option'. $selected .' value="'. $s .'">'. __($s) .'</option>';
                }
            ?>
                </select>
            </p>
            <?php
        }
    }


    class VMenuWidget extends WP_Widget {

        function VMenuWidget() {
            $widget_ops = array( 'description' => __('Use this widget to add one of your custom menus as a widget.') );
            parent::WP_Widget( 'vmenu', __('Navigation Menu'), $widget_ops );
        }

        function widget($args, $instance) {
            extract($args);
            global $art_config;
            $depth = (!$art_config['vmenu']['showSubitems'] ? 1 : 0);
            $id = art_get_widget_id($before_widget);
            $class = art_get_widget_class($before_widget);
            $caption = $instance['title'];
            $content = '<ul class="art-vmenu">' . art_get_menu($instance['source'], $depth, wp_get_nav_menu_object( $instance['nav_menu'] )) . '</ul>';
            echo art_get_block($caption, $content, $id, $class, 'vmenu') .$after_widget;
        }

        function update( $new_instance, $old_instance ) {
            $instance['title'] = strip_tags( stripslashes($new_instance['title']) );
            $instance['source'] = $new_instance['source'];
            $instance['nav_menu'] = (int) $new_instance['nav_menu'];
            return $instance;
        }

        function form( $instance ) {
            $title = isset( $instance['title'] ) ? $instance['title'] : '';
            $source = isset( $instance['source'] ) ? $instance['source'] : '';
            $nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : '';

            // Get menus
            $menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
            $sources = array('Pages', 'Categories', 'Custom Menu');
            ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('source'); ?>"><?php _e('Source:') ?></label>
                <select class="widefat" id="<?php echo $this->get_field_id('source'); ?>" name="<?php echo $this->get_field_name('source'); ?>" onchange="var s = jQuery('#p-<?php echo $this->get_field_id('nav_menu');?>'); if (this.value == 'Custom Menu') s.show(); else s.hide();">
            <?php
                
                foreach ( $sources as $s ) {
                    $selected = ($source == $s ? ' selected="selected"' : '');
                    echo '<option'. $selected .' value="'. $s .'">'. __($s) .'</option>';
                }
            ?>
                </select>
            </p>
            <p id="p-<?php echo $this->get_field_id('nav_menu'); ?>" <?php if ($source !== 'Custom Menu') echo ' style="display:none"'?>>
            
            <?php // If no menus exists, direct the user to go and create some.
                if ( !$menus ) {
                    echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php') ) .'</p>';
                } else { ?>
            
                <label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Menu:'); ?></label>
                <select class="widefat" id="<?php echo $this->get_field_id('nav_menu'); ?>" name="<?php echo $this->get_field_name('nav_menu'); ?>">
            <?php 
                }
                foreach ( $menus as $menu ) {
                    $selected = $nav_menu == $menu->term_id ? ' selected="selected"' : '';
                    echo '<option'. $selected .' value="'. $menu->term_id .'">'. $menu->name .'</option>';
                }
            ?>
                </select>
            </p>
            <?php
        }
    }

}
// init
function artWidgetsInit(){
    if (WP_VERSION < 2.7) {
        if ( function_exists('register_sidebar_widget') ) {
            register_sidebar_widget(array('Vertical menu', 'widgets'), 'widget_verticalmenu');
        }
    } elseif (WP_VERSION < 3.0) {
        if (class_exists('LegacyVMenuWidget')) {
            register_widget('LegacyVMenuWidget');
        }
    } else {
        if (class_exists('VMenuWidget')) {
            register_widget('VMenuWidget');
        }
    }
}

add_action('widgets_init', 'artWidgetsInit');