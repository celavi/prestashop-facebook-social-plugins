<?php
if (! defined ( '_CAN_LOAD_FILES_' ))
    exit ();

/**
 * Facebook Pack
 *
 * Facebook Pack contains Facebook Social Plugins which let you see what your friends have liked,
 * commented on or shared on sites across the web.
 *
 * @ (C) Copyright 2016 by (celavi.org) Ales Loncar
 * @ Version 1.1
 *
 */
class FacebookPack extends Module {
    private $_validationErrors = array();

    private $_fbPack_app_locale = 'en_US';
    private $_fbPack_sdk_version = 'v2.3';

    private $_fbPack_app_id = '';
    private $_fbPack_app_secret = '';

    private $_fbPack_like_button = 0;
    private $_fbPack_like_url = '';
    private $_fbPack_like_width = 300;
    private $_fbPack_like_layout = 'standard';
    private $_fbPack_like_action = 'like';
    private $_fbPack_like_faces = 0;
    private $_fbPack_like_share = 0;
    private $_fbPack_like_colorscheme = 'light';

    private $_fbPack_page_plugin = 0;
    private $_fbPack_page_url = 'http://www.facebook.com/prestashop/';
    private $_fbPack_page_name = 'PrestaShop';
    private $_fbPack_page_width = 180;
    private $_fbPack_page_height = 70;


//    private $_fbPack_like_box = 'no';
//    private $_fbPack_facebook_page_url = '';
//    private $_fbPack_box_width = 190;
//    private $_fbPack_box_height = 390;
//    private $_fbPack_box_color = 'light';
//    private $_fbPack_box_faces = 1;
//    private $_fbPack_box_border_color = '#000000';
//    private $_fbPack_box_stream = 0;
//    private $_fbPack_box_header = 1;

    private $_fbPack_comments = 'no';
    private $_fbPack_comments_posts = 4;
    private $_fbPack_comments_width = 515;
    private $_fbPack_comments_color = 'light';
    private $_fbPack_comments_moderators = '';

    private $_fbPack_login_button = 'no';
    private $_fbPack_login_button_label = 'Login with Facebook';

    public $_errors = array();

    public function __construct()
    {
        $this->name = 'facebookpack';
        $this->tab = 'social_networks';
        $this->author = 'Ales Loncar';
        $this->version = 1.1;

        $this->displayName = $this->l('Facebook Pack');
        $this->description = $this->l('Facebook Pack contains Facebook Social Plugins: Like Button, Like Box, Login Button, Facebook Comments');

        $this->_readProperties();
        parent::__construct();
    }

    public function install()
    {
        if (!parent::install() or !$this->registerHook('header') or
            !$this->registerHook('top') or !$this->registerHook('footer') or !$this->registerHook('leftColumn') or
            !$this->registerHook('extraLeft') or !$this->registerHook('productTab') or
            !$this->registerHook('productTabContent'))
            return false;

        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('FBPACK_APP_ID') or !Configuration::deleteByName('FBPACK_APP_SECRET') or
            !Configuration::deleteByName('FBPACK_APP_LOCALE') or

            !Configuration::deleteByName('FBPACK_LIKE_BUTTON') or !Configuration::deleteByName('FBPACK_LIKE_URL') or
            !Configuration::deleteByName('FBPACK_LIKE_SHARE') or !Configuration::deleteByName('FBPACK_LIKE_LAYOUT') or
            !Configuration::deleteByName('FBPACK_LIKE_WIDTH') or !Configuration::deleteByName('FBPACK_LIKE_FACES') or
            !Configuration::deleteByName('FBPACK_LIKE_ACTION') or !Configuration::deleteByName('FBPACK_LIKE_COLORSCHEME') or

            !Configuration::deleteByName('FBPACK_PAGE_PLUGIN') or !Configuration::deleteByName('FBPACK_PAGE_URL') or
            !Configuration::deleteByName('FBPACK_PAGE_NAME') or

            !Configuration::deleteByName('FBPACK_COMMENTS') or
            !Configuration::deleteByName('FBPACK_COMMENTS_POSTS') or !Configuration::deleteByName('FBPACK_COMMENTS_WIDTH') or
            !Configuration::deleteByName('FBPACK_COMMENTS_COLOR') or !Configuration::deleteByName('FBPACK_COMMENTS_MODERATORS') or
            !Configuration::deleteByName('FBPACK_LOGIN_BUTTON') or
            !parent::uninstall ()) {
                return false;
        }


