<?php
defined( 'ABSPATH' ) or exit;
/**
 * Plugin Name: The Events Calendar Show Attachments
 * Plugin URI: https://github.com/csalzano/the-events-calendar-show-attachments
 * Description: An add-on for The Events Calendar that lists Media Library attachments on the Single Event view
 * Version: 1.0.0
 * Author: Corey Salzano
 * Author URI: https://coreysalzano.com
 * Text Domain: tec-show-attachments
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

class Breakfast_TEC_Show_Attachments
{
	function hooks()
	{
		add_action( 'tribe_events_single_event_after_the_meta', array( $this, 'output_attachments' ) );
	}

	/**
	 * Captures any output generated by an action hook and returns it instead.
	 *
	 * @param string $hook The action hook to run
	 * @return string All output generated when do_action is called on the $hook
	 */
	private static function capture_action_hook_output( $hook )
	{
		ob_start();
		do_action( $hook );
		return ob_get_clean();
	}

	public static function get_attachments_html( $event_post_id = null )
	{
		$post_id = empty( $event_post_id ) ? get_the_ID() : $event_post_id;

		//are we sure this ID belongs to a post?
		$post_type = get_post_type( $post_id );
		if ( Tribe__Events__Main::POSTTYPE != $post_type )
		{
			//it does not
			return '';
		}

		//does this event even have attachments?
		$attachments = get_posts( array(
			'post_parent'    => $post_id,
			'post_type'      => 'attachment',
			'posts_per_page' => -1,
		) );
		if( empty( $attachments ) )
		{
			return '';
		}

		//Ok, we're going to return some HTML
		//Capture any output generated by an action hook
		$html = self::capture_action_hook_output( 'tribe_events_single_meta_before' );

		// Check for skeleton mode (no outer wrappers per section)
		$not_skeleton = ! apply_filters( 'tribe_events_single_event_the_meta_skeleton', false, $post_id );
		if( $not_skeleton )
		{
			$html .= '<div class="tribe-events-single-section tribe-events-event-meta tribe-events-event-meta primary tribe-clearfix">';
		}

		//Capture any output generated by an action hook
		$html .= self::capture_action_hook_output( 'tribe_events_single_event_meta_primary_section_start' );

		//Default section title is "Attachments"
		$section_title = apply_filters( 'tec_show_attachments_section_title', __( 'Attachments', 'tec-show-attachments' ) );
		$html .= sprintf(
			'<h2 class="tribe-events-single-section-title">%s</h2><ul>',
			$section_title
		);
		foreach( $attachments as $attachment )
		{
			$html .= sprintf(
				'<li><a href="%s">%s</a></li>',
				$attachment->guid, //URL to the actual attachment payload
				apply_filters( 'the_title', $attachment->post_title )
			);
		}
		$html .= '</ul>';

		//Capture any output generated by an action hook
		$html .= self::capture_action_hook_output( 'tribe_events_single_event_meta_primary_section_end' );

		if( $not_skeleton )
		{
			$html .= '</div>';
		}

		//Capture any output generated by an action hook
		$html .= self::capture_action_hook_output( 'tribe_events_single_meta_after' );

		return $html;
	}

	function output_attachments()
	{
		echo self::get_attachments_html();
	}
}
$breakfast_tec_show_attachments_20384203482340 = new Breakfast_TEC_Show_Attachments();
$breakfast_tec_show_attachments_20384203482340->hooks();
