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
    class Export extends Widget_Base
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
            return 'Export';
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
            return __('Export', 'elementor-osmova-plugin');
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

        /**
         * Retrieves the form headers
         *
         * @param $form_id - The ID of the Fluent Form
         *
         * @access protected
         */
        protected function getSurveyHeaders($form_id) {
            $query = self::$db->prepare(
                "SELECT form_fields
                    FROM `".self::$table_prefix."fluentform_forms`
                    WHERE id = :form_id;"
            );
            $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
            $query->execute();
            $form_fields = $query->fetchColumn();
            $form_fields = json_decode($form_fields, true);
            return $form_fields['fields'];
        }

        /**
         * Retrieves the users data
         *
         * @param $form_id - The ID of the Fluent Form
         *
         * @access protected
         */
        protected function getUsersData($form_id) {
            $query = self::$db->prepare(
                "SELECT fs.response, fs.user_id, fs.created_at AS date, u.user_email AS email
                    FROM `".self::$table_prefix."fluentform_submissions` fs
                    LEFT JOIN `".self::$table_prefix."users` u ON u.ID = fs.user_id
                    WHERE form_id = :form_id;"
            );
            $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
            $query->execute();

            $results = [];
            while($user = $query->fetchObject()) {
                // Récup des user_meta
                $user_meta = get_user_meta($user->user_id);
                $user_data = [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                    'form_data' => json_decode($user->response, true),
                    'date' => $user->date,
                    'first_name' => isset($user_meta['first_name']) ? reset($user_meta['first_name']) : '',
                    'last_name' => isset($user_meta['last_name']) ? reset($user_meta['last_name']) : ''
                ];
                $results[] = $user_data;
            }
            return $results;
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
            self::connect();

            // Loading the survey headers and user responses
            $headers = $this->getSurveyHeaders(1);
            $users = $this->getUsersData(1);
            ?>
            <a class="export-btn" href="/export.php" download>
                Télécharger au format .csv
            </a>
            <br class="clear"/>
            <div class="table-container">
                <table class="export-table">
                    <thead>
                        <th>ID Utilisateur</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>E-mail</th>
                        <?php
                        // Displaying fluent form headers
                        foreach($headers as $header): ?>
                            <th><?= $header['settings']['label'] ?></th>
                        <?php endforeach; ?>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                            <tr>
                                <td><?= $user['user_id'] ?></td>
                                <td><?= $user['last_name'] ?></td>
                                <td><?= $user['first_name'] ?></td>
                                <td><?= $user['email'] ?></td>
                                <?php
                                // Displaying fluent form data
                                foreach ($headers as $header):
                                    $field_name = $header['attributes']['name']; ?>
                                    <td><?= $user['form_data'][$field_name] ?? '' ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <style>
                /* Table */
                .table-container {
                    overflow-x: auto;
                    margin: 24px auto;
                }
                .export-table {
                    table-layout: fixed;
                    white-space: nowrap;
                }
                .export-table th,
                .export-table td {
                    overflow-x: hidden;
                    text-overflow: ellipsis;
                    width: 180px;
                    padding: 8px;
                    border-width: 0 !important;
                }
                .export-table th {
                    padding: 16px 8px;
                    background-color: #dde6f3;
                }
                .export-table td {
                    border-width: 0;
                }
                table tbody > tr:nth-child(odd) > td {
                    background-color: #f6faff;
                }
                /* Export button */
                .export-btn {
                    display: inline-block;
                    padding: 12px 24px;
                    color: #fff;
                    border-radius: 30px;
                    background-color: #00439b;
                    float: right;
                }
                .clear {
                    clear: both;
                }
            </style>
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
