<?php
/**
 * Post Plugin Theme Handler.
 *
 * @package MainWP/Dashboard
 */

namespace MainWP\Dashboard;

/**
 * Class MainWP_Post_Plugin_Theme_Handler
 *
 * @package MainWP\Dashboard
 */
class MainWP_Post_Plugin_Theme_Handler extends MainWP_Post_Base_Handler {

	/**
	 * Protected static variable to hold the single instance of the class.
	 *
	 * @var mixed Default null
	 */
	private static $instance = null;

	/**
	 * Method instance()
	 *
	 * Create public static instance.
	 *
	 * @static
	 * @return MainWP_Post_Plugin_Theme_Handler
	 */
	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init plugins/themes actions
	 */
	public function init() {
		// Page: ManageSites.
		$this->add_action( 'mainwp_ext_prepareinstallplugintheme', array( &$this, 'mainwp_ext_prepareinstallplugintheme' ) );
		$this->add_action( 'mainwp_ext_performinstallplugintheme', array( &$this, 'mainwp_ext_performinstallplugintheme' ) );

		// Page: InstallPlugins/Themes.
		$this->add_action( 'mainwp_preparebulkinstallplugintheme', array( &$this, 'mainwp_preparebulkinstallplugintheme' ) );
		$this->add_action( 'mainwp_installbulkinstallplugintheme', array( &$this, 'mainwp_installbulkinstallplugintheme' ) );
		$this->add_action( 'mainwp_preparebulkuploadplugintheme', array( &$this, 'mainwp_preparebulkuploadplugintheme' ) );
		$this->add_action( 'mainwp_installbulkuploadplugintheme', array( &$this, 'mainwp_installbulkuploadplugintheme' ) );
		$this->add_action( 'mainwp_cleanbulkuploadplugintheme', array( &$this, 'mainwp_cleanbulkuploadplugintheme' ) );

		// Widget: RightNow.
		$this->add_action( 'mainwp_upgradewp', array( &$this, 'mainwp_upgradewp' ) );
		$this->add_action( 'mainwp_upgradeplugintheme', array( &$this, 'mainwp_upgrade_plugintheme' ) );
		$this->add_action( 'mainwp_ignoreplugintheme', array( &$this, 'mainwp_ignoreplugintheme' ) );
		$this->add_action( 'mainwp_unignoreplugintheme', array( &$this, 'mainwp_unignoreplugintheme' ) );
		$this->add_action( 'mainwp_ignorepluginsthemes', array( &$this, 'mainwp_ignorepluginsthemes' ) );
		$this->add_action( 'mainwp_unignorepluginsthemes', array( &$this, 'mainwp_unignorepluginsthemes' ) );
		$this->add_action( 'mainwp_unignoreabandonedplugintheme', array( &$this, 'mainwp_unignoreabandonedplugintheme' ) );
		$this->add_action( 'mainwp_unignoreabandonedpluginsthemes', array( &$this, 'mainwp_unignoreabandonedpluginsthemes' ) );
		$this->add_action( 'mainwp_dismissoutdateplugintheme', array( &$this, 'mainwp_dismissoutdateplugintheme' ) );
		$this->add_action( 'mainwp_dismissoutdatepluginsthemes', array( &$this, 'mainwp_dismissoutdatepluginsthemes' ) );
		$this->add_action( 'mainwp_trust_plugin', array( &$this, 'mainwp_trust_plugin' ) );
		$this->add_action( 'mainwp_trust_theme', array( &$this, 'mainwp_trust_theme' ) );

		// Page: Themes.
		$this->add_action( 'mainwp_themes_search', array( &$this, 'mainwp_themes_search' ) );
		$this->add_action( 'mainwp_themes_search_all', array( &$this, 'mainwp_themes_search_all' ) );
		if ( mainwp_current_user_have_right( 'dashboard', 'activate_themes' ) ) {
			$this->add_action( 'mainwp_theme_activate', array( &$this, 'mainwp_theme_activate' ) );
		}
		if ( mainwp_current_user_have_right( 'dashboard', 'delete_themes' ) ) {
			$this->add_action( 'mainwp_theme_delete', array( &$this, 'mainwp_theme_delete' ) );
		}
		$this->add_action( 'mainwp_trusted_theme_notes_save', array( &$this, 'mainwp_trusted_theme_notes_save' ) );
		if ( mainwp_current_user_have_right( 'dashboard', 'ignore_unignore_updates' ) ) {
			$this->add_action( 'mainwp_theme_ignore_updates', array( &$this, 'mainwp_theme_ignore_updates' ) );
		}

		// Page: Plugins.
		$this->add_action( 'mainwp_plugins_search', array( &$this, 'mainwp_plugins_search' ) );
		$this->add_action( 'mainwp_plugins_search_all_active', array( &$this, 'mainwp_plugins_search_all_active' ) );

		if ( mainwp_current_user_have_right( 'dashboard', 'activate_deactivate_plugins' ) ) {
			$this->add_action( 'mainwp_plugin_activate', array( &$this, 'mainwp_plugin_activate' ) );
			$this->add_action( 'mainwp_plugin_deactivate', array( &$this, 'mainwp_plugin_deactivate' ) );
		}
		if ( mainwp_current_user_have_right( 'dashboard', 'delete_plugins' ) ) {
			$this->add_action( 'mainwp_plugin_delete', array( &$this, 'mainwp_plugin_delete' ) );
		}

		if ( mainwp_current_user_have_right( 'dashboard', 'ignore_unignore_updates' ) ) {
			$this->add_action( 'mainwp_plugin_ignore_updates', array( &$this, 'mainwp_plugin_ignore_updates' ) );
		}
		$this->add_action( 'mainwp_trusted_plugin_notes_save', array( &$this, 'mainwp_trusted_plugin_notes_save' ) );

		// Widget: Plugins.
		$this->add_action( 'mainwp_widget_plugin_activate', array( &$this, 'mainwp_widget_plugin_activate' ) );
		$this->add_action( 'mainwp_widget_plugin_deactivate', array( &$this, 'mainwp_widget_plugin_deactivate' ) );
		$this->add_action( 'mainwp_widget_plugin_delete', array( &$this, 'mainwp_widget_plugin_delete' ) );

		// Widget: Themes.
		$this->add_action( 'mainwp_widget_theme_activate', array( &$this, 'mainwp_widget_theme_activate' ) );
		$this->add_action( 'mainwp_widget_theme_delete', array( &$this, 'mainwp_widget_theme_delete' ) );
	}

