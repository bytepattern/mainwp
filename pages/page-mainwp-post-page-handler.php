<?php
namespace MainWP\Dashboard;

/**
 * MainWP Posts Handler
 *
 * @uses MainWP_Bulk_Add
 */
class MainWP_Post_Page_Handler {

	public static function get_class_name() {
		return __CLASS__;
	}

	/**
	 * Add post meta data defined in $_POST superglobal for post with given ID.
	 *
	 * @since 1.2.0
	 *
	 * @param int $post_ID
	 * @return int|bool
	 */

	public static function add_meta( $post_ID ) {
		$post_ID = (int) $post_ID;

		$metakeyselect = isset( $_POST['metakeyselect'] ) ? wp_unslash( trim( $_POST['metakeyselect'] ) ) : '';
		$metakeyinput  = isset( $_POST['metakeyinput'] ) ? wp_unslash( trim( $_POST['metakeyinput'] ) ) : '';
		$metavalue     = isset( $_POST['metavalue'] ) ? $_POST['metavalue'] : '';
		if ( is_string( $metavalue ) ) {
			$metavalue = trim( $metavalue );
		}

		if ( ( ( '#NONE#' !== $metakeyselect ) && ! empty( $metakeyselect ) ) || ! empty( $metakeyinput ) ) {
			if ( '#NONE#' !== $metakeyselect ) {
				$metakey = $metakeyselect;
			}

			if ( $metakeyinput ) {
				$metakey = $metakeyinput;
			}

			if ( is_protected_meta( $metakey, 'post' ) || ! current_user_can( 'add_post_meta', $post_ID, $metakey ) ) {
				return false;
			}

			$metakey = wp_slash( $metakey );

			return add_post_meta( $post_ID, $metakey, $metavalue );
		}

		return false;
	}

	/**
	 * Method ajax_add_meta()
	 *
	 * Ajax process to add post meta data
	 *
	 * @return exit json result
	 */
	public static function ajax_add_meta() {

		MainWP_Post_Handler::instance()->secure_request( 'mainwp_post_addmeta' );

		$c   = 0;
		$pid = (int) $_POST['post_id'];

		if ( isset( $_POST['metakeyselect'] ) || isset( $_POST['metakeyinput'] ) ) {
			if ( ! current_user_can( 'edit_post', $pid ) ) {
				wp_die( -1 );
			}
			if ( isset( $_POST['metakeyselect'] ) && '#NONE#' === $_POST['metakeyselect'] && empty( $_POST['metakeyinput'] ) ) {
				wp_die( 1 );
			}
			$mid = self::add_meta( $pid );
			if ( ! $mid ) {
				wp_send_json( array( 'error' => __( 'Please provide a custom field value.', 'mainwp' ) ) );
			}

			$meta = get_metadata_by_mid( 'post', $mid );
			$pid  = (int) $meta->post_id;
			$meta = get_object_vars( $meta );

			$data = MainWP_Post::list_meta_row( $meta, $c );

		} elseif ( isset( $_POST['delete_meta'] ) && 'yes' === $_POST['delete_meta'] ) {
			$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

			check_ajax_referer( "delete-meta_$id", 'meta_nonce' );
			$meta = get_metadata_by_mid( 'post', $id );
			if ( ! $meta ) {
				wp_send_json( array( 'ok' => 1 ) );
			}

			if ( is_protected_meta( $meta->meta_key, 'post' ) || ! current_user_can( 'delete_post_meta', $meta->post_id, $meta->meta_key ) ) {
				wp_die( -1 );
			}

			if ( delete_meta( $meta->meta_id ) ) {
				wp_send_json( array( 'ok' => 1 ) );
			}

			wp_die( 0 );

		} else {
			$mid   = (int) key( $_POST['meta'] );
			$key   = wp_unslash( $_POST['meta'][ $mid ]['key'] );
			$value = wp_unslash( $_POST['meta'][ $mid ]['value'] );
			if ( '' == trim( $key ) ) {
				wp_send_json( array( 'error' => __( 'Please provide a custom field name.', 'mainwp' ) ) );
			}
			$meta = get_metadata_by_mid( 'post', $mid );
			if ( ! $meta ) {
				wp_die( 0 );
			}
			if ( is_protected_meta( $meta->meta_key, 'post' ) || is_protected_meta( $key, 'post' ) ||
				! current_user_can( 'edit_post_meta', $meta->post_id, $meta->meta_key ) ||
				! current_user_can( 'edit_post_meta', $meta->post_id, $key ) ) {
				wp_die( -1 );
			}
			if ( $meta->meta_value != $value || $meta->meta_key != $key ) {
				$u = update_metadata_by_mid( 'post', $mid, $value, $key );
				if ( ! $u ) {
					wp_die( 0 );
				}
			}

			$data = MainWP_Post::list_meta_row(
				array(
					'meta_key'   => $key,
					'meta_value' => $value,
					'meta_id'    => $mid,
				),
				$c
			);
		}

		wp_send_json( array( 'result' => $data ) );
	}


	/**
	 * Method get_categories()
	 *
	 * Get categories
	 *
	 * @return exit html result
	 */
	public static function get_categories() {
		$websites = array();
		if ( isset( $_REQUEST['sites'] ) && ( '' !== $_REQUEST['sites'] ) ) {
			$siteIds          = explode( ',', urldecode( $_REQUEST['sites'] ) );
			$siteIdsRequested = array();
			foreach ( $siteIds as $siteId ) {
				$siteId = $siteId;
				if ( ! MainWP_Utility::ctype_digit( $siteId ) ) {
					continue;
				}
				$siteIdsRequested[] = $siteId;
			}

			$websites = MainWP_DB::instance()->get_websites_by_ids( $siteIdsRequested );
		} elseif ( isset( $_REQUEST['groups'] ) && ( '' !== $_REQUEST['groups'] ) ) {
			$groupIds          = explode( ',', urldecode( $_REQUEST['groups'] ) );
			$groupIdsRequested = array();
			foreach ( $groupIds as $groupId ) {
				$groupId = $groupId;

				if ( ! MainWP_Utility::ctype_digit( $groupId ) ) {
					continue;
				}
				$groupIdsRequested[] = $groupId;
			}

			$websites = MainWP_DB::instance()->get_websites_by_group_ids( $groupIdsRequested );
		}

		$selectedCategories  = array();
		$selectedCategories2 = array();

		if ( isset( $_REQUEST['selected_categories'] ) && ( '' !== $_REQUEST['selected_categories'] ) ) {
			$selectedCategories = explode( ',', urldecode( $_REQUEST['selected_categories'] ) );
		}

		if ( ! is_array( $selectedCategories ) ) {
			$selectedCategories = array();
		}

		$allCategories = array( 'Uncategorized' );
		if ( 0 < count( $websites ) ) {
			foreach ( $websites as $website ) {
				$cats = json_decode( $website->categories, true );
				if ( is_array( $cats ) && ( 0 < count( $cats ) ) ) {
					$allCategories = array_unique( array_merge( $allCategories, $cats ) );
				}
			}
		}
		$allCategories = array_unique( array_merge( $allCategories, $selectedCategories ) );

		if ( 0 < count( $allCategories ) ) {
			natcasesort( $allCategories );
			foreach ( $allCategories as $category ) {
				echo '<option value="' . $category . '" class="sitecategory">' . $category . '</option>';
			}
		}
		die();
	}

	/**
	 * Method posting()
	 *
	 * Create bulk posts on sites
	 *
	 * @return html result
	 */
	public static function posting() { // phpcs:ignore -- complex method
		$succes_message = '';
		if ( isset( $_GET['id'] ) ) {
			$edit_id = get_post_meta( $_GET['id'], '_mainwp_edit_post_id', true );
			if ( $edit_id ) {
				$succes_message = __( 'Post has been updated successfully', 'mainwp' );
			} else {
				$succes_message = __( 'New post created', 'mainwp' );
			}
		}

		?>
		<div class="ui modal" id="mainwp-posting-post-modal">
			<div class="header"><?php $edit_id ? esc_html_e( 'Edit Post', 'mainwp' ) : esc_html_e( 'New Post', 'mainwp' ); ?></div>
			<div class="scrolling content">
				<?php
				do_action( 'mainwp_bulkpost_before_post', $_GET['id'] );

				$skip_post = false;
				if ( isset( $_GET['id'] ) ) {
					if ( 'yes' === get_post_meta( $_GET['id'], '_mainwp_skip_posting', true ) ) {
						$skip_post = true;
						wp_delete_post( $_GET['id'], true );
					}
				}

				if ( ! $skip_post ) {
					if ( isset( $_GET['id'] ) ) {
						$id    = intval( $_GET['id'] );
						$_post = get_post( $id );
						if ( $_post ) {
							$selected_by     = get_post_meta( $id, '_selected_by', true );
							$val             = get_post_meta( $id, '_selected_sites', true );
							$selected_sites  = MainWP_Utility::maybe_unserialyze( $val );
							$val             = get_post_meta( $id, '_selected_groups', true );
							$selected_groups = MainWP_Utility::maybe_unserialyze( $val );

							$post_category = base64_decode( get_post_meta( $id, '_categories', true ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.

							$post_tags   = base64_decode( get_post_meta( $id, '_tags', true ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
							$post_slug   = base64_decode( get_post_meta( $id, '_slug', true ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
							$post_custom = get_post_custom( $id );

							$galleries           = get_post_gallery( $id, false );
							$post_gallery_images = array();

							if ( is_array( $galleries ) && isset( $galleries['ids'] ) ) {
								$attached_images = explode( ',', $galleries['ids'] );
								foreach ( $attached_images as $attachment_id ) {
									$attachment = get_post( $attachment_id );
									if ( $attachment ) {
										$post_gallery_images[] = array(
											'id'          => $attachment_id,
											'alt'         => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
											'caption'     => $attachment->post_excerpt,
											'description' => $attachment->post_content,
											'src'         => $attachment->guid,
											'title'       => $attachment->post_title,
										);
									}
								}
							}

							include_once ABSPATH . 'wp-includes' . DIRECTORY_SEPARATOR . 'post-thumbnail-template.php';
							$featured_image_id   = get_post_thumbnail_id( $id );
							$post_featured_image = null;
							$featured_image_data = null;
							$mainwp_upload_dir   = wp_upload_dir();

							$post_status = get_post_meta( $id, '_edit_post_status', true );

							if ( 'pending' !== $post_status ) {
								$post_status = $_post->post_status;
							}
							$post_status = apply_filters( 'mainwp_posting_bulkpost_post_status', $post_status, $id );
							$new_post    = array(
								'post_title'     => $_post->post_title,
								'post_content'   => $_post->post_content,
								'post_status'    => $post_status,
								'post_date'      => $_post->post_date,
								'post_date_gmt'  => $_post->post_date_gmt,
								'post_tags'      => $post_tags,
								'post_name'      => $post_slug,
								'post_excerpt'   => $_post->post_excerpt,
								'comment_status' => $_post->comment_status,
								'ping_status'    => $_post->ping_status,
								'mainwp_post_id' => $_post->ID,
							);

							if ( null != $featured_image_id ) {
								$img                 = wp_get_attachment_image_src( $featured_image_id, 'full' );
								$post_featured_image = $img[0];
								$attachment          = get_post( $featured_image_id );
								$featured_image_data = array(
									'alt'            => get_post_meta( $featured_image_id, '_wp_attachment_image_alt', true ),
									'caption'        => $attachment->post_excerpt,
									'description'    => $attachment->post_content,
									'title'          => $attachment->post_title,
								);
							}

							$dbwebsites = array();
							if ( 'site' === $selected_by ) {
								foreach ( $selected_sites as $k ) {
									if ( MainWP_Utility::ctype_digit( $k ) ) {
										$website                    = MainWP_DB::instance()->get_website_by_id( $k );
										$dbwebsites[ $website->id ] = MainWP_Utility::map_site(
											$website,
											array(
												'id',
												'url',
												'name',
												'adminname',
												'nossl',
												'privkey',
												'nosslkey',
												'http_user',
												'http_pass',
											)
										);
									}
								}
							} else {
								foreach ( $selected_groups as $k ) {
									if ( MainWP_Utility::ctype_digit( $k ) ) {
										$websites = MainWP_DB::instance()->query( MainWP_DB::instance()->get_sql_websites_by_group_id( $k ) );
										while ( $websites && ( $website  = MainWP_DB::fetch_object( $websites ) ) ) {
											if ( '' !== $website->sync_errors ) {
												continue;
											}
											$dbwebsites[ $website->id ] = MainWP_Utility::map_site(
												$website,
												array(
													'id',
													'url',
													'name',
													'adminname',
													'nossl',
													'privkey',
													'nosslkey',
													'http_user',
													'http_pass',
												)
											);
										}
										MainWP_DB::free_result( $websites );
									}
								}
							}

							$output         = new \stdClass();
							$output->ok     = array();
							$output->errors = array();
							$startTime      = time();

							if ( 0 < count( $dbwebsites ) ) {
								$post_data = array(
									'new_post'            => base64_encode( serialize( $new_post ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
									'post_custom'         => base64_encode( serialize( $post_custom ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
									'post_category'       => base64_encode( $post_category ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
									'post_featured_image' => base64_encode( $post_featured_image ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
									'post_gallery_images' => base64_encode( serialize( $post_gallery_images ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
									'mainwp_upload_dir'   => base64_encode( serialize( $mainwp_upload_dir ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
									'featured_image_data' => base64_encode( serialize( $featured_image_data ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
								);
								MainWP_Connect::fetch_urls_authed(
									$dbwebsites,
									'newpost',
									$post_data,
									array(
										MainWP_Bulk_Add::get_class_name(),
										'posting_bulk_handler',
									),
									$output
								);
							}

							$failed_posts = array();
							foreach ( $dbwebsites as $website ) {
								if ( isset( $output->ok[ $website->id ] ) && ( 1 == $output->ok[ $website->id ] ) && ( isset( $output->added_id[ $website->id ] ) ) ) {
									$links = isset( $output->link[ $website->id ] ) ? $output->link[ $website->id ] : null;
									do_action_deprecated( 'mainwp-post-posting-post', array( $website, $output->added_id[ $website->id ], $links ), '4.0.1', 'mainwp_post_posting_post' ); // @deprecated Use 'mainwp_post_posting_page' instead.
									do_action_deprecated( 'mainwp-bulkposting-done', array( $_post, $website, $output ), '4.0.1', 'mainwp_bulkposting_done' ); // @deprecated Use 'mainwp_bulkposting_done' instead.

									do_action( 'mainwp_post_posting_post', $website, $output->added_id[ $website->id ], $links );
									do_action( 'mainwp_bulkposting_done', $_post, $website, $output );
								} else {
									$failed_posts[] = $website->id;
								}
							}

							/*
							* @deprecated Use 'mainwp_after_posting_bulkpost_result' instead.
							*
							*/
							$newExtensions = apply_filters_deprecated( 'mainwp-after-posting-bulkpost-result', array( false, $_post, $dbwebsites, $output ), '4.0.1', 'mainwp_after_posting_bulkpost_result' );
							$after_posting = apply_filters( 'mainwp_after_posting_bulkpost_result', $newExtensions, $_post, $dbwebsites, $output );

							if ( false === $after_posting ) {
								?>
							<div class="ui relaxed list">
								<?php
								foreach ( $dbwebsites as $website ) {
									?>
									<div class="item"><a href="<?php echo admin_url( 'admin.php?page=managesites&dashboard=' . $website->id ); ?>"><?php echo stripslashes( $website->name ); ?></a>
									: <?php echo( isset( $output->ok[ $website->id ] ) && 1 == $output->ok[ $website->id ] ? $succes_message . ' <a href="' . $output->link[ $website->id ] . '" class="mainwp-may-hide-referrer" target="_blank">View Post</a>' : $output->errors[ $website->id ] ); ?>
									</div>
							<?php } ?>
							</div>
								<?php
							}

							$do_not_del = get_post_meta( $id, '_bulkpost_do_not_del', true );
							if ( 'yes' !== $do_not_del ) {
								wp_delete_post( $id, true );
							}

							$countSites     = 0;
							$countRealItems = 0;
							foreach ( $dbwebsites as $website ) {
								if ( isset( $output->ok[ $website->id ] ) && 1 == $output->ok[ $website->id ] ) {
									$countSites++;
									$countRealItems++;
								}
							}

							if ( ! empty( $countSites ) ) {
								$seconds = ( time() - $startTime );
								MainWP_Twitter::update_twitter_info( 'new_post', $countSites, $seconds, $countRealItems, $startTime, 1 );
							}

							if ( MainWP_Twitter::enabled_twitter_messages() ) {
								$twitters = MainWP_Twitter::get_twitter_notice( 'new_post' );
								if ( is_array( $twitters ) ) {
									foreach ( $twitters as $timeid => $twit_mess ) {
										if ( ! empty( $twit_mess ) ) {
											$sendText = MainWP_Twitter::get_twit_to_send( 'new_post', $timeid );
											?>
										<div class="mainwp-tips ui info message twitter" style="margin:0">
											<i class="ui close icon mainwp-dismiss-twit"></i><span class="mainwp-tip" twit-what="new_post" twit-id="<?php echo $timeid; ?>"><?php echo $twit_mess; ?></span>&nbsp;<?php MainWP_Twitter::gen_twitter_button( $sendText ); ?>
										</div>
											<?php
										}
									}
								}
							}
						}
					} else {
						?>
					<div class="error">
						<p>
							<strong><?php esc_html_e( 'ERROR', 'mainwp' ); ?></strong>: <?php esc_html_e( 'An undefined error occured!', 'mainwp' ); ?>
						</p>
					</div>
						<?php
					}
				}
				?>
		</div>
		<div class="actions">
			<a href="admin.php?page=PostBulkAdd" class="ui green button"><?php esc_html_e( 'New Post', 'mainwp' ); ?></a>
			<div class="ui cancel button"><?php esc_html_e( 'Close', 'mainwp' ); ?></div>
		</div>
	</div>
	<div class="ui active inverted dimmer" id="mainwp-posting-running">
	<div class="ui indeterminate large text loader"><?php esc_html_e( 'Running ...', 'mainwp' ); ?></div>
	</div>
		<script type="text/javascript">
			jQuery( document ).ready( function () {
				jQuery( "#mainwp-posting-running" ).hide();
				jQuery( "#mainwp-posting-post-modal" ).modal( {
					closable: true,
					onHide: function() {
						location.href = 'admin.php?page=PostBulkManage';
					}
				} ).modal( 'show' );
			} );
		</script>
		<?php
	}

	/**
	 * Method get_post()
	 *
	 * Get post from child site to edit
	 *
	 * @return exit json result
	 */
	public static function get_post() {
		$postId    = $_POST['postId'];
		$postType  = $_POST['postType'];
		$websiteId = $_POST['websiteId'];

		if ( ! MainWP_Utility::ctype_digit( $postId ) ) {
			die( wp_json_encode( array( 'error' => 'Invalid request!' ) ) );
		}
		if ( ! MainWP_Utility::ctype_digit( $websiteId ) ) {
			die( wp_json_encode( array( 'error' => 'Invalid request!' ) ) );
		}

		$website = MainWP_DB::instance()->get_website_by_id( $websiteId );
		if ( ! MainWP_Utility::can_edit_website( $website ) ) {
			die( wp_json_encode( array( 'error' => 'You can not edit this website!' ) ) );
		}

		try {
			$information = MainWP_Connect::fetch_url_authed(
				$website,
				'post_action',
				array(
					'action'     => 'get_edit',
					'id'         => $postId,
					'post_type'  => $postType,
				)
			);
		} catch ( MainWP_Exception $e ) {
			die( wp_json_encode( array( 'error' => MainWP_Error_Helper::get_error_message( $e ) ) ) );
		}

		if ( is_array( $information ) && isset( $information['error'] ) ) {
			die( wp_json_encode( array( 'error' => $information['error'] ) ) );
		}

		if ( ! isset( $information['status'] ) || ( 'SUCCESS' !== $information['status'] ) ) {
			die( wp_json_encode( array( 'error' => 'Unexpected error.' ) ) );
		} else {
			$ret = self::new_post( $information['my_post'] );
			if ( is_array( $ret ) && isset( $ret['id'] ) ) {
				// to support edit post.
				update_post_meta( $ret['id'], '_selected_sites', array( $websiteId ) );
				update_post_meta( $ret['id'], '_mainwp_edit_post_site_id', $websiteId );
			}
			wp_send_json( $ret );
		}
	}

	/**
	 * Method new_post()
	 *
	 * Create new post.
	 *
	 * @return array result
	 */
	public static function new_post( $post_data = array() ) {
		$new_post            = maybe_unserialize( base64_decode( $post_data['new_post'] ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
		$post_custom         = maybe_unserialize( base64_decode( $post_data['post_custom'] ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
		$post_category       = rawurldecode( isset( $post_data['post_category'] ) ? base64_decode( $post_data['post_category'] ) : null ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
		$post_tags           = rawurldecode( isset( $new_post['post_tags'] ) ? $new_post['post_tags'] : null );
		$post_featured_image = base64_decode( $post_data['post_featured_image'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
		$post_gallery_images = base64_decode( $post_data['post_gallery_images'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
		$upload_dir          = maybe_unserialize( base64_decode( $post_data['child_upload_dir'] ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
		return self::create_post( $new_post, $post_custom, $post_category, $post_featured_image, $upload_dir, $post_tags, $post_gallery_images );
	}

	/**
	 * Method create_post()
	 *
	 * Create post.
	 *
	 * @return array result
	 */
	public static function create_post( $new_post, $post_custom, $post_category, $post_featured_image, $upload_dir, $post_tags, $post_gallery_images ) { // phpcs:ignore -- complex method.
		global $current_user;

		if ( ! isset( $new_post['edit_id'] ) ) {
			return array( 'error' => 'Empty post id' );
		}

		$post_author             = $current_user->ID;
		$new_post['post_author'] = $post_author;
		$new_post['post_type']   = isset( $new_post['post_type'] ) && ( 'page' === $new_post['post_type'] ) ? 'bulkpage' : 'bulkpost';

		$foundMatches = preg_match_all( '/(<a[^>]+href=\"(.*?)\"[^>]*>)?(<img[^>\/]*src=\"((.*?)(png|gif|jpg|jpeg))\")/ix', $new_post['post_content'], $matches, PREG_SET_ORDER );
		if ( 0 < $foundMatches ) {
			foreach ( $matches as $match ) {
				$hrefLink = $match[2];
				$imgUrl   = $match[4];

				if ( ! isset( $upload_dir['baseurl'] ) || ( 0 !== strripos( $imgUrl, $upload_dir['baseurl'] ) ) ) {
					continue;
				}

				if ( preg_match( '/-\d{3}x\d{3}\.[a-zA-Z0-9]{3,4}$/', $imgUrl, $imgMatches ) ) {
					$search         = $imgMatches[0];
					$replace        = '.' . $match[6];
					$originalImgUrl = str_replace( $search, $replace, $imgUrl );
				} else {
					$originalImgUrl = $imgUrl;
				}

				try {
					$downloadfile = MainWP_Utility::upload_image( $originalImgUrl );
					$localUrl     = $downloadfile['url'];

					$linkToReplaceWith = dirname( $localUrl );
					if ( '' !== $hrefLink ) {
						$server     = get_option( 'mainwp_child_server' );
						$serverHost = wp_parse_url( $server, PHP_URL_HOST );
						if ( ! empty( $serverHost ) && false !== strpos( $hrefLink, $serverHost ) ) {
							$serverHref               = 'href="' . $serverHost;
							$replaceServerHref        = 'href="' . wp_parse_url( $localUrl, PHP_URL_SCHEME ) . '://' . wp_parse_url( $localUrl, PHP_URL_HOST );
							$new_post['post_content'] = str_replace( $serverHref, $replaceServerHref, $new_post['post_content'] );
						}
					}
					$lnkToReplace = dirname( $imgUrl );
					if ( 'http:' !== $lnkToReplace && 'https:' !== $lnkToReplace ) {
						$new_post['post_content'] = str_replace( $lnkToReplace, $linkToReplaceWith, $new_post['post_content'] );
					}
				} catch ( Exception $e ) {
					// ok.
				}
			}
		}

		if ( has_shortcode( $new_post['post_content'], 'gallery' ) ) {
			if ( preg_match_all( '/\[gallery[^\]]+ids=\"(.*?)\"[^\]]*\]/ix', $new_post['post_content'], $matches, PREG_SET_ORDER ) ) {
				$replaceAttachedIds = array();
				if ( is_array( $post_gallery_images ) ) {
					foreach ( $post_gallery_images as $gallery ) {
						if ( isset( $gallery['src'] ) ) {
							try {
								$upload = MainWP_Utility::upload_image( $gallery['src'], $gallery, true );
								if ( null !== $upload ) {
									$replaceAttachedIds[ $gallery['id'] ] = $upload['id'];
								}
							} catch ( Exception $e ) {
								// ok.
							}
						}
					}
				}
				if ( 0 < count( $replaceAttachedIds ) ) {
					foreach ( $matches as $match ) {
						$idsToReplace     = $match[1];
						$idsToReplaceWith = '';
						$originalIds      = explode( ',', $idsToReplace );
						foreach ( $originalIds as $attached_id ) {
							if ( ! empty( $originalIds ) && isset( $replaceAttachedIds[ $attached_id ] ) ) {
								$idsToReplaceWith .= $replaceAttachedIds[ $attached_id ] . ',';
							}
						}
						$idsToReplaceWith = rtrim( $idsToReplaceWith, ',' );
						if ( ! empty( $idsToReplaceWith ) ) {
							$new_post['post_content'] = str_replace( '"' . $idsToReplace . '"', '"' . $idsToReplaceWith . '"', $new_post['post_content'] );
						}
					}
				}
			}
		}

		$is_sticky = false;
		if ( isset( $new_post['is_sticky'] ) ) {
			$is_sticky = ! empty( $new_post['is_sticky'] ) ? true : false;
			unset( $new_post['is_sticky'] );
		}
		$edit_id = $new_post['edit_id'];
		unset( $new_post['edit_id'] );

		$wp_error = null;
		remove_filter( 'content_save_pre', 'wp_filter_post_kses' );
		$post_status             = $new_post['post_status'];
		$new_post['post_status'] = 'auto-draft';
		$new_post_id             = wp_insert_post( $new_post, $wp_error );

		if ( is_wp_error( $wp_error ) ) {
			return array( 'error' => $wp_error->get_error_message() );
		}

		if ( empty( $new_post_id ) ) {
			return array( 'error' => 'Undefined error' );
		}

		wp_update_post(
			array(
				'ID'          => $new_post_id,
				'post_status' => $post_status,
			)
		);

		foreach ( $post_custom as $meta_key => $meta_values ) {
			foreach ( $meta_values as $meta_value ) {
				add_post_meta( $new_post_id, $meta_key, $meta_value );
			}
		}

		update_post_meta( $new_post_id, '_mainwp_edit_post_id', $edit_id );
		update_post_meta( $new_post_id, '_slug', base64_encode( $new_post['post_name'] ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
		if ( isset( $post_category ) && '' !== $post_category ) {
			update_post_meta( $new_post_id, '_categories', base64_encode( $post_category ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
		}

		if ( isset( $post_tags ) && '' !== $post_tags ) {
			update_post_meta( $new_post_id, '_tags', base64_encode( $post_tags ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
		}
		if ( $is_sticky ) {
			update_post_meta( $new_post_id, '_sticky', base64_encode( 'sticky' ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
		}

		if ( null !== $post_featured_image ) {
			try {
				$upload = MainWP_Utility::upload_image( $post_featured_image );

				if ( null !== $upload ) {
					update_post_meta( $new_post_id, '_thumbnail_id', $upload['id'] );
				}
			} catch ( Exception $e ) {
				// ok.
			}
		}

		$ret['success'] = true;
		$ret['id']      = $new_post_id;
		return $ret;
	}


	/**
	 * Method add_sticky_handle()
	 *
	 * Add post meta
	 *
	 * @return int post id
	 */
	public static function add_sticky_handle( $post_id ) {
		$_post = get_post( $post_id );
		if ( 'bulkpost' === $_post->post_type && isset( $_POST['sticky'] ) ) {
			update_post_meta( $post_id, '_sticky', base64_encode( $_POST['sticky'] ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.

			return base64_encode( $_POST['sticky'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for benign reasons.
		}

		if ( 'bulkpost' === $_post->post_type && isset( $_POST['mainwp_edit_post_status'] ) ) {
			update_post_meta( $post_id, '_edit_post_status', $_POST['mainwp_edit_post_status'] );
		}

		return $post_id;
	}

}
