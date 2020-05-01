<?php
/**
 * MainWP Server Information Page.
 *
 * @package MainWP/Dashboard
 */

namespace MainWP\Dashboard;

/**
 * MainWP Server Information Page.
 */
class MainWP_Server_Information {

	const WARNING = 1;
	const ERROR   = 2;

	/**
	 * @static
	 * @var $subPages Server Information sub pages.
	 */
	public static $subPages;

	/**
	 * Get Class Name
	 *
	 * @return string __CLASS__
	 */
	public static function get_class_name() {
		return __CLASS__;
	}

	/**
	 * Method init_menu()
	 * 
	 * Initiate Server Informaion subPage menu.
	 * 
	 * @return html Server Information subPage menu html.
	 */
	public static function init_menu() {
		add_submenu_page(
			'mainwp_tab',
			__( 'Server Information', 'mainwp' ),
			' <span id="mainwp-ServerInformation">' . __( 'Server Information', 'mainwp' ) . '</span>',
			'read',
			'ServerInformation',
			array(
				self::get_class_name(),
				'render',
			)
		);
		if ( ! MainWP_Menu::is_disable_menu_item( 3, 'ServerInformationCron' ) ) {
			add_submenu_page(
				'mainwp_tab',
				__( 'Cron Schedules', 'mainwp' ),
				'<div class="mainwp-hidden">' . __( 'Cron Schedules', 'mainwp' ) . '</div>',
				'read',
				'ServerInformationCron',
				array(
					self::get_class_name(),
					'render_cron',
				)
			);
		}

		if ( ! MainWP_Menu::is_disable_menu_item( 3, 'ErrorLog' ) ) {
			add_submenu_page(
				'mainwp_tab',
				__( 'Error Log', 'mainwp' ),
				'<div class="mainwp-hidden">' . __( 'Error Log', 'mainwp' ) . '</div>',
				'read',
				'ErrorLog',
				array(
					self::get_class_name(),
					'render_error_log_page',
				)
			);
		}
		if ( ! MainWP_Menu::is_disable_menu_item( 3, 'WPConfig' ) ) {
			add_submenu_page(
				'mainwp_tab',
				__( 'WP-Config File', 'mainwp' ),
				'<div class="mainwp-hidden">' . __( 'WP-Config File', 'mainwp' ) . '</div>',
				'read',
				'WPConfig',
				array(
					self::get_class_name(),
					'render_wp_config',
				)
			);
		}
		if ( ! MainWP_Menu::is_disable_menu_item( 3, '.htaccess' ) ) {
			if ( MainWP_Server_Information_Handler::is_apache_server_software() ) {
				add_submenu_page(
					'mainwp_tab',
					__( '.htaccess File', 'mainwp' ),
					'<div class="mainwp-hidden">' . __( '.htaccess File', 'mainwp' ) . '</div>',
					'read',
					'.htaccess',
					array(
						self::get_class_name(),
						'render_htaccess',
					)
				);
			}
		}
		if ( ! MainWP_Menu::is_disable_menu_item( 3, 'ActionLogs' ) ) {
			add_submenu_page(
				'mainwp_tab',
				__( 'Action logs', 'mainwp' ),
				'<div class="mainwp-hidden">' . __( 'Action logs', 'mainwp' ) . '</div>',
				'read',
				'ActionLogs',
				array(
					self::get_class_name(),
					'render_action_logs',
				)
			);
		}

		/** @deprecated Use 'mainwp_getsubpages_server' instead. */
		$sub_pages      = apply_filters_deprecated( 'mainwp-getsubpages-server', array( array() ), '4.0.1', 'mainwp_getsubpages_server' );
		self::$subPages = apply_filters( 'mainwp_getsubpages_server', $sub_pages );

		if ( isset( self::$subPages ) && is_array( self::$subPages ) ) {
			foreach ( self::$subPages as $subPage ) {
				if ( MainWP_Menu::is_disable_menu_item( 3, 'Server' . $subPage['slug'] ) ) {
					continue;
				}
				add_submenu_page( 'mainwp_tab', $subPage['title'], '<div class="mainwp-hidden">' . $subPage['title'] . '</div>', 'read', 'Server' . $subPage['slug'], $subPage['callback'] );
			}
		}
		self::init_left_menu( self::$subPages );
	}

