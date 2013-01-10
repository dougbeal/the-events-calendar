<?php
/**
 * @for Day Template
 * This file contains the hook logic required to create an effective day grid view.
 *
 * @package TribeEventsCalendarPro
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }


if( !class_exists('Tribe_Events_Day_Template')){
	class Tribe_Events_Day_Template extends Tribe_Template_Factory {

		static $timeslots = array();

		public static function init(){
		
			add_filter( 'tribe_events_list_show_separators', '__return_false' );

			// Override list methods
			//add_filter( 'tribe_events_list_the_title', array( __CLASS__, 'the_title'), 20, 1);
			add_filter( 'tribe_events_list_inside_before_loop', array( __CLASS__, 'inside_before_loop'), 20, 1);
			add_filter( 'tribe_events_list_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 20, 1 );
			// remove list pagination
			/*
			remove_filter( 'tribe_events_list_before_pagination', array( 'Tribe_Events_List_Template', 'before_pagination' ), 20 );
			remove_filter( 'tribe_events_list_pagination', array( 'Tribe_Events_List_Template', 'pagination' ), 20 );
			remove_filter( 'tribe_events_list_after_pagination', array( 'Tribe_Events_List_Template', 'after_pagination' ), 20 );
			*/
			add_filter( 'tribe_events_list_before_header', array( __CLASS__, 'before_header' ), 1, 1 );
			add_filter( 'tribe_events_list_before_header_nav', array( __CLASS__, 'before_header_nav' ), 1, 1 );
			add_filter( 'tribe_events_list_header_nav', array( __CLASS__, 'header_navigation' ), 1, 1 );
			add_filter( 'tribe_events_list_before_footer', array( __CLASS__, 'before_footer' ), 1, 1 );
			add_filter( 'tribe_events_list_before_footer_nav', array( __CLASS__, 'before_footer_nav' ), 1, 1 );
			add_filter( 'tribe_events_list_footer_nav', array( __CLASS__, 'footer_navigation' ), 1, 1 );
		}
		// Start Day Template
		/*
		public static function the_title( $pass_through ){
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();

			$current_day = $wp_query->get('start_date');
			$yesterday = Date('Y-m-d', strtotime($current_day . " -1 day") );
			$tomorrow = Date('Y-m-d', strtotime($current_day . " +1 day") );

			// Display Day Navigation
			$html = sprintf('<div id="tribe-events-header" data-date="%s" data-title="%s" data-header="%s"><h3 class="tribe-events-visuallyhidden">%s</h3><ul class="tribe-events-sub-nav"><li class="tribe-events-nav-prev"><a href="%s" data-day="%s" rel="prev">&larr; %s</a></li><li class="tribe-events-nav-next"><a href="%s" data-day="%s" rel="next">%s &rarr;</a><img id="ajax-loading" class="tribe-spinner-medium" src="'. trailingslashit( TribeEvents::instance()->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" /></li></ul></div>',
								Date('Y-m-d', strtotime($current_day) ),
								wp_title( '&raquo;', false ),
								Date("l, F jS Y", strtotime($wp_query->get('start_date'))),
								__( 'Day Navigation', 'tribe-events-calendar' ),
								//tribe_get_day_permalink( $yesterday ),
								//$yesterday,
								//__( 'Prev Day', 'tribe-events-calendar-pro' ),
								//tribe_get_day_permalink( $tomorrow ),
								//$tomorrow,
								__( 'Next Day', 'tribe-events-calendar-pro' )
								);
			return apply_filters('tribe_template_factory_debug', $pass_through . $html, 'tribe_events_day_the_title');
		}
		*/
		// Day Header
		public static function before_header( $html ){
			global $wp_query;
			$current_day = $wp_query->get('start_date');
			
			$html = '<div id="tribe-events-header" data-date="'. Date('Y-m-d', strtotime($current_day) ) .'" data-title="'. wp_title( '&raquo;', false ) .'" data-header="'. Date("l, F jS Y", strtotime($wp_query->get('start_date'))) .'">';
		}
		// Day Navigation
		public static function before_header_nav( $html ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Day Navigation', 'tribe-events-calendar-pro' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
		}
		public static function header_navigation( $html ){
			$tribe_ecp = TribeEvents::instance();
			global $wp_query;

			$yesterday = Date('Y-m-d', strtotime($current_day . " -1 day") );
			$tomorrow = Date('Y-m-d', strtotime($current_day . " +1 day") );
			
			// Display Previous Page Navigation
			$html .= '<li class="tribe-nav-previous"><a href="'. tribe_get_day_permalink( $yesterday ) .'" data-day="'. $yesterday .'" rel="prev">&larr; '. __( 'Prev Day', 'tribe-events-calendar-pro' ) .'</a></li>';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next"><a href="'. tribe_get_day_permalink( $tomorrow ) .'" data-day="'. $tomorrow .'" rel="next">'. __( 'Next Day', 'tribe-events-calendar-pro' ) .' &rarr;</a>';
			// Loading spinner
			$html .= '<img class="tribe-ajax-loading tribe-spinner-medium" src="'. trailingslashit( $tribe_ecp->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return $html;
		}
		// Day Before Loop
		public static function inside_before_loop( $pass_through ){
			global $post;

			$html = '';

			// setup the "start time" for the event header
			$start_time = ( $post->tribe_is_allday ) ? 
				__( 'All Day', 'tribe-events-calendar' ) :
				tribe_get_start_date( null, false, 'ga ' );

			// determine if we want to open up a new time block
			if( ! in_array( $start_time, self::$timeslots ) ) {

				self::$timeslots[] = $start_time;	

				// close out any prior opened time blocks
				$html .= ( Tribe_Events_List_Template::$loop_increment > 0 ) ? '</div>' : '';

				// open new time block & time vs all day header
				$html .= sprintf( '<div class="tribe-events-day-time-slot"><h5>%s</h5>', $start_time );

			}
			return apply_filters('tribe_template_factory_debug', $html . $pass_through, 'tribe_events_day_inside_before_loop');
		}
		// Day Inside After Loop
		public static function inside_after_loop( $pass_through ){
			global $wp_query;

			// close out the last time block
			$html = ( Tribe_Events_List_Template::$loop_increment == count($wp_query->posts) ) ? '</div>' : '';

			return apply_filters('tribe_template_factory_debug', $pass_through . $html, 'tribe_events_day_inside_after_loop');
		}
		// Day Footer
		public static function before_footer( $html ){
			global $wp_query;
			$current_day = $wp_query->get('start_date');
			
			$html = '<div id="tribe-events-footer" data-date="'. Date('Y-m-d', strtotime($current_day) ) .'" data-title="'. wp_title( '&raquo;', false ) .'" data-header="'. Date("l, F jS Y", strtotime($wp_query->get('start_date'))) .'">';
		}
		// Day Navigation
		public static function before_footer_nav( $html ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Day Navigation', 'tribe-events-calendar-pro' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
		}
		public static function footer_navigation( $html ){
			$tribe_ecp = TribeEvents::instance();
			global $wp_query;

			$yesterday = Date('Y-m-d', strtotime($current_day . " -1 day") );
			$tomorrow = Date('Y-m-d', strtotime($current_day . " +1 day") );
			
			// Display Previous Page Navigation
			$html .= '<li class="tribe-nav-previous"><a href="'. tribe_get_day_permalink( $yesterday ) .'" data-day="'. $yesterday .'" rel="prev">&larr; '. __( 'Previous Day', 'tribe-events-calendar-pro' ) .'</a></li>';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next"><a href="'. tribe_get_day_permalink( $tomorrow ) .'" data-day="'. $tomorrow .'" rel="next">'. __( 'Next Day', 'tribe-events-calendar-pro' ) .' &rarr;</a>';
			// Loading spinner
			$html .= '<img class="tribe-ajax-loading tribe-spinner-medium" src="'. trailingslashit( $tribe_ecp->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return $html;
		}
	}
	Tribe_Events_Day_Template::init();
}