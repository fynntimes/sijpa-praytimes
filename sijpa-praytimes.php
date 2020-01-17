<?php
/*
Plugin Name: SIJPA PrayTimes
Plugin URI: https://faizaand.com/
Description: Builds a sijpa_ptimes shortcode to show latest prayer times.
Author: Faizaan Datoo
Version: 1.0.0
Author URI: https://faizaand.com/
*/

// SETTINGS
// Edit here

if ( ! defined( 'TIMEZONE' ) ) {
	define( 'TIMEZONE', 'America/New_York' );
}
if ( ! defined( 'LATITUDE' ) ) {
	define( 'LATITUDE', '40.602295' ); // Allentown, PA latitude
}
if ( ! defined( 'LONGITUDE' ) ) {
	define( 'LONGITUDE', '-75.471413' ); // Allentown, PA longitude
}

// Stop editing, and happy website-ing!

function sijpa_ptimes_shortcode( $atts = [], $content = null, $tag = '' ) {
	// normalize attribute keys, lowercase
	$atts = array_change_key_case( (array) $atts, CASE_LOWER );

	// override default attributes with user attributes
	$wporg_atts = shortcode_atts( [
		'for' => 'fajr',
	], $atts, $tag );

	$prayer = $wporg_atts['for'];

	try {
		$date = new DateTime( "now", new DateTimeZone( 'America/New_York' ) );
	} catch ( Exception $e ) {
		return "Error loading prayer times";
	}

	$month = $date->format( 'm' );
	$year  = $date->format( 'Y' );
	$day   = $date->format( 'd' );
	$url   = "http://praytime.info/getprayertimes.php?lat=" . LATITUDE . "&lon=" . LONGITUDE . "&gmt=-300&m=${month}&y=${year}";

	try {
		$response = get_remote_data( $url );
	} catch ( Exception $e ) {
		return "Error loading prayer times";
	}

	$timings = json_decode( $response['body'], true )[ $day ];
	$timings = array_change_key_case( $timings, CASE_LOWER );

	$time = $timings[ $prayer ];
	$time = date( 'h:i a', strtotime( $time ) );

	return $time;
}

function get_remote_data( $url ) {
	$key = 'prayertime-cache';
	if ( ! wp_cache_get( $key ) ) {
		$response = wp_remote_get( $url );
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			throw new Exception();
		}
		wp_cache_add( $key, $response );

		return $response;
	} else {
		return wp_cache_get( $key );
	}
}

function wporg_shortcodes_init() {
	add_shortcode( "sijpa_ptimes", 'sijpa_ptimes_shortcode' );
}

add_action( 'init', 'wporg_shortcodes_init' );
