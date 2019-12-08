<?php
    namespace ElementorOsmovaPlugin\Widgets;

    use Elementor\Widget_Base;
    use Elementor\Controls_Manager;
    use \PDO;
    use \PDOException;

    if (!defined('ABSPATH')) {
        exit;
    } // Exit if accessed directly

    /**
     * @since 1.0.0
     */
    class Statistics extends Widget_Base
    {
        /* @var $db PDO */
        private static $db;
        private static $dbConnected = false;

        /**
         * Retrieve the widget name.
         *
         * @return string Widget name.
         * @since 1.0.0
         *
         * @access public
         *
         */
        public function get_name() {
            return 'Statistiques';
        }

        /**
         * Retrieve the widget title.
         *
         * @return string Widget title.
         * @since 1.0.0
         *
         * @access public
         *
         */
        public function get_title() {
            return __('Statistiques', 'elementor-osmova-plugin');
        }

        /**
         * Retrieve the widget icon.
         *
         * @return string Widget icon.
         * @since 1.0.0
         *
         * @access public
         *
         */
        public function get_icon() {
            return 'far fa-chart-bar';
        }

        /**
         * Retrieve the list of categories the widget belongs to.
         *
         * Used to determine where to display the widget in the editor.
         *
         * Note that currently Elementor supports only one category.
         * When multiple categories passed, Elementor uses the first one.
         *
         * @return array Widget categories.
         * @since 1.0.0
         *
         * @access public
         *
         */
        public function get_categories() {
            return ['general'];
        }

        /**
         * Retrieve the list of scripts the widget depended on.
         *
         * Used to set scripts dependencies required to run the widget.
         *
         * @return array Widget scripts dependencies.
         * @since 1.0.0
         *
         * @access public
         *
         */
        public function get_script_depends() {
            return ['elementor-osmova-plugin'];
        }

        /**
         * Register the widget controls.
         *
         * Adds different input fields to allow the user to change and customize the widget settings.
         *
         * @since 1.0.0
         *
         * @access protected
         */
        protected function _register_controls() {
            if (!defined('FLUENTFORM')) {
                $this->start_controls_section(
                    'eael_global_warning',
                    [
                        'label' => __('Warning!', 'essential-addons-elementor'),
                    ]
                );

                $this->add_control(
                    'eael_global_warning_text',
                    [
                        'type' => Controls_Manager::RAW_HTML,
                        'raw' => __('<strong>Fluent Form</strong> is not installed/activated on your site. Please install and activate <a href="plugin-install.php?s=fluentform&tab=search&type=term" target="_blank">Fluent Form</a> first.', 'essential-addons-elementor'),
                        'content_classes' => 'eael-warning',
                    ]
                );

                $this->end_controls_section();
                return;
            }

            $this->start_controls_section(
                'section_form_info_box',
                [
                    'label' => __('Fluent Form', 'essential-addons-elementor'),
                ]
            );

            $this->add_control(
                'form_list',
                [
                    'label' => esc_html__('Fluent Form', 'essential-addons-elementor'),
                    'type' => Controls_Manager::SELECT,
                    'label_block' => true,
                    'options' => $this->eael_select_fluent_forms(),
                    'default' => '0',
                ]
            );

            $this->end_controls_section();

//		$this->start_controls_section(
//			'section_style',
//			[
//				'label' => __( 'Style', 'elementor-statistics' ),
//				'tab' => Controls_Manager::TAB_STYLE,
//			]
//		);
//
//		$this->add_control(
//			'text_transform',
//			[
//				'label' => __( 'Text Transform', 'elementor-osmova-plugin' ),
//				'type' => Controls_Manager::SELECT,
//				'default' => '',
//				'options' => [
//					'' => __( 'None', 'elementor-osmova-plugin' ),
//					'uppercase' => __( 'UPPERCASE', 'elementor-osmova-plugin' ),
//					'lowercase' => __( 'lowercase', 'elementor-osmova-plugin' ),
//					'capitalize' => __( 'Capitalize', 'elementor-osmova-plugin' ),
//				],
//				'selectors' => [
//					'{{WRAPPER}} .title' => 'text-transform: {{VALUE}};',
//				],
//			]
//		);

//		$this->end_controls_section();
        }

        private static function connect() {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
            try {
                self::$db = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASSWORD,
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                );
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                self::$dbConnected = true;
            } catch (PDOException $e) {
                file_put_contents('../logs/bdd.log', $e->getMessage());
                die();
            }
        }

        protected function getFormFields($form_id) {
            $form = self::$db->query(
                "SELECT 'form_fields' AS fields 
                FROM `wp_fluentform_forms` 
                WHERE 'id'= ". self::$db->quote($form_id).";"
            );
        }

        protected function getStats() {
            $regions = self::$db->query(
                "SELECT COUNT(umeta_id) AS count, meta_key, meta_value FROM `wp_usermeta` 
                WHERE 1 GROUP BY meta_key, meta_value"
            );

            $stats = [
                'region' => [],
                'connu' => [],
                'annee' => [],
            ];

            while ($line = $regions->fetchObject()) {
                $key = false;
                if ($line->meta_key == 'user_registration_userform_region') {
                    $key = 'region';
                }
                if ($line->meta_key == 'user_registration_userform_connu_asso') {
                    $key = 'connu';
                }
                if ($line->meta_key == 'user_registration_userform_annee_arrivee') {
                    $key = 'annee';
                }
                if ($key) {
                    $stats[$key][] = $line;
                }
            }

            return $stats;
        }

        /**
         * Render the widget output on the frontend.
         *
         * Written in PHP and used to generate the final HTML.
         *
         * @since 1.0.0
         *
         * @access protected
         */
        protected function render() {
            if( ! defined('FLUENTFORM') ) return;
            $settings = $this->get_settings_for_display();
            self::connect();
            $stats = $this->getStats();
            ?>
            <div>
                <?php foreach ($stats as $key => $data): ?>
                    <p><?= $key ?></p>
                    <table>
                        <thead>
                        <th>Valeur</th>
                        <th>Nombre</th>
                        </thead>
                        <tbody>
                        <?php foreach ($data as $line) { ?>
                            <tr>
                                <td><?= $line->meta_value; ?></td>
                                <td><?= $line->count; ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>
            </div>
            <?php
        }

        /**
         * Render the widget output in the editor.
         *
         * Written as a Backbone JavaScript template and used to generate the live preview.
         *
         * @since 1.0.0
         *
         * @access protected
         */
        protected
        function _content_template() {
            ?>
            <div class="title">
                {{{ settings.title }}}
            </div>
            <?php
        }
    }
