<?php
namespace ElementorOsmovaPlugin\Traits;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Typography;
use \Elementor\Utils;
use \Elementor\Group_Control_Background;

trait Helper
{
    /**
     * Get all types of post.
     * @return array
     */
    public function eo_get_all_types_post()
    {
        $posts = get_posts([
            'post_type' => 'any',
            'post_style' => 'all_types',
            'post_status' => 'publish',
            'posts_per_page' => '-1',
        ]);

        if (!empty($posts)) {
            return wp_list_pluck($posts, 'post_title', 'ID');
        }

        return [];
    }

    /**
     * Query Controls
     *
     */
    protected function eo_query_controls()
    {
        $post_types = $this->eo_get_post_types();
        $post_types['by_id'] = __('Manual Selection', 'elementor-osmova-plugin');
        $taxonomies = get_taxonomies([], 'objects');

        if ('eo-content-ticker' === $this->get_name()) {
            $this->start_controls_section(
                'eo_section_content_ticker_filters',
                [
                    'label' => __('Dynamic Content Settings', 'elementor-osmova-plugin'),
                    'condition' => [
                        'eo_ticker_type' => 'dynamic',
                    ],
                ]
            );
        } else if ('eo-content-timeline' === $this->get_name()) {
            $this->start_controls_section(
                'eo_section_timeline__filters',
                [
                    'label' => __('Dynamic Content Settings', 'elementor-osmova-plugin'),
                    'condition' => [
                        'eo_content_timeline_choose' => 'dynamic',
                    ],
                ]
            );
        } else {
            $this->start_controls_section(
                'eo_section_post__filters',
                [
                    'label' => __('Query', 'elementor-osmova-plugin'),
                ]
            );
        }

        $this->add_control(
            'post_type',
            [
                'label' => __('Source', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::SELECT,
                'options' => $post_types,
                'default' => key($post_types),
            ]
        );

        $this->add_control(
            'posts_ids',
            [
                'label' => __('Search & Select', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::SELECT2,
                'options' => $this->eo_get_all_types_post(),
                'label_block' => true,
                'multiple' => true,
                'condition' => [
                    'post_type' => 'by_id',
                ],
            ]
        );

        $this->add_control(
            'authors', [
                'label' => __('Author', 'elementor-osmova-plugin'),
                'label_block' => true,
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'default' => [],
                'options' => $this->eo_get_authors(),
                'condition' => [
                    'post_type!' => 'by_id',
                ],
            ]
        );

        foreach ($taxonomies as $taxonomy => $object) {
            if (!isset($object->object_type[0]) || !in_array($object->object_type[0], array_keys($post_types))) {
                continue;
            }

            $this->add_control(
                $taxonomy . '_ids',
                [
                    'label' => $object->label,
                    'type' => Controls_Manager::SELECT2,
                    'label_block' => true,
                    'multiple' => true,
                    'object_type' => $taxonomy,
                    'options' => wp_list_pluck(get_terms($taxonomy), 'name', 'term_id'),
                    'condition' => [
                        'post_type' => $object->object_type,
                    ],
                ]
            );
        }

        $this->add_control(
            'post__not_in',
            [
                'label' => __('Exclude', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::SELECT2,
                'options' => $this->eo_get_all_types_post(),
                'label_block' => true,
                'post_type' => '',
                'multiple' => true,
                'condition' => [
                    'post_type!' => 'by_id',
                ],
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Posts Per Page', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::NUMBER,
                'default' => '4',
            ]
        );

        $this->add_control(
            'offset',
            [
                'label' => __('Offset', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::NUMBER,
                'default' => '0',
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->eo_get_post_orderby_options(),
                'default' => 'date',

            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'asc' => 'Ascending',
                    'desc' => 'Descending',
                ],
                'default' => 'desc',

            ]
        );

        $this->end_controls_section();
    }

    protected function eo_betterdocs_content_controls()
    {
        /**
         * ----------------------------------------------------------
         * Section: Content Area
         * ----------------------------------------------------------
         */
        $this->start_controls_section(
            'section_content_area',
            [
                'label' => __('Content Area', 'elementor-osmova-plugin'),
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'content_area_bg',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .betterdocs-categories-wrap'
            ]
        );

        $this->add_responsive_control(
            'content_area_padding',
            [
                'label' => esc_html__('Padding', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .betterdocs-categories-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_area_width',
            [
                'label' => __('Width', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 100,
                    'unit' => '%',
                ],
                'size_units' => ['%', 'px', 'em'],
                'range' => [
                    '%' => [
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .betterdocs-categories-wrap' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_area_max_width',
            [
                'label' => __('Max Width', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 1600,
                    'unit' => 'px',
                ],
                'size_units' => [ 'px', 'em'],
                'range' => [
                    'px' => [
                        'max' => 1600,
                        'step' => 1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .betterdocs-categories-wrap' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section(); # end of 'Content Area'
    }

    /**
     * Layout Controls For Post Block
     *
     */
    protected function eo_layout_controls()
    {
        $this->start_controls_section(
            'eo_section_post_timeline_layout',
            [
                'label' => __('Layout Settings', 'elementor-osmova-plugin'),
            ]
        );

        if ('eo-post-grid' === $this->get_name()) {
            $this->add_control(
                'eo_post_grid_columns',
                [
                    'label' => esc_html__('Number of Columns', 'elementor-osmova-plugin'),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'eo-col-4',
                    'options' => [
                        'eo-col-1' => esc_html__('Single Column', 'elementor-osmova-plugin'),
                        'eo-col-2' => esc_html__('Two Columns', 'elementor-osmova-plugin'),
                        'eo-col-3' => esc_html__('Three Columns', 'elementor-osmova-plugin'),
                        'eo-col-4' => esc_html__('Four Columns', 'elementor-osmova-plugin'),
                        'eo-col-5' => esc_html__('Five Columns', 'elementor-osmova-plugin'),
                        'eo-col-6' => esc_html__('Six Columns', 'elementor-osmova-plugin'),
                    ],
                ]
            );
        }

        if ('eo-post-block' === $this->get_name()) {
            $this->add_control(
                'grid_style',
                [
                    'label' => esc_html__('Post Block Style Preset', 'elementor-osmova-plugin'),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'post-block-style-default',
                    'options' => [
                        'post-block-style-default' => esc_html__('Default', 'elementor-osmova-plugin'),
                        'post-block-style-overlay' => esc_html__('Overlay', 'elementor-osmova-plugin'),
                    ],
                ]
            );
        }

        if ('eo-post-carousel' !== $this->get_name()) {

            /**
             * Show Read More
             * @uses ContentTimeLine Elements - EAE
             */
            if ('eo-content-timeline' === $this->get_name()) {

                $this->add_control(
                    'content_timeline_layout',
                    [
                        'label' => esc_html__('Layout', 'elementor-osmova-plugin'),
                        'type' => Controls_Manager::SELECT,
                        'default' => 'center',
                        'options' => [
                            'left'   => esc_html__('Right', 'elementor-osmova-plugin'),
                            'center' => esc_html__('Center', 'elementor-osmova-plugin'),
                            'right'  => esc_html__('Left', 'elementor-osmova-plugin'),
                        ],
                        'default'   => 'center'
                    ]
                );

                $this->add_control(
                    'date_position',
                    [
                        'label' => esc_html__('Date Position', 'elementor-osmova-plugin'),
                        'type' => Controls_Manager::SELECT,
                        'default' => 'inside',
                        'options' => [
                            'inside'   => esc_html__('Inside', 'elementor-osmova-plugin'),
                            'outside' => esc_html__('Outside', 'elementor-osmova-plugin')
                        ],
                        'default'   => 'inside',
                        'condition' => [
                            'content_timeline_layout!'  => 'center'
                        ]
                    ]
                );

            } else {
                $this->add_control(
                    'show_load_more',
                    [
                        'label' => __('Show Load More', 'elementor-osmova-plugin'),
                        'type'      => Controls_Manager::SWITCHER,
                        'label_on'  => __( 'Show', 'elementor-osmova-plugin' ),
                        'label_off' => __( 'Hide', 'elementor-osmova-plugin' ),
                        'return_value' => 'yes',
                        'default' => ''
                    ]
                );

                $this->add_control(
                    'show_load_more_text',
                    [
                        'label' => esc_html__('Label Text', 'elementor-osmova-plugin'),
                        'type' => Controls_Manager::TEXT,
                        'label_block' => false,
                        'default' => esc_html__('Load More', 'elementor-osmova-plugin'),
                        'condition' => [
                            'show_load_more' => 'yes',
                        ],
                    ]
                );
            }

        }

        if ('eo-content-timeline' !== $this->get_name()) {
            $this->add_control(
                'eo_show_image',
                [
                    'label' => __('Show Image', 'elementor-osmova-plugin'),
                    'type'      => Controls_Manager::SWITCHER,
                    'label_on'  => __( 'Show', 'elementor-osmova-plugin' ),
                    'label_off' => __( 'Hide', 'elementor-osmova-plugin' ),
                    'return_value' => 'yes',
                    'default' => 'yes'
                ]
            );

            $this->add_group_control(
                Group_Control_Image_Size::get_type(),
                [
                    'name' => 'image',
                    'exclude' => ['custom'],
                    'default' => 'medium',
                    'condition' => [
                        'eo_show_image' => 'yes',
                    ],
                ]
            );

        }

        if ('eo-content-timeline' === $this->get_name()) {

            $this->add_control(
                'eo_show_image_or_icon',
                [
                    'label' => __('Show Circle Image / Icon', 'elementor-osmova-plugin'),
                    'type' => Controls_Manager::CHOOSE,
                    'options' => [
                        'img' => [
                            'title' => __('Image', 'elementor-osmova-plugin'),
                            'icon' => 'fa fa-picture-o',
                        ],
                        'icon' => [
                            'title' => __('Icon', 'elementor-osmova-plugin'),
                            'icon' => 'fa fa-info',
                        ],
                        'bullet' => [
                            'title' => __('Bullet', 'elementor-osmova-plugin'),
                            'icon' => 'fa fa-circle',
                        ],
                    ],
                    'default' => 'icon',
                    'condition' => [
                        'eo_content_timeline_choose' => 'dynamic',
                    ],
                ]
            );

            $this->add_control(
                'eo_icon_image',
                [
                    'label' => esc_html__('Icon Image', 'elementor-osmova-plugin'),
                    'type' => Controls_Manager::MEDIA,
                    'default' => [
                        'url' => Utils::get_placeholder_image_src(),
                    ],
                    'condition' => [
                        'eo_show_image_or_icon' => 'img',
                    ],
                ]
            );
            $this->add_control(
                'eo_icon_image_size',
                [
                    'label' => esc_html__('Icon Image Size', 'elementor-osmova-plugin'),
                    'type' => Controls_Manager::SLIDER,
                    'default' => [
                        'size' => 24,
                    ],
                    'range' => [
                        'px' => [
                            'max' => 60,
                        ],
                    ],
                    'condition' => [
                        'eo_show_image_or_icon' => 'img',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .eo-content-timeline-img img' => 'width: {{SIZE}}px;',
                    ],
                ]
            );

            $this->add_control(
                'eo_content_timeline_circle_icon_new',
                [
                    'label' => esc_html__('Icon', 'elementor-osmova-plugin'),
                    'fa4compatibility' 		=> 'eo_content_timeline_circle_icon',
                    'type' => Controls_Manager::ICONS,
                    'default' => [
                        'value' => 'fas fa-pencil-alt',
                        'library' => 'fa-solid',
                    ],
                    'condition' => [
                        'eo_content_timeline_choose' => 'dynamic',
                        'eo_show_image_or_icon' => 'icon',
                    ],
                ]
            );

        }

        $this->add_control(
            'eo_show_title',
            [
                'label' => __('Show Title', 'elementor-osmova-plugin'),
                'type'      => Controls_Manager::SWITCHER,
                'label_on'  => __( 'Show', 'elementor-osmova-plugin' ),
                'label_off' => __( 'Hide', 'elementor-osmova-plugin' ),
                'return_value' => 'yes',
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'eo_show_excerpt',
            [
                'label' => __('Show excerpt', 'elementor-osmova-plugin'),
                'type'      => Controls_Manager::SWITCHER,
                'label_on'  => __( 'Show', 'elementor-osmova-plugin' ),
                'label_off' => __( 'Hide', 'elementor-osmova-plugin' ),
                'return_value' => 'yes',
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'eo_excerpt_length',
            [
                'label' => __('Excerpt Words', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::NUMBER,
                'default' => '10',
                'condition' => [
                    'eo_show_excerpt' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'excerpt_expanison_indicator',
            [
                'label' => esc_html__('Expanison Indicator', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::TEXT,
                'label_block' => false,
                'default' => esc_html__('...', 'elementor-osmova-plugin'),
                'condition' => [
                    'eo_show_excerpt' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'eo_show_read_more',
            [
                'label' => __('Show Read More', 'elementor-osmova-plugin'),
                'type'      => Controls_Manager::SWITCHER,
                'label_on'  => __( 'Show', 'elementor-osmova-plugin' ),
                'label_off' => __( 'Hide', 'elementor-osmova-plugin' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'eo_content_timeline_choose' => 'dynamic',
                ],
            ]
        );

        $this->add_control(
            'eo_read_more_text',
            [
                'label' => esc_html__('Label Text', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::TEXT,
                'label_block' => false,
                'default' => esc_html__('Read More', 'elementor-osmova-plugin'),
                'condition' => [
                    'eo_content_timeline_choose' => 'dynamic',
                    'eo_show_read_more' => 'yes',
                ],
            ]
        );

        if (
            'eo-post-grid' === $this->get_name()
            || 'eo-post-block' === $this->get_name()
            || 'eo-post-carousel' === $this->get_name()
        ) {
            $this->add_control(
                'eo_show_read_more_button',
                [
                    'label' => __('Show Read More Button', 'elementor-osmova-plugin'),
                    'type'      => Controls_Manager::SWITCHER,
                    'label_on'  => __( 'Show', 'elementor-osmova-plugin' ),
                    'label_off' => __( 'Hide', 'elementor-osmova-plugin' ),
                    'return_value' => 'yes',
                    'default' => 'yes'
                ]
            );

            $this->add_control(
                'read_more_button_text',
                [
                    'label' => __('Button Text', 'elementor-osmova-plugin'),
                    'type' => Controls_Manager::TEXT,
                    'default' => __('Read More', 'elementor-osmova-plugin'),
                    'condition' => [
                        'eo_show_read_more_button' => 'yes',
                    ],
                ]
            );
        }

        if ('eo-post-grid' === $this->get_name() || 'eo-post-block' === $this->get_name() || 'eo-post-carousel' === $this->get_name()) {

            $this->add_control(
                'eo_show_meta',
                [
                    'label' => __('Show Meta', 'elementor-osmova-plugin'),
                    'type'      => Controls_Manager::SWITCHER,
                    'label_on'  => __( 'Show', 'elementor-osmova-plugin' ),
                    'label_off' => __( 'Hide', 'elementor-osmova-plugin' ),
                    'return_value' => 'yes',
                    'default' => 'yes'
                ]
            );

            $this->add_control(
                'meta_position',
                [
                    'label' => esc_html__('Meta Position', 'elementor-osmova-plugin'),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'meta-entry-footer',
                    'options' => [
                        'meta-entry-header' => esc_html__('Entry Header', 'elementor-osmova-plugin'),
                        'meta-entry-footer' => esc_html__('Entry Footer', 'elementor-osmova-plugin'),
                    ],
                    'condition' => [
                        'eo_show_meta' => 'yes',
                    ],
                ]
            );

        }

        $this->end_controls_section();
    }

    protected function eo_read_more_button_style()
    {
        if (
            'eo-post-grid' === $this->get_name()
            || 'eo-post-block' === $this->get_name()
            || 'eo-post-carousel' === $this->get_name()
            || 'eo-post-list' === $this->get_name()
            || 'eo-post-timeline' === $this->get_name()
        ) {
            $this->start_controls_section(
                'eo_section_read_more_btn',
                [
                    'label' => __('Read More Button Style', 'elementor-osmova-plugin'),
                    'tab' => Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'eo_show_read_more_button' => 'yes',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'eo_post_read_more_btn_typography',
                    'selector' => '{{WRAPPER}} .eo-post-elements-readmore-btn',
                ]
            );

            $this->start_controls_tabs('read_more_button_tabs');

                $this->start_controls_tab(
                    'read_more_button_style_normal',
                    [
                        'label' => __( 'Normal', 'elementor-osmova-plugin' ),
                    ]
                );

                $this->add_control(
                    'eo_post_read_more_btn_color',
                    [
                        'label' => esc_html__('Text Color', 'elementor-osmova-plugin'),
                        'type' => Controls_Manager::COLOR,
                        'default' => '#61ce70',
                        'selectors' => [
                            '{{WRAPPER}} .eo-post-elements-readmore-btn' => 'color: {{VALUE}};',
                        ],
                    ]
                );
    
                $this->add_group_control(
                    Group_Control_Background::get_type(),
                    [
                        'name' => 'read_more_btn_background',
                        'label' => __( 'Background', 'elementor-osmova-plugin' ),
                        'types' => [ 'classic', 'gradient' ],
                        'selector' => '{{WRAPPER}} .eo-post-elements-readmore-btn',
                        'exclude' => [
                            'image'
                        ],
                    ]
                );
    
                $this->add_group_control(
                    Group_Control_Border::get_type(),
                    [
                        'name' => 'read_more_btn_border',
                        'label' => __( 'Border', 'elementor-osmova-plugin' ),
                        'selector' => '{{WRAPPER}} .eo-post-elements-readmore-btn',
                    ]
                );
    
                $this->add_responsive_control(
                    'read_more_btn_border_radius',
                    [
                        'label' => esc_html__('Border Radius', 'elementor-osmova-plugin'),
                        'type' => Controls_Manager::DIMENSIONS,
                        'size_units' => ['px', 'em', '%'],
                        'selectors' => [
                            '{{WRAPPER}} .eo-post-elements-readmore-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ]
                );

                $this->end_controls_tab();

                $this->start_controls_tab(
                    'read_more_button_style_hover',
                    [
                        'label' => __( 'Hover', 'elementor-osmova-plugin' ),
                    ]
                );

                $this->add_control(
                    'eo_post_read_more_btn_hover_color',
                    [
                        'label' => esc_html__('Text Color', 'elementor-osmova-plugin'),
                        'type' => Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .eo-post-elements-readmore-btn:hover' => 'color: {{VALUE}};',
                        ],
                    ]
                );
    
                $this->add_group_control(
                    Group_Control_Background::get_type(),
                    [
                        'name' => 'read_more_btn_hover_background',
                        'label' => __( 'Background', 'elementor-osmova-plugin' ),
                        'types' => [ 'classic', 'gradient' ],
                        'selector' => '{{WRAPPER}} .eo-post-elements-readmore-btn:hover',
                        'exclude' => [
                            'image'
                        ],
                    ]
                );
    
                $this->add_group_control(
                    Group_Control_Border::get_type(),
                    [
                        'name' => 'read_more_btn_hover_border',
                        'label' => __( 'Border', 'elementor-osmova-plugin' ),
                        'selector' => '{{WRAPPER}} .eo-post-elements-readmore-btn:hover',
                    ]
                );
    
                $this->add_responsive_control(
                    'read_more_btn_border_hover_radius',
                    [
                        'label' => esc_html__('Border Radius', 'elementor-osmova-plugin'),
                        'type' => Controls_Manager::DIMENSIONS,
                        'size_units' => ['px', 'em', '%'],
                        'selectors' => [
                            '{{WRAPPER}} .eo-post-elements-readmore-btn:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ]
                );
                
                $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_responsive_control(
                'eo_post_read_more_btn_padding',
                [
                    'label' => esc_html__('Padding', 'elementor-osmova-plugin'),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', 'em', '%'],
                    'selectors' => [
                        '{{WRAPPER}} .eo-post-elements-readmore-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'read_more_btn_margin',
                [
                    'label' => esc_html__('Margin', 'elementor-osmova-plugin'),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', 'em', '%'],
                    'selectors' => [
                        '{{WRAPPER}} .eo-post-elements-readmore-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->end_controls_section();
        }
    }

    /**
     * Load More Button Style
     *
     */
    protected function eo_load_more_button_style()
    {
        $this->start_controls_section(
            'eo_section_load_more_btn',
            [
                'label' => __('Load More Button Style', 'elementor-osmova-plugin'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_load_more' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'eo_post_grid_load_more_btn_padding',
            [
                'label' => esc_html__('Padding', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .eo-load-more-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'eo_post_grid_load_more_btn_margin',
            [
                'label' => esc_html__('Margin', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .eo-load-more-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'eo_post_grid_load_more_btn_typography',
                'selector' => '{{WRAPPER}} .eo-load-more-button',
            ]
        );

        $this->start_controls_tabs('eo_post_grid_load_more_btn_tabs');

        // Normal State Tab
        $this->start_controls_tab('eo_post_grid_load_more_btn_normal', ['label' => esc_html__('Normal', 'elementor-osmova-plugin')]);

        $this->add_control(
            'eo_post_grid_load_more_btn_normal_text_color',
            [
                'label' => esc_html__('Text Color', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .eo-load-more-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'eo_cta_btn_normal_bg_color',
            [
                'label' => esc_html__('Background Color', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::COLOR,
                'default' => '#29d8d8',
                'selectors' => [
                    '{{WRAPPER}} .eo-load-more-button' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'eo_post_grid_load_more_btn_normal_border',
                'label' => esc_html__('Border', 'elementor-osmova-plugin'),
                'selector' => '{{WRAPPER}} .eo-load-more-button',
            ]
        );

        $this->add_control(
            'eo_post_grid_load_more_btn_border_radius',
            [
                'label' => esc_html__('Border Radius', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eo-load-more-button' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'eo_post_grid_load_more_btn_shadow',
                'selector' => '{{WRAPPER}} .eo-load-more-button',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        // Hover State Tab
        $this->start_controls_tab('eo_post_grid_load_more_btn_hover', ['label' => esc_html__('Hover', 'elementor-osmova-plugin')]);

        $this->add_control(
            'eo_post_grid_load_more_btn_hover_text_color',
            [
                'label' => esc_html__('Text Color', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .eo-load-more-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'eo_post_grid_load_more_btn_hover_bg_color',
            [
                'label' => esc_html__('Background Color', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::COLOR,
                'default' => '#27bdbd',
                'selectors' => [
                    '{{WRAPPER}} .eo-load-more-button:hover' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'eo_post_grid_load_more_btn_hover_border_color',
            [
                'label' => esc_html__('Border Color', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .eo-load-more-button:hover' => 'border-color: {{VALUE}};',
                ],
            ]

        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'eo_post_grid_load_more_btn_hover_shadow',
                'selector' => '{{WRAPPER}} .eo-load-more-button:hover',
                'separator' => 'before',
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'eo_post_grid_loadmore_button_alignment',
            [
                'label' => __('Button Alignment', 'elementor-osmova-plugin'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __('Left', 'elementor-osmova-plugin'),
                        'icon' => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'elementor-osmova-plugin'),
                        'icon' => 'fa fa-align-center',
                    ],
                    'flex-end' => [
                        'title' => __('Right', 'elementor-osmova-plugin'),
                        'icon' => 'fa fa-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .eo-load-more-button-wrap' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    public function fix_old_query($settings)
    {
        $update_query = false;
        
        foreach ($settings as $key => $value) {
            if (strpos($key, 'eaeposts_') !== false) {
                $settings[str_replace('eaeposts_', '', $key)] = $value;
                $update_query = true;
            }
        }

        if ($update_query) {
            global $wpdb;
            
            $post_id = get_the_ID();
            $data = get_post_meta($post_id, '_elementor_data', true);
            $data = str_replace('eaeposts_', '', $data);
            $wpdb->update(
                $wpdb->postmeta,
                [
                    'meta_value' => $data,
                ],
                [
                    'post_id' => $post_id,
                    'meta_key' => '_elementor_data',
                ]
            );
        }

        return $settings;
    }

    public function eo_get_query_args($settings = [])
    {
        $settings = wp_parse_args($settings, [
            'post_type' => 'post',
            'posts_ids' => [],
            'orderby' => 'date',
            'order' => 'desc',
            'posts_per_page' => 3,
            'offset' => 0,
            'post__not_in' => [],
        ]);

        $args = [
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'ignore_sticky_posts' => 1,
            'post_status' => 'publish',
            'posts_per_page' => $settings['posts_per_page'],
            'offset' => $settings['offset']
        ];

        if ('by_id' === $settings['post_type']) {
            $args['post_type'] = 'any';
            $args['post__in'] = empty($settings['posts_ids']) ? [0] : $settings['posts_ids'];
        } else {
            $args['post_type'] = $settings['post_type'];
            $args['tax_query'] = [];

            $taxonomies = get_object_taxonomies($settings['post_type'], 'objects');

            foreach ($taxonomies as $object) {
                $setting_key = $object->name . '_ids';

                if (!empty($settings[$setting_key])) {
                    $args['tax_query'][] = [
                        'taxonomy' => $object->name,
                        'field' => 'term_id',
                        'terms' => $settings[$setting_key],
                    ];
                }
            }

            if (!empty($args['tax_query'])) {
                $args['tax_query']['relation'] = 'AND';
            }
        }

        if (!empty($settings['authors'])) {
            $args['author__in'] = $settings['authors'];
        }

        if (!empty($settings['post__not_in'])) {
            $args['post__not_in'] = $settings['post__not_in'];
        }

        return $args;
    }

    /**
     * Get All POst Types
     * @return array
     */
    public function eo_get_post_types()
    {
        $post_types = get_post_types(['public' => true, 'show_in_nav_menus' => true], 'objects');
        $post_types = wp_list_pluck($post_types, 'label', 'name');

        return array_diff_key($post_types, ['elementor_library', 'attachment']);
    }

    /**
     * Get Post Thumbnail Size
     *
     * @return array
     */
    public function eo_get_thumbnail_sizes()
    {
        $sizes = get_intermediate_image_sizes();
        foreach ($sizes as $s) {
            $ret[$s] = $s;
        }

        return $ret;
    }

    /**
     * POst Orderby Options
     *
     * @return array
     */
    public function eo_get_post_orderby_options()
    {
        $orderby = array(
            'ID' => 'Post ID',
            'author' => 'Post Author',
            'title' => 'Title',
            'date' => 'Date',
            'modified' => 'Last Modified Date',
            'parent' => 'Parent Id',
            'rand' => 'Random',
            'comment_count' => 'Comment Count',
            'menu_order' => 'Menu Order',
        );

        return $orderby;
    }

    /**
     * Get Post Categories
     *
     * @return array
     */
    public function eo_post_type_categories($type = 'term_id')
    {
        $terms = get_terms(array(
            'taxonomy' => 'category',
            'hide_empty' => true,
        ));

        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $options[$term->{$type}] = $term->name;
            }
        }

        return $options;
    }

    /**
     * WooCommerce Product Query
     *
     * @return array
     */
    public function eo_woocommerce_product_categories()
    {
        $terms = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
        ));

        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $options[$term->slug] = $term->name;
            }
            return $options;
        }
    }

    /**
     * WooCommerce Get Product By Id
     *
     * @return array
     */
    public function eo_woocommerce_product_get_product_by_id()
    {
        $postlist = get_posts(array(
            'post_type' => 'product',
            'showposts' => 9999,
        ));
        $options = array();

        if (!empty($postlist) && !is_wp_error($postlist)) {
            foreach ($postlist as $post) {
                $options[$post->ID] = $post->post_title;
            }
            return $options;

        }
    }

    /**
     * WooCommerce Get Product Category By Id
     *
     * @return array
     */
    public function eo_woocommerce_product_categories_by_id()
    {
        $terms = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
        ));

        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $options[$term->term_id] = $term->name;
            }
            return $options;
        }

    }

    /**
     * Get Contact Form 7 [ if exists ]
     */
    public function eo_select_contact_form()
    {
        $options = array();

        if (function_exists('wpcf7')) {
            $wpcf7_form_list = get_posts(array(
                'post_type' => 'wpcf7_contact_form',
                'showposts' => 999,
            ));
            $options[0] = esc_html__('Select a Contact Form', 'elementor-osmova-plugin');
            if (!empty($wpcf7_form_list) && !is_wp_error($wpcf7_form_list)) {
                foreach ($wpcf7_form_list as $post) {
                    $options[$post->ID] = $post->post_title;
                }
            } else {
                $options[0] = esc_html__('Create a Form First', 'elementor-osmova-plugin');
            }
        }
        return $options;
    }

    /**
     * Get Gravity Form [ if exists ]
     *
     * @return array
     */
    public function eo_select_gravity_form()
    {
        $options = array();

        if (class_exists('GFCommon')) {
            $gravity_forms = \RGFormsModel::get_forms(null, 'title');

            if (!empty($gravity_forms) && !is_wp_error($gravity_forms)) {

                $options[0] = esc_html__('Select Gravity Form', 'elementor-osmova-plugin');
                foreach ($gravity_forms as $form) {
                    $options[$form->id] = $form->title;
                }

            } else {
                $options[0] = esc_html__('Create a Form First', 'elementor-osmova-plugin');
            }
        }

        return $options;
    }

    /**
     * Get WeForms Form List
     *
     * @return array
     */
    public function eo_select_weform()
    {
        $wpuf_form_list = get_posts(array(
            'post_type' => 'wpuf_contact_form',
            'showposts' => 999,
        ));

        $options = array();

        if (!empty($wpuf_form_list) && !is_wp_error($wpuf_form_list)) {
            $options[0] = esc_html__('Select weForm', 'elementor-osmova-plugin');
            foreach ($wpuf_form_list as $post) {
                $options[$post->ID] = $post->post_title;
            }
        } else {
            $options[0] = esc_html__('Create a Form First', 'elementor-osmova-plugin');
        }

        return $options;
    }

    /**
     * Get Ninja Form List
     *
     * @return array
     */
    public function eo_select_ninja_form()
    {
        $options = array();

        if (class_exists('Ninja_Forms')) {
            $contact_forms = Ninja_Forms()->form()->get_forms();

            if (!empty($contact_forms) && !is_wp_error($contact_forms)) {

                $options[0] = esc_html__('Select Ninja Form', 'elementor-osmova-plugin');

                foreach ($contact_forms as $form) {
                    $options[$form->get_id()] = $form->get_setting('title');
                }
            }
        } else {
            $options[0] = esc_html__('Create a Form First', 'elementor-osmova-plugin');
        }

        return $options;
    }

    /**
     * Get Caldera Form List
     *
     * @return array
     */
    public function eo_select_caldera_form()
    {
        $options = array();

        if (class_exists('Caldera_Forms')) {
            $contact_forms = \Caldera_Forms_Forms::get_forms(true, true);

            if (!empty($contact_forms) && !is_wp_error($contact_forms)) {
                $options[0] = esc_html__('Select Caldera Form', 'elementor-osmova-plugin');
                foreach ($contact_forms as $form) {
                    $options[$form['ID']] = $form['name'];
                }
            }
        } else {
            $options[0] = esc_html__('Create a Form First', 'elementor-osmova-plugin');
        }

        return $options;
    }

    /**
     * Get WPForms List
     *
     * @return array
     */
    public function eo_select_wpforms_forms()
    {
        $options = array();

        if (class_exists('\WPForms\WPForms')) {
            $args = array(
                'post_type' => 'wpforms',
                'posts_per_page' => -1,
            );

            $contact_forms = get_posts($args);

            if (!empty($contact_forms) && !is_wp_error($contact_forms)) {
                $options[0] = esc_html__('Select a WPForm', 'elementor-osmova-plugin');
                foreach ($contact_forms as $post) {
                    $options[$post->ID] = $post->post_title;
                }
            }
        } else {
            $options[0] = esc_html__('Create a Form First', 'elementor-osmova-plugin');
        }

        return $options;
    }

    /**
     * Get FluentForms List
     * 
     * @return array
     */
    public static function eo_select_fluent_forms()
    {

        $options = array();

        if(defined('FLUENTFORM')) {
            global $wpdb;
            
            $result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fluentform_forms" );
            if($result) {
                $options[0] = esc_html__('Select a Fluent Form', 'elementor-osmova-plugin');
                foreach($result as $form) {
                    $options[$form->id] = $form->title;
                }
            }else {
                $options[0] = esc_html__('Create a Form First', 'elementor-osmova-plugin');
            }
        }

        return $options;

    }

    /**
     * Get all elementor page templates
     *
     * @return array
     */
    public function eo_get_page_templates($type = null)
    {
        $args = [
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
        ];

        if ($type) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'elementor_library_type',
                    'field' => 'slug',
                    'terms' => $type,
                ],
            ];
        }

        $page_templates = get_posts($args);
        $options = array();

        if (!empty($page_templates) && !is_wp_error($page_templates)) {
            foreach ($page_templates as $post) {
                $options[$post->ID] = $post->post_title;
            }
        }
        return $options;
    }

    /**
     * Get all Authors
     *
     * @return array
     */
    public function eo_get_authors()
    {
        $users = get_users([
            'who' => 'authors',
            'has_published_posts' => true,
            'fields' => [
                'ID',
                'display_name',
            ],
        ]);

        if (!empty($users)) {
            return wp_list_pluck($users, 'display_name', 'ID');
        }

        return [];
    }

    /**
     * Get all Tags
     *
     * @return array
     */
    public function eo_get_tags()
    {
        $options = array();
        $tags = get_tags();

        foreach ($tags as $tag) {
            $options[$tag->term_id] = $tag->name;
        }

        return $options;
    }

    /**
     * Get all Posts
     *
     * @return array
     */
    public function eo_get_posts()
    {
        $post_list = get_posts(array(
            'post_type' => 'post',
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => -1,
        ));

        $posts = array();

        if (!empty($post_list) && !is_wp_error($post_list)) {
            foreach ($post_list as $post) {
                $posts[$post->ID] = $post->post_title;
            }
        }

        return $posts;
    }

    /**
     * Get all Pages
     *
     * @return array
     */
    public function eo_get_pages()
    {
        $page_list = get_posts(array(
            'post_type' => 'page',
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => -1,
        ));

        $pages = array();

        if (!empty($page_list) && !is_wp_error($page_list)) {
            foreach ($page_list as $page) {
                $pages[$page->ID] = $page->post_title;
            }
        }

        return $pages;
    }

    /**
     * This function is responsible for get the post data.
     * It will return HTML markup with AJAX call and with normal call.
     *
     * @return string of an html markup with AJAX call.
     * @return array of content and found posts count without AJAX call.
     */
    public function eo_load_more_ajax()
    {
        parse_str($_REQUEST['args'], $args);
        parse_str($_REQUEST['settings'], $settings);

        $class = '\\' . str_replace('\\\\', '\\', $_REQUEST['class']);
        $args['offset'] = (int) $args['offset'] + (((int) $_REQUEST['page'] - 1) * (int) $args['posts_per_page']);

        if(isset($_REQUEST['taxonomy']) && $_REQUEST['taxonomy']['taxonomy'] != 'all') {
            $args['tax_query'] = [
                $_REQUEST['taxonomy']
            ];
        }

        $html = $class::__render_template($args, $settings);

        echo $html;
        wp_die();
    }

    /**
     * Twitter Feed
     *
     * @since 3.0.6
     */
    public function twitter_feed_render_items($id, $settings, $class = '')
    {
        $token = get_option($id . '_' . $settings['eo_twitter_feed_ac_name'] . '_tf_token');
        $items = get_transient($id . '_' . $settings['eo_twitter_feed_ac_name'] . '_tf_cache');
        $html = '';

        if (empty($settings['eo_twitter_feed_consumer_key']) || empty($settings['eo_twitter_feed_consumer_secret'])) {
            return;
        }

        if ($items === false) {
            if (empty($token)) {
                $credentials = base64_encode($settings['eo_twitter_feed_consumer_key'] . ':' . $settings['eo_twitter_feed_consumer_secret']);

                add_filter('https_ssl_verify', '__return_false');

                $response = wp_remote_post('https://api.twitter.com/oauth2/token', [
                    'method' => 'POST',
                    'httpversion' => '1.1',
                    'blocking' => true,
                    'headers' => [
                        'Authorization' => 'Basic ' . $credentials,
                        'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
                    ],
                    'body' => ['grant_type' => 'client_credentials'],
                ]);

                $body = json_decode(wp_remote_retrieve_body($response));

                if ($body) {
                    update_option($id . '_' . $settings['eo_twitter_feed_ac_name'] . '_tf_token', $body->access_token);
                    $token = $body->access_token;
                }
            }

            $args = array(
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array(
                    'Authorization' => "Bearer $token",
                ),
            );

            add_filter('https_ssl_verify', '__return_false');

            $response = wp_remote_get('https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=' . $settings['eo_twitter_feed_ac_name'] . '&count=999&tweet_mode=extended', [
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => [
                    'Authorization' => "Bearer $token",
                ],
            ]);

            if (!is_wp_error($response)) {
                $items = json_decode(wp_remote_retrieve_body($response), true);
                set_transient($id . '_' . $settings['eo_twitter_feed_ac_name'] . '_tf_cache', $items, 1800);
            }
        }

        if (empty($items)) {
            return;
        }

        if ($settings['eo_twitter_feed_hashtag_name']) {
            foreach ($items as $key => $item) {
                $match = false;

                if ($item['entities']['hashtags']) {
                    foreach ($item['entities']['hashtags'] as $tag) {
                        if (strcasecmp($tag['text'], $settings['eo_twitter_feed_hashtag_name']) == 0) {
                            $match = true;
                        }
                    }
                }

                if ($match == false) {
                    unset($items[$key]);
                }
            }
        }

        $items = array_splice($items, 0, $settings['eo_twitter_feed_post_limit']);

        foreach ($items as $item) {
            $html .= '<div class="eo-twitter-feed-item ' . $class . '">
				<div class="eo-twitter-feed-item-inner">
				    <div class="eo-twitter-feed-item-header clearfix">';
            if ($settings['eo_twitter_feed_show_avatar'] == 'true') {
                $html .= '<a class="eo-twitter-feed-item-avatar avatar-' . $settings['eo_twitter_feed_avatar_style'] . '" href="//twitter.com/' . $settings['eo_twitter_feed_ac_name'] . '" target="_blank">
                                <img src="' . $item['user']['profile_image_url_https'] . '">
                            </a>';
            }
            $html .= '<a class="eo-twitter-feed-item-meta" href="//twitter.com/' . $settings['eo_twitter_feed_ac_name'] . '" target="_blank">';
            if ($settings['eo_twitter_feed_show_icon'] == 'true') {
                $html .= '<i class="fab fa-twitter eo-twitter-feed-item-icon"></i>';
            }

            $html .= '<span class="eo-twitter-feed-item-author">' . $item['user']['name'] . '</span>
                        </a>';
            if ($settings['eo_twitter_feed_show_date'] == 'true') {
                $html .= '<span class="eo-twitter-feed-item-date">' . sprintf(__('%s ago', 'elementor-osmova-plugin'), human_time_diff(strtotime($item['created_at']))) . '</span>';
            }
            $html .= '</div>
                    <div class="eo-twitter-feed-item-content">
                        <p>' . substr(str_replace(@$item['entities']['urls'][0]['url'], '', $item['full_text']), 0, $settings['eo_twitter_feed_content_length']) . '...</p>';

            if ($settings['eo_twitter_feed_show_read_more'] == 'true') {
                $html .= '<a href="//twitter.com/' . @$item['user']['screen_name'] . '/status/' . $item['id'] . '" target="_blank" class="read-more-link">Read More <i class="fas fa-angle-double-right"></i></a>';
            }
            $html .= '</div>
                    ' . (isset($item['extended_entities']['media'][0]) && $settings['eo_twitter_feed_media'] == 'true' ? ($item['extended_entities']['media'][0]['type'] == 'photo' ? '<img src="' . $item['extended_entities']['media'][0]['media_url_https'] . '">' : '') : '') . '
                </div>
			</div>';
        }

        return $html;
    }

    /**
     * Facebook Feed
     *
     * @since 3.4.0
     */
    public function facebook_feed_render_items() {
        // check if ajax request
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'facebook_feed_load_more') {
            // check ajax referer
            check_ajax_referer('elementor-osmova-plugin', 'security');
            
            // init vars
            $page = $_REQUEST['page'];
            parse_str($_REQUEST['settings'], $settings);
        } else {
            // init vars
            $page = 0;
            $settings = $this->get_settings_for_display();
        }

        $html = '';
        $page_id = $settings['eo_facebook_feed_page_id'];
        $token = $settings['eo_facebook_feed_access_token'];
        
        if(empty($page_id) || empty($token)) {
            return;
        }
        
        $key = 'eo_facebook_feed_' . substr(str_rot13(str_replace('.', '', $page_id . $token)), 32);

        if (get_transient($key) === false) {
            $facebook_data = wp_remote_retrieve_body(wp_remote_get("https://graph.facebook.com/v4.0/{$page_id}/posts?fields=status_type,created_time,from,message,story,full_picture,permalink_url,attachments.limit(1){type,media_type,title,description,unshimmed_url},comments.summary(total_count),reactions.summary(total_count)&access_token={$token}"));
            set_transient($key, $facebook_data, 1800);
        } else {
            $facebook_data = get_transient($key);
        }

        $facebook_data = json_decode($facebook_data, true);
        
        if (isset($facebook_data['data'])) {
            $facebook_data = $facebook_data['data'];
        } else {
            return;
        }
        
        switch ($settings['eo_facebook_feed_sort_by']) {
            case 'least-recent':
            $facebook_data = array_reverse($facebook_data);
            break;
        }
        
        $items = array_splice($facebook_data, ($page * $settings['eo_facebook_feed_image_count']['size']), $settings['eo_facebook_feed_image_count']['size']);

        foreach($items as $item) {
            $message = wp_trim_words((isset($item['message']) ? $item['message'] : (isset($item['story']) ? $item['story'] : '')), $settings['eo_facebook_feed_message_max_length']['size'], '...');
            $photo = (isset($item['full_picture']) ? $item['full_picture'] : '');
            $likes = (isset($item['reactions']) ? $item['reactions']['summary']['total_count'] : 0);
            $comments = (isset($item['comments']) ? $item['comments']['summary']['total_count'] : 0);

            if($settings['eo_facebook_feed_layout'] == 'card') {
                $html .= '<div class="eo-facebook-feed-item">
                    <div class="eo-facebook-feed-item-inner">
                        <header class="eo-facebook-feed-item-header clearfix">
                            <div class="eo-facebook-feed-item-user clearfix">
                                <a href="https://www.facebook.com/' . $page_id . '" target="' . ($settings['eo_facebook_feed_link_target'] == 'yes' ? '_blank' : '_self') . '"><img src="https://graph.facebook.com/v4.0/' . $page_id . '/picture" alt="' . $item['from']['name'] . '" class="eo-facebook-feed-avatar"></a>
                                <a href="https://www.facebook.com/' . $page_id . '" target="' . ($settings['eo_facebook_feed_link_target'] == 'yes' ? '_blank' : '_self') . '"><p class="eo-facebook-feed-username">' . $item['from']['name'] . '</p></a>
                            </div>';

                            if ($settings['eo_facebook_feed_date']) {
                                $html .= '<a href="' . $item['permalink_url'] . '" target="' . ($settings['eo_facebook_feed_link_target'] ? '_blank' : '_self') . '" class="eo-facebook-feed-post-time"><i class="far fa-clock" aria-hidden="true"></i> ' . date("d M Y", strtotime($item['created_time'])) . '</a>';
                            }
                        $html .= '</header>';
                        
                        if ($settings['eo_facebook_feed_message'] && !empty($message)) {
                            $html .= '<div class="eo-facebook-feed-item-content">
                                <p class="eo-facebook-feed-message">' . esc_html($message) . '</p>
                            </div>';
                        }

                        if(!empty($photo) || isset($item['attachments']['data'])) {
                            $html .= '<div class="eo-facebook-feed-preview-wrap">';
                                if($item['status_type'] == 'shared_story') {
                                    $html .= '<a href="' . $item['permalink_url'] . '" target="' . ($settings['eo_facebook_feed_link_target'] == 'yes' ? '_blank' : '_self') . '" class="eo-facebook-feed-preview-img">';
                                        if($item['attachments']['data'][0]['media_type'] == 'video') {
                                            $html .= '<img class="eo-facebook-feed-img" src="' . $photo . '">
                                            <div class="eo-facebook-feed-preview-overlay"><i class="far fa-play-circle" aria-hidden="true"></i></div>';
                                        } else {
                                            $html .= '<img class="eo-facebook-feed-img" src="' . $photo . '">';
                                        }
                                    $html .= '</a>';
    
                                    $html .= '<div class="eo-facebook-feed-url-preview">
                                        <p class="eo-facebook-feed-url-host">' . parse_url($item['attachments']['data'][0]['unshimmed_url'])['host'] . '</p>
                                        <h2 class="eo-facebook-feed-url-title">' . $item['attachments']['data'][0]['title'] . '</h2>
                                        <p class="eo-facebook-feed-url-description">' . @$item['attachments']['data'][0]['description'] . '</p>
                                    </div>';
                                } else if ($item['status_type'] == 'added_video') {
                                    $html .= '<a href="' . $item['permalink_url'] . '" target="' . ($settings['eo_facebook_feed_link_target'] == 'yes' ? '_blank' : '_self') . '" class="eo-facebook-feed-preview-img">
                                        <img class="eo-facebook-feed-img" src="' . $photo . '">
                                        <div class="eo-facebook-feed-preview-overlay"><i class="far fa-play-circle" aria-hidden="true"></i></div>
                                    </a>';
                                } else {
                                    $html .= '<a href="' . $item['permalink_url'] . '" target="' . ($settings['eo_facebook_feed_link_target'] == 'yes' ? '_blank' : '_self') . '" class="eo-facebook-feed-preview-img">
                                        <img class="eo-facebook-feed-img" src="' . $photo . '">
                                    </a>';
                                }
                            $html .= '</div>';
                        }

                        if ($settings['eo_facebook_feed_likes'] || $settings['eo_facebook_feed_comments']) {
                            $html .= '<footer class="eo-facebook-feed-item-footer">
                                <div class="clearfix">';
                                    if ($settings['eo_facebook_feed_likes']) {
                                        $html .= '<span class="eo-facebook-feed-post-likes"><i class="far fa-thumbs-up" aria-hidden="true"></i> ' . $likes . '</span>';
                                    }
                                    if ($settings['eo_facebook_feed_comments']) {
                                        $html .= '<span class="eo-facebook-feed-post-comments"><i class="far fa-comments" aria-hidden="true"></i> ' . $comments . '</span>';
                                    }
                                $html .= '</div>
                            </footer>';
                        }
                    $html .= '</div>
                </div>';
            } else {
                $html .= '<a href="' . $item['permalink_url'] . '" target="' . ($settings['eo_facebook_feed_link_target'] ? '_blank' : '_self') . '" class="eo-facebook-feed-item">
                    <div class="eo-facebook-feed-item-inner">
                        <img class="eo-facebook-feed-img" src="' . (empty($photo) ? eo_PLUGIN_URL . 'assets/front-end/img/flexia-preview.jpg' : $photo) . '">';
                        
                        if ($settings['eo_facebook_feed_likes'] || $settings['eo_facebook_feed_comments']) {
                            $html .= '<div class="eo-facebook-feed-item-overlay">
                                <div class="eo-facebook-feed-item-overlay-inner">
                                    <div class="eo-facebook-feed-meta">';
                                        if ($settings['eo_facebook_feed_likes']) {
                                            $html .= '<span class="eo-facebook-feed-post-likes"><i class="far fa-thumbs-up" aria-hidden="true"></i> ' . $likes . '</span>';
                                        }
                                        if ($settings['eo_facebook_feed_comments']) {
                                            $html .= '<span class="eo-facebook-feed-post-comments"><i class="far fa-comments" aria-hidden="true"></i> ' . $comments . '</span>';
                                        }
                                    $html .= '</div>
                                </div>
                            </div>';
                        }
                    $html .= '</div>
                </a>';
            }
        }

        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'facebook_feed_load_more') {
            wp_send_json([
                'num_pages' => ceil(count($facebook_data) / $settings['eo_facebook_feed_image_count']['size']),
                'html' => $html
            ]);
        }

        return $html;
    }
}
