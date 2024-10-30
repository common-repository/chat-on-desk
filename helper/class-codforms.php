<?php
/**
 * Emementer Widget helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */

use Helper\ElementorWidget;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Group_Control_Background;
use Elementor\Core\Schemes\Color as Scheme_Color;


if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Emementer Widget helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * SMSAlertForms class
 */
class CodForms extends Widget_Base
{
    
    /**
     * Get name function.
     *
     * @return array
     */
    public function get_name()
    {
        return 'chatondesk-form-widget';
    }

    /**
     * Get title function.
     *
     * @return array
     */
    public function get_title()
    {
        return __('Chat On Desk Forms', 'chat-on-desk');
    }

    /**
     * Get icon function.
     *
     * @return array
     */
    public function get_icon()
    {
        return 'eicon-form-horizontal';
    }

    /**
     * Get keywords function.     
     *
     * @return array
     */
    public function get_keywords()
    {
        return [
            'chatondeskform',
            'chatondeskform',
            'chatondesk form',
            'chatondeskform forms',
            'contact form',
            'form',
            'elementor form',
        ];
    }

    /**
     * Get  categories function.     
     *
     * @return array
     */
    public function get_categories()
    {        
        return ['general'];
    }

    /**
     * Get style depends function.     
     *
     * @return array
     */
    public function get_style_depends()
    {
        return [
            'chatondesk-form-styles',
            'chatondesk-public-default',
        ];
    }
     
    /**
     * Get  scrip depends function.     
     *
     * @return array
     */
    public function get_script_depends()
    {
        return ['chatondesk-elementor'];
    }

    /**
     * Register controls function.     
     *
     * @return array
     */
    protected function register_controls()
    {
        $this->registerGeneralControls();        
        $this->registerTitleDescriptionStyleControls();
        $this->registerFormContainerStyleControls();
        $this->registerLabelStyleControls();
        $this->registerInputTextareaStyleControls();
        $this->registerPlaceholderStyleControls();       
        $this->registerSubmitButtonStyleControls();
        
    }

    /**
     * Register general controls function.     
     *
     * @return array
     */
    protected function registerGeneralControls()
    {
        $this->start_controls_section(
            'section_chatondesk_form',
            [
                'label' => __('Chat On Desk Forms', 'chat-on-desk'),
            ]
        );

        $this->add_control(
            'form_list',
            [
                'label'       => esc_html__('Chat On Desk Forms', 'chat-on-desk'),
                'type'        => Controls_Manager::SELECT,
                'label_block' => true,
                'options'     => array('0'=>'Select Form','1'=>'Signup With Mobile','2'=>'Login With Otp','3'=>'Share Cart Button'),
            'default' => '0',
                
                
            ]
        );

        $this->add_control(
            'custom_title_description',
            [
                'label'        => __('Enable Title & Description', 'chat-on-desk'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'chat-on-desk'),
                'label_off'    => __('No', 'chat-on-desk'),
                'return_value' => 'yes',
            'condition' => [
                    'form_list' => ['1','2'],
                ],
            ]
        );

        $this->add_control(
            'form_title_custom',
            [
                'label'       => esc_html__('Title', 'chat-on-desk'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '',
                'condition'   => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_description_custom',
            [
                'label'     => esc_html__('Description', 'chat-on-desk'),
                'type'      => Controls_Manager::TEXTAREA,
                'default'   => '',
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'labels_switch',
            [
                'label'        => __('Enable Label', 'chat-on-desk'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'label_on'     => __('Show', 'chat-on-desk'),
                'label_off'    => __('Hide', 'chat-on-desk'),
                'return_value' => 'yes',
            'condition' => [
                    'form_list' => ['1','2'],
                ],
            ]
        ); 
        $this->add_control(
            'cod_ele_f_mobile_lbl',
            [
                'label'        => __('Label', 'chat-on-desk'),
                'type'         => "text",
                'placeholder'      => 'Enter Label',
            'condition' => [
                    'form_list' => ['1','2'],
					'labels_switch'=>'yes',
                ],
                
            ]
        );        

        $this->add_control(
            'placeholder_switch',
            [
                'label'        => __('Enable Placeholder', 'chat-on-desk'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'label_on'     => __('Show', 'chat-on-desk'),
                'label_off'    => __('Hide', 'chat-on-desk'),
                'return_value' => 'yes',
            'condition' => [
                    'form_list' => ['1','2'],
                ],
            ]
        );
        $this->add_control(
            'cod_ele_f_mobile_place',
            [
                'label'        => __('Placeholder', 'chat-on-desk'),
                'type'         => "text",
                'placeholder'      => 'Enter Placeholder',
            'condition' => [
                    'form_list' => ['1','2'],
					'placeholder_switch'=>'yes',
                ],
                
            ]
        );
        $this->add_control(
            'cod_ele_f_mobile_botton',
            [
                'label'        => __('Button Text', 'chat-on-desk'),
                'type'         => "text",
                'placeholder'      => 'Enter Button Text',
            'condition' => [
                    'form_list' => ['1','2'],
                ],
                
            ]
        );
        $this->end_controls_section();
    }    
    
    /**
     * Register title description style controls function.     
     *
     * @return array
     */
    protected function registerTitleDescriptionStyleControls()
    {
        $this->start_controls_section(
            'section_form_title_style',
            [
                'label'     => __('Title & Description', 'chat-on-desk'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'heading_alignment',
            [
                'label'   => __('Alignment', 'chat-on-desk'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'chat-on-desk'),
                        'icon'  => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'chat-on-desk'),
                        'icon'  => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'chat-on-desk'),
                        'icon'  => 'fa fa-align-right',
                    ],
                ],
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-title'       => 'text-align: {{VALUE}};',
                    '{{WRAPPER}} .chatondeskform-widget-description' => 'text-align: {{VALUE}};',
                ],
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'heading_title',
            [
                'label'     => __('Title', 'chat-on-desk'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_title_text_color',
            [
                'label'     => __('Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-title' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_title_typography',
                'label'     => __('Typography', 'chat-on-desk'),
                'selector'  => '{{WRAPPER}} .chatondeskform-widget-title',
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_title_margin',
            [
                'label'              => __('Margin', 'chat-on-desk'),
                'type'               => Controls_Manager::DIMENSIONS,
                'size_units'         => ['px', 'em', '%'],
                'allowed_dimensions' => 'vertical',
                'placeholder'        => [
                    'top'    => '',
                    'right'  => 'auto',
                    'bottom' => '',
                    'left'   => 'auto',
                ],
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_title_padding',
            [
                'label'      => esc_html__('Padding', 'chat-on-desk'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_description',
            [
                'label'     => __('Description', 'chat-on-desk'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'heading_description_text_color',
            [
                'label'     => __('Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-description' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'heading_description_typography',
                'label'     => __('Typography', 'chat-on-desk'),
                'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
                'selector'  => '{{WRAPPER}} .chatondeskform-widget-description',
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'heading_description_margin',
            [
                'label'              => __('Margin', 'chat-on-desk'),
                'type'               => Controls_Manager::DIMENSIONS,
                'size_units'         => ['px', 'em', '%'],
                'allowed_dimensions' => 'vertical',
                'placeholder'        => [
                    'top'    => '',
                    'right'  => 'auto',
                    'bottom' => '',
                    'left'   => 'auto',
                ],
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'heading_description_padding',
            [
                'label'      => esc_html__('Padding', 'chat-on-desk'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-description' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }
 
    /**
     * Register form container style controls function.     
     *
     * @return array
     */
    protected function registerFormContainerStyleControls()
    {
        $this->start_controls_section(
            'section_form_container_style',
            [
                'label' => __('Form Container', 'chat-on-desk'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'form_container_background',
                'label'    => __('Background', 'chat-on-desk'),
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .chatondeskform-widget-wrapper',
            ]
        );

        $this->add_control(
            'form_container_link_color',
            [
                'label'     => __('Link Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_container_max_width',
            [
                'label'      => esc_html__('Max Width', 'chat-on-desk'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range'      => [
                    'px' => [
                        'min' => 10,
                        'max' => 1500,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 80,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_container_alignment',
            [
                'label'       => esc_html__('Alignment', 'chat-on-desk'),
                'type'        => Controls_Manager::CHOOSE,
                'label_block' => true,
                'options'     => [
                    'default' => [
                        'title' => __('Default', 'chat-on-desk'),
                        'icon'  => 'fa fa-ban',
                    ],
                    'left' => [
                        'title' => esc_html__('Left', 'chat-on-desk'),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'chat-on-desk'),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'chat-on-desk'),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'default',
            ]
        );

        $this->add_responsive_control(
            'form_container_margin',
            [
                'label'      => esc_html__('Margin', 'chat-on-desk'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_container_padding',
            [
                'label'      => esc_html__('Padding', 'chat-on-desk'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'form_container_border',
                'selector' => '{{WRAPPER}} .chatondeskform-widget-wrapper',
            ]
        );

        $this->add_control(
            'form_container_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'chat-on-desk'),
                'type'       => Controls_Manager::DIMENSIONS,
                'separator'  => 'before',
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'form_container_box_shadow',
                'selector' => '{{WRAPPER}} .chatondeskform-widget-wrapper',
            ]
        );

        $this->end_controls_section();
    }
    
    /**
     * Register label style controls function.     
     *
     * @return array
     */
    protected function registerLabelStyleControls()
    {
        $this->start_controls_section(
            'section_form_label_style',
            [
                'label' => __('Labels', 'chat-on-desk'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'form_label_text_color',
            [
                'label'     => __('Text Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper .cod-lwo-form label' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'form_label_typography',
                'label'    => __('Typography', 'chat-on-desk'),
                'selector' => '{{WRAPPER}} .chatondeskform-widget-wrapper .cod-lwo-form label',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register input textarea style controls function.     
     *
     * @return array
     */
    protected function registerInputTextareaStyleControls()
    {
        $this->start_controls_section(
            'section_form_fields_style',
            [
                'label' => __('Input & Textarea', 'chat-on-desk'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'input_alignment',
            [
                'label'   => __('Alignment', 'chat-on-desk'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'chat-on-desk'),
                        'icon'  => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'chat-on-desk'),
                        'icon'  => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'chat-on-desk'),
                        'icon'  => 'fa fa-align-right',
                    ],
                ],
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group select' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_form_fields_style');

        $this->start_controls_tab(
            'tab_form_fields_normal',
            [
                'label' => __('Normal', 'chat-on-desk'),
            ]
        );

        $this->add_control(
            'form_field_bg_color',
            [
                'label'     => __('Background Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):not(.select2-search__field), {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group select, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group .select2-container--default .select2-selection--multiple' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'form_field_text_color',
            [
                'label'     => __('Text Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group select' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'form_field_border',
                'label'       => __('Border', 'chat-on-desk'),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):not(.select2-search__field), {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group select,  {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group .select2-container--default .select2-selection--multiple',
                'separator'   => 'before',
            ]
        );

        $this->add_control(
            'form_field_radius',
            [
                'label'      => __('Border Radius', 'chat-on-desk'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group select,  {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group .select2-container--default .select2-selection--multiple' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_field_text_indent',
            [
                'label' => __('Text Indent', 'chat-on-desk'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 60,
                        'step' => 1,
                    ],
                    '%' => [
                        'min'  => 0,
                        'max'  => 30,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group select' => 'text-indent: {{SIZE}}{{UNIT}}',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'form_input_width',
            [
                'label' => __('Input Width', 'chat-on-desk'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group select' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_input_height',
            [
                'label' => __('Input Height', 'chat-on-desk'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 80,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group select' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_textarea_width',
            [
                'label' => __('Textarea Width', 'chat-on-desk'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_textarea_height',
            [
                'label' => __('Textarea Height', 'chat-on-desk'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 400,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_field_padding',
            [
                'label'      => __('Padding', 'chat-on-desk'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_field_spacing',
            [
                'label' => __('Spacing', 'chat-on-desk'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_field_typography',
                'label'     => __('Typography', 'chat-on-desk'),
                'selector'  => '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group select',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'form_field_box_shadow',
                'selector'  => '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group select',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_form_fields_focus',
            [
                'label' => __('Focus', 'chat-on-desk'),
            ]
        );

        $this->add_control(
            'form_field_bg_color_focus',
            [
                'label'     => __('Background Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):focus, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea:focus' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'form_input_focus_border',
                'label'       => __('Border', 'chat-on-desk'),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):focus, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea:focus',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'form_input_focus_box_shadow',
                'selector'  => '{{WRAPPER}} .chatondeskform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):focus, {{WRAPPER}} .chatondeskform-widget-wrapper .cod-el-group textarea:focus',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }
    
    /**
     * Register placeholder style controls function.     
     *
     * @return array
     */
    protected function registerPlaceholderStyleControls()
    {
        $this->start_controls_section(
            'section_placeholder_style',
            [
                'label'     => __('Placeholder', 'chat-on-desk'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'placeholder_switch' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_placeholder_text_color',
            [
                'label'     => __('Text Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form input::-webkit-input-placeholder, {{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form textarea::-webkit-input-placeholder' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'placeholder_switch' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }
    
     /**
      * Register submit button style controls function.     
      *
      * @return array
      */
    protected function registerSubmitButtonStyleControls()
    {
        $this->start_controls_section(
            'section_form_submit_button_style',
            [
                'label' => __('Submit Button', 'chat-on-desk'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'form_submit_button_align',
            [
                'label'   => __('Alignment', 'chat-on-desk'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'chat-on-desk'),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'chat-on-desk'),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'chat-on-desk'),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'default'      => '',
                'prefix_class' => 'chatondeskform-widget-submit-button-',
                'condition'    => [
                    'form_submit_button_width_type' => 'custom',
                ],
            ]
        );

        $this->add_control(
            'form_submit_button_width_type',
            [
                'label'   => __('Width', 'chat-on-desk'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'custom',
                'options' => [
                    'full-width' => __('Full Width', 'chat-on-desk'),
                    'custom'     => __('Custom', 'chat-on-desk'),
                ],
                'prefix_class' => 'chatondeskform-widget-submit-button-',
            ]
        );

        $this->add_responsive_control(
            'form_submit_button_width',
            [
                'label' => __('Width', 'chat-on-desk'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn' => 'width: {{SIZE}}{{UNIT}}', ],
                'condition' => [
                    'form_submit_button_width_type' => 'custom',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_submit_button_style');

        $this->start_controls_tab(
            'tab_submit_button_normal',
            [
                'label' => __('Normal', 'chat-on-desk'),
            ]
        );

        $this->add_control(
            'form_submit_button_bg_color_normal',
            [
                'label'     => __('Background Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#409EFF',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'form_submit_button_text_color_normal',
            [
                'label'     => __('Text Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'form_submit_button_border_normal',
                'label'       => __('Border', 'chat-on-desk'),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn',
            ]
        );

        $this->add_control(
            'form_submit_button_border_radius',
            [
                'label'      => __('Border Radius', 'chat-on-desk'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_submit_button_padding',
            [
                'label'      => __('Padding', 'chat-on-desk'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_submit_button_margin',
            [
                'label' => __('Margin Top', 'chat-on-desk'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 150,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn' => 'margin-top: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_submit_button_typography',
                'label'     => __('Typography', 'chat-on-desk'),
                'selector'  => '{{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'form_submit_button_box_shadow',
                'selector'  => '{{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_submit_button_hover',
            [
                'label' => __('Hover', 'chat-on-desk'),
            ]
        );

        $this->add_control(
            'form_submit_button_bg_color_hover',
            [
                'label'     => __('Background Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'form_submit_button_text_color_hover',
            [
                'label'     => __('Text Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'form_submit_button_border_color_hover',
            [
                'label'     => __('Border Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskform-widget-wrapper #chatondesk_share_cart,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskform-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }     

    /**
     * Render the widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     *
     * @access protected
     *
     * @return array
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        extract($settings);
        $this->add_render_attribute(
            'chatondeskform_widget_wrapper',
            [
                'class' => [
                    'chatondeskform-widget-wrapper',
                ],
            ]
        );

        if ('yes' != $placeholder_switch) {
            $this->add_render_attribute('chatondeskform_widget_wrapper', 'class', 'hide-placeholder');
        }
        
        if ('yes' != $labels_switch) {
            $this->add_render_attribute('chatondeskform_widget_wrapper', 'class', 'hide-chatondesk-form-labels');
            
        }
        if ($form_container_alignment) {
            $this->add_render_attribute('chatondeskform_widget_wrapper', 'class', 'chatondeskform-widget-align-' . $form_container_alignment . '');
        }
        if (!empty($form_list)) { ?>

            <div <?php echo wp_kses_post($this->get_render_attribute_string('chatondeskform_widget_wrapper')); ?>>

            <?php if ('yes' == $custom_title_description) { ?>
                <div class="chatondeskform-widget-heading">
                    <?php if ('' != $form_title_custom) { ?>
                    <h3 class="chatondeskform-widget-title">
                        <?php echo esc_attr($form_title_custom); ?>
                    </h3>
                    <?php } ?>
                    <?php if ('' != $form_description_custom) { ?>
                    <p class="chatondeskform-widget-description">
                        <?php echo wp_kses_post($this->parse_text_editor($form_description_custom)); ?>
                    </p>
                    <?php } ?>
                </div>                
            <?php } ?> 

            <?php 
            $values = $form_list;            
            switch ($values) {
            case 1:
                echo do_shortcode("[cod_signupwithmobile cod_label='".$cod_ele_f_mobile_lbl."' cod_placeholder = '".$cod_ele_f_mobile_place."' cod_button = '".$cod_ele_f_mobile_botton."']");
                break; 
            case 2:
                echo do_shortcode("[cod_loginwithotp cod_label='".$cod_ele_f_mobile_lbl."' cod_placeholder = '".$cod_ele_f_mobile_place."' cod_button = '".$cod_ele_f_mobile_botton."']");
                break;
            case 3:
                echo do_shortcode("[cod_sharecart empty_msg='Your cart is empty!</br><small>Please add product into cart to see the preview and select share cart from widget dropdown</small>']");
                break;     
            }
        }
    }    

     /**
      * Content template function.     
      *
      * @return array
      */
    protected function content_template()
    {
    }
}