	/**
	 * Method mainwp_themes_search()
	 *
	 * Search handler for Themes.
	 */
	public function mainwp_themes_search() {
		$this->secure_request( 'mainwp_themes_search' );

		$keyword = isset( $_POST['keyword'] ) ? esc_html( wp_unslash( $_POST['keyword'] ) ) : '';
		$status  = isset( $_POST['status'] ) ? esc_html( wp_unslash( $_POST['status'] ) ) : '';
		$groups  = isset( $_POST['groups'] ) && is_array( $_POST['groups'] ) ? array_map( 'sanitize_text_field', (array) $_POST['groups'] ) : array();
		$sites   = isset( $_POST['sites'] ) && is_array( $_POST['sites'] ) ? array_map( 'sanitize_text_field', (array) $_POST['sites'] ) : array();

		MainWP_Cache::init_session();
		$result = MainWP_Themes::render_table( $keyword, $status, $groups, $sites );
		wp_send_json( $result );
	}

	/**
	 * Method mainwp_theme_activate()
	 *
	 * Activate Theme,
	 * Page: Themes.
	 */
	public function mainwp_theme_activate() {
		$this->secure_request( 'mainwp_theme_activate' );
		MainWP_Themes_Handler::activate_theme();
		die();
	}

	/**
	 * Method mainwp_theme_delete()
	 *
	 * Delete Theme,
	 * Page: Themes.
	 */
	public function mainwp_theme_delete() {
		$this->secure_request( 'mainwp_theme_delete' );
		MainWP_Themes_Handler::delete_themes();
		die();
	}

	/**
	 * Method mainwp_theme_ignore_updates()
	 *
	 * Ignore theme updates,
	 * Page: Themes.
	 */
	public function mainwp_theme_ignore_updates() {
		$this->secure_request( 'mainwp_theme_ignore_updates' );
		MainWP_Themes_Handler::ignore_updates();
		die();
	}

	/**
	 * Method mainwp_themes_search_all()
	 *
	 * Search ALL handler for,
	 * Page: Themes.
	 */
	public function mainwp_themes_search_all() {
		$this->secure_request( 'mainwp_themes_search_all' );
		MainWP_Cache::init_session();
		MainWP_Themes::render_all_themes_table();
		die();
	}

