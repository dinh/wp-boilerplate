<?php
/**
 * The file that defines the main start class
 *
 * A class definition that includes attributes and functions used across both the
 * theme-facing side of the site and the admin area.
 *
 * @package    theme_name
 * @since      1.0.0
 */

namespace Inf_Theme\Includes;

use Inf_Theme\Admin as Admin;
use Inf_Theme\Theme as Theme;
use Inf_Theme\Theme\Menu as Menu;
use Inf_Theme\Theme\Acf as Acf;
use Inf_Theme\Theme\Utils as Utils;

/**
 * The main start class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * theme-facing site hooks.
 *
 * Also maintains the unique identifier of this theme as well as the current
 * version of the theme.
 *
 * @package    theme_name
 * @since      1.0.0
 */
class Main {


  /**
   * Loader variable for hooks
   *
   * @var Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * Global theme name
   *
   * @var string
   */
  protected $theme_name;

  /**
   * Global theme version
   *
   * @var string
   */
  protected $theme_version;

  /**
   * Global assets version
   *
   * @var string
   */
  protected $assets_version;

  /**
   * Define the main functionality.
   *
   * Set the name and the version that can be used.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @package    theme_name
   * @since      1.0.0
   */
  public function __construct() {

    if ( defined( 'INF_ASSETS_VERSION' ) ) {
      $this->theme_version = INF_ASSETS_VERSION;
    } else {
      $this->theme_version = '1.0.0';
    }

    if ( defined( 'INF_ASSETS_VERSION' ) ) {
      $this->assets_version = INF_ASSETS_VERSION;
    } else {
      $this->assets_version = '1.0.0';
    }

    if ( defined( 'INF_THEME_NAME' ) ) {
      $this->theme_name = INF_THEME_NAME;
    } else {
      $this->theme_name = 'theme_name';
    }

    $this->load_dependencies();
    $this->define_admin_hooks();
    $this->define_theme_hooks();
  }

  /**
   * Load the required dependencies.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @package    theme_name
   * @since      1.0.0
   */
  private function load_dependencies() {
    $this->loader = new Loader();
  }

  /**
   * Register all of the hooks related to the admin area functionality.
   *
   * @package    theme_name
   * @since      1.0.0
   */
  private function define_admin_hooks() {
    $this->admin = new Admin\Admin( $this->get_theme_info() );
    $this->login = new Admin\Login( $this->get_theme_info() );
    $this->editor = new Admin\Editor( $this->get_theme_info() );
    $this->sidebar = new Admin\Sidebar( $this->get_theme_info() );
    $this->users = new Admin\Users( $this->get_theme_info() );
    $this->acf = new Admin\Acf( $this->get_theme_info() );

    // Admin.
    $this->loader->add_action( 'login_enqueue_scripts', $this->admin, 'enqueue_styles' );
    $this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_styles', 50 );
    $this->loader->add_action( 'admin_body_class', $this->admin, 'set_enviroment_body_class' );

    // Login page.
    $this->loader->add_filter( 'login_headerurl', $this->login, 'custom_login_url' );

    // Editor.
    $this->loader->add_action( 'admin_init', $this->editor, 'add_editor_styles' );

    // Sidebar.
    $this->loader->add_action( 'admin_menu', $this->sidebar, 'remove_sub_menus' );

    // Users.
    $this->loader->add_action( 'set_user_role', $this->users, 'send_main_when_user_role_changes', 10, 2 );
    $this->loader->add_action( 'admin_init', $this->users, 'edit_editors_compatibilities' );

    // ACF.
    $this->loader->add_action( 'acf/fields/google_map/api', $this->acf, 'set_google_map_api_key' );
    $this->loader->add_action( 'acf/fields/wysiwyg/toolbars', $this->acf, 'add_wysiwyg_toolbars' );

  }

