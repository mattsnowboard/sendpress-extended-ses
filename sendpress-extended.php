<?php

/*
  Plugin Name: SendPress Extended: Email Marketing and Newsletters
  Version: 0.8.8.1
  Plugin URI: http://sendpress.com
  Description: Extending SendPress plugin (which must be active and working) with features to use SES
  Author: Matt Durak
  Depends: SendPress: Email Marketing and Newsletters, AWS SES Email
 */

//require 'classes/views/class-sendpress-view-settings-account2.php';

if (!defined('SENDPRESS_PATH')) {
    //die;
}

define( 'SENDPRESS_EXTENDED_PATH', plugin_dir_path(__FILE__) );

class SendPressExtended
{

    public function init()
    {
        if (is_admin()) {
            add_action('admin_menu', array($this, 'initAdmin'), 20);
        }
        SendPressExtended_Sender_SES::init();
    }

    public function initAdmin()
    {
        if (current_user_can('sendpress_view')) {
            $role = "sendpress_view";
        } else {
            $role = "manage_options";
        }
        add_submenu_page('sp-overview', __('SES Settings', 'sendpress'),
                                           __('SES Settings', 'sendpress'),
                                              $role, 'sp-ses',
                                              array(&$this, 'render_my_settings_view'));
        
        //$sendpress = SendPress::get_instance();
        //$sendpress->adminpages[] = 'sp-ses';
    }

    function render_my_settings_view()
    {
        $sendpress = SendPress::get_instance();
        
        if ( !empty($_POST) && check_admin_referer($sendpress->_nonce_value) ){
            $options =  array();

            if (isset($_POST['action'])) {
                if ($_POST['action'] == 'ses-setup' && isset($_POST['sendmethod'])) {
                    $options['sendmethod'] = $_POST['sendmethod'];

                    SendPress_Option::set($options);

                    //wp_redirect( admin_url('admin.php?page=sp-ses') );
                } else if ($_POST['action'] == 'test-account-setup' && isset($_POST['testemail'])) {
                    $options['testemail'] = $_POST['testemail'];
        
                    SendPress_Option::set($options);
                    
                    $sendpress->send_test();
                    
                    //wp_redirect( admin_url('admin.php?page=sp-ses') );
                }
            }
        }
        
        wp_enqueue_style( 'farbtastic' );
        wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload'));
        wp_enqueue_style('thickbox');
        wp_register_script('spfarb', SENDPRESS_URL .'js/farbtastic.js' ,'', SENDPRESS_VERSION );
        wp_enqueue_script( 'spfarb' );
        wp_register_script('sendpress-admin-js', SENDPRESS_URL .'js/sendpress.js','', SENDPRESS_VERSION );
        wp_enqueue_script('sendpress-admin-js');
        wp_register_script('sendpress_bootstrap', SENDPRESS_URL .'bootstrap/js/bootstrap.min.js' ,'',SENDPRESS_VERSION);
        wp_enqueue_script('sendpress_bootstrap');
        wp_register_style( 'sendpress_bootstrap_css', SENDPRESS_URL . 'bootstrap/css/bootstrap.css', '', SENDPRESS_VERSION );
        wp_enqueue_style( 'sendpress_bootstrap_css' );
        
        wp_register_script('sendpress_ls', SENDPRESS_URL .'js/jquery.autocomplete.js' ,'', SENDPRESS_VERSION );
        wp_enqueue_script('sendpress_ls');
        
        wp_register_style( 'sendpress_css_base', SENDPRESS_URL . 'css/style.css', false, SENDPRESS_VERSION );
        wp_enqueue_style( 'sendpress_css_base' );

        do_action('sendpress_admin_scripts');
        
        $view_class = 'SendPressExtended_View_Ses';
        //echo "About to render: $view_class, $this->_page";
        $view_class = NEW $view_class;

        //add tabs
        $view_class->add_tab(__('Overview', 'sendpress'), 'sp-overview', false);
        $view_class->add_tab(__('Emails', 'sendpress'), 'sp-emails', false);
        $view_class->add_tab(__('Reports', 'sendpress'), 'sp-reports', false);
        $view_class->add_tab(__('Subscribers', 'sendpress'), 'sp-subscribers',
                                false);
        $view_class->add_tab(__('Queue', 'sendpress'), 'sp-queue', false);
        $view_class->add_tab(__('Settings', 'sendpress'), 'sp-settings', false);
        $view_class->add_tab(__('SES Settings', 'sendpress'), 'sp-ses', true);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $view_class->add_tab(__('Add-ons', 'sendpress'), 'sp-pro', false);
        }
        $view_class->add_tab(__('Help', 'sendpress'), 'sp-help', false);
        $view_class->prerender($sendpress);
        $view_class->render($sendpress);
    }

    public static function autoload($className)
    {
        if (strpos($className, 'SendPressExtended') !== 0) {
            return;
        }

        $cls = str_replace('_', '-', strtolower($className));
        if (substr($cls, -1) == '-') {
            $cls = substr($cls, 0, -1);
            $className = substr($className, 0, -1);
        }
        if (class_exists($className)) {
            return;
        }

        if (strpos($className, 'Public_View') != false) {
            if (defined('SENDPRESS_PRO_PATH')) {
                $pro_file = SENDPRESS_PRO_PATH . "classes/public-views/class-" . $cls . ".php";
                if (file_exists($pro_file)) {
                    include SENDPRESS_PRO_PATH . "classes/public-views/class-" . $cls . ".php";
                    return;
                }
            }
            include SENDPRESS_EXTENDED_PATH . "classes/public-views/class-" . $cls . ".php";
            return;
        }

        if (strpos($className, 'View') != false) {
            if (defined('SENDPRESS_PRO_PATH')) {
                $pro_file = SENDPRESS_PRO_PATH . "classes/views/class-" . $cls . ".php";
                if (file_exists($pro_file)) {
                    include SENDPRESS_PRO_PATH . "classes/views/class-" . $cls . ".php";
                    return;
                }
            }
            include SENDPRESS_EXTENDED_PATH . "classes/views/class-" . $cls . ".php";
            return;
        }

        if (strpos($className, 'Module') != false) {
            include SENDPRESS_EXTENDED_PATH . "classes/modules/class-" . $cls . ".php";
            return;
        }

        if (defined('SENDPRESS_PRO_PATH')) {
            $pro_file = SENDPRESS_PRO_PATH . "classes/class-" . $cls . ".php";
            if (file_exists($pro_file)) {
                include SENDPRESS_PRO_PATH . "classes/class-" . $cls . ".php";
                return;
            }
        }
        include SENDPRESS_EXTENDED_PATH . "classes/class-" . $cls . ".php";
    }

}

function sendpress_extended_init()
{
    $sendpressExtended = new SendPressExtended();
    $sendpressExtended->init();
}

spl_autoload_register(array('SendPressExtended', 'autoload'));

add_action('sendpress_loaded', 'sendpress_extended_init');