	/**
	 * Method mainwp_trusted_theme_notes_save()
	 *
	 * Save trusted theme notes,
	 * Page: Themes.
	 */
	public function mainwp_trusted_theme_notes_save() {
		$this->secure_request( 'mainwp_trusted_theme_notes_save' );
		MainWP_Themes_Handler::save_trusted_theme_note();
		die( wp_json_encode( array( 'result' => 'SUCCESS' ) ) );
	}

	/**
	 * Method mainwp_plugins_search()
	 *
	 * Search handler for Plugins.
	 */
	public function mainwp_plugins_search() {
		$this->secure_request( 'mainwp_plugins_search' );

		$keyword = isset( $_POST['keyword'] ) ? esc_html( wp_unslash( $_POST['keyword'] ) ) : '';
		$status  = isset( $_POST['status'] ) ? esc_html( wp_unslash( $_POST['status'] ) ) : '';
		$groups  = isset( $_POST['groups'] ) && is_array( $_POST['groups'] ) ? array_map( 'sanitize_text_field', (array) $_POST['groups'] ) : '';
		$sites   = isset( $_POST['sites'] ) && is_array( $_POST['sites'] ) ? array_map( 'sanitize_text_field', (array) $_POST['sites'] ) : '';

		MainWP_Cache::init_session();
		$result = MainWP_Plugins::render_table( $keyword, $status, $groups, $sites );
		wp_send_json( $result );
	}

	/**
	 * Method mainwp_plugins_search_all_active()
	 *
	 * Search all Active handler for,
	 * Page: Plugins.
	 */
	public function mainwp_plugins_search_all_active() {
		$this->secure_request( 'mainwp_plugins_search_all_active' );
		MainWP_Cache::init_session();
		MainWP_Plugins::render_all_active_table();
		die();
	}

	/**
	 * Method mainwp_plugin_activate()
	 *
	 * Activate plugins,
	 * Page: Plugins.
	 */
	public function mainwp_plugin_activate() {
		$this->secure_request( 'mainwp_plugin_activate' );
		MainWP_Plugins_Handler::activate_plugins();
		die();
	}

	/**
	 * Method mainwp_plugin_deactivate()
	 *
	 * Deactivate plugins,
	 * Page: Plugins.
	 */
	public function mainwp_plugin_deactivate() {
		$this->secure_request( 'mainwp_plugin_deactivate' );
		MainWP_Plugins_Handler::deactivate_plugins();
		die();
	}

	/**
	 * Method mainwp_plugin_delete()
	 *
	 * Delete plugins,
	 * Page: Plugins.
	 */
	public function mainwp_plugin_delete() {
		$this->secure_request( 'mainwp_plugin_delete' );
		MainWP_Plugins_Handler::delete_plugins();
		die();
	}

	/**
	 * Method mainwp_plugin_ignore_updates()
	 *
	 * Ignore plugins updates,
	 * Page: Plugins.
	 */
	public function mainwp_plugin_ignore_updates() {
		$this->secure_request( 'mainwp_plugin_ignore_updates' );
		MainWP_Plugins_Handler::ignore_updates();
		die();
	}

	/**
	 * Method mainwp_trusted_plugin_notes_save()
	 *
	 * Save trusted plugin notes,
	 * Page: Plugins.
	 */
	public function mainwp_trusted_plugin_notes_save() {
		$this->secure_request( 'mainwp_trusted_plugin_notes_save' );
		MainWP_Plugins_Handler::save_trusted_plugin_note();
		die( wp_json_encode( array( 'result' => 'SUCCESS' ) ) );
	}

	/**
	 * Method mainwp_widget_plugin_activate()
	 *
	 * Activate plugin,
	 * Widget: Plugins.
	 */
	public function mainwp_widget_plugin_activate() {
		$this->secure_request( 'mainwp_widget_plugin_activate' );
		MainWP_Widget_Plugins::activate_plugin();
	}

	/**
	 * Method mainwp_widget_plugin_deactivate()
	 *
	 * Deactivate plugin,
	 * Widget: Plugins.
	 */
	public function mainwp_widget_plugin_deactivate() {
		$this->secure_request( 'mainwp_widget_plugin_deactivate' );
		MainWP_Widget_Plugins::deactivate_plugin();
	}