        return true;
    }

    public function getContent()
    {
        global $smarty;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->_validateData();
            if (count($this->_validationErrors)) {
                $smarty->assign('validationErrors', $this->_validationErrors);
            } else {
                $this->_updateData();
                $this->_readProperties();
                $smarty->assign('settingUpdated', $this->l('Setting updated'));
            }
        }

        $smarty->assign('displayName', $this->displayName);
        $smarty->assign('path', $this->_path);
        $smarty->assign('enablePlugin', $this->l('Enable Plugin'));
        $smarty->assign('yes', $this->l('yes'));
        $smarty->assign('no', $this->l('no'));

        // social part
        $smarty->assign('socialTitle', $this->l('This module contains Facebook Social Plugins'));
        $smarty->assign('socialDescription_common', $this->l('One of the easiest ways to make your online presence more social is by adding Facebook social plugins to your shop. Here you can choose to add four different Facebook social plugins.'));
        $smarty->assign('socialDescription_simple', $this->l('Two simple plugins: Like Button, Like Box.'));
        $smarty->assign('socialDescription_complex', $this->l('Two plugins requires Facebook Connect to work properly: Comments and Login Button.'));

        // like button
        $smarty->assign('likeButton', $this->l('Like Button'));
        $smarty->assign('fbPack_like_button', $this->_fbPack_like_button);
        $smarty->assign('fbPack_like_button_description', $this->l('Check the checbox to enable \'Like Button\''));
        $smarty->assign('fbPack_like_url_label', $this->l('URL to Like'));
        $smarty->assign('fbPack_like_url', $this->_fbPack_like_url);
        $smarty->assign('fbPack_like_url_placeholder', $this->l('The URL to like. Defaults to the current page'));
        $smarty->assign('fbPack_like_width_label', 'Width');
        $smarty->assign('fbPack_like_width', $this->_fbPack_like_width);
        $smarty->assign('fbPack_like_width_placeholder', $this->l('The pixel width of the plugin'));
        $smarty->assign('fbPack_like_width_description', $this->l('Width in pixels'));
        $smarty->assign('fbPack_like_layout_label', $this->l('Layout'));
        $smarty->assign('fbPack_like_layout', $this->_fbPack_like_layout);
        $smarty->assign('fbPack_like_layout_standard', $this->l('Standard'));
        $smarty->assign('fbPack_like_layout_box_count', $this->l('Box (Count)'));
        $smarty->assign('fbPack_like_layout_button_count', $this->l('Button (Count)'));
        $smarty->assign('fbPack_like_layout_button', $this->l('Button'));
        $smarty->assign('fbPack_like_layout_description', $this->l('Determines the size and amount of social context next to the button'));
        $smarty->assign('fbPack_like_action_label', $this->l('Action Type'));
        $smarty->assign('fbPack_like_action', $this->_fbPack_like_action);
        $smarty->assign('fbPack_like_action_like', $this->l('Like'));
        $smarty->assign('fbPack_like_action_recommend', $this->l('Recommend'));
        $smarty->assign('fbPack_like_action_description', $this->l('The verb to display in the button'));
        $smarty->assign('fbPack_like_faces_label', $this->l('Show Friends\' Faces'));
        $smarty->assign('fbPack_like_faces', $this->_fbPack_like_faces);
        $smarty->assign('fbPack_like_faces_description', $this->l('Check the checkbox to display profile photos below the button (standard layout only)'));
        $smarty->assign('fbPack_like_share_label', $this->l('Include Share Button'));
        $smarty->assign('fbPack_like_share', $this->_fbPack_like_share);
        $smarty->assign('fbPack_like_share_description', $this->l('Check the checkbox to include a share button beside the Like button.'));
        $smarty->assign('fbPack_like_colorscheme_label', $this->l('Color Scheme'));
        $smarty->assign('fbPack_like_colorscheme', $this->_fbPack_like_colorscheme);
        $smarty->assign('fbPack_like_colorscheme_light', $this->l('Light'));
        $smarty->assign('fbPack_like_colorscheme_dark', $this->l('Dark'));
        $smarty->assign('fbPack_like_colorscheme_description', $this->l('The color scheme used by the plugin for any text outside of the button itself'));
        $smarty->assign('fbPack_like_submit', $this->l('\'Like Button\' - update settings'));

        // page plugin
        $smarty->assign('pagePlugin', $this->l('Page Plugin'));
        $smarty->assign('fbPack_page_plugin', $this->_fbPack_page_plugin);
        $smarty->assign('fbPack_page_plugin_description', $this->l('Check the checbox to enable \'Page Plugin\''));
        $smarty->assign('fbPack_page_url_label', $this->l('Facebook Page URL'));
        $smarty->assign('fbPack_page_url', $this->_fbPack_page_url);
        $smarty->assign('fbPack_page_url_placeholder', $this->l('The URL of the Facebook Page'));
        $smarty->assign('fbPack_page_name_label', $this->l('Facebook Page Name'));
        $smarty->assign('fbPack_page_name', $this->_fbPack_page_name);
        $smarty->assign('fbPack_page_name_placeholder', $this->l('The Name of the Facebook Page'));
        $smarty->assign('fbPack_page_tabs_label', $this->l('Tabs'));
        $smarty->assign('fbPack_page_tabs_timeline', $this->l('Timeline'));
        $smarty->assign('fbPack_page_tabs_events', $this->l('Events'));
        $smarty->assign('fbPack_page_tabs_messages', $this->l('Messages'));
        $smarty->assign('fbPack_page_tabs_description', $this->l('Check which tabs to render'));
        $smarty->assign('fbPack_page_width_label', $this->l('Width'));
        $smarty->assign('fbPack_page_width', $this->_fbPack_page_width);
        $smarty->assign('fbPack_page_width_placeholder', $this->l('The pixel width of the embed (Min. 180 to Max. 500)'));
        $smarty->assign('fbPack_page_height_label', $this->l('Height'));
        $smarty->assign('fbPack_page_height', $this->_fbPack_page_height);
        $smarty->assign('fbPack_page_height_placeholder', $this->l('The pixel height of the embed (Min. 70)'));
        $smarty->assign('fbPack_page_height_description', $this->l('Height in pixels'));





        return $this->display(__FILE__, '/tpl/content.tpl');

