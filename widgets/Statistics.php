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
        private static $ddb;
        private static $ddbConnected = false;

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
            $this->start_controls_section(
                'section_content',
                [
                    'label' => __('Content', 'elementor-statistics'),
                ]
            );

            $this->add_control(
                'title',
                [
                    'label' => __('Title', 'elementor-statistics'),
                    'type' => Controls_Manager::TEXT,
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
                # Read settings from INI file, set UTF8
                self::$ddb = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASSWORD,
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                );
                # We can now log any exceptions on Fatal error.
                self::$ddb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                # Disable emulation of prepared statements, use REAL prepared statements instead.
                self::$ddb->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                # Connection succeeded, set the boolean to true.
                self::$ddbConnected = true;
            } catch (PDOException $e) {
                # Write into log
                file_put_contents('../logs/bdd.log', $e->getMessage());
                die();
            }
        }

        protected function getStats() {
            self::connect();
            $regions = self::$ddb->query(
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
            $settings = $this->get_settings_for_display();
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