	/**
	 * Method mainwp_widget_plugin_delete()
	 *
	 * Delete plugin,
	 * Widget: Plugins.
	 */
	public function mainwp_widget_plugin_delete() {
		$this->secure_request( 'mainwp_widget_plugin_delete' );
		MainWP_Widget_Plugins::delete_plugin();
	}

	/**
	 * Method mainwp_widget_plugin_activate()
	 *
	 * Activate theme,
	 * Widget: Themes.
	 */
	public function mainwp_widget_theme_activate() {
		$this->secure_request( 'mainwp_widget_theme_activate' );
		MainWP_Widget_Themes::activate_theme();
	}

	/**
	 * Method mainwp_widget_plugin_delete()
	 *
	 * Delete theme,
	 * Widget: Themes.
	 */
	public function mainwp_widget_theme_delete() {
		$this->secure_request( 'mainwp_widget_theme_delete' );
		MainWP_Widget_Themes::delete_theme();
	}

	/**
	 * Method mainwp_preparebulkinstallplugintheme()
	 *
	 * Prepair bulk installation of plugins & themes,
	 * Page: InstallPlugins/Themes.
	 */
	public function mainwp_preparebulkinstallplugintheme() {
		$this->secure_request( 'mainwp_preparebulkinstallplugintheme' );
		MainWP_Install_Bulk::prepare_install();
	}

	/**
	 * Method mainwp_installbulkinstallplugintheme()
	 *
	 * Installation of plugins & themes,
	 * Page: InstallPlugins/Themes.
	 */
	public function mainwp_installbulkinstallplugintheme() {
		$this->secure_request( 'mainwp_installbulkinstallplugintheme' );
		MainWP_Install_Bulk::perform_install();
	}

	/**
	 * Method mainwp_preparebulkuploadplugintheme()
	 *
	 * Prepair bulk upload of plugins & themes,
	 * Page: InstallPlugins/Themes.
	 */
	public function mainwp_preparebulkuploadplugintheme() {
		$this->secure_request( 'mainwp_preparebulkuploadplugintheme' );
		MainWP_Install_Bulk::prepare_upload();
	}

	/**
	 * Method mainwp_installbulkuploadplugintheme()
	 *
	 * Bulk upload of plugins & themes,
	 * Page: InstallPlugins/Themes.
	 */
	public function mainwp_installbulkuploadplugintheme() {
		$this->secure_request( 'mainwp_installbulkuploadplugintheme' );
		MainWP_Install_Bulk::perform_upload();
	}

	/**
	 * Method mainwp_cleanbulkuploadplugintheme()
	 *
	 * Clean upload of plugins & themes,
	 * Page: InstallPlugins/Themes.
	 */
	public function mainwp_cleanbulkuploadplugintheme() {
		$this->secure_request( 'mainwp_cleanbulkuploadplugintheme' );
		MainWP_Install_Bulk::clean_upload();
	}

	/**
	 * Method mainwp_ext_prepareinstallplugintheme()
	 *
	 * Prepair Installation of plugins & themes,
	 * Page: ManageSites.
	 */
	public function mainwp_ext_prepareinstallplugintheme() {
		do_action( 'mainwp_prepareinstallplugintheme' );
	}

	/**
	 * Method mainwp_ext_performinstallplugintheme()
	 *
	 * Installation of plugins & themes,
	 * Page: ManageSites.
	 */
	public function mainwp_ext_performinstallplugintheme() {
		do_action( 'mainwp_performinstallplugintheme' );
	}

	/**
	 * Method mainwp_upgradewp()
	 *
	 * Update a specific WP core.
	 */
	public function mainwp_upgradewp() {
		if ( ! mainwp_current_user_have_right( 'dashboard', 'update_wordpress' ) ) {
			die( wp_json_encode( array( 'error' => mainwp_do_not_have_permissions( __( 'update WordPress', 'mainwp' ), $echo = false ) ) ) );
		}

		$this->secure_request( 'mainwp_upgradewp' );

		try {
			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : false;
			die( wp_json_encode( array( 'result' => MainWP_Updates_Handler::upgrade_site( $id ) ) ) ); // ok.
		} catch ( MainWP_Exception $e ) {
			die(
				wp_json_encode(
					array(
						'error' => array(
							'message' => $e->getMessage(),
							'extra'   => $e->get_message_extra(),
						),
					)
				)
			);
		}
	}