//		$this->_html = '<h2>' . $this->displayName . '</h2>';
//		if (! empty ( $_POST )) {
//			$this->_postValidation ();
//			if (! sizeof ( $this->_postErrors ))
//				$this->_postProcess ();
//			else
//				foreach ( $this->_postErrors as $err )
//					$this->_html .= '<div class="alert error">' . $err . '</div>';
//		} else
//			$this->_html .= '<br />';
//
//		$this->_displaySocialPlugins();
//		$this->_displayDonation();
//		$this->_displayForm();
//
//		return $this->_html;
    }

    private function _validateData() {
        if (isset($_POST['submitLikeButton'])) {
            $this->_validateLikeButton();
        }
//
//		if (isset ( $_POST ['submitLikeBox'] )) {
//			if (empty ( $_POST ['fbPack_facebook_page_url'] ))
//				$this->_postErrors [] = $this->l ( 'The URL of the Facebook Page is required.' );
//			if (empty ( $_POST ['fbPack_box_width'] ))
//				$this->_postErrors [] = $this->l ( 'The width of the Like Box is required.' );
//			if (empty ( $_POST ['fbPack_box_height'] ))
//				$this->_postErrors [] = $this->l ( 'The height of the Like Box is required.' );
//		}
//
//		if (isset ( $_POST ['submitComments'] )) {
//			if ($_POST ['fbPack_comments'] == 'yes') {
//				$config = Configuration::getMultiple ( array ('FBPACK_APP_ID' ) );
//				if (! isset ( $config ['FBPACK_APP_ID'] ))
//					$this->_postErrors [] = $this->l ( 'APP ID is required for Facebook Comments.' );
//				if (empty ( $_POST ['fbPack_comments_posts'] ))
//					$this->_postErrors [] = $this->l ( 'Number of posts is required.' );
//				if (empty ( $_POST ['fbPack_comments_width'] ))
//					$this->_postErrors [] = $this->l ( 'The width of the Comments Box is required.' );
//				if (empty ( $_POST ['fbPack_comments_moderators'] ))
//					$this->_postErrors [] = $this->l ( 'One Comments moderator is required.' );
//			}
//		}
//
//        if (isset ( $_POST ['submitLogin'] )) {
//            if ($_POST ['fbPack_login_button'] == 'yes') {
//                $config = Configuration::getMultiple ( array ('FBPACK_APP_ID', 'FBPACK_APP_SECRET' ) );
//				if (! isset ( $config ['FBPACK_APP_ID'] ))
//					$this->_postErrors [] = $this->l ( 'APP ID is required for Facebook Login.' );
//				if (! isset ( $config ['FBPACK_APP_SECRET'] ))
//					$this->_postErrors [] = $this->l ( 'APP Secret is required for Facebook Login.' );
//            }
//        }

    }

    private function _validateLikeButton()
    {
        if ($_POST['fbPack_like_layout'] != 'standard' && Tools::getValue('fbPack_like_faces')) {
            $this->_validationErrors [] = $this->l('Profile photos below the button are for standard layout only.');
        }

    }

    /**
     * Hook Header
     *
     * @param mixed $params
     */
    public function hookHeader($params) {
        global $smarty;

//		if ($this->_fbPack_comments == 'yes') {
//			$smarty->assign ( 'fbPack_comments', true );
//			$smarty->assign ( 'fbPack_app_id', $this->_fbPack_app_id );
//			$smarty->assign ( 'fbPack_app_locale', $this->_fbPack_app_locale );
//			$smarty->assign ( 'fbPack_comments_moderators', $this->_fbPack_comments_moderators );
//		}
//
//		if ($this->_fbPack_login_button == 'yes') {
//			$smarty->assign ( 'fbPack_login_button', true );
//		}

        return $this->display(__FILE__, 'tpl/header.tpl');
    }

    /**
     * Returns module content for Top
     *
     * @param array $params Parameters
     * @return string Content
     */
    public function hookTop($params) {
        global $smarty, $cookie;

        $smarty->assign('locale', $this->_fbPack_app_locale);
        $smarty->assign('sdk_version', $this->_fbPack_sdk_version);

//        if ($this->_fbPack_login_button == 'yes') {
//        	// User is not logged in
//
//        	if (!$cookie->isLogged()) {
//        		$smarty->assign ( 'isLogged', false );
//        	} else {
//        		$smarty->assign ( 'isLogged', true );
//        		$smarty->assign ('fbUser', isset($cookie->fb_user_id) ? true : false);
//        		$smarty->assign ('fb_user_id', isset($cookie->fb_user_id) ? $cookie->fb_user_id : null);
//        	}
//        	/**
//
//
//            if(!empty($fb_user)){
//                $smarty->assign ( 'fb_img', 'profile' );
//                $smarty->assign ( 'fb_user_id', $fb_user_id);
//            } else {
//                $url = $facebook->getLoginUrl(array(
//                    'display'   => 'popup',
//                    'scope'     => 'email,user_birthday',
//                    'redirect_uri' => _PS_BASE_URL_ . '/my-account.php',
//                    'next'      => _PS_BASE_URL_ . '/my-account.php'
//                ));
//                $smarty->assign ( 'fb_img', 'fb_connect.gif' );
//                $smarty->assign ( 'isLogged', !$cookie->isLogged() );
//                $smarty->assign ( 'fb_url', $url );
//            }
//            */
//        }

        return $this->display(__FILE__, 'tpl/top.tpl');
    }

    /**
     * Hook Footer
     *
     * @param mixed $params
     */
    public function hookFooter($params) {
        global $smarty, $cookie;

        $smarty->assign( 'fbPack_app_id', $this->_fbPack_app_id );
        $smarty->assign( 'fbPack_app_locale', $this->_fbPack_app_locale );

        if ($this->_fbPack_comments == 'yes') {
            $smarty->assign( 'fbPack_comments', true );
        }

        if ($this->_fbPack_login_button == 'yes') {
            $smarty->assign( 'fbPack_login_button', true );
            $smarty->assign( 'isLogged', ($cookie->isLogged()) ? 'true' : 'false');
        }

        return $this->display ( __FILE__, 'footer.tpl' );
    }

    /**
     * Hook Left Column
     *
     * @param mixed $params
     */
    public function hookLeftColumn($params) {
        global $smarty;

//        // Only show if like box is enabled
//        if ($this->_fbPack_like_box == 'yes') {
//
//            $smarty->assign ( 'fbPack_facebook_page_url', $this->_fbPack_facebook_page_url );
//            $smarty->assign ( 'fbPack_box_width', $this->_fbPack_box_width );
//            $smarty->assign ( 'fbPack_box_height', $this->_fbPack_box_height );
//            $smarty->assign ( 'fbPack_box_color', $this->_fbPack_box_color );
//            $smarty->assign ( 'fbPack_box_faces', ($this->_fbPack_box_faces) ? 'true' : 'false' );
//            $smarty->assign ( 'fbPack_box_border_color', $this->_fbPack_box_border_color );
//            $smarty->assign ( 'fbPack_box_stream', ($this->_fbPack_box_stream) ? 'true' : 'false' );
//            $smarty->assign ( 'fbPack_box_header', ($this->_fbPack_box_header) ? 'true' : 'false' );
//
//            return $this->display ( __FILE__, 'like_box.tpl' );
//        }
    }

    /**
     * Hook Extra Left
     *
     * @param mixed $params
     */
    public function hookExtraLeft($params) {
        global $smarty;

        if ($this->_fbPack_like_button) {

            $smarty->assign('fbPack_like_url', $this->_fbPack_like_url);
            $smarty->assign('fbPack_like_width', $this->_fbPack_like_width);
            $smarty->assign('fbPack_like_layout', $this->_fbPack_like_layout);
            $smarty->assign('fbPack_like_action', $this->_fbPack_like_action);
            $smarty->assign('fbPack_like_faces', ($this->_fbPack_like_faces) ? 'true' : 'false');
            $smarty->assign('fbPack_like_share', ($this->_fbPack_like_share) ? 'true' : 'false');
            $smarty->assign('fbPack_like_colorscheme', $this->_fbPack_like_colorscheme);

            return $this->display(__FILE__, 'tpl/like_button.tpl');
        }
    }

    /**
     * Hook Tab on product page
     *
     * @param mixed $params
     */
    public function hookProductTab($params) {
        global $smarty;

        if ($this->_fbPack_comments == 'yes') {

            return $this->display ( __FILE__, 'tab_comments.tpl' );
        }
    }

    /**
     * Hook Content of tab on product page
     *
     * @param mixed $params
     */
    public function hookProductTabContent($params) {
        global $smarty;

        if ($this->_fbPack_comments == 'yes') {

            $smarty->assign ( 'fbPack_comments_posts', $this->_fbPack_comments_posts );
            $smarty->assign ( 'fbPack_comments_width', $this->_fbPack_comments_width );
            $smarty->assign ( 'fbPack_comments_color', $this->_fbPack_comments_color );

            return $this->display ( __FILE__, 'tab_content_comments.tpl' );
        }
    }

    public function displayErrors()
    {
        if ($nbErrors = sizeof($this->_errors)) {
            echo '<div class="alert error"><h3>'.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors', __CLASS__) : $this->l('error', __CLASS__)).'</h3>
                <ol>';
            foreach ($this->_errors AS $error) {
                echo '<li>'.$error.'</li>';
            }
            echo '
                </ol></div>';
        }
    }

    private function _updateData() {

//		if (isset ( $_POST ['submitBasicSettings'] )) {
//			Configuration::updateValue ( 'FBPACK_APP_ID', $_POST ['fbPack_app_id'] );
//			Configuration::updateValue ( 'FBPACK_APP_SECRET', $_POST ['fbPack_app_secret'] );
//			Configuration::updateValue ( 'FBPACK_APP_LOCALE', $_POST ['fbPack_app_locale'] );
//		}

        if (isset($_POST['submitLikeButton'])) {
            $this->_updateLikeButton();
        }
//
//		if (isset ( $_POST ['submitLikeBox'] )) {
//			Configuration::updateValue ( 'FBPACK_LIKE_BOX', $_POST ['fbPack_like_box'] );
//			Configuration::updateValue ( 'FBPACK_FACEBOOK_PAGE_URL', $_POST ['fbPack_facebook_page_url'] );
//			Configuration::updateValue ( 'FBPACK_BOX_WIDTH', $_POST ['fbPack_box_width'] );
//			Configuration::updateValue ( 'FBPACK_BOX_HEIGHT', $_POST ['fbPack_box_height'] );
//			Configuration::updateValue ( 'FBPACK_BOX_COLOR', $_POST ['fbPack_box_color'] );
//			Configuration::updateValue ( 'FBPACK_BOX_FACES', Tools::getValue ( 'fbPack_box_faces' ) );
//			Configuration::updateValue ( 'FBPACK_BOX_BORDER_COLOR', $_POST ['fbPack_box_border_color'] );
//			Configuration::updateValue ( 'FBPACK_BOX_STREAM', Tools::getValue ( 'fbPack_box_stream' ) );
//			Configuration::updateValue ( 'FBPACK_BOX_HEADER', Tools::getValue ( 'fbPack_box_header' ) );
//		}
//
//		if (isset ( $_POST ['submitComments'] )) {
//			Configuration::updateValue ( 'FBPACK_COMMENTS', $_POST ['fbPack_comments'] );
//			Configuration::updateValue ( 'FBPACK_COMMENTS_POSTS', $_POST ['fbPack_comments_posts'] );
//			Configuration::updateValue ( 'FBPACK_COMMENTS_WIDTH', $_POST ['fbPack_comments_width'] );
//			Configuration::updateValue ( 'FBPACK_COMMENTS_COLOR', $_POST ['fbPack_comments_color'] );
//			Configuration::updateValue ( 'FBPACK_COMMENTS_MODERATORS', $_POST ['fbPack_comments_moderators'] );
//		}
//
//        if (isset ( $_POST ['submitLogin'] )) {
//            Configuration::updateValue ( 'FBPACK_LOGIN_BUTTON', $_POST ['fbPack_login_button'] );
//        }

//		$this->_refreshProperties ();
    }

    private function _updateLikeButton() {
        Configuration::updateValue('FBPACK_LIKE_BUTTON', Tools::getValue('fbPack_like_button'));
        Configuration::updateValue('FBPACK_LIKE_URL', Tools::getValue('fbPack_like_url'));
        Configuration::updateValue('FBPACK_LIKE_WIDTH', Tools::getValue('fbPack_like_width'));
        Configuration::updateValue('FBPACK_LIKE_LAYOUT', Tools::getValue('fbPack_like_layout'));
        Configuration::updateValue('FBPACK_LIKE_ACTION', Tools::getValue('fbPack_like_action'));
        Configuration::updateValue('FBPACK_LIKE_FACES', Tools::getValue('fbPack_like_faces'));
        Configuration::updateValue('FBPACK_LIKE_SHARE', Tools::getValue('fbPack_like_share'));
        Configuration::updateValue('FBPACK_LIKE_COLORSCHEME', Tools::getValue('fbPack_like_colorscheme'));
    }

    private function _readProperties() {
        // general
//		$config = Configuration::getMultiple ( array ('FBPACK_APP_ID', 'FBPACK_APP_SECRET', 'FBPACK_APP_LOCALE' ) );
//		if (isset ( $config ['FBPACK_APP_ID'] ))
//			$this->_fbPack_app_id = $config ['FBPACK_APP_ID'];
//		if (isset ( $config ['FBPACK_APP_SECRET'] ))
//			$this->_fbPack_app_secret = $config ['FBPACK_APP_SECRET'];
//		if (isset ( $config ['FBPACK_APP_LOCALE'] ))
//			$this->_fbPack_app_locale = $config ['FBPACK_APP_LOCALE'];

        $this->_readLikeButtonProperties();
        $this->_readPagePluginProperties();

        // like button
//		$config = Configuration::getMultiple ( array ('FBPACK_LIKE_BOX', 'FBPACK_FACEBOOK_PAGE_URL', 'FBPACK_BOX_WIDTH', 'FBPACK_BOX_HEIGHT', 'FBPACK_BOX_COLOR', 'FBPACK_BOX_FACES', 'FBPACK_BOX_BORDER_COLOR', 'FBPACK_BOX_STREAM', 'FBPACK_BOX_HEADER' ) );
//		if (isset ( $config ['FBPACK_LIKE_BOX'] ))
//			$this->_fbPack_like_box = $config ['FBPACK_LIKE_BOX'];
//		if (isset ( $config ['FBPACK_FACEBOOK_PAGE_URL'] ))
//			$this->_fbPack_facebook_page_url = $config ['FBPACK_FACEBOOK_PAGE_URL'];
//		if (isset ( $config ['FBPACK_BOX_WIDTH'] ))
//			$this->_fbPack_box_width = $config ['FBPACK_BOX_WIDTH'];
//		if (isset ( $config ['FBPACK_BOX_HEIGHT'] ))
//			$this->_fbPack_box_height = $config ['FBPACK_BOX_HEIGHT'];
//		if (isset ( $config ['FBPACK_BOX_COLOR'] ))
//			$this->_fbPack_box_color = $config ['FBPACK_BOX_COLOR'];
//		if (isset ( $config ['FBPACK_BOX_FACES'] ))
//			$this->_fbPack_box_faces = $config ['FBPACK_BOX_FACES'];
//		if (isset ( $config ['FBPACK_BOX_BORDER_COLOR'] ))
//			$this->_fbPack_box_border_color = $config ['FBPACK_BOX_BORDER_COLOR'];
//		if (isset ( $config ['FBPACK_BOX_STREAM'] ))
//			$this->_fbPack_box_stream = $config ['FBPACK_BOX_STREAM'];
//		if (isset ( $config ['FBPACK_BOX_HEADER'] ))
//			$this->_fbPack_box_header = $config ['FBPACK_BOX_HEADER'];

        // comments
//		$config = Configuration::getMultiple ( array ('FBPACK_COMMENTS', 'FBPACK_COMMENTS_POSTS', 'FBPACK_COMMENTS_WIDTH', 'FBPACK_COMMENTS_COLOR', 'FBPACK_COMMENTS_MODERATORS' ) );
//		if (isset ( $config ['FBPACK_COMMENTS'] ))
//			$this->_fbPack_comments = $config ['FBPACK_COMMENTS'];
//		if (isset ( $config ['FBPACK_COMMENTS_POSTS'] ))
//			$this->_fbPack_comments_posts = $config ['FBPACK_COMMENTS_POSTS'];
//		if (isset ( $config ['FBPACK_COMMENTS_WIDTH'] ))
//			$this->_fbPack_comments_width = $config ['FBPACK_COMMENTS_WIDTH'];
//		if (isset ( $config ['FBPACK_COMMENTS_COLOR'] ))
//			$this->_fbPack_comments_color = $config ['FBPACK_COMMENTS_COLOR'];
//		if (isset ( $config ['FBPACK_COMMENTS_MODERATORS'] ))
//			$this->_fbPack_comments_moderators = $config ['FBPACK_COMMENTS_MODERATORS'];


        // login
//		$config = Configuration::getMultiple ( array ('FBPACK_LOGIN_BUTTON' ) );
//        if (isset ( $config ['FBPACK_LOGIN_BUTTON'] ))
//			$this->_fbPack_login_button = $config ['FBPACK_LOGIN_BUTTON'];
    }

    private function _readLikeButtonProperties() {
        $config = Configuration::getMultiple(array('FBPACK_LIKE_BUTTON', 'FBPACK_LIKE_URL', 'FBPACK_LIKE_SHARE',
            'FBPACK_LIKE_LAYOUT', 'FBPACK_LIKE_WIDTH', 'FBPACK_LIKE_FACES', 'FBPACK_LIKE_ACTION',
            'FBPACK_LIKE_COLORSCHEME'));

        if (isset($config['FBPACK_LIKE_BUTTON'])) {
            $this->_fbPack_like_button = $config['FBPACK_LIKE_BUTTON'];
        }
        if (isset($config ['FBPACK_LIKE_URL'])) {
            $this->_fbPack_like_url = $config['FBPACK_LIKE_URL'];
        }
        if (isset($config['FBPACK_LIKE_WIDTH'])) {
            $this->_fbPack_like_width = $config['FBPACK_LIKE_WIDTH'];
        }
        if (isset($config['FBPACK_LIKE_SHARE'])) {
            $this->_fbPack_like_share = $config['FBPACK_LIKE_SHARE'];
        }
        if (isset($config['FBPACK_LIKE_LAYOUT'])) {
            $this->_fbPack_like_layout = $config['FBPACK_LIKE_LAYOUT'];
        }
        if (isset($config['FBPACK_LIKE_FACES'])) {
            $this->_fbPack_like_faces = $config['FBPACK_LIKE_FACES'];
        }
        if (isset($config['FBPACK_LIKE_ACTION'])) {
            $this->_fbPack_like_action = $config['FBPACK_LIKE_ACTION'];
        }
        if (isset($config['FBPACK_LIKE_COLORSCHEME'])) {
            $this->_fbPack_like_colorscheme = $config['FBPACK_LIKE_COLORSCHEME'];
        }
    }

    private function _readPagePluginProperties() {
        $config = Configuration::getMultiple(array('FBPACK_PAGE_PLUGIN', 'FBPACK_PAGE_URL', 'FBPACK_PAGE_NAME'));

        if (isset($config['FBPACK_PAGE_PLUGIN'])) {
            $this->_fbPack_page_plugin = $config['FBPACK_PAGE_PLUGIN'];
        }
        if (isset($config['FBPACK_PAGE_URL'])) {
            $this->_fbPack_page_url = $config['FBPACK_PAGE_URL'];
        }
        if (isset($config['FBPACK_PAGE_NAME'])) {
            $this->_fbPack_page_url = $config['FBPACK_PAGE_NAME'];
        }
    }

    private function _displayDonation() {
        $this->_html .= '<form>
                    <fieldset class="width3" style="width:850px">
                        <legend><img src="' . $this->_path . 'donate.png" />' . $this->l ( 'Donate' ) . '</legend>
                        <p class="clear">' . $this->l ( 'If you like this plugin and find it useful, help keep this plugin free and actively developed by clicking the donate button. Also, don\'t forget to follow me on Twitter.' ) . '</p>
                        <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=E4H8QSP3NPYU2" title="' . $this->l ( 'Donate with Paypal' ) . '" target="_blank"><img alt="' . $this->l ( 'Donate with Paypal' ) . '" src="' . $this->_path . 'donate.jpg"></a>
                        <a href="http://twitter.com/alesl/" title="' . $this->l ( 'Follow me on Twitter' ) . '" target="_blank"><img alt="' . $this->l ( 'Follow me on Twitter' ) . '" src="' . $this->_path . 'twitter.jpg"></a>
                    </fieldset>
        </form><br />';
    }

    private function _displayForm() {
        $this->_html .= '<form action="' . $_SERVER ['REQUEST_URI'] . '" method="post">
            <fieldset class="width3" style="width:850px">
                <legend><img src="' . $this->_path . 'fb.png" />' . $this->l ( 'Basic Settings' ) . '</legend>
                <label>' . $this->l ( 'App ID' ) . '</label>
                <div class="margin-form">
                    <input style="width:500px;" type="text" name="fbPack_app_id" value="' . Tools::getValue ( 'fbPack_app_id', $this->_fbPack_app_id ) . '" />
                    <p class="clear">' . $this->l ( 'This is the APP ID you need to get for Comments and Login Button. This can be retrieved from your Facebook application page: http://www.facebook.com/developers/apps' ) . '</p>
                </div>
                <label>' . $this->l ( 'App Secret' ) . '</label>
                    <div class="margin-form">
                        <input style="width:500px;" type="text" name="fbPack_app_secret" value="' . Tools::getValue ( 'fbPack_app_secret', $this->_fbPack_app_secret ) . '" />
                        <p class="clear">' . $this->l ( 'This is the APP Secret you need to get for Comments and Login Button. This can be retrieved from your Facebook application page: http://www.facebook.com/developers/apps' ) . '</p>
                    </div>
                    <label>' . $this->l ( 'Internationalization' ) . '</label>
                    <div class="margin-form">
                        <select name="fbPack_app_locale" style="width:150px">
                            <option value="af_ZA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "af_ZA" ? 'selected="selected"' : "") . '>Afrikaans</option>
                            <option value="sq_AL" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sq_AL" ? 'selected="selected"' : "") . '>Albanian</option>
                            <option value="ar_AR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ar_AR" ? 'selected="selected"' : "") . '>Arabic</option>
                            <option value="hy_AM" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "hy_AM" ? 'selected="selected"' : "") . '>Armenian</option>
                            <option value="ay_BO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ay_BO" ? 'selected="selected"' : "") . '>Aymara</option>
                            <option value="az_AZ" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "az_AZ" ? 'selected="selected"' : "") . '>Azeri</option>
                            <option value="eu_ES" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "eu_ES" ? 'selected="selected"' : "") . '>Basque</option>
                            <option value="be_BY" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "be_BY" ? 'selected="selected"' : "") . '>Belarusian</option>
                            <option value="bn_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "bn_IN" ? 'selected="selected"' : "") . '>Bengali</option>
                            <option value="bs_BA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "bs_BA" ? 'selected="selected"' : "") . '>Bosnian</option>
                            <option value="bg_BG" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "bg_BG" ? 'selected="selected"' : "") . '>Bulgarian</option>
                            <option value="ca_ES" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ca_ES" ? 'selected="selected"' : "") . '>Catalan</option>
                            <option value="ck_US" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ck_US" ? 'selected="selected"' : "") . '>Cherokee</option>
                            <option value="hr_HR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "hr_HR" ? 'selected="selected"' : "") . '>Croatian</option>
                            <option value="cs_CZ" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "cs_CZ" ? 'selected="selected"' : "") . '>Czech</option>
                            <option value="da_DK" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "da_DK" ? 'selected="selected"' : "") . '>Danish</option>
                            <option value="nl_BE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "nl_BE" ? 'selected="selected"' : "") . '>Dutch (Belgi&euml;)</option>
                            <option value="nl_NL" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "nl_NL" ? 'selected="selected"' : "") . '>Dutch</option>
                            <option value="en_PI" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "en_PI" ? 'selected="selected"' : "") . '>English (Pirate)</option>
                            <option value="en_GB" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "en_GB" ? 'selected="selected"' : "") . '>English (UK)</option>
                            <option value="en_US" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "en_US" ? 'selected="selected"' : "") . '>English (US)</option>
                            <option value="en_UD" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "en_UD" ? 'selected="selected"' : "") . '>English (Upside Down)</option>
                            <option value="eo_EO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "eo_EO" ? 'selected="selected"' : "") . '>Esperanto</option>
                            <option value="et_EE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "et_EE" ? 'selected="selected"' : "") . '>Estonian</option>
                            <option value="fo_FO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fo_FO" ? 'selected="selected"' : "") . '>Faroese</option>
                            <option value="tl_PH" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "tl_PH" ? 'selected="selected"' : "") . '>Filipino</option>
                            <option value="fb_FI" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fb_FI" ? 'selected="selected"' : "") . '>Finnish (test)</option>
                            <option value="fi_FI" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fi_FI" ? 'selected="selected"' : "") . '>Finnish</option>
                            <option value="fr_CA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fr_CA" ? 'selected="selected"' : "") . '>French (Canada)</option>
                            <option value="fr_FR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fr_FR" ? 'selected="selected"' : "") . '>French (France)</option>
                            <option value="gl_ES" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "gl_ES" ? 'selected="selected"' : "") . '>Galician</option>
                            <option value="ka_GE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ka_GE" ? 'selected="selected"' : "") . '>Georgian</option>
                            <option value="de_DE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "de_DE" ? 'selected="selected"' : "") . '>German</option>
                            <option value="el_GR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "el_GR" ? 'selected="selected"' : "") . '>Greek</option>
                            <option value="gn_PY" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "gn_PY" ? 'selected="selected"' : "") . '>Guaran&iacute;</option>
                            <option value="gu_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "gu_IN" ? 'selected="selected"' : "") . '>Gujarati</option>
                            <option value="he_IL" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "he_IL" ? 'selected="selected"' : "") . '>Hebrew</option>
                            <option value="hi_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "hi_IN" ? 'selected="selected"' : "") . '>Hindi</option>
                            <option value="hu_HU" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "hu_HU" ? 'selected="selected"' : "") . '>Hungarian</option>
                            <option value="is_IS" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "is_IS" ? 'selected="selected"' : "") . '>Icelandic</option>
                            <option value="id_ID" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "id_ID" ? 'selected="selected"' : "") . '>Indonesian</option>
                            <option value="ga_IE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ga_IE" ? 'selected="selected"' : "") . '>Irish</option>
                            <option value="it_IT" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "it_IT" ? 'selected="selected"' : "") . '>Italian</option>
                            <option value="ja_JP" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ja_JP" ? 'selected="selected"' : "") . '>Japanese</option>
                            <option value="jv_ID" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "jv_ID" ? 'selected="selected"' : "") . '>Javanese</option>
                            <option value="kn_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "kn_IN" ? 'selected="selected"' : "") . '>Kannada</option>
                            <option value="kk_KZ" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "kk_KZ" ? 'selected="selected"' : "") . '>Kazakh</option>
                            <option value="km_KH" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "km_KH" ? 'selected="selected"' : "") . '>Khmer</option>
                            <option value="tl_ST" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "tl_ST" ? 'selected="selected"' : "") . '>Klingon</option>
                            <option value="ko_KR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ko_KR" ? 'selected="selected"' : "") . '>Korean</option>
                            <option value="ku_TR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ku_TR" ? 'selected="selected"' : "") . '>Kurdish</option>
                            <option value="la_VA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "la_VA" ? 'selected="selected"' : "") . '>Latin</option>
                            <option value="lv_LV" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "lv_LV" ? 'selected="selected"' : "") . '>Latvian</option>
                            <option value="fb_LT" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fb_LT" ? 'selected="selected"' : "") . '>Leet Speak</option>
                            <option value="li_NL" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "li_NL" ? 'selected="selected"' : "") . '>Limburgish</option>
                            <option value="lt_LT" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "lt_LT" ? 'selected="selected"' : "") . '>Lithuanian</option>
                            <option value="mk_MK" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "mk_MK" ? 'selected="selected"' : "") . '>Macedonian</option>
                            <option value="mg_MG" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "mg_MG" ? 'selected="selected"' : "") . '>Malagasy</option>
                            <option value="ms_MY" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ms_MY" ? 'selected="selected"' : "") . '>Malay</option>
                            <option value="ml_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ml_IN" ? 'selected="selected"' : "") . '>Malayalam</option>
                            <option value="mt_MT" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "mt_MT" ? 'selected="selected"' : "") . '>Maltese</option>
                            <option value="mr_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "mr_IN" ? 'selected="selected"' : "") . '>Marathi</option>
                            <option value="mn_MN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "mn_MN" ? 'selected="selected"' : "") . '>Mongolian</option>
                            <option value="ne_NP" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ne_NP" ? 'selected="selected"' : "") . '>Nepali</option>
                            <option value="se_NO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "se_NO" ? 'selected="selected"' : "") . '>Northern S&aacute;mi</option>
                            <option value="nb_NO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "nb_NO" ? 'selected="selected"' : "") . '>Norwegian (bokmal)</option>
                            <option value="nn_NO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "nn_NO" ? 'selected="selected"' : "") . '>Norwegian (nynorsk)</option>
                            <option value="ps_AF" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ps_AF" ? 'selected="selected"' : "") . '>Pashto</option>
                            <option value="fa_IR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "fa_IR" ? 'selected="selected"' : "") . '>Persian</option>
                            <option value="pl_PL" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "pl_PL" ? 'selected="selected"' : "") . '>Polish</option>
                            <option value="pt_BR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "pt_BR" ? 'selected="selected"' : "") . '>Portuguese (Brazil)</option>
                            <option value="pt_PT" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "pt_PT" ? 'selected="selected"' : "") . '>Portuguese (Portugal)</option>
                            <option value="pa_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "pa_IN" ? 'selected="selected"' : "") . '>Punjabi</option>
                            <option value="qu_PE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "qu_PE" ? 'selected="selected"' : "") . '>Quechua</option>
                            <option value="ro_RO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ro_RO" ? 'selected="selected"' : "") . '>Romanian</option>
                            <option value="rm_CH" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "rm_CH" ? 'selected="selected"' : "") . '>Romansh</option>
                            <option value="ru_RU" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ru_RU" ? 'selected="selected"' : "") . '>Russian</option>
                            <option value="sa_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sa_IN" ? 'selected="selected"' : "") . '>Sanskrit</option>
                            <option value="sr_RS" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sr_RS" ? 'selected="selected"' : "") . '>Serbian</option>
                            <option value="zh_CN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "zh_CN" ? 'selected="selected"' : "") . '>Simplified Chinese (China)</option>
                            <option value="sk_SK" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sk_SK" ? 'selected="selected"' : "") . '>Slovak</option>
                            <option value="sl_SI" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sl_SI" ? 'selected="selected"' : "") . '>Slovenian</option>
                            <option value="so_SO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "so_SO" ? 'selected="selected"' : "") . '>Somali</option>
                            <option value="es_CL" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "es_CL" ? 'selected="selected"' : "") . '>Spanish (Chile)</option>
                            <option value="es_CO" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "es_CO" ? 'selected="selected"' : "") . '>Spanish (Colombia)</option>
                            <option value="es_MX" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "es_MX" ? 'selected="selected"' : "") . '>Spanish (Mexico)</option>
                            <option value="es_ES" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "es_ES" ? 'selected="selected"' : "") . '>Spanish (Spain)</option>
                            <option value="es_VE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "es_VE" ? 'selected="selected"' : "") . '>Spanish (Venezuela)</option>
                            <option value="es_LA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "es_LA" ? 'selected="selected"' : "") . '>Spanish</option>
                            <option value="sw_KE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sw_KE" ? 'selected="selected"' : "") . '>Swahili</option>
                            <option value="sv_SE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sv_SE" ? 'selected="selected"' : "") . '>Swedish</option>
                            <option value="sy_SY" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "sy_SY" ? 'selected="selected"' : "") . '>Syriac</option>
                            <option value="tg_TJ" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "tg_TJ" ? 'selected="selected"' : "") . '>Tajik</option>
                            <option value="ta_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ta_IN" ? 'selected="selected"' : "") . '>Tamil</option>
                            <option value="tt_RU" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "tt_RU" ? 'selected="selected"' : "") . '>Tatar</option>
                            <option value="te_IN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "te_IN" ? 'selected="selected"' : "") . '>Telugu</option>
                            <option value="th_TH" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "th_TH" ? 'selected="selected"' : "") . '>Thai</option>
                            <option value="zh_HK" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "zh_HK" ? 'selected="selected"' : "") . '>Traditional Chinese (Hong Kong)</option>
                            <option value="zh_TW" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "zh_TW" ? 'selected="selected"' : "") . '>Traditional Chinese (Taiwan)</option>
                            <option value="tr_TR" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "tr_TR" ? 'selected="selected"' : "") . '>Turkish</option>
                            <option value="uk_UA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "uk_UA" ? 'selected="selected"' : "") . '>Ukrainian</option>
                            <option value="ur_PK" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "ur_PK" ? 'selected="selected"' : "") . '>Urdu</option>
                            <option value="uz_UZ" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "uz_UZ" ? 'selected="selected"' : "") . '>Uzbek</option>
                            <option value="vi_VN" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "vi_VN" ? 'selected="selected"' : "") . '>Vietnamese</option>
                            <option value="cy_GB" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "cy_GB" ? 'selected="selected"' : "") . '>Welsh</option>
                            <option value="xh_ZA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "xh_ZA" ? 'selected="selected"' : "") . '>Xhosa</option>
                            <option value="yi_DE" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "yi_DE" ? 'selected="selected"' : "") . '>Yiddish</option>
                            <option value="zu_ZA" ' . (Tools::getValue ( 'fbPack_app_locale', $this->_fbPack_app_locale ) == "zu_ZA" ? 'selected="selected"' : "") . '>Zulu</option>
                        </select>
                        <p class="clear">' . $this->l ( 'Set appropriate locale for your site. You can read more about supported locales here: http://developers.facebook.com/docs/internationalization/' ) . '</p>
                    </div>
                <input type="submit" name="submitBasicSettings" value="' . $this->l ( 'Update basic settings' ) . '" class="button" />
            </fieldset>
        </form>
        <br />
        <form action="' . $_SERVER ['REQUEST_URI'] . '" method="post">
            <fieldset class="width3" style="width:850px">
                <legend><img src="' . $this->_path . 'like.png" />' . $this->l ( 'Facebook Like Button' ) . '</legend>
                <label>' . $this->l ( 'Enable Plugin' ) . '</label>
                <div class="margin-form">
                    <input type="radio" name="fbPack_like_button" value="yes" ' . (Tools::getValue ( 'fbPack_like_button', $this->_fbPack_like_button ) == 'yes' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'yes' ) . '
                    <input type="radio" name="fbPack_like_button" value="no" ' . (Tools::getValue ( 'fbPack_like_button', $this->_fbPack_like_button ) == 'no' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'no' ) . '
                    <p class="clear">' . $this->l ( 'Enable or Disable Facebook Like Button' ) . '</p>
                </div>
                <label>' . $this->l ( 'URL to Like' ) . '</label>
                <div class="margin-form">
                    <input style="width:500px;" type="text" name="fbPack_like_url" value="' . Tools::getValue ( 'fbPack_like_url', $this->_fbPack_like_url ) . '" />
                    <p class="clear">' . $this->l ( 'The URL to like. Defaults to the current page.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Send Button' ) . '</label>
                <div class="margin-form">
                    <input type="radio" name="fbPack_like_send" value="yes" ' . (Tools::getValue ( 'fbPack_like_send', $this->_fbPack_like_send ) == 'yes' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'yes' ) . '
                    <input type="radio" name="fbPack_like_send" value="no" ' . (Tools::getValue ( 'fbPack_like_send', $this->_fbPack_like_send ) == 'no' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'no' ) . '
                    <p class="clear">' . $this->l ( 'Include a Send button.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Layout Style' ) . '</label>
                <div class="margin-form">
                    <select name="fbPack_like_layout" style="width:150px">
                        <option value="standard" ' . (Tools::getValue ( 'fbPack_like_layout', $this->_fbPack_like_layout ) == "standard" ? 'selected="selected"' : "") . '>' . $this->l ( 'Standard' ) . '</option>
                        <option value="button_count" ' . (Tools::getValue ( 'fbPack_like_layout', $this->_fbPack_like_layout ) == "button_count" ? 'selected="selected"' : "") . '>' . $this->l ( 'Button (Count)' ) . '</option>
                        <option value="box_count" ' . (Tools::getValue ( 'fbPack_like_layout', $this->_fbPack_like_layout ) == "box_count" ? 'selected="selected"' : "") . '>' . $this->l ( 'Box (Count)' ) . '</option>
                    </select>
                    <p class="clear">' . $this->l ( 'Determines the size and amount of social context next to the button.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Width' ) . '</label>
                <div class="margin-form">
                    <input type="text" name="fbPack_like_width" value="' . Tools::getValue ( 'fbPack_like_width', $this->_fbPack_like_width ) . '" />
                    <p class="clear">' . $this->l ( 'The width of the plugin, in pixels.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Show Faces' ) . '</label>
                <div class="margin-form">
                    <input type="checkbox" name="fbPack_like_faces" value="1" ' . (Tools::getValue ( 'fbPack_like_faces', $this->_fbPack_like_faces ) ? 'checked="checked"' : false) . ' />
                    <p class="clear">' . $this->l ( 'Show profile pictures below the button. (Standard layout only)' ) . '</p>
                </div>
                <label>' . $this->l ( 'Verb to display' ) . '</label>
                <div class="margin-form">
                    <select name="fbPack_like_action" style="width:150px">
                        <option value="like" ' . (Tools::getValue ( 'fbPack_like_action', $this->_fbPack_like_action ) == "like" ? 'selected="selected"' : "") . '>' . $this->l ( 'Like' ) . '</option>
                        <option value="recommend" ' . (Tools::getValue ( 'fbPack_like_action', $this->_fbPack_like_action ) == "recommend" ? 'selected="selected"' : "") . '>' . $this->l ( 'Recommend' ) . '</option>
                    </select>
                    <p class="clear">' . $this->l ( 'The verb to display in the button. Currently only \'like\' and \'recommend\' are supported.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Color Scheme' ) . '</label>
                <div class="margin-form">
                    <select name="fbPack_like_color" style="width:150px">
                        <option value="light" ' . (Tools::getValue ( 'fbPack_like_color', $this->_fbPack_like_color ) == "light" ? 'selected="selected"' : "") . '>' . $this->l ( 'Light' ) . '</option>
                        <option value="dark" ' . (Tools::getValue ( 'fbPack_like_color', $this->_fbPack_like_color ) == "dark" ? 'selected="selected"' : "") . '>' . $this->l ( 'Dark' ) . '</option>
                    </select>
                    <p class="clear">' . $this->l ( 'The color scheme of the plugin.' ) . '</p>
                </div>
                <input type="submit" name="submitLikeButton" value="' . $this->l ( 'Update settings for Like Button' ) . '" class="button" />
            </fieldset>
        </form>
        <br />
        <form action="' . $_SERVER ['REQUEST_URI'] . '" method="post">
            <fieldset class="width3" style="width:850px">
                <legend><img src="' . $this->_path . 'like.png" />' . $this->l ( 'Facebook Like Box' ) . '</legend>
                <label>' . $this->l ( 'Enable Plugin' ) . '</label>
                <div class="margin-form">
                    <input type="radio" name="fbPack_like_box" value="yes" ' . (Tools::getValue ( 'fbPack_like_box', $this->_fbPack_like_box ) == 'yes' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'yes' ) . '
                    <input type="radio" name="fbPack_like_box" value="no" ' . (Tools::getValue ( 'fbPack_like_box', $this->_fbPack_like_box ) == 'no' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'no' ) . '
                    <p class="clear">' . $this->l ( 'Enable or Disable Facebook Like Box' ) . '</p>
                </div>
                <label>' . $this->l ( 'Facebook Page URL' ) . '</label>
                <div class="margin-form">
                    <input style="width:500px;" type="text" name="fbPack_facebook_page_url" value="' . Tools::getValue ( 'fbPack_facebook_page_url', $this->_fbPack_facebook_page_url ) . '" />
                    <p class="clear">' . $this->l ( 'The URL of the Facebook Page for this Like box.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Width' ) . '</label>
                <div class="margin-form">
                    <input type="text" name="fbPack_box_width" value="' . Tools::getValue ( 'fbPack_box_width', $this->_fbPack_box_width ) . '" />
                    <p class="clear">' . $this->l ( 'The width of the plugin in pixels.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Height' ) . '</label>
                <div class="margin-form">
                    <input type="text" name="fbPack_box_height" value="' . Tools::getValue ( 'fbPack_box_height', $this->_fbPack_box_height ) . '" />
                    <p class="clear">' . $this->l ( 'The height of the plugin in pixels.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Color Scheme' ) . '</label>
                <div class="margin-form">
                    <select name="fbPack_box_color" style="width:150px">
                        <option value="light" ' . (Tools::getValue ( 'fbPack_box_color', $this->_fbPack_box_color ) == "light" ? 'selected="selected"' : "") . '>' . $this->l ( 'Light' ) . '</option>
                        <option value="dark" ' . (Tools::getValue ( 'fbPack_box_color', $this->_fbPack_box_color ) == "dark" ? 'selected="selected"' : "") . '>' . $this->l ( 'Dark' ) . '</option>
                    </select>
                    <p class="clear">' . $this->l ( 'The color scheme of the plugin.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Show Faces' ) . '</label>
                <div class="margin-form">
                    <input type="checkbox" name="fbPack_box_faces" value="1" ' . (Tools::getValue ( 'fbPack_box_faces', $this->_fbPack_box_faces ) ? 'checked="checked"' : false) . ' />
                    <p class="clear">' . $this->l ( 'Show profile photos in the plugin.' ) . '</p>
                </div>
                 <label>' . $this->l ( 'Border Color' ) . '</label>
                <div class="margin-form">
                    <input type="text" name="fbPack_box_border_color" value="' . Tools::getValue ( 'fbPack_box_border_color', $this->_fbPack_box_border_color ) . '" />
                    <p class="clear">' . $this->l ( 'The border color of the plugin.' ) . '</p>
                </div>

                <label>' . $this->l ( 'Show Stream' ) . '</label>
                <div class="margin-form">
                    <input type="checkbox" name="fbPack_box_stream" value="1" ' . (Tools::getValue ( 'fbPack_box_stream', $this->_fbPack_box_stream ) ? 'checked="checked"' : false) . ' />
                    <p class="clear">' . $this->l ( 'Show the profile stream for the public profile.' ) . '</p>
                </div>

                <label>' . $this->l ( 'Show Header' ) . '</label>
                <div class="margin-form">
                    <input type="checkbox" name="fbPack_box_header" value="1" ' . (Tools::getValue ( 'fbPack_box_header', $this->_fbPack_box_header ) ? 'checked="checked"' : false) . ' />
                    <p class="clear">' . $this->l ( 'Show the \'Find us on Facebook\' bar at top. Only shown when either stream or faces are present.' ) . '</p>
                </div>
                <input type="submit" name="submitLikeBox" value="' . $this->l ( 'Update settings for Like Box' ) . '" class="button" />
            </fieldset>
        </form>
        <br />
        <form action="' . $_SERVER ['REQUEST_URI'] . '" method="post">
            <fieldset class="width3" style="width:850px">
                <legend><img src="' . $this->_path . 'wall_post.png" />' . $this->l ( 'Comments' ) . '</legend>
                <label>' . $this->l ( 'Enable Plugin' ) . '</label>
                <div class="margin-form">
                    <input type="radio" name="fbPack_comments" value="yes" ' . (Tools::getValue ( 'fbPack_comments', $this->_fbPack_comments ) == 'yes' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'yes' ) . '
                    <input type="radio" name="fbPack_comments" value="no" ' . (Tools::getValue ( 'fbPack_comments', $this->_fbPack_comments ) == 'no' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'no' ) . '
                    <p class="clear">' . $this->l ( 'Enable or Disable Comments' ) . '</p>
                </div>
                <label>' . $this->l ( 'Number of posts' ) . '</label>
                <div class="margin-form">
                    <input type="text" name="fbPack_comments_posts" value="' . Tools::getValue ( 'fbPack_comments_posts', $this->_fbPack_comments_posts ) . '" />
                    <p class="clear">' . $this->l ( 'The number of posts to display by default.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Width' ) . '</label>
                <div class="margin-form">
                    <input type="text" name="fbPack_comments_width" value="' . Tools::getValue ( 'fbPack_comments_width', $this->_fbPack_comments_width ) . '" />
                    <p class="clear">' . $this->l ( 'The width of the plugin, in pixels.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Color Scheme' ) . '</label>
                <div class="margin-form">
                    <select name="fbPack_comments_color" style="width:150px">
                        <option value="light" ' . (Tools::getValue ( 'fbPack_comments_color', $this->_fbPack_comments_color ) == "light" ? 'selected="selected"' : "") . '>' . $this->l ( 'Light' ) . '</option>
                        <option value="dark" ' . (Tools::getValue ( 'fbPack_comments_color', $this->_fbPack_comments_color ) == "dark" ? 'selected="selected"' : "") . '>' . $this->l ( 'Dark' ) . '</option>
                    </select>
                    <p class="clear">' . $this->l ( 'The color scheme of the plugin.' ) . '</p>
                </div>
                <label>' . $this->l ( 'Moderators' ) . '</label>
                <div class="margin-form">
                    <input style="width:500px;" type="text" name="fbPack_comments_moderators" value="' . Tools::getValue ( 'fbPack_comments_moderators', $this->_fbPack_comments_moderators ) . '" />
                    <p class="clear">' . $this->l ( 'Comments moderators (user ID, see: http://www.facebook.com/note.php?note_id=91532827198). To add multiple moderators, separate the uids by comma without spaces.' ) . '</p>
                </div>
                <input type="submit" name="submitComments" value="' . $this->l ( 'Update settings for Comments' ) . '" class="button" />
            </fieldset>
        </form>
        <br />
        <form action="' . $_SERVER ['REQUEST_URI'] . '" method="post">
            <fieldset class="width3" style="width:850px">
                <legend><img src="' . $this->_path . 'fb_white.png" />' . $this->l ( 'Login Button' ) . '</legend>
                <label>' . $this->l ( 'Enable Plugin' ) . '</label>
                <div class="margin-form">
                    <input type="radio" name="fbPack_login_button" value="yes" ' . (Tools::getValue ( 'fbPack_login_button', $this->_fbPack_login_button ) == 'yes' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'yes' ) . '
                    <input type="radio" name="fbPack_login_button" value="no" ' . (Tools::getValue ( 'fbPack_login_button', $this->_fbPack_login_button ) == 'no' ? 'checked="checked" ' : '') . '/>' . $this->l ( 'no' ) . '
                    <p class="clear">' . $this->l ( 'Enable or Disable Login Button' ) . '</p>
                </div>
                <input type="submit" name="submitLogin" value="' . $this->l ( 'Update settings for Login Button' ) . '" class="button" />
            </fieldset>
        </form>';
    }
}