	/**
	 * Method init_subpages_menu()
	 * 
	 * Render Sub Pages Menu.
	 * 
	 * @return html Subpages Menu.
	 */
	public static function init_subpages_menu() {
		?>
		<div id="menu-mainwp-ServerInformation" class="mainwp-submenu-wrapper">
			<div class="wp-submenu sub-open">
				<div class="mainwp_boxout">
					<div class="mainwp_boxoutin"></div>
					<a href="<?php echo admin_url( 'admin.php?page=ServerInformation' ); ?>" class="mainwp-submenu"><?php esc_html_e( 'Server', 'mainwp' ); ?></a>
					<?php if ( ! MainWP_Menu::is_disable_menu_item( 3, 'ServerInformationCron' ) ) { ?>
						<a href="<?php echo admin_url( 'admin.php?page=ServerInformationCron' ); ?>" class="mainwp-submenu"><?php esc_html_e( 'Cron Schedules', 'mainwp' ); ?></a>
					<?php } ?>
					<?php if ( ! MainWP_Menu::is_disable_menu_item( 3, 'ErrorLog' ) ) { ?>
						<a href="<?php echo admin_url( 'admin.php?page=ErrorLog' ); ?>" class="mainwp-submenu"><?php esc_html_e( 'Error Log', 'mainwp' ); ?></a>
					<?php } ?>
					<?php if ( ! MainWP_Menu::is_disable_menu_item( 3, 'WPConfig' ) ) { ?>
						<a href="<?php echo admin_url( 'admin.php?page=WPConfig' ); ?>" class="mainwp-submenu"><?php esc_html_e( 'WP-Config File', 'mainwp' ); ?></a>
					<?php } ?>
					<?php
					if ( ! MainWP_Menu::is_disable_menu_item( 3, '.htaccess' ) ) {
						if ( MainWP_Server_Information_Handler::is_apache_server_software() ) {
							?>
							<a href="<?php echo admin_url( 'admin.php?page=.htaccess' ); ?>" class="mainwp-submenu"><?php esc_html_e( '.htaccess File', 'mainwp' ); ?></a>
							<?php
						}
					}
					?>
					<?php
					if ( isset( self::$subPages ) && is_array( self::$subPages ) ) {
						foreach ( self::$subPages as $subPage ) {
							if ( ! isset( $subPage['menu_hidden'] ) || ( isset( $subPage['menu_hidden'] ) && true !== $subPage['menu_hidden'] ) ) {
								if ( MainWP_Menu::is_disable_menu_item( 3, 'Server' . $subPage['slug'] ) ) {
									continue;
								}
								?>
								<a href="<?php echo admin_url( 'admin.php?page=Server' . $subPage['slug'] ); ?>" class="mainwp-submenu"><?php echo esc_html( $subPage['title'] ); ?></a>
								<?php
							}
						}
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Method init_left_menu()
	 * 
	 * Initiate Server Information left menu.
	 * 
	 * @param array $subPages Array of subpages.
	 */
	public static function init_left_menu( $subPages = array() ) {
		MainWP_Menu::add_left_menu(
			array(
				'title'      => __( 'Status', 'mainwp' ),
				'parent_key' => 'mainwp_tab',
				'slug'       => 'ServerInformation',
				'href'       => 'admin.php?page=ServerInformation',
				'icon'       => '<i class="server icon"></i>',
			),
			1
		);

		global $_mainwp_menu_active_slugs;
		$_mainwp_menu_active_slugs['ActionLogs'] = 'ServerInformation';

		$init_sub_subleftmenu = array(
			array(
				'title'      => __( 'Server', 'mainwp' ),
				'parent_key' => 'ServerInformation',
				'href'       => 'admin.php?page=ServerInformation',
				'slug'       => 'ServerInformation',
				'right'      => '',
			),
			array(
				'title'      => __( 'Cron Schedules', 'mainwp' ),
				'parent_key' => 'ServerInformation',
				'href'       => 'admin.php?page=ServerInformationCron',
				'slug'       => 'ServerInformationCron',
				'right'      => '',
			),
			array(
				'title'      => __( 'Error Log', 'mainwp' ),
				'parent_key' => 'ServerInformation',
				'href'       => 'admin.php?page=ErrorLog',
				'slug'       => 'ErrorLog',
				'right'      => '',
			),
			array(
				'title'      => __( 'WP-Config File', 'mainwp' ),
				'parent_key' => 'ServerInformation',
				'href'       => 'admin.php?page=WPConfig',
				'slug'       => 'WPConfig',
				'right'      => '',
			),
			array(
				'title'      => __( '.htaccess File', 'mainwp' ),
				'parent_key' => 'ServerInformation',
				'href'       => 'admin.php?page=.htaccess',
				'slug'       => '.htaccess',
				'right'      => '',
			),
		);

		if ( ! MainWP_Server_Information_Handler::is_apache_server_software() ) {
			if ( '.htaccess' === $init_sub_subleftmenu[4]['slug'] ) {
				unset( $init_sub_subleftmenu[4] );
			}
		}

		MainWP_Menu::init_subpages_left_menu( $subPages, $init_sub_subleftmenu, 'ServerInformation', 'Server' );
		foreach ( $init_sub_subleftmenu as $item ) {
			if ( MainWP_Menu::is_disable_menu_item( 3, $item['slug'] ) ) {
				continue;
			}
			MainWP_Menu::add_left_menu( $item, 2 );
		}
	}

	/**
	 * Method render_header()
	 * 
	 * Render Server Information header.
	 * 
	 * @param string $shownPage Current page.
	 * 
	 * @return html Server Information header html.
	 */
	public static function render_header( $shownPage = '' ) {
			$params = array(
				'title' => __( 'Server Information', 'mainwp' ),
			);

			MainWP_UI::render_top_header( $params );

			$renderItems = array();

			$renderItems[] = array(
				'title'  => __( 'Server', 'mainwp' ),
				'href'   => 'admin.php?page=ServerInformation',
				'active' => ( '' === $shownPage ) ? true : false,
			);

			if ( ! MainWP_Menu::is_disable_menu_item( 3, 'ServerInformationCron' ) ) {
				$renderItems[] = array(
					'title'  => __( 'Cron Schedules', 'mainwp' ),
					'href'   => 'admin.php?page=ServerInformationCron',
					'active' => ( 'ServerInformationCron' === $shownPage ) ? true : false,
				);
			}

			if ( ! MainWP_Menu::is_disable_menu_item( 3, 'ErrorLog' ) ) {
				$renderItems[] = array(
					'title'      => __( 'Error Log', 'mainwp' ),
					'href'       => 'admin.php?page=ErrorLog',
					'active'     => ( 'ErrorLog' === $shownPage ) ? true : false,
				);
			}

			if ( ! MainWP_Menu::is_disable_menu_item( 3, 'WPConfig' ) ) {
				$renderItems[] = array(
					'title'  => __( 'WP-Config File', 'mainwp' ),
					'href'   => 'admin.php?page=WPConfig',
					'active' => ( 'WPConfig' === $shownPage ) ? true : false,
				);
			}

			if ( ! MainWP_Menu::is_disable_menu_item( 3, '.htaccess' ) ) {
				if ( MainWP_Server_Information_Handler::is_apache_server_software() ) {
					$renderItems[] = array(
						'title'  => __( '.htaccess File', 'mainwp' ),
						'href'   => 'admin.php?page=.htaccess',
						'active' => ( '.htaccess' === $shownPage ) ? true : false,
					);
				}
			}

			MainWP_UI::render_page_navigation( $renderItems );
			echo '<div class="ui segment">';
	}

	/**
	 * Method render_footer()
	 * 
	 * @param string $shownPage Page that is shown.
	 * 
	 * @return html Page closing tag.
	 */
	public static function render_footer( $shownPage ) {
		echo '</div>';
	}

	/**
	 * Method render()
	 * 
	 * Render Server Information page.
	 * 
	 * @return html Server Information html.
	 */
	public static function render() {
		if ( ! mainwp_current_user_have_right( 'dashboard', 'see_server_information' ) ) {
			mainwp_do_not_have_permissions( 'server information', 'mainwp' );

			return;
		}

		self::render_header( '' );

		do_action( 'mainwp_before_server_info_table' );

		?>
			<div class="ui two column grid">
			<div class="column"></div>
			<div class="right aligned column">
			<a href="#" style="margin-left:5px" class="ui small basic green button" id="mainwp-copy-meta-system-report" data-inverted="" data-position="left center" data-tooltip="<?php esc_attr_e( 'Copy the system report to paste it to the MainWP Community.', 'mainwp' ); ?>"><?php esc_html_e( 'Copy System Report for the MainWP Community', 'mainwp' ); ?></a>
			<a href="#" class="ui small green button" id="mainwp-download-system-report"><?php esc_html_e( 'Download System Report', 'mainwp' ); ?></a>
			</div>
			</div>
			<table class="ui stackable celled table fixed mainwp-system-info-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Server Info', 'mainwp' ); ?></th>
						<th><?php esc_html_e( 'Required', 'mainwp' ); ?></th>
						<th><?php esc_html_e( 'Detected', 'mainwp' ); ?></th>
						<th><?php esc_html_e( 'Status', 'mainwp' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr><td colspan="4"><div class="ui ribbon inverted grey label"><?php esc_html_e( 'MainWP Dashboard', 'mainwp' ); ?></div></td></tr>
					<tr>
						<td><?php esc_html_e( 'MainWP Dashboard Version', 'mainwp' ); ?></td>
						<td><?php echo MainWP_Server_Information_Handler::get_mainwp_version(); ?></td>
						<td><?php echo MainWP_Server_Information_Handler::get_current_version(); ?></td>
						<td><?php echo self::get_mainwp_version_check(); ?></td>
					</tr>
				<?php self::check_directory_mainwp_directory(); ?>
					<tr>
						<td colspan="4"><div class="ui ribbon inverted grey label"><?php esc_html_e( 'MainWP Extensions', 'mainwp' ); ?></div></td>
					</tr>
				<?php
				$extensions       = MainWP_Extensions_Handler::load_extensions();
				$extensions_slugs = array();
				if ( 0 == count( $extensions ) ) {
					echo '<tr><td colspan="4">' . esc_html__( 'No installed extensions', 'mainwp' ) . '</td></tr>';
				}
				foreach ( $extensions as $extension ) {
					$extensions_slugs[] = $extension['slug'];
					?>
						<tr>
							<td><?php echo esc_html( $extension['name'] ); ?></td>
							<td><?php echo esc_html( $extension['version'] ); ?></td>
							<td><?php echo isset( $extension['activated_key'] ) && 'Activated' === $extension['activated_key'] ? __( 'Active', 'mainwp' ) : __( 'Inactive', 'mainwp' ); ?></td>
							<td><?php echo isset( $extension['activated_key'] ) && 'Activated' === $extension['activated_key'] ? '<div class="ui green basic label"><i class="check circle icon"></i> ' . __( 'Pass', 'mainwp' ) . '</div>' : self::get_warning_html( self::WARNING ); ?></td>
						</tr>
						<?php
				}
				?>
					<tr><td colspan="4"><div class="ui ribbon inverted grey label"><?php esc_html_e( 'WordPress', 'mainwp' ); ?></div></td></tr>
					<?php
					self::render_row( 'WordPress Version', '>=', '3.6', 'get_wordpress_version', '', '', null, null, self::ERROR );
					self::render_row( 'WordPress Memory Limit', '>=', '64M', 'get_wordpress_memory_limit', '', '', null );
					self::render_row( 'MultiSite Disabled', '=', true, 'check_if_multisite', '', '', null );
					?>
					<tr>
						<td><?php esc_html_e( 'FileSystem Method', 'mainwp' ); ?></td>
						<td><?php echo esc_html( '= direct' ); ?></td>
						<td><?php echo MainWP_Server_Information_Handler::get_file_system_method(); ?></td>
						<td><?php echo self::get_file_system_method_check(); ?></td>
					</tr>
					<tr><td colspan="4"><div class="ui ribbon inverted grey label"><?php esc_html_e( 'PHP', 'mainwp' ); ?></div></td></tr>
					<?php
					self::render_row( 'PHP Version', '>=', '5.6', 'get_php_version', '', '', null, null, self::ERROR );
					self::render_row( 'PHP Safe Mode Disabled', '=', true, 'get_php_safe_mode', '', '', null );
					self::render_row( 'PHP Max Execution Time', '>=', '30', 'get_max_execution_time', 'seconds', '=', '0' );
					self::render_row( 'PHP Max Input Time', '>=', '30', 'get_max_input_time', 'seconds', '=', '0' );
					self::render_row( 'PHP Memory Limit', '>=', '128M', 'get_php_memory_limit', '', '', null, 'filesize' );
					self::render_row( 'PCRE Backtracking Limit', '>=', '10000', 'get_output_buffer_size', '', '', null );
					self::render_row( 'PHP Upload Max Filesize', '>=', '2M', 'get_upload_max_filesize', '', '', null, 'filesize' );
					self::render_row( 'PHP Post Max Size', '>=', '2M', 'get_post_max_size', '', '', null, 'filesize' );
					self::render_row( 'SSL Extension Enabled', '=', true, 'get_ssl_support', '', '', null );
					self::render_row( 'SSL Warnings', '=', '', 'get_ssl_warning', 'empty', '', null );
					self::render_row( 'cURL Extension Enabled', '=', true, 'get_curl_support', '', '', null, null, self::ERROR );
					self::render_row( 'cURL Timeout', '>=', '300', 'get_curl_timeout', 'seconds', '=', '0' );
					if ( function_exists( 'curl_version' ) ) {
						self::render_row( 'cURL Version', '>=', '7.18.1', 'get_curl_version', '', '', null );
						self::render_row(
							'cURL SSL Version',
							'>=',
							array(
								'version_number' => 0x009080cf,
								'version'        => 'OpenSSL/0.9.8l',
							),
							'get_curl_ssl_version',
							'',
							'',
							null,
							'curlssl'
						);
					}
					?>
					<tr>
						<td colspan="2"><?php esc_html_e( 'PHP Allow URL fopen', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_php_allow_url_fopen(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'PHP Exif Support', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_php_exif(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'PHP IPTC Support', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_php_iptc(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'PHP XML Support', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_php_xml(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'PHP Disabled Functions', 'mainwp' ); ?></td>
						<td colspan="2"><?php self::php_disabled_functions(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'PHP Loaded Extensions', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_loaded_php_extensions(); ?></td>
					</tr>
					<tr>
						<td colspan="4"><div class="ui ribbon inverted grey label"><?php esc_html_e( 'MySQL', 'mainwp' ); ?></div></td>
					</tr>
					<?php self::render_row( 'MySQL Version', '>=', '5.0', 'get_mysql_version', '', '', null, null, self::ERROR ); ?>
					<tr>
						<td colspan="2"><?php esc_html_e( 'MySQL Mode', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_sql_mode(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'MySQL Client Encoding', 'mainwp' ); ?></td>
						<td colspan="2"><?php echo defined( 'DB_CHARSET' ) ? DB_CHARSET : ''; ?></td>
					</tr>
					<tr>
						<td colspan="4"><div class="ui ribbon inverted grey label"><?php esc_html_e( 'Server Info', 'mainwp' ); ?></div></td>
					</tr>
					<tr class="mwp-not-generate-row">
						<td colspan="2"><?php esc_html_e( 'WordPress Root Directory', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_wp_root(); ?></td>
					</tr>
					<tr class="mwp-not-generate-row">
						<td colspan="2"><?php esc_html_e( 'Server Name', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_server_name(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Server Software', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_server_software(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Operating System', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_os(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Architecture', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_architecture(); ?></td>
					</tr>
					<tr class="mwp-not-generate-row">
						<td colspan="2"><?php esc_html_e( 'Server IP', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_server_ip(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Server Protocol', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_server_protocol(); ?></td>
					</tr>
					<tr class="mwp-not-generate-row">
						<td colspan="2"><?php esc_html_e( 'HTTP Host', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_http_host(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'HTTPS', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_https(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Server self connect', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::server_self_connect(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'User Agent', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_user_agent(); ?></td>
					</tr>
					<tr class="mwp-not-generate-row">
						<td colspan="2"><?php esc_html_e( 'Server Port', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_server_port(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Gateway Interface', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_server_gateway_interface(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Memory Usage', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::memory_usage(); ?></td>
					</tr>
					<tr class="mwp-not-generate-row">
						<td colspan="2"><?php esc_html_e( 'Complete URL', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_complete_url(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Request Time', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_server_request_time(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Accept Content', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_server_http_accept(); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Accept-Charset Content', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_server_accept_charset(); ?></td>
					</tr>
					<tr class="mwp-not-generate-row">
						<td colspan="2"><?php esc_html_e( 'Currently Executing Script Pathname', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_script_file_name(); ?></td>
					</tr>
					<tr class="mwp-not-generate-row">
						<td colspan="2"><?php esc_html_e( 'Current Page URI', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_current_page_uri(); ?></td>
					</tr>
					<tr class="mwp-not-generate-row">
						<td colspan="2"><?php esc_html_e( 'Remote Address', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_remote_address(); ?></td>
					</tr>
					<tr class="mwp-not-generate-row">
						<td colspan="2"><?php esc_html_e( 'Remote Host', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_remote_host(); ?></td>
					</tr>
					<tr class="mwp-not-generate-row">
						<td colspan="2"><?php esc_html_e( 'Remote Port', 'mainwp' ); ?></td>
						<td colspan="2"><?php MainWP_Server_Information_Handler::get_remote_port(); ?></td>
					</tr>
					<tr><td colspan="4"><div class="ui ribbon inverted grey label"><?php esc_html_e( 'MainWP Settings', 'mainwp' ); ?></div></td></tr>
					<?php self::display_mainwp_options(); ?>
					<tr><td colspan="4"><div class="ui ribbon inverted grey label"><?php esc_html_e( 'Active Plugins', 'mainwp' ); ?></div></td></tr>
					<?php
					$all_extensions = MainWP_Extensions_View::get_available_extensions();
					$all_plugins    = get_plugins();
					foreach ( $all_plugins as $slug => $plugin ) {
						if ( isset( $all_extensions[ dirname( $slug ) ] ) ) {
							continue;
						}
						?>
						<tr>
							<td><?php echo esc_html( $plugin['Name'] ); ?></td>
							<td><?php echo esc_html( $plugin['Version'] ); ?></td>
							<td colspan="2"><?php echo is_plugin_active( $slug ) ? __( 'Active', 'mainwp' ) : __( 'Inactive', 'mainwp' ); ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
				<tfoot class="full-width">
					<tr>
						<th colspan="4">
							<a href="#" class="ui right floated small green button" id="mainwp-download-system-report"><?php esc_html_e( 'Download System Report', 'mainwp' ); ?></a>
							<div><?php esc_html_e( 'Please include this information when requesting support.', 'mainwp' ); ?></div>
						</th>
					</tr>
				</tfoot>
			</table>
			<div id="download-server-information" style="display: none">
				<textarea readonly="readonly" wrap="off"></textarea>
			</div>
		<?php

		do_action( 'mainwp_after_server_info_table' );

		self::render_footer( '' );
	}

	/**
	 * Method render_quick_setup_system_check()
	 * 
	 * Render MainWP system requirements check.
	 * 
	 * @return html  MainWP system requirements check html.
	 */
	public static function render_quick_setup_system_check() {
		?>
		<table id="mainwp-quick-system-requirements-check" class="ui tablet stackable single line table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Check', 'mainwp' ); ?></th>
					<th><?php esc_html_e( 'Required Value', 'mainwp' ); ?></th>
					<th><?php esc_html_e( 'Detected Value', 'mainwp' ); ?></th>
					<th class="collapsing center aligned"><?php esc_html_e( 'Status', 'mainwp' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				self::render_row_with_description( __( 'PHP Version', 'mainwp' ), '>=', '5.6', 'get_php_version', '', '', null );
				self::render_row_with_description( __( 'SSL Extension Enabled', 'mainwp' ), '=', true, 'get_ssl_support', '', '', null );
				self::render_row_with_description( __( 'cURL Extension Enabled', 'mainwp' ), '=', true, 'get_curl_support', '', '', null );
				self::render_row_with_description( __( 'MySQL Version', 'mainwp' ), '>=', '5.0', 'get_mysql_version', '', '', null );
				?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Method get_mainwp_version_check()
	 * 
	 * Compare the detected MainWP Dashboard version agains the verion in WP.org.
	 * 
	 * @return mixed Pass|self::get_warning_html().
	 */
	public static function get_mainwp_version_check() {
		$current = get_option( 'mainwp_plugin_version' );
		$latest  = MainWP_Server_Information_Handler::get_mainwp_version();
		if ( $current == $latest ) {
			return '<div class="ui green basic label"><i class="check circle icon"></i> ' . __( 'Pass', 'mainwp' ) . '</div>';
		} else {
			return self::get_warning_html();
		}
	}

	/**
	 * Method render_cron()
	 * 
	 * Render the Cron Schedule page
	 * 
	 * @return html Cron Schedual Page html.
	 */
	public static function render_cron() {

		if ( ! mainwp_current_user_have_right( 'dashboard', 'see_server_information' ) ) {
			mainwp_do_not_have_permissions( 'cron schedules', 'mainwp' );

			return;
		}

		self::render_header( 'ServerInformationCron' );

		$cron_jobs = array(
			'Check for available updates'            => array( 'mainwp_cron_last_updatescheck', 'mainwp_cronupdatescheck_action', __( 'Once every minute', 'mainwp' ) ),
			'Check for new statistics'               => array( 'mainwp_cron_last_stats', 'mainwp_cronstats_action', __( 'Once hourly', 'mainwp' ) ),
			'Ping childs sites'                      => array( 'mainwp_cron_last_ping', 'mainwp_cronpingchilds_action', __( 'Once daily', 'mainwp' ) ),
		);

		if ( get_option( 'mainwp_enableLegacyBackupFeature' ) ) {
			$cron_jobs['Start backups (Legacy)']    = array( 'mainwp_cron_last_backups', 'mainwp_cronbackups_action', __( 'Once hourly', 'mainwp' ) );
			$cron_jobs['Continue backups (Legacy)'] = array( 'mainwp_cron_last_backups_continue', 'mainwp_cronbackups_continue_action', __( 'Once every five minutes', 'mainwp' ) );
		}

		?>

		<?php do_action( 'mainwp_before_cron_jobs_table' ); ?>

		<table class="ui stackable celled table fixed" id="mainwp-cron-jobs-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Cron Job', 'mainwp' ); ?></th>
					<th><?php esc_html_e( 'Hook', 'mainwp' ); ?></th>
					<th><?php esc_html_e( 'Schedule', 'mainwp' ); ?></th>
					<th><?php esc_html_e( 'Last Run', 'mainwp' ); ?></th>
					<th><?php esc_html_e( 'Next Run', 'mainwp' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $cron_jobs as $cron_job => $hook ) {
					$next_run = wp_next_scheduled( $hook[1] );
					?>
					<tr>
						<td><?php echo $cron_job; ?></td>
						<td><?php echo $hook[1]; ?></td>
						<td><?php echo $hook[2]; ?></td>
						<td><?php echo ( false === get_option( $hook[0] ) || 0 == get_option( $hook[0] ) ) ? esc_html__( 'Never', 'mainwp' ) : MainWP_Utility::format_timestamp( MainWP_Utility::get_timestamp( get_option( $hook[0] ) ) ); ?></td>
						<td><?php echo MainWP_Utility::format_timestamp( MainWP_Utility::get_timestamp( $next_run ) ); ?></td>
					</tr>
					<?php
				}
				do_action( 'mainwp_cron_jobs_list' );
				?>
			</tbody>
		</table>
		<script type="text/javascript">
		jQuery( '#mainwp-cron-jobs-table' ).DataTable( {
				"paging": false,
		} );
		</script>
		<?php

		do_action( 'mainwp_after_cron_jobs_table' );

		self::render_footer( 'ServerInformationCron' );
	}

	/**
	 * Method check_directory_mainwp_directory()
	 * 
	 * Check if the ../wp-content/uploads/mainwp/ directory is writable
	 * 
	 * @return boolean true|false.
	 */
	public static function check_directory_mainwp_directory() {
		$dirs = MainWP_Utility::get_mainwp_dir();
		$path = $dirs[0];

		if ( ! is_dir( dirname( $path ) ) ) {
			return self::render_directory_row( 'MainWP Upload Directory', 'Writable', 'Not Found', false, self::ERROR );
		}

		$hasWPFileSystem = MainWP_Utility::get_wp_file_system();

		/** @global WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( $hasWPFileSystem && ! empty( $wp_filesystem ) ) {
			if ( ! $wp_filesystem->is_writable( $path ) ) {
				return self::render_directory_row( 'MainWP Upload Directory', 'Writable', 'Not Writable', false, self::ERROR );
			}
		} else {
			if ( ! is_writable( $path ) ) {
				return self::render_directory_row( 'MainWP Upload Directory', 'Writable', 'Not Writable', false, self::ERROR );
			}
		}
		return self::render_directory_row( 'MainWP Upload Directory', 'Writable', 'Writable', true, self::ERROR );
	}

	/**
	 * Method 
	 * Print the directory check row.
	 */
	/**
	 * Method render_directory_row()
	 * 
	 * Render the directory check row.
	 * 
	 * @param mixed $pName path name
	 * @param mixed $pCheck Path Check.
	 * @param mixed $pResult Path result.
	 * @param mixed $pPassed Rath Passed
	 * @param int $errorType Global variable self::WARNING = 1.
	 * 
	 * @return mixed html|True. 
	 */
	public static function render_directory_row( $pName, $pCheck, $pResult, $pPassed, $errorType = self::WARNING ) {
		?>
		<tr>
			<td><?php echo esc_html( $pName ); ?></td>
			<td><?php echo esc_html( $pCheck ); ?></td>
			<td><?php echo esc_html( $pResult ); ?></td>
			<td><?php echo ( $pPassed ? '<div class="ui green basic label"><i class="check circle icon"></i> ' . __( 'Pass', 'mainwp' ) . '</div>' : self::get_warning_html( $errorType ) ); ?></td>
		</tr>
		<?php
		return true;
	}

	/**
	 * Method render_row()
	 * 
	 * Render Row.
	 * 
	 * @param mixed $pConfig Config Path.
	 * @param mixed $pCompare Compair Path.
	 * @param mixed $pVersion 
	 * @param mixed $pGetter
	 * @param string $pExtraText
	 * @param null $pExtraCompare
	 * @param null $pExtraVersion
	 * @param null $whatType curlSSL Type.
	 * @param int $errorType Global variable self::WARNING = 1.
	 * 
	 * @return html Row html.
	 */
	public static function render_row( $pConfig, $pCompare, $pVersion, $pGetter, $pExtraText = '', $pExtraCompare = null, $pExtraVersion = null, $whatType = null, $errorType = self::WARNING ) {
		$currentVersion = call_user_func( array( MainWP_Server_Information_Handler::get_class_name(), $pGetter ) );
		?>
		<tr>
			<td><?php echo esc_html( $pConfig ); ?></td>
			<td><?php echo esc_html( $pCompare ); ?><?php echo ( true === $pVersion ? 'true' : ( is_array( $pVersion ) && isset( $pVersion['version'] ) ? $pVersion['version'] : $pVersion ) ) . ' ' . $pExtraText; ?></td>
			<td><?php echo( true === $currentVersion ? 'true' : $currentVersion ); ?></td>
			<?php if ( 'filesize' === $whatType ) { ?>
				<td><?php echo ( MainWP_Server_Information_Handler::filesize_compare( $currentVersion, $pVersion, $pCompare ) ? '<div class="ui green basic label"><i class="check circle icon"></i> ' . __( 'Pass', 'mainwp' ) . '</div>' : self::get_warning_html( $errorType ) ); ?></td>
			<?php } elseif ( 'curlssl' === $whatType ) { ?>
				<td><?php echo ( MainWP_Server_Information_Handler::curlssl_compare( $pVersion, $pCompare ) ? '<div class="ui green basic label"><i class="check circle icon"></i> ' . __( 'Pass', 'mainwp' ) . '</div>' : self::get_warning_html( $errorType ) ); ?></td>
			<?php } elseif ( ( 'get_max_input_time' === $pGetter || 'get_max_execution_time' === $pGetter ) && -1 == $currentVersion ) { ?>
				<td><?php echo '<div class="ui green basic label"><i class="check circle icon"></i> ' . __( 'Pass', 'mainwp' ) . '</div>'; ?></td>
			<?php } else { ?>
				<td><?php echo ( version_compare( $currentVersion, $pVersion, $pCompare ) || ( ( null != $pExtraCompare ) && version_compare( $currentVersion, $pExtraVersion, $pExtraCompare ) ) ? '<div class="ui green basic label"><i class="check circle icon"></i> ' . __( 'Pass', 'mainwp' ) . '</div>' : self::get_warning_html( $errorType ) ); ?></td>
		<?php } ?>
		</tr>
		<?php
	}
	
	/**
	 * Method render_row_with_description()
	 * 
	 * Render row with description. 
	 * 
	 * @param mixed $pConfig
	 * @param mixed $pCompare
	 * @param mixed $pVersion
	 * @param mixed $pGetter
	 * @param string $pExtraText
	 * @param null $pExtraCompare
	 * @param null $pExtraVersion
	 * @param null $whatType
	 * @param undefined $errorType
	 * 
	 * @return html Row with description html.
	 */
	public static function render_row_with_description( $pConfig, $pCompare, $pVersion, $pGetter, $pExtraText = '', $pExtraCompare = null, $pExtraVersion = null, $whatType = null, $errorType = self::WARNING ) {
		$currentVersion = call_user_func( array( self::get_class_name(), $pGetter ) );
		?>
		<tr>
			<td><?php echo esc_html( $pConfig ); ?></td>
			<td><?php echo esc_html( $pCompare ); ?>  <?php echo ( true === $pVersion ? 'true' : ( is_array( $pVersion ) && isset( $pVersion['version'] ) ? $pVersion['version'] : $pVersion ) ) . ' ' . $pExtraText; ?></td>
			<td><?php echo ( true === $currentVersion ? 'true' : $currentVersion ); ?></td>
			<?php if ( 'filesize' === $whatType ) { ?>
				<td><?php echo ( MainWP_Server_Information_Handler::filesize_compare( $currentVersion, $pVersion, $pCompare ) ? '<div class="ui green basic label"><i class="check circle icon"></i> ' . __( 'Pass', 'mainwp' ) . '</div>' : self::get_warning_html( $errorType ) ); ?></td>
			<?php } elseif ( 'curlssl' === $whatType ) { ?>
				<td><?php echo ( MainWP_Server_Information_Handler::curlssl_compare( $pVersion, $pCompare ) ? '<div class="ui green basic label"><i class="check circle icon"></i> ' . __( 'Pass', 'mainwp' ) . '</div>' : self::get_warning_html( $errorType ) ); ?></td>
			<?php } elseif ( 'get_max_input_time' === $pGetter && -1 == $currentVersion ) { ?>
				<td><?php echo '<div class="ui green basic label"><i class="check circle icon"></i> ' . __( 'Pass', 'mainwp' ) . '</div>'; ?></td>
		<?php } else { ?>
				<td><?php echo( version_compare( $currentVersion, $pVersion, $pCompare ) || ( ( null != $pExtraCompare ) && version_compare( $currentVersion, $pExtraVersion, $pExtraCompare ) ) ? '<div class="ui green basic label"><i class="check circle icon"></i> ' . __( 'Pass', 'mainwp' ) . '</div>' : self::get_warning_html( $errorType ) ); ?></td>
		<?php } ?>
		</tr>
		<?php
	}

	/**
	 * Method get_file_system_method_check()
	 * 
	 * Check if file system method is direct. 
	 * 
	 * @return mixed html|self::get_warning_html().
	 */
	public static function get_file_system_method_check() {
		$fsmethod = MainWP_Server_Information_Handler::get_file_system_method();
		if ( 'direct' === $fsmethod ) {
			return '<div class="ui green basic label"><i class="check circle icon"></i> ' . __( 'Pass', 'mainwp' ) . '</div>';
		} else {
			return self::get_warning_html();
		}
	}

	/**
	 * Method render_error_log_page()
	 * 
	 * Render Error Log page.
	 * 
	 * Plugin Name: Error Log Dashboard Widget
	 * Plugin URI: http://wordpress.org/extend/plugins/error-log-dashboard-widget/
	 * Description: Robust zero-configuration and low-memory way to keep an eye on error log.
	 * Author: Andrey "Rarst" Savchenko
	 * Author URI: http://www.rarst.net/
	 * Version: 1.0.2
	 * License: GPLv2 or later

	 * Includes last_lines() function by phant0m, licensed under cc-wiki and GPLv2+
	 */
	public static function render_error_log_page() {

		if ( ! mainwp_current_user_have_right( 'dashboard', 'see_server_information' ) ) {
			mainwp_do_not_have_permissions( 'error log', 'mainwp' );

			return;
		}

		self::render_header( 'ErrorLog' );
		?>
		<table class="ui stackable celled table" id="mainwp-error-log-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Time', 'mainwp' ); ?></th>
					<th><?php esc_html_e( 'Error', 'mainwp' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php self::render_error_log(); ?>
			</tbody>
		</table>
		<?php
		self::render_footer( 'ErrorLog' );
	}

	/**
	 * Method render_error_log()
	 * 
	 * Render error log page.
	 * 
	 * @return html Error log page html.
	 */
	public static function render_error_log() {
		$log_errors = ini_get( 'log_errors' );
		if ( ! $log_errors ) {
			echo '<tr><td colspan="2">' . esc_html__( 'Error logging disabled.', 'mainwp' );
			echo '<br/>' . sprintf( esc_html__( 'To enable error logging, please check this %1$shelp document%2$s.', 'mainwp' ), '<a href="https://codex.wordpress.org/Debugging_in_WordPress" target="_blank">', '</a>' );
			echo '</td></tr>';
		}

		$error_log = ini_get( 'error_log' );
		$logs      = apply_filters( 'error_log_mainwp_logs', array( $error_log ) );
		$count     = apply_filters( 'error_log_mainwp_lines', 50 );
		$lines     = array();

		foreach ( $logs as $log ) {

			if ( is_readable( $log ) ) {
				$lines = array_merge( $lines, MainWP_Server_Information_Handler::last_lines( $log, $count ) );
			}
		}

		$lines = array_map( 'trim', $lines );
		$lines = array_filter( $lines );

		if ( empty( $lines ) ) {

			echo '<tr><td colspan="2">' . esc_html__( 'MainWP is unable to find your error logs, please contact your host for server error logs.', 'mainwp' ) . '</td></tr>';

			return;
		}

		foreach ( $lines as $key => $line ) {

			if ( false !== strpos( $line, ']' ) ) {
				list( $time, $error ) = explode( ']', $line, 2 );
			} else {
				list( $time, $error ) = array( '', $line );
			}

			$time          = trim( $time, '[]' );
			$error         = trim( $error );
			$lines[ $key ] = compact( 'time', 'error' );
		}

		if ( 1 < count( $lines ) ) {

			uasort( $lines, array( MainWP_Server_Information_Handler::get_class_name(), 'time_compare' ) );
			$lines = array_slice( $lines, 0, $count );
		}

		foreach ( $lines as $line ) {

			$error = esc_html( $line['error'] );
			$time  = esc_html( $line['time'] );
			if ( ! empty( $error ) ) {
				echo "<tr><td>{$time}</td><td>{$error}</td></tr>";
			}
		}
	}

	/**
	 * Method render_wp_config()
	 * 
	 * Render the wp comfig page. 
	 * 
	 * @return html WP Config page html.
	 */
	public static function render_wp_config() {

		if ( ! mainwp_current_user_have_right( 'dashboard', 'see_server_information' ) ) {
			mainwp_do_not_have_permissions( 'WP-Config.php', 'mainwp' );

			return;
		}

		self::render_header( 'WPConfig' );
		?>
		<div id="mainwp-show-wp-config">
			<?php
			if ( false !== strpos( ini_get( 'disable_functions' ), 'show_source' ) ) {
				esc_html_e( 'File content could not be displayed.', 'mainwp' );
				echo '<br />';
				esc_html_e( 'It appears that the show_source() PHP function has been disabled on the servre.', 'mainwp' );
				echo '<br />';
				esc_html_e( 'Please, contact your host support and have them enable the show_source() function for the proper functioning of this feature.', 'mainwp' );
			} else {
				if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
					show_source( ABSPATH . 'wp-config.php' );
				} else {

					$files       = get_included_files();
					$configFound = false;
					if ( is_array( $files ) ) {
						foreach ( $files as $file ) {
							if ( stristr( $file, 'wp-config.php' ) ) {
								$configFound = true;
								show_source( $file );
								break;
							}
						}
					}

					if ( ! $configFound ) {
						esc_html_e( 'wp-config.php not found', 'mainwp' );
					}
				}
			}
			?>
		</div>
		<?php
		self::render_footer( 'WPConfig' );
	}

	/**
	 * Method render_action_logs()
	 * 
	 * Render action logs page.
	 * 
	 * @return html Action logs html.
	 */
	public static function render_action_logs() {
		self::render_header( 'Action logs' );

		if ( isset( $_REQUEST['actionlogs_status'] ) ) {
			if ( MainWP_Logger::DISABLED != $_REQUEST['actionlogs_status'] ) {
				MainWP_Logger::instance()->set_log_priority( $_REQUEST['actionlogs_status'] );
			}

			MainWP_Logger::instance()->log( 'Action logs set to: ' . MainWP_Logger::instance()->get_log_text( $_REQUEST['actionlogs_status'] ), MainWP_Logger::LOG );

			if ( MainWP_Logger::DISABLED == $_REQUEST['actionlogs_status'] ) {
				MainWP_Logger::instance()->set_log_priority( $_REQUEST['actionlogs_status'] );
			}

			MainWP_Utility::update_option( 'mainwp_actionlogs', $_REQUEST['actionlogs_status'] );
		}

		if ( isset( $_REQUEST['actionlogs_clear'] ) ) {
			MainWP_Logger::clear_log();
		}

		$enabled = get_option( 'mainwp_actionlogs' );
		if ( false === $enabled ) {
			$enabled = MainWP_Logger::DISABLED;
		}
		?>
		<div class="postbox">
			<h3 class="hndle" style="padding: 8px 12px; font-size: 14px;"><span>Action logs</span></h3>

			<div style="padding: 1em;">
				<form method="POST" action="">
					<?php wp_nonce_field( 'mainwp-admin-nonce' ); ?>
					Status:
					<select name="actionlogs_status">
						<option value="<?php echo MainWP_Logger::DISABLED; ?>" <?php echo ( MainWP_Logger::DISABLED == $enabled ? 'selected' : '' ); ?>>
							<?php esc_html_e( 'Disabled', 'mainwp' ); ?>
						</option>
						<option value="<?php echo MainWP_Logger::WARNING; ?>" <?php echo ( MainWP_Logger::WARNING == $enabled ? 'selected' : '' ); ?>>
							<?php esc_html_e( 'Warning', 'mainwp' ); ?>
						</option>
						<option value="<?php echo MainWP_Logger::INFO; ?>" <?php echo ( MainWP_Logger::INFO == $enabled ? 'selected' : '' ); ?>>
							<?php esc_html_e( 'Info', 'mainwp' ); ?>
						</option>
						<option value="<?php echo MainWP_Logger::DEBUG; ?>" <?php echo ( MainWP_Logger::DEBUG == $enabled ? 'selected' : '' ); ?>>
							<?php esc_html_e( 'Debug', 'mainwp' ); ?>
						</option>
						<option value="<?php echo MainWP_Logger::INFO_UPDATE; ?>" <?php echo ( MainWP_Logger::INFO_UPDATE == $enabled ? 'selected' : '' ); ?>>
							<?php esc_html_e( 'Info Update', 'mainwp' ); ?>
						</option>
					</select>
					<input type="submit" class="button button-primary" value="Save"/> <input type="submit" class="button button-primary" name="actionlogs_clear" value="Clear"/>
				</form>
			</div>
			<div style="padding: 1em;"><?php MainWP_Logger::show_log(); ?></div>
		</div>
		<?php
		self::render_footer( 'Action logs' );
	}

	/**
	 * Method render_htaccess()
	 * 
	 * Render htaccess page.
	 * 
	 * @return html Htaccess page html.
	 */
	public static function render_htaccess() {

		if ( ! mainwp_current_user_have_right( 'dashboard', 'see_server_information' ) ) {
			mainwp_do_not_have_permissions( '.htaccess', 'mainwp' );

			return;
		}

		self::render_header( '.htaccess' );
		?>
		<div id="mainwp-show-htaccess">
			<?php
			if ( false !== strpos( ini_get( 'disable_functions' ), 'show_source' ) ) {
				esc_html_e( 'File content could not be displayed.', 'mainwp' );
				echo '<br />';
				esc_html_e( 'It appears that the show_source() PHP function has been disabled on the servre.', 'mainwp' );
				echo '<br />';
				esc_html_e( 'Please, contact your host support and have them enable the show_source() function for the proper functioning of this feature.', 'mainwp' );
			} else {
				show_source( ABSPATH . '.htaccess' );
			}
			?>
		</div>
		<?php
		self::render_footer( '.htaccess' );
	}

	/**
	 * Method php_disabled_functions()
	 * 
	 * Check for disable PHP Functions.
	 * 
	 * @return html Disabled functions list.
	 */
	public static function php_disabled_functions() {
		$disabled_functions = ini_get( 'disable_functions' );
		if ( '' !== $disabled_functions ) {
			$arr = explode( ',', $disabled_functions );
			sort( $arr );
			$_count = count( $arr );
			for ( $i = 0; $i < $_count; $i ++ ) {
				echo $arr[ $i ] . ', ';
			}
		} else {
			esc_html_e( 'No functions disabled.', 'mainwp' );
		}
	}

	/**
	 * Method display_mainwp_options()
	 * 
	 * Render MainWP Settings 'Options'.
	 * 
	 * @return html MainWP Settings html.
	 */
	public static function display_mainwp_options() {
		$options = MainWP_Server_Information_Handler::mainwp_options();
		foreach ( $options as $option ) {
			echo '<tr><td colspan="2">' . $option['label'] . '</td><td colspan="2">' . $option['value'] . '</td></tr>';
		}
	}

	/**
	 * Method get_warning_html()
	 * 
	 * Render PHP Warning HTML.
	 * 
	 * @param int $errorType Global variable self::WARNING = 1.
	 * 
	 * @return html PHP Warning html.
	 */
	private static function get_warning_html( $errorType = self::WARNING ) {
		if ( self::WARNING == $errorType ) {
			return '<div class="ui yellow basic label"><i class="exclamation circle icon"></i> ' . __( 'Warning', 'mainwp' ) . '</div>';
		}
		return '<span class="ui red basic label"><i class="times circle icon"></i> ' . __( 'Fail', 'mainwp' ) . '</div>';
	}

}