	/**
	 * Method mainwp_upgrade_plugintheme()
	 *
	 * Update plugin or theme.
	 */
	public function mainwp_upgrade_plugintheme() { // phpcs:ignore -- Current complexity is the only way to achieve desired results, pull request solutions appreciated.

		if ( ! isset( $_POST['type'] ) ) {
			die( wp_json_encode( array( 'error' => '<i class="red times icon"></i> ' . __( 'Invalid request', 'mainwp' ) ) ) );
		}

		if ( 'plugin' === $_POST['type'] && ! mainwp_current_user_have_right( 'dashboard', 'update_plugins' ) ) {
			die( wp_json_encode( array( 'error' => mainwp_do_not_have_permissions( __( 'update plugins', 'mainwp' ), $echo = false ) ) ) );
		}

		if ( 'theme' === $_POST['type'] && ! mainwp_current_user_have_right( 'dashboard', 'update_themes' ) ) {
			die( wp_json_encode( array( 'error' => mainwp_do_not_have_permissions( __( 'update themes', 'mainwp' ), $echo = false ) ) ) );
		}

		if ( 'translation' === $_POST['type'] && ! mainwp_current_user_have_right( 'dashboard', 'update_translations' ) ) {
			die( wp_json_encode( array( 'error' => mainwp_do_not_have_permissions( __( 'update translations', 'mainwp' ), $echo = false ) ) ) );
		}

		$this->secure_request( 'mainwp_upgradeplugintheme' );

		// support chunk update for manage sites page only.
		$chunk_support = isset( $_POST['chunk_support'] ) && $_POST['chunk_support'] ? true : false;
		$max_update    = 0;
		$websiteId     = null;
		$slugs         = '';

		if ( isset( $_POST['websiteId'] ) ) {
			$websiteId = intval( $_POST['websiteId'] );

			if ( $chunk_support ) {
				/**
				 * Filter: mainwp_update_plugintheme_max
				 *
				 * Filters the max number of plugins/themes to be updated in one run in order to improve performance.
				 *
				 * @param int $websiteId Child site ID.
				 *
				 * @since Unknown
				 */
				$max_update = apply_filters( 'mainwp_update_plugintheme_max', false, $websiteId );
				if ( empty( $max_update ) ) {
					$chunk_support = false; // there is no hook so disable chunk update support.
				}
			}
			if ( $chunk_support ) {
				if ( isset( $_POST['chunk_slugs'] ) ) {
					$slugs = wp_unslash( $_POST['chunk_slugs'] );  // chunk slugs send so use this, do not sanitize text this.
				} else {
					$slugs = MainWP_Updates_Handler::get_plugin_theme_slugs( $websiteId, sanitize_text_field( wp_unslash( $_POST['type'] ) ) );
				}
			} elseif ( isset( $_POST['slug'] ) ) {
				$slugs = wp_unslash( $_POST['slug'] ); // do not sanitize text this.
			} else {
				$slugs = MainWP_Updates_Handler::get_plugin_theme_slugs( $websiteId, sanitize_text_field( wp_unslash( $_POST['type'] ) ) );
			}
		}

		if ( MainWP_DB_Backup::instance()->backup_full_task_running( $websiteId ) ) {
			die( wp_json_encode( array( 'error' => __( 'Backup process in progress on the child site. Please, try again later.', 'mainwp' ) ) ) );
		}

		$chunk_slugs = array();

		if ( $chunk_support ) {
			// calculate update slugs here.
			if ( $max_update ) {
				$slugs        = explode( ',', $slugs );
				$chunk_slugs  = array_slice( $slugs, $max_update );
				$update_slugs = array_diff( $slugs, $chunk_slugs );
				$slugs        = implode( ',', $update_slugs );
			}
		}

		if ( empty( $slugs ) && ! $chunk_support ) {
			die( wp_json_encode( array( 'message' => __( 'Item slug could not be found. Update process could not be executed.', 'mainwp' ) ) ) );
		}
		$website = MainWP_DB::instance()->get_website_by_id( $websiteId );
		try {
			$info = array( 'result' => MainWP_Updates_Handler::upgrade_plugin_theme_translation( $websiteId, sanitize_text_field( wp_unslash( $_POST['type'] ) ), $slugs ) );

			if ( $chunk_support && ( 0 < count( $chunk_slugs ) ) ) {
				$info['chunk_slugs'] = implode( ',', $chunk_slugs );
			}

			if ( ! empty( $website ) ) {
				$info['site_url'] = esc_url( $website->url );
			}
			wp_send_json( $info );
		} catch ( MainWP_Exception $e ) {
			die(
				wp_json_encode(
					array(
						'error' => array(
							'message' => $e->getMessage(),
							'extra'   => $e->get_message_extra(),
						),
					)
				)
			);
		}
	}

