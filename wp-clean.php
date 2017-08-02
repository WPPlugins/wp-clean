<?php
/*
Plugin Name: WP Clean
Plugin URI: https://artemsannikov.ru/plugins/wp-clean/
Description: Плагин WP-Clean удаляет лишние meta-теги и отключает невостребованные функции в CMS WordPress.
Version: 1.4
Author: Artem Sannikov
Author URI: https://artemsannikov.ru
Donate link: https://artemsannikov.ru/donate/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*
Copyright 2016  Artem Sannikov  (email : info@artemsannikov.ru)
 
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.
 
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//Убираем излишние meta теги системы wordpress
function wpcl_remove_meta_tags(){
	remove_action('wp_head', 'wp_generator');//выводит номер версии движка.
	remove_action('wp_head', 'wlwmanifest_link');//используется блог-клиентом Windows Live Writer.
	remove_action('wp_head', 'start_post_rel_link', 10, 0);
	remove_action('wp_head', 'parent_post_rel_link', 10, 0);
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);//Ссылки на соседние статьи (<link rel='next'... <link rel='prev'...)
	remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);//Короткая ссылка, ссылка без ЧПУ <link rel='shortlink'	
}
$wpcl_remove_meta_tags = 'wpcl_remove_meta_tags';
$wpcl_remove_meta_tags();


//Удаление <link rel='dns-prefetch'

//пока пользователь находится на странице и мощности компьютера простаивают вхолостую, можно самостоятельно указать для загрузки либо статическое изображение, либо js-библиотеку, либо целую страницу, которые теоретически понадобятся этому пользователю для дальнейшей работы с сайтом.

function wpcl_remove_dns_prefetch(){
	remove_action( 'wp_head', 'wp_resource_hints', 2 );
}
$wpcl_remove_dns_prefetch = 'wpcl_remove_dns_prefetch';
$wpcl_remove_dns_prefetch();


//Удаление RSS-ленты

function wpcl_disable_feed(){
	wp_redirect(get_option('siteurl'));
}

add_action('do_feed', 'wpcl_disable_feed', 1);
add_action('do_feed_rdf', 'wpcl_disable_feed', 1);
add_action('do_feed_rss', 'wpcl_disable_feed', 1);
add_action('do_feed_rss2', 'wpcl_disable_feed', 1);
add_action('do_feed_atom', 'wpcl_disable_feed', 1);

remove_action( 'wp_head', 'feed_links_extra', 3 );//выводит ссылки на дополнительные RSS-ленты сайта.
remove_action( 'wp_head', 'feed_links', 2 );//выводит ссылки на основные RSS-ленты сайта. 
remove_action( 'wp_head', 'rsd_link' );//используется блог-клиентами.


//Удаляем WP и левые ссылки из панели администратор (Верхний admin bar)
function wpcl_remove_link_admin_bar(){
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('wp-logo');
	$wp_admin_bar->remove_menu('about');
	$wp_admin_bar->remove_menu('wporg');
	$wp_admin_bar->remove_menu('documentation');
	$wp_admin_bar->remove_menu('support-forums');
	$wp_admin_bar->remove_menu('feedback');
	$wp_admin_bar->remove_menu('view-site');
}
add_action('wp_before_admin_bar_render', 'wpcl_remove_link_admin_bar');


//Защита - убирает записи в админ панели что введенный пароль/логин не верный
function wpcl_remove_login_text(){
	add_filter('login_errors',create_function('$a', "return null;"));
}
$wpcl_remove_login_text = 'wpcl_remove_meta_tags';
$wpcl_remove_login_text();


//Удаляем Wp-json, Oembed, Embed
function wpcl_remove_json_oe_em(){
	// Filters for WP-API version 1.x
	add_filter('json_enabled', '__return_false');
	add_filter('json_jsonp_enabled', '__return_false');
	// Отключаем сам REST API - Filters for WP-API version 2.x
	add_filter('rest_enabled', '__return_false');
	add_filter('rest_jsonp_enabled', '__return_false');
	 
	// Отключаем фильтры REST API
	remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd');
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10, 0);
	remove_action( 'template_redirect', 'rest_output_link_header', 11, 0);
	remove_action( 'auth_cookie_malformed', 'rest_cookie_collect_status');
	remove_action( 'auth_cookie_expired', 'rest_cookie_collect_status');
	remove_action( 'auth_cookie_bad_username', 'rest_cookie_collect_status');
	remove_action( 'auth_cookie_bad_hash', 'rest_cookie_collect_status');
	remove_action( 'auth_cookie_valid', 'rest_cookie_collect_status');
	remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100);
	 
	// Отключаем события REST API
	remove_action( 'init', 'rest_api_init');
	remove_action( 'rest_api_init', 'rest_api_default_filters', 10, 1);
	remove_action( 'parse_request', 'rest_api_loaded');
	 
	// Отключаем Embeds связанные с REST API
	remove_action( 'rest_api_init', 'wp_oembed_register_route');
	remove_filter( 'rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4);
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );	
}
$wpcl_remove_json_oe_em = 'wpcl_remove_json_oe_em';
$wpcl_remove_json_oe_em();

//Удаление из поиска всех статических страниц
function wpcl_excludes_static_page_search($query){
	if($query->is_search) {
		$query->set('post_type', 'post');
	}
	return $query;
}
add_filter('pre_get_posts','wpcl_excludes_static_page_search');


//Отключение стандартных виджетов
/*function wpcl_disable_default_widget() {
	unregister_widget('WP_Widget_Archives'); // Архивы
	unregister_widget('WP_Widget_Calendar'); // Календарь
	unregister_widget('WP_Widget_Categories'); // Рубрики
	unregister_widget('WP_Widget_Meta'); // Мета
	unregister_widget('WP_Widget_Pages'); // Страницы
	unregister_widget('WP_Widget_Recent_Comments'); // Свежие комментарии
	unregister_widget('WP_Widget_Recent_Posts'); // Свежие записи
	unregister_widget('WP_Widget_RSS'); // RSS
	unregister_widget('WP_Widget_Search'); // Поиск
	unregister_widget('WP_Widget_Tag_Cloud'); // Облако меток
	//unregister_widget('WP_Widget_Text'); // Текст
	//unregister_widget('WP_Nav_Menu_Widget'); // Произвольное меню
}
add_action( 'widgets_init', 'wpcl_disable_default_widget', 20 );*/


