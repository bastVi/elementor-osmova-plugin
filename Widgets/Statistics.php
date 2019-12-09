<?php
    namespace ElementorOsmovaPlugin\Widgets;

    use Elementor\Widget_Base;
    use Elementor\Controls_Manager;
    use ElementorOsmovaPlugin\Traits\Helper;
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
        use Helper;

        /* @var $db PDO */
        private static $db;
        private static $dbConnected = false;
        protected static $table_prefix = 'wp_';

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
                    'eo_global_warning',
                    [
                        'label' => __('Warning!', 'essential-addons-elementor'),
                    ]
                );

                $this->add_control(
                    'eo_global_warning_text',
                    [
                        'type' => Controls_Manager::RAW_HTML,
                        'raw' => __('<strong>Fluent Form</strong> is not installed/activated on your site. Please install and activate <a href="plugin-install.php?s=fluentform&tab=search&type=term" target="_blank">Fluent Form</a> first.', 'essential-addons-elementor'),
                        'content_classes' => 'eo-warning',
                    ]
                );

                $this->end_controls_section();
                return;
            }

            $this->start_controls_section(
                'section_form_info_box',
                [
                    'label' => __('Formulaire', 'essential-addons-elementor'),
                ]
            );

            $this->add_control(
                'form_list',
                [
                    'label' => esc_html__('Formulaire', 'essential-addons-elementor'),
                    'type' => Controls_Manager::SELECT,
                    'label_block' => true,
                    'options' => $this->eo_select_fluent_forms(),
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
            global $wpdb;
            self::$table_prefix = $wpdb->prefix;
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

        protected function getFieldsData($form_id) {
            $query = self::$db->prepare(
                "SELECT form_fields AS fields FROM `".self::$table_prefix."fluentform_forms`
                WHERE id = :form_id;"
            );
            $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
            $query->execute();
            $form = $query->fetchColumn();
            $fields_data = json_decode((is_array($form) ? array_shift($form) : $form), true);
            return $fields_data['fields'] ?? null;
        }

        protected function getEntriesData($form_id) {
            $query = self::$db->prepare(
                "SELECT COUNT(submission_id) AS submissions, field_name AS name, field_value AS value 
                FROM `". self::$table_prefix ."fluentform_entry_details` WHERE form_id = :form_id 
                GROUP BY name, value;"
            );
            $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }

        public function isSubfield($field_key, $field_data = [], $survey_model = [], $end_string_marker = 'o', $c_class = 'subfield') {
            return (!empty($field_data) ? $field_data['settings']['container_class'] == $c_class : false)
                || (!empty($survey_model) ? !array_key_exists($field_key, $survey_model) : false)
            || (substr($field_key, -1) === $end_string_marker);
        }

        protected function getSurveyQuestions($raw_fields) {
            $survey = array();
            foreach ($raw_fields as $field => $data) {
                $data_name = $data['attributes']['name'];
                $is_subfield = $this->isSubfield($data_name, $data);
                $question_num = $is_subfield ? $raw_fields[$field - 1]['attributes']['name'] : $data_name;
                if(!$is_subfield) $survey[$question_num]['label'] = $data['settings']['label'];
            }
            return $survey;
        }

        protected function getSurveyData($form_id) {
            $entries = $this->getEntriesData($form_id);
            $survey_fields = $this->getSurveyQuestions($this->getFieldsData($form_id));
            foreach ($entries as $entry => $data) {
                $isSubfield = $this->isSubfield($data['name'], [], $survey_fields);
                $question = $data['name'];
                if($isSubfield) $question = rtrim($data['name'], 'o');
                $survey_fields[$question]['results'][$data['value']] = $data['submissions'];
            }
            return $survey_fields;
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
            $form_id = $settings['form_list'];
            if(!$form_id) {
                return;
            }
            self::connect();
            $questions = $this->getSurveyData($form_id);
            ?>
            <div id="survey-results">
                <?php if(isset($questions[1]) && isset($questions[1]['results'])):?>
                <?php foreach ($questions as $num => $data): ?>
                    <h4><?= $data['label'] ?></h4>
                    <table>
                        <thead>
                        <th>Réponses</th>
                        <th>Nombre d'entées</th>
                        </thead>
                        <tbody>
                        <?php if(isset($data['results'])): foreach ($data['results'] as $value => $entries): ?>
                            <tr>
                                <td><?= $value; ?></td>
                                <td><?= $entries; ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>
                <?php else: ?>
                    <div  class="no-data-message alert alert-warning">
                        <p class="text-center">Aucune donnée à afficher, aucun formulaire n'a encore été soumis</p>
                    </div>
                <?php endif; ?>
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