	/**
	 * Method mainwp_ignoreplugintheme()
	 *
	 * Ignore plugin or theme.
	 */
	public function mainwp_ignoreplugintheme() {
		$this->secure_request( 'mainwp_ignoreplugintheme' );

		if ( ! isset( $_POST['id'] ) ) {
			die( wp_json_encode( array( 'error' => __( 'Invalid request!', 'mainwp' ) ) ) );
		}
		$type = isset( $_POST['type'] ) ? esc_html( wp_unslash( $_POST['type'] ) ) : '';
		$slug = isset( $_POST['slug'] ) ? esc_html( wp_unslash( $_POST['slug'] ) ) : '';
		$id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		wp_send_json( array( 'result' => MainWP_Updates_Handler::ignore_plugin_theme( $type, $slug, $id ) ) );
	}

	/**
	 * Method mainwp_unignoreabandonedplugintheme()
	 *
	 * Unignore abandoned plugin or theme.
	 */
	public function mainwp_unignoreabandonedplugintheme() {
		$this->secure_request( 'mainwp_unignoreabandonedplugintheme' );

		if ( ! isset( $_POST['id'] ) ) {
			die( wp_json_encode( array( 'error' => __( 'Invalid request!', 'mainwp' ) ) ) );
		}

		$type = isset( $_POST['type'] ) ? esc_html( wp_unslash( $_POST['type'] ) ) : '';
		$slug = isset( $_POST['slug'] ) ? esc_html( wp_unslash( $_POST['slug'] ) ) : '';
		$id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		die( wp_json_encode( array( 'result' => MainWP_Updates_Handler::unignore_abandoned_plugin_theme( $type, $slug, $id ) ) ) ); // ok.
	}

	/**
	 * Method mainwp_unignoreabandonedpluginthemes()
	 *
	 * Unignore abandoned plugins or themes.
	 */
	public function mainwp_unignoreabandonedpluginsthemes() {
		$this->secure_request( 'mainwp_unignoreabandonedpluginsthemes' );

		if ( ! isset( $_POST['slug'] ) ) {
			die( wp_json_encode( array( 'error' => __( 'Invalid request!', 'mainwp' ) ) ) );
		}

		$type = isset( $_POST['type'] ) ? esc_html( wp_unslash( $_POST['type'] ) ) : '';
		$slug = isset( $_POST['slug'] ) ? esc_html( wp_unslash( $_POST['slug'] ) ) : '';
		die( wp_json_encode( array( 'result' => MainWP_Updates_Handler::unignore_abandoned_plugins_themes( $type, $slug ) ) ) );
	}

	/**
	 * Method mainwp_dismissoutdateplugintheme()
	 *
	 * Dismiss outdated plugin or theme.
	 */
	public function mainwp_dismissoutdateplugintheme() {
		$this->secure_request( 'mainwp_dismissoutdateplugintheme' );

		if ( ! isset( $_POST['id'] ) ) {
			die( wp_json_encode( array( 'error' => __( 'Invalid request!', 'mainwp' ) ) ) );
		}
		$type = isset( $_POST['type'] ) ? esc_html( wp_unslash( $_POST['type'] ) ) : '';
		$slug = isset( $_POST['slug'] ) ? esc_html( wp_unslash( $_POST['slug'] ) ) : '';
		$name = isset( $_POST['name'] ) ? esc_html( wp_unslash( $_POST['name'] ) ) : '';
		$id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		die( wp_json_encode( array( 'result' => MainWP_Updates_Handler::dismiss_plugin_theme( $type, $slug, $name, $id ) ) ) );
	}

