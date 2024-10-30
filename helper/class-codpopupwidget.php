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
 * CodPopupWidget class
 */
class CodPopupWidget extends Widget_Base
{
    
    /**
     * Get name function.
     *
     * @return array
     */
    public function get_name()
    {
        return 'chatondesk-modal-widget';
    }

    /**
     * Get title function.
     *
     * @return array
     */
    public function get_title()
    {
        return __('Chat On Desk Modal', 'chat-on-desk');
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
            'chatondeskmodal',
            'chatondeskmodal',
            'chatondesks modal',
            'chatondeskmodal modals',
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
            'chatondesks-form-styles',
            'chatondesks-public-default',
        ];
    }
     
    /**
     * Get  scrip depends function.     
     *
     * @return array
     */
    public function get_script_depends()
    {
        return ['chatondesks-elementor'];
    }
    /**
     * Register controls function.     
     *
     * @return array
     */
    protected function register_controls()
    {
        $this->registerGeneralControls();        
        $this->registerFormContainerStyleControls();
        $this->registerLabelStyleControls();
        $this->registerInputTextareaStyleControls();       
        $this->registerAddressLineStyleControls();       
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
            'section_chatondesks_form',
            [
                'label' => __('Chat On Desk Modal', 'chat-on-desk'),
            ]
        );

        $this->add_control(
            'form_list',
            [
                'label'       => esc_html__('Chat On Desk Modal', 'chat-on-desk'),
                'type'        => Controls_Manager::SELECT,
                'label_block' => true,
                'options'     => array('popup-1'=>'Style1', 'popup-2'=>'Style2', 'popup-3'=>'Style3'),
                'default' => 'popup-1',                
            ]
        );
 
        $this->add_control(
            'cod_ele_f_mobile_lbl',
            [    
                'label'        => __('Modal Text', 'chat-on-desk'),
                				
                'type'         => "textarea",
                'placeholder'      => 'Enter text',              				
                'condition' => [
                    'form_list' => ['popup-1','popup-2','popup-3'],
                ],
                'description' => esc_html__('Use ##phone## for mobile number', 'chat-on-desk'),                
            ]
        );

        $this->add_control(
            'cod_ele_f_mobile_placeholder',
            [
                'label'        => __('Placeholder', 'chat-on-desk'),				
                'type'         => "text",
                'placeholder'      => 'Enter Placeholder', 
                'condition' => [
                    'form_list' => ['popup-1'],
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
                    'form_list' => ['popup-1','popup-2','popup-3'],
                ],                
            ]
        );
        $this->add_control(
            'cod_ele_f_otp_resend',
            [
                'label'        => __('Resend Text', 'chat-on-desk'),
                'type'         => "text",
                'placeholder'      => 'Enter Resend Text',
                'condition' => [
                    'form_list' => ['popup-1','popup-2','popup-3'],
                ],                
            ]
        );
        $this->add_control(
            'cod_ele_f_resend_btn',
            [
                'label'        => __('Resend Button Text', 'chat-on-desk'),
                'type'         => "text",
                'placeholder'      => 'Enter Resend Button Text',
                'condition' => [
                    'form_list' => ['popup-1','popup-2','popup-3'],
                ],                
            ]
        );
        $this->add_control(
            'cod_otp_re_send_timer',
            [
                'label'        => __('OTP Re-send Timer', 'chat-on-desk'),
                'type'         => "number",
                'min'          => "15",
                'max'          => "300",				
                'placeholder'  => 'Enter Number',
                'condition' => [
                    'form_list' => ['popup-1','popup-2','popup-3'],
                ],                
            ]
        );
        $this->add_control(
            'max_otp_resend_allowed',
            [
                'label'        => __('Max OTP Re-send Allowed', 'chat-on-desk'),
                'type'         => "number",
                  'min'          => "1",
                  'max'          => "5",
                'placeholder'  => 'Enter number',
                'condition' => [
                    'form_list' => ['popup-1','popup-2','popup-3'],
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
                'types'    => ['classic'],
                'selector' => '{{WRAPPER}} .chatondeskmodal-widget-wrapper .modal-body',
                'exclude' => ['image'],        
            ]
        );   
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'form_container_border',
                'selector' => '{{WRAPPER}} .chatondeskmodal-widget-wrapper .modal-body',
            'exclude' => ['Width'],
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .modal-body' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
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
                'label' => __('Modal Text', 'chat-on-desk'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'form_label_text_color',
            [
                'label'     => __('Text Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-message, .chatondeskmodal-widget-wrapper .cod-lwo-form label' => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'form_label_bg_color',
            [
                'label'     => __('Background Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-message, .chatondeskmodal-widget-wrapper .cod-lwo-form label' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'form_label_typography',
                'label'    => __('Typography', 'chat-on-desk'),
                'selector' => '{{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-message,.chatondeskmodal-widget-wrapper .cod-lwo-form label',
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
                'label' => __('OTP Field', 'chat-on-desk'),
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-message, .chatondeskmodal-widget-wrapper .cod-el-group select' => 'text-align: {{VALUE}};',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):not(.select2-search__field), {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group select, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group .select2-container--default .select2-selection--multiple' => 'background-color: {{VALUE}}',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group select' => 'color: {{VALUE}}',
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
                'selector'    => '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):not(.select2-search__field), {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group select,  {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group .select2-container--default .select2-selection--multiple',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group select,  {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group .select2-container--default .select2-selection--multiple' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group select' => 'text-indent: {{SIZE}}{{UNIT}}',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group select' => 'width: {{SIZE}}{{UNIT}}',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group select' => 'height: {{SIZE}}{{UNIT}}',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_field_typography',
                'label'     => __('Typography', 'chat-on-desk'),
                'selector'  => '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group select',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'form_field_box_shadow',
                'selector'  => '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group textarea, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group select',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):focus, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group textarea:focus' => 'background-color: {{VALUE}}',
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
                'selector'    => '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):focus, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group textarea:focus',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'form_input_focus_box_shadow',
                'selector'  => '{{WRAPPER}} .chatondeskmodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):focus, {{WRAPPER}} .chatondeskmodal-widget-wrapper .cod-el-group textarea:focus',
                'separator' => 'before',
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }
    
    /**
     * Register Address style controls function.     
     *
     * @return array
     */
    protected function registerAddressLineStyleControls()
    {
        $this->start_controls_section(
            'section_form_address_line_style',
            [
                'label' => __('Resend Otp', 'chat-on-desk'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'address_line_text_color',
            [
                'label'     => __('text Color', 'chat-on-desk'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .cod_resend_btn,.chatondeskmodal-widget-wrapper .cod_forgot,.chatondeskmodal-widget-wrapper .cod_timer' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'address_line_label_typography',
                'label'    => __('Typography', 'chat-on-desk'),
                'selector' => '{{WRAPPER}} .chatondeskmodal-widget-wrapper .cod_resend_btn, .chatondeskmodal-widget-wrapper .cod_forgot,.chatondeskmodal-widget-wrapper .cod_timer',
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
                'prefix_class' => 'chatondeskmodal-widget-submit-button-',
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
                'prefix_class' => 'chatondeskmodal-widget-submit-button-',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .chatondesk_otp_validate_submit,.chatondeskmodal-widget-wrapper #chatondesk_share_cart,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn' => 'width: {{SIZE}}{{UNIT}}', ],
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .chatondesk_otp_validate_submit,.chatondeskmodal-widget-wrapper #chatondesk_share_cart,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn' => 'background-color: {{VALUE}} !important;',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .chatondesk_otp_validate_submit,.chatondeskmodal-widget-wrapper #chatondesk_share_cart,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn' => 'color: {{VALUE}} !important;',
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
                'selector'    => '{{WRAPPER}} .chatondeskmodal-widget-wrapper .chatondesk_otp_validate_submit,.chatondeskmodal-widget-wrapper #chatondesk_share_cart,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn',
            ]
        );

        $this->add_control(
            'form_submit_button_border_radius',
            [
                'label'      => __('Border Radius', 'chat-on-desk'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .chatondesk_otp_validate_submit,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .chatondesk_otp_validate_submit,.chatondeskmodal-widget-wrapper #chatondesk_share_cart,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .chatondesk_otp_validate_submit,.chatondeskmodal-widget-wrapper #chatondesk_share_cart,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn' => 'margin-top: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_submit_button_typography',
                'label'     => __('Typography', 'chat-on-desk'),
                'selector'  => '{{WRAPPER}} .chatondeskmodal-widget-wrapper .chatondesk_otp_validate_submit,.chatondeskmodal-widget-wrapper #chatondesk_share_cart,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'form_submit_button_box_shadow',
                'selector'  => '{{WRAPPER}} .chatondeskmodal-widget-wrapper .chatondesk_otp_validate_submit,.chatondeskmodal-widget-wrapper #chatondesk_share_cart,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .chatondesk_otp_validate_submit,.chatondeskmodal-widget-wrapper #chatondesk_share_cart,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn:hover' => 'background-color: {{VALUE}} !important;',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .chatondesk_otp_validate_submit,.chatondeskmodal-widget-wrapper #chatondesk_share_cart,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn:hover' => 'color: {{VALUE}} !important;',
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
                    '{{WRAPPER}} .chatondeskmodal-widget-wrapper .chatondesk_otp_validate_submit,.chatondeskmodal-widget-wrapper #chatondesk_share_cart,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_reg_with_otp_btn,.chatondeskmodal-widget-wrapper .cod-lwo-form .chatondesk_login_with_otp_btn:hover' => 'border-color: {{VALUE}}',
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
            'chatondeskmodal_widget_wrapper',
            [
                'class' => [
                    'chatondeskmodal-widget-wrapper',
                ],
            ]
        ); 		
        if (!empty($form_list)) { ?>
            <div <?php echo wp_kses_post($this->get_render_attribute_string('chatondeskmodal_widget_wrapper')); ?>>
           	    <?php    
                           
			
		 $values = $form_list;
		 
            switch ($values) {
            case "popup-1": 
                echo CodPopup::getModelStyle(array('cod_label'=>$cod_ele_f_mobile_lbl, 'placeholder' =>$cod_ele_f_mobile_placeholder, 'cod_button' =>$cod_ele_f_mobile_botton, 'cod_resend_otp' =>$cod_ele_f_otp_resend, 'cod_resend_btns' =>$cod_ele_f_resend_btn,'otp_template_style'=>'popup-1'));
                break; 
             case "popup-2":
                echo CodPopup::getModelStyle(array('cod_label'=>$cod_ele_f_mobile_lbl, 'placeholder' =>$cod_ele_f_mobile_placeholder, 'cod_button' =>$cod_ele_f_mobile_botton, 'cod_resend_otp' =>$cod_ele_f_otp_resend, 'cod_resend_btns' =>$cod_ele_f_resend_btn,'otp_template_style'=>'popup-2'));
                break;
            case "popup-3":
               echo CodPopup::getModelStyle(array('cod_label'=>$cod_ele_f_mobile_lbl, 'placeholder' =>$cod_ele_f_mobile_placeholder, 'cod_button' =>$cod_ele_f_mobile_botton, 'cod_resend_otp' =>$cod_ele_f_otp_resend, 'cod_resend_btns' =>$cod_ele_f_resend_btn,'otp_template_style'=>'popup-3'));
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