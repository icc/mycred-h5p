<?php
/**
 * @wordpress-plugin
 * Plugin Name:       myCRED H5P
 * Plugin URI:        http://h5p.org/
 * Description:       Adds a myCRED hook for tracking points scored in H5P content.
 * Version:           0.1.0
 * Author URI:        http://joubel.com
 * Text Domain:       mycredh5p
 * License:           MIT
 * License URI:       http://opensource.org/licenses/MIT
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/h5p/mycred-h5p-wordpress-plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

/**
 *
 */
function mycredh5p_register($installed) {
	$installed['mycredh5p'] = array(
		'title'       => __('myCRED H5P', 'mycredh5p'),
		'description' => __('Adds a myCRED hook for tracking points scored in H5P content.', 'mycredh5p'),
		'callback'    => array('myCRED_Hook_H5P')
	);
	return $installed;
}
add_filter('mycred_setup_hooks', 'mycredh5p_register');


/**
 *
 */
function mycredh5p_badge($references) {
	$references['completing_h5p'] = __('Completing H5P', 'mycredh5p');
	return $references;
}
add_filter('mycred_all_references', 'mycredh5p_badge');

/**
 *
 */
function mycredh5p_init() {

  /**
   * Class
   */
  class myCRED_Hook_H5P extends myCRED_Hook {

    /**
  	 * Construct
  	 */
  	function __construct($hook_prefs, $type) {
  		parent::__construct(array(
  			'id'       => 'completing_h5p',
  			'defaults' => array(
  				'creds'   => 1,
  				'log'     => '%plural% for completing H5P content'
  			)
  		), $hook_prefs, $type);
  	}

  	/**
  	 * Hook into H5P
  	 */
    public function run() {
      add_action('h5p_alter_user_result',  array($this, 'h5p_result'), 10, 4);
    }

    /**
     * Give points for H5P result
     */
    public function h5p_result($data, $result_id, $content_id, $user_id) {
      // Check if full score
      if ($data['score'] !== $data['max_score']) return;

      // Make sure this is the first result for this content
      //if ($result_id) return; // (result_id is only used when updating an old score)

      // Make sure this is a unique event
      if ($this->has_entry('completing_h5p', $content_id, $user_id)) return;

      // Execute
      $this->core->add_creds(
        'completing_h5p',
        $user_id,
        $this->prefs['creds'],
        $this->prefs['log'],
        $content_id,
        '', // TODO: Save score?
        $m
      );
    }

  /*
    public function preferences() {

    }

    public function sanitise_preferences() {

    }
    */
  }
}
add_action('mycred_pre_init', 'mycredh5p_init');