	/**
	 * Method mainwp_dismissoutdatepluginthemes()
	 *
	 * Dismiss outdated plugins or themes.
	 */
	public function mainwp_dismissoutdatepluginsthemes() {
		$this->secure_request( 'mainwp_dismissoutdatepluginsthemes' );

		if ( ! mainwp_current_user_have_right( 'dashboard', 'ignore_unignore_updates' ) ) {
			die( wp_json_encode( array( 'error' => mainwp_do_not_have_permissions( __( 'ignore/unignore updates', 'mainwp' ) ) ) ) );
		}

		if ( ! isset( $_POST['slug'] ) ) {
			die( wp_json_encode( array( 'error' => __( 'Invalid request!', 'mainwp' ) ) ) );
		}

		$type = isset( $_POST['type'] ) ? esc_html( wp_unslash( $_POST['type'] ) ) : '';
		$slug = isset( $_POST['slug'] ) ? esc_html( wp_unslash( $_POST['slug'] ) ) : '';
		$name = isset( $_POST['name'] ) ? esc_html( wp_unslash( $_POST['name'] ) ) : '';

		die( wp_json_encode( array( 'result' => MainWP_Updates_Handler::dismiss_plugins_themes( $type, $slug, $name ) ) ) );
	}

	/**
	 * Method mainwp_unignoreplugintheme()
	 *
	 * Unignore plugin or theme.
	 */
	public function mainwp_unignoreplugintheme() {
		$this->secure_request( 'mainwp_unignoreplugintheme' );

		if ( ! isset( $_POST['id'] ) ) {
			die( wp_json_encode( array( 'error' => __( 'Invalid request!', 'mainwp' ) ) ) );
		}
		$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$slug = isset( $_POST['slug'] ) ? wp_unslash( $_POST['slug'] ) : ''; // do not sanitize slug.
		$id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : '';
		die( wp_json_encode( array( 'result' => MainWP_Updates_Handler::unignore_plugin_theme( $type, $slug, $id ) ) ) );
	}

	/**
	 * Method mainwp_ignorepluginthemes()
	 *
	 * Ignore plugins or themes.
	 */
	public function mainwp_ignorepluginsthemes() {
		$this->secure_request( 'mainwp_ignorepluginsthemes' );

		if ( ! mainwp_current_user_have_right( 'dashboard', 'ignore_unignore_updates' ) ) {
			die( wp_json_encode( array( 'error' => mainwp_do_not_have_permissions( __( 'ignore/unignore updates', 'mainwp' ) ) ) ) );
		}

		if ( ! isset( $_POST['slug'] ) ) {
			die( wp_json_encode( array( 'error' => __( 'Invalid request!', 'mainwp' ) ) ) );
		}

		$type = isset( $_POST['type'] ) ? esc_html( wp_unslash( $_POST['type'] ) ) : '';
		$slug = isset( $_POST['slug'] ) ? esc_html( wp_unslash( $_POST['slug'] ) ) : '';
		$name = isset( $_POST['name'] ) ? esc_html( wp_unslash( $_POST['name'] ) ) : '';
		die( wp_json_encode( array( 'result' => MainWP_Updates_Handler::ignore_plugins_themes( $type, $slug, $name ) ) ) );
	}

	/**
	 * Method mainwp_unignorepluginthemes()
	 *
	 * Unignore plugins or themes.
	 */
	public function mainwp_unignorepluginsthemes() {
		$this->secure_request( 'mainwp_unignorepluginsthemes' );

		if ( ! isset( $_POST['slug'] ) ) {
			die( wp_json_encode( array( 'error' => __( 'Invalid request!', 'mainwp' ) ) ) );
		}
		$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$slug = isset( $_POST['slug'] ) ? wp_unslash( $_POST['slug'] ) : ''; // do not sanitize slug.
		die( wp_json_encode( array( 'result' => MainWP_Updates_Handler::unignore_plugins_themes( $type, $slug ) ) ) );
	}

	/**
	 * Method mainwp_trust_plugin()
	 *
	 * Trust plugin.
	 */
	public function mainwp_trust_plugin() {
		$this->secure_request( 'mainwp_trust_plugin' );

		MainWP_Plugins_Handler::trust_post();
		die( wp_json_encode( array( 'result' => true ) ) );
	}

	/**
	 * Method mainwp_trust_theme()
	 *
	 * Trust theme.
	 */
	public function mainwp_trust_theme() {
		$this->secure_request( 'mainwp_trust_theme' );

		MainWP_Themes_Handler::trust_post();
		die( wp_json_encode( array( 'result' => true ) ) );
	}

}
