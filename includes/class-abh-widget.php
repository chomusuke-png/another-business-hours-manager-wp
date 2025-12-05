<?php
if (!defined('ABSPATH')) exit;

class ABH_Hours_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'abh_hours_widget', 
            'Business Hours (ABH)', 
            array('description' => 'Muestra la tabla de horarios y estado.')
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo do_shortcode('[abh_hours]');
        echo $args['after_widget'];
    }

    public function form($instance) {
        echo '<p>Configura los horarios en el menú "Business Hours".</p>';
    }
}