  /**
   * Register all of the hooks related to the admin area functionality.
   *
   * @package    theme_name
   * @since      1.0.0
   */
  private function define_theme_hooks() {
    $this->theme = new Theme\Theme( $this->get_theme_info() );
    $this->legacy_browsers = new Theme\Legacy_Browsers( $this->get_theme_info() );
    $this->widgets = new Theme\Widgets( $this->get_theme_info() );
    $this->menu = new Menu\Menu( $this->get_theme_info() );
    $this->theme_options_general = new Acf\Theme_Options_General( $this->get_theme_info() );
    $this->media = new Theme\Media( $this->get_theme_info() );
    $this->gallery = new Utils\Gallery( $this->get_theme_info() );
    $this->general = new Theme\General( $this->get_theme_info() );
    $this->pagination = new Theme\Pagination( $this->get_theme_info() );

    // Enque styles and scripts.
    $this->loader->add_action( 'wp_enqueue_scripts', $this->theme, 'enqueue_styles' );
    $this->loader->add_action( 'wp_enqueue_scripts', $this->theme, 'enqueue_scripts' );

    // Remove inline gallery css.
    $this->loader->add_filter( 'use_default_gallery_style', $this->theme, '__return_false' );

    // Legacy Browsers.
    $this->loader->add_action( 'template_redirect', $this->legacy_browsers, 'redirect_to_legacy_browsers_page' );

    // Widgets.
    $this->loader->add_action( 'widgets_init', $this->widgets, 'register_widget_position' );

    // Menu.
    $this->loader->add_action( 'after_setup_theme', $this->menu, 'register_menu_positions' );

    // Optimizations
    // This is removing the functionality but it is removing meta tags from head.
    $this->loader->remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    $this->loader->remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    $this->loader->remove_action( 'wp_print_styles', 'print_emoji_styles' );
    $this->loader->remove_action( 'admin_print_styles', 'print_emoji_styles' );
    $this->loader->remove_action( 'wp_head', 'wp_generator' );
    $this->loader->remove_action( 'wp_head', 'wlwmanifest_link' );
    $this->loader->remove_action( 'wp_head', 'wp_shortlink_wp_head' );
    $this->loader->remove_action( 'wp_head', 'rsd_link' );
    $this->loader->remove_action( 'wp_head', 'feed_links', 2 );
    $this->loader->remove_action( 'wp_head', 'feed_links_extra', 3 );
    $this->loader->remove_action( 'wp_head', 'rest_output_link_wp_head' );

    // Theme Options.
    $this->loader->add_action( 'acf/init', $this->theme_options_general, 'create_theme_options_page' );
    $this->loader->add_action( 'acf/init', $this->theme_options_general, 'register_theme_options' );
    $this->loader->add_action( 'acf/init', $this->theme_options_general, 'register_global_theme_options_variable' );
    $this->loader->add_action( 'acf/save_post', $this->theme_options_general, 'delete_theme_options_transient' );

    // Media.
    $this->loader->add_action( 'upload_mimes', $this->media, 'enable_mime_types' );
    $this->loader->add_action( 'wp_prepare_attachment_for_js', $this->media, 'enable_svg_library_preview', 10, 3 );
    $this->loader->add_action( 'embed_oembed_html', $this->media, 'wrap_responsive_oembed_filter', 10, 4 );
    $this->loader->add_action( 'after_setup_theme', $this->media, 'add_theme_support' );
    $this->loader->add_action( 'after_setup_theme', $this->media, 'add_custom_image_sizes' );

    // Gallery.
    $this->loader->add_filter( 'post_gallery', $this->gallery, 'wrap_post_gallery', 10 ,3 );

    // General.
    $this->loader->add_action( 'after_setup_theme', $this->general, 'add_theme_support' );

    // Pagination.
    $this->loader->add_filter( 'next_posts_link_attributes', $this->pagination, 'pagination_link_next_class' );
    $this->loader->add_filter( 'previous_posts_link_attributes', $this->pagination, 'pagination_link_prev_class' );

  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @package    theme_name
   * @since      1.0.0
   */
  public function run() {
    $this->loader->run();
  }

  /**
   * The reference to the class that orchestrates the hooks.
   *
   * @package    theme_name
   * @since      1.0.0
   * @return    Loader    Orchestrates the hooks.
   */
  public function get_loader() {
    return $this->loader;
  }

  /**
   * The name used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @package   theme_name
   * @since     1.0.0
   * @return    string    The name.
   */
  public function get_theme_name() {
    return $this->theme_name;
  }

  /**
   * Retrieve the version number.
   *
   * @package   theme_name
   * @since     1.0.0
   * @return    string    The version number.
   */
  public function get_theme_version() {
    return $this->theme_version;
  }

  /**
   * Retrieve the assets version number.
   *
   * @since     1.0.0
   * @return    string    The assets version number.
   */
  public function get_assets_version() {
    return $this->assets_version;
  }

  /**
   * Retrieve the theme info.
   *
   * @since     1.0.0
   * @return    array    The theme info.
   */
  public function get_theme_info() {
    return array(
        'theme_name' => $this->theme_name,
        'theme_version' => $this->theme_version,
        'assets_version' => $this->assets_version,
    );
  }
}