//Отключаем Emoji WordPress (смайлы Эмодзи)
function wpcl_disable_emoji(){
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );	
}
$wpcl_disable_emoji = 'wpcl_disable_emoji';
$wpcl_disable_emoji();


//Отключаем функцию трекбек на себя
function wpcl_disable_trackback_self(&$links){
	$site_url = get_option( 'home' );
	foreach ( $links as $key => $val)
	if(strpos( $val, $site_url ) !== false)unset($links[$key]);
}
add_action('pre_ping','wpcl_disable_trackback_self');

//Отключение автоматического сохранения записи
function disableAutoSave(){
	wp_deregister_script('autosave');
}
add_action( 'wp_print_scripts', 'disableAutoSave' );


//Как скрыть фразу «Спасибо вам за творчество с WordPress»?
function wph_admin_footer_text () {

}
add_filter('admin_footer_text', 'wph_admin_footer_text');

//Удаление поля сайт в форме комментариев
function remove_comment_fields($fields) {
	unset($fields['url']);
	return $fields;
}
add_filter('comment_form_default_fields', 'remove_comment_fields');


//отключение возможности выбора цветовой гаммы в WordPress
function disable_admin_color_scheme() {
   global $_wp_admin_css_colors;// создаём глобальные настройки
   $_wp_admin_css_colors = 0;//отмена выбора цвета
}
add_action('admin_head', 'disable_admin_color_scheme');


//Удаление раздела "Помощь" в панели управления
add_action('admin_head', 'wp_admin_remove_help_tabs');
 
function wp_admin_remove_help_tabs() {
    $screen = get_current_screen();
    $screen->remove_help_tabs();
}

//удаление сообщения в панели управления "Ваш браузер устарел"
function remove_warning_text_browser_wp_admin() {
    remove_meta_box('dashboard_browser_nag', 'dashboard', 'normal');
}
add_action('wp_dashboard_setup', 'remove_warning_text_browser_wp_admin');

//Админка wordpress: удаляем ненужные ссылки, элементы
/*function remove_dashboard_meta() {
        remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');//since 3.8
}
add_action( 'admin_init', 'remove_dashboard_meta' );*/


//Удаление ?ver из скриптов и стилей
function _remove_script_version($src){
	$parts = explode('?', $src);
	return $parts[0];
}
add_filter('script_loader_src','_remove_script_version',15,1);//Это для скриптов
add_filter('style_loader_src','_remove_script_version',15,1);//Это для стилей
?>