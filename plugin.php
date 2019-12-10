
<?php
namespace ElementorOsmovaPlugin;

/**
 * Class Plugin
 *
 * Main Plugin class
 * @since 1.2.0
 */
class Plugin {

	/**
	 * Instance
	 *
	 * @since 1.2.0
	 * @access private
	 * @static
	 *
	 * @var Plugin The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * widget_scripts
	 *
	 * Load required plugin core files.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function widget_scripts() {
		wp_register_script( 'elementor-osmova-plugin', plugins_url( '/assets/js/statistics.js', __FILE__ ), [ 'jquery' ], false, true );
        if( defined('FLUENTFORM') ) {
            wp_enqueue_style(
                'fluent-form-styles',
                WP_PLUGIN_URL . '/fluentform/public/css/fluent-forms-public.css',
                array(),
                FLUENTFORM_VERSION
            );

            wp_enqueue_style(
                'fluentform-public-default',
                WP_PLUGIN_URL . '/fluentform/public/css/fluentform-public-default.css',
                array(),
                FLUENTFORM_VERSION
            );
        }
	}

	/**
	 * widget_styles
	 *
	 * Load required plugin core files.
	 *
	 * @since 1.2.1
	 * @access public
	 */
	public function widget_styles() {
        wp_register_style( 'elementor-osmova-plugin', plugins_url( '/assets/css/fluentform/index.min.css', __FILE__ ));
        wp_register_style( 'elementor-osmova-plugin', plugins_url( '/assets/css/statistics.min.css', __FILE__ ));
	}

	/**
	 * Include Widgets files
	 *
	 * Load widgets files
	 *
	 * @since 1.2.0
	 * @access private
	 */
	private function include_widgets_files() {
		require_once( __DIR__ . '/Widgets/FluentForm.php' );
		require_once( __DIR__ . '/Widgets/InlineEditing.php' );
        require_once( __DIR__ . '/Widgets/Statistics.php' );
        require_once( __DIR__ . '/Widgets/Export.php' );
	}

	/**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function register_widgets() {
		// Its is now safe to include Widgets files
		$this->include_widgets_files();

		// Register Widgets
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\FluentForm() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\InlineEditing() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\Statistics() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\Export() );
	}

	/**
	 *  Plugin class constructor
	 *
	 * Register plugin action hooks and filters
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function __construct() {

        // Register Widget Styles
        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'widget_styles' ] );

		// Register widget scripts
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'widget_scripts' ] );

		// Register widgets
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
	}
}

// Instantiate Plugin Class
Plugin::instance();

