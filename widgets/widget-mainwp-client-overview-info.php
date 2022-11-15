<?php
/**
 * MainWP Client Overview Info Widget
 *
 * Displays the Client Info.
 *
 * @package MainWP/MainWP_Client_Overview_Info
 */

namespace MainWP\Dashboard;

/**
 * Class MainWP_Client_Overview_Info
 *
 * Displays the Client info.
 */
class MainWP_Client_Overview_Info {

	/**
	 * Method get_class_name()
	 *
	 * @return string __CLASS__ Class name.
	 */
	public static function get_class_name() {
		return __CLASS__;
	}

	/**
	 * Method render()
	 *
	 * @return mixed render_site_info()
	 */
	public static function render() {
		$client_id = isset( $_GET['client_id'] ) ? $_GET['client_id'] : 0;
		if ( empty( $client_id ) ) {
			return;
		}
		self::render_client_overview( $client_id );
	}


	/**
	 * Render client overview Info.
	 *
	 * @param object $client_id Client ID.
	 */
	public static function render_client_overview( $client_id ) {  // phpcs:ignore -- complex function.
		$params      = array( 'with_selected_sites' => true );
		$client_info = MainWP_DB_Client::instance()->get_wp_client_by( 'client_id', $client_id, ARRAY_A, $params );

		?>
			<h3 class="ui header handle-drag">
				<?php echo esc_html( $client_info['name'] ); ?>
				<div class="sub header"><?php echo esc_html( $client_info['name'] ); ?> <?php echo __( ' information', 'mainwp' ); ?></div>
			</h3>
			<div class="ui section hidden divider"></div>
			<div class="mainwp-widget-client-card" client-id="<?php echo intval( $client_id ); ?>">
				<?php
				/**
				 * Actoin: mainwp_clients_overview_overview_widget_top
				 *
				 * Fires at the top of the Site Info widget on the Individual site overview page.
				 *
				 * @param object $client_info Object containing the child site info.
				 *
				 * @since 4.0
				 */
				do_action( 'mainwp_clients_overview_overview_widget_top', $client_info );
				?>
				<?php
				if ( $client_info ) {
					$selected_sites = isset( $client_info['selected_sites'] ) ? trim( $client_info['selected_sites'] ) : '';
					?>
					<div class="ui stackable grid">
						<div class="four wide middle aligned column">
							<?php if ( empty( $client_info['image'] ) ) : ?>
								<i class="user icon massive"></i>
							<?php else : ?>
								<?php $image_url = MainWP_Client_Handler::get_client_image_url( $client_info['image'] ); ?>
								<img class="ui medium circular image" src="<?php echo esc_attr( $image_url ); ?>">
							<?php endif; ?>
						</div>
						<div class="twelve wide middle aligned column">
							<div class="ui relaxed list">
								<?php if ( isset( $client_info['client_email'] ) && '' != $client_info['client_email'] ) : ?>
								<div class="item">
								<i class="envelope grey icon"></i>
									<div class="content"><a href="mailto:<?php echo esc_url( $client_info['client_email'] ); ?>" target="_blank"><?php echo esc_html( $client_info['client_email'] ); ?></a> <i data-clipboard-text="<?php echo esc_html( $client_info['client_email'] ); ?>" style="cursor:pointer" class="copy green icon copy-to-clipboard"></i></div>
								</div>
								<?php endif; ?>
								<?php if ( isset( $client_info['client_phone'] ) && '' != $client_info['client_phone'] ) : ?>
								<div class="item">
									<i class="phone grey rotated icon"></i>
									<div class="content"><a href="tel:<?php echo esc_url( $client_info['client_phone'] ); ?>" target="_blank"><?php echo esc_html( $client_info['client_phone'] ); ?></a></div>
								</div>
								<?php endif; ?>
								<?php if ( isset( $client_info['client_facebook'] ) && '' != $client_info['client_facebook'] ) : ?>
								<div class="item">
									<i class="facebook grey icon"></i>
									<div class="content"><a href="<?php echo esc_url( $client_info['client_facebook'] ); ?>" target="_blank"><?php echo esc_html( $client_info['client_facebook'] ); ?></a></div>
								</div>
								<?php endif; ?>
								<?php if ( isset( $client_info['client_twitter'] ) && '' != $client_info['client_twitter'] ) : ?>
								<div class="item">
									<i class="twitter grey icon"></i>
									<div class="content"><a href="<?php echo esc_url( $client_info['client_twitter'] ); ?>" target="_blank"><?php echo esc_html( $client_info['client_twitter'] ); ?></a></div>
								</div>
								<?php endif; ?>
								<?php if ( isset( $client_info['client_instagram'] ) && '' != $client_info['client_instagram'] ) : ?>
								<div class="item">
									<i class="instagram grey icon"></i>
									<div class="content"><a href="<?php echo esc_url( $client_info['client_instagram'] ); ?>" target="_blank"><?php echo esc_html( $client_info['client_instagram'] ); ?></a></div>
								</div>
								<?php endif; ?>
								<?php if ( isset( $client_info['client_linkedin'] ) && '' != $client_info['client_linkedin'] ) : ?>
								<div class="item">
									<i class="linkedin grey icon"></i>
									<div class="content"><a href="<?php echo esc_url( $client_info['client_linkedin'] ); ?>" target="_blank"><?php echo esc_html( $client_info['client_linkedin'] ); ?></a></div>
								</div>
								<?php endif; ?>

								<?php if ( ( isset( $client_info['address_1'] ) && '' != $client_info['address_1'] ) || ( isset( $client_info['address_2'] ) && '' != $client_info['address_2'] ) || ( isset( $client_info['city'] ) && '' != $client_info['city'] ) || ( isset( $client_info['state'] ) && '' != $client_info['state'] ) || ( isset( $client_info['zip'] ) && '' != $client_info['zip'] ) || ( isset( $client_info['country'] ) && '' != $client_info['country'] ) ) : ?>
								<div class="item">
								<i class="map marker grey icon"></i>
										<div class="content">
											<?php if ( isset( $client_info['address_1'] ) && '' != $client_info['address_1'] ) : ?>
												<?php echo esc_html( $client_info['address_1'] ); ?>
											<?php endif; ?>
									<?php if ( isset( $client_info['address_2'] ) && '' != $client_info['address_2'] ) : ?>
												<?php echo esc_html( $client_info['address_2'] ); ?>
											<?php endif; ?>
											<?php if ( isset( $client_info['city'] ) && '' != $client_info['city'] ) : ?>
												<?php echo esc_html( $client_info['city'] ); ?>
											<?php endif; ?>
											<?php if ( isset( $client_info['zip'] ) && '' != $client_info['zip'] ) : ?>
												<?php echo esc_html( $client_info['zip'] ); ?>
											<?php endif; ?>
											<?php if ( isset( $client_info['state'] ) && '' != $client_info['state'] ) : ?>
												<?php echo esc_html( $client_info['state'] ); ?>
											<?php endif; ?>
											<?php if ( isset( $client_info['country'] ) && '' != $client_info['country'] ) : ?>
												<?php echo esc_html( $client_info['country'] ); ?>
								<?php endif; ?>
								</div>
								</div>
								<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="ui hidden divider"></div>
				<div class="ui hidden divider"></div>

				<a href="admin.php?page=ClientAddNew&client_id=<?php echo intval( $client_id ); ?>" title="" class="ui button green"><?php echo esc_html__( 'Edit Client', 'mainwp' ); ?></a>
				<a class="ui green basic button" href="admin.php?page=managesites&client=<?php echo intval( $client_id ); ?>"><?php esc_html_e( 'Manage Sites', 'mainwp' ); ?></a>
				<?php if ( is_plugin_active( 'mainwp-pro-reports-extension/mainwp-pro-reports-extension.php' ) ) { ?>
					<a class="ui green basic button" href="admin.php?page=Extensions-Mainwp-Pro-Reports-Extension&tab=report&action=newreport&selected_sites=<?php echo esc_html( $selected_sites ); ?>"><?php esc_html_e( 'Create Report', 'mainwp' ); ?></a>
				<?php } ?>
				<a href="javascript:void(0);" suspend-status="<?php echo intval( $client_info['suspended'] ); ?>" title="" class="ui right floated button client-suspend-unsuspend-sites"><?php echo 0 == $client_info['suspended'] ? esc_html__( 'Suspend Sites', 'mainwp' ) : esc_html__( 'Unsuspend Sites', 'mainwp' ); ?></a>

					<script type="text/javascript">
							jQuery( document ).ready( function ($) {
								new ClipboardJS('.copy-to-clipboard');
							} );
					</script>					
				<?php } ?>
				<?php
				/**
				 * Action: mainwp_clients_overview_overview_widget_bottom
				 *
				 * Fires at the bottom of the Site Info widget on the Individual site overview page.
				 *
				 * @param object $client_info Object containing the child site info.
				 *
				 * @since 4.0
				 */
				do_action( 'mainwp_clients_overview_overview_widget_bottom', $client_info );
				?>
			</div>
			<?php
	}

}
