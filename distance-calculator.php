<?php
/*
Plugin Name: Gravity Forms Distance Calculator Add-On
Plugin URI: http://www.inzite.dk
Description: Creates a Gravity Forms Distance Calculator with Google Maps integration.
Version: 1.0
Author: Johnnie Bertelsen
Author URI: http://www.spinx-web.dk
Text Domain: gravityformsdistancecalculator

*/

add_action( 'gform_loaded', array( 'GF_Distance_Calculator_Bootstrap', 'load' ), 5 );

class GF_Distance_Calculator_Bootstrap {

	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-distance-calculator.php' );

		GFAddOn::register( 'GFDistanceCalculator' );
	}
}

function gf_distance_calculator() {
	return GFDistanceCalculator::get_instance();
}
