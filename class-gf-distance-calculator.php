<?php

GFForms::include_addon_framework();

class GFDistanceCalculator extends GFAddOn {

	protected $_version = '1.0';
	protected $_min_gravityforms_version = '1.9';
	protected $_full_path = __FILE__;
	protected $_google_maps_key = 'AIzaSyAvyOtvhy0crTZYYX6Uc405_-mdUHAVTHM';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFDistanceCalculator
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFDistanceCalculator();
		}

		return self::$_instance;
	}

	private function __clone() {
	} /* do nothing */

	/**
	 * Handles anything which requires early initialization.
	 */
	public function pre_init() {
		parent::pre_init();

		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
			require_once( 'includes/class-gf-field-distance-calculator.php' );
		}
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();
	}

	/**
	 * Initialize the admin specific hooks.
	 */
	public function init_admin() {
		parent::init_admin();
	}

	public function get_rate() {
		return '1';
	}

	/**
	* Append scripts if field type exists
	*/
	public function scripts() {
		$scripts = array(
			array(
				'handle'    => 'distance_calculator',
				'src'       => $this->get_base_url() . '/assets/distance-calculator.js',
				'version'   => $this->_version,
				'deps'      => array( 'jquery' ),
				'in_footer' => true,
				'strings'   => array(
					'dc_ajax_path' => $this->get_base_url() . '/api/distance-calculator.php'
				),
				'enqueue'   => array(
					array( 'field_types' => array( 'distance_calculator' ) ),
				),
			),
			array(
				'handle'    => 'distance_calculator_libs',
				'src'       => 'https://maps.googleapis.com/maps/api/js?key=' . $this->_google_maps_key . '&libraries=places,distancematrix&language=da',
				'version'   => $this->_version,
				'deps'      => array( 'jquery' ),
				'in_footer' => true,
				'enqueue'   => array(
					array( 'field_types' => array( 'distance_calculator' ) ),
				),
			)
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	* Append styles if field type exists
	*/
	public function styles() {
		$styles = array(
			array(
				'handle'  => 'distance_calculator',
				'src'     => $this->get_base_url() . '/assets/distance-calculator.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( 'distance_calculator' ) ),
				)
			)
		);

		return array_merge( parent::styles(), $styles );
	}

}
