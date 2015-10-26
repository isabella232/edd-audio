<?php
/*
Plugin Name: Easy Digital Downloads - Audio Player
Plugin URI: http://easydigitaldownloads.com/extension/audio-player
Description: Adds an audio player for previewing music tracks to your download details page
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
Version: 1.4
*/

// plugin folder url
if ( !defined( 'EDD_AP_PLUGIN_URL' ) ) {
	define( 'EDD_AP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

define( 'EDD_AP_STORE_API_URL', 'http://easydigitaldownloads.com' );
define( 'EDD_AP_PRODUCT_NAME', 'Audio Player' );
define( 'EDD_AP_VERSION', '1.4' );

if ( class_exists( 'EDD_License' ) ) {
	$edd_ap_license = new EDD_License( __FILE__, EDD_AP_PRODUCT_NAME, EDD_AP_VERSION, 'Pippin Williamson', 'edd_ap_license_key' );
}

/*****************************************
load the languages
*****************************************/

function edd_ap_load_textdomain() {
	load_plugin_textdomain( 'edd_ap', false, dirname( plugin_basename( EDD_PLUGIN_FILE ) ) . '/languages/' );
}
add_action( 'init', 'edd_ap_load_textdomain' );

function edd_ap_meta_fields_save( $fields ) {
	$fields[] = '_edd_show_audio_player';
	$fields[] = '_edd_ap_placement';
	$fields[] = 'edd_preview_files';
	$fields[] = '_edd_ap_theme';
	return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_ap_meta_fields_save' );

function edd_ap_sanitize_file_save( $files ) {

	foreach ( $files as $id => $file ) {

		if ( empty( $file['file'] ) && empty( $file['name'] ) ) {

			unset( $files[ $id ] );
			continue;

		}

	}

	return $files;
}
add_filter( 'edd_metabox_save_edd_preview_files', 'edd_ap_sanitize_file_save' );

function edd_ap_register_metabox() {
	add_meta_box( 'edd_product_notes', __( ' Audio Player', 'edd_ap' ), 'edd_ap_metabox', 'download', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'edd_ap_register_metabox' );

function edd_ap_metabox() {

	global $post;

	$show_button = get_post_meta( $post->ID, '_edd_show_audio_player', true );
	echo '<div id="edd_show_audio_player"><strong>' . __( 'Audio Player', 'edd_ap' ) . '</strong></div>';
	echo '<p>';
	echo '<input type="checkbox" name="_edd_show_audio_player" id="_edd_show_audio_player" value="1" ' . checked( 1, $show_button, false ) . '/>&nbsp;';
	echo '<label for="_edd_show_audio_player">' . __( 'Include a preview audio player for each music file included below? For complete placement control, use the short code below.', 'edd_ap' ) . '</label>';
	echo '</p>';

	$placement = get_post_meta( $post->ID, '_edd_ap_placement', true );

	$show_button = get_post_meta( $post->ID, '_edd_show_audio_player', true );

	$style = $show_button ? '' : 'style="display:none;"';

	echo '<script type="text/javascript">
		//<![CDATA[
			jQuery(function($){
				$("#_edd_show_audio_player").on("click", function() {
					$("#edd_ap_placement").toggle();
				});
			}); // end jquery(function($))
		//]]>
	</script>';

	echo '<p id="edd_ap_placement" ' . $style . '>';
	echo '<select name="_edd_ap_placement" id="_edd_ap_placement">';
	$placements = array( 'top' => __( 'Top', 'edd_ap' ), 'bottom' => __( 'Bottom', 'edd_ap' ) );
	foreach ( $placements as $key => $placement ) {
		echo '<option value="' . $key . '" ' . selected( $key, $placement, false ) . '>' . $placement . '</option>';
	}
	echo '</select>&nbsp;';
	echo '<label for="_edd_ap_placement">' . __( 'Place the audio player above or below the download content?', 'edd_ap' ) . '</label>';
	echo '</p>';

	//  button color
	$theme = get_post_meta( $post->ID, '_edd_ap_theme', true );
	echo '<div id="edd_ap_theme">';
	echo '<p>';
	echo '<select name="_edd_ap_theme" id="_edd_ap_theme">';
	$themes = apply_filters( 'edd_ap_audio_themes',
		array(
			'premium_pixels' => __( 'Premium Pixels', 'edd_ap' ),
			'midnight_black' => __( 'Midnight Black', 'edd_ap' ),
			'morning_light'  => __( 'Morning Light', 'edd_ap' ),
			'blue' => __( 'jPlayer Blue', 'edd_ap' ),
			'pink' => __( 'jPlayer Pink', 'edd_ap' ),
			'none' => __( 'None', 'edd_ap' )
		)
	);
	foreach ( $themes as $key => $option ) {
		echo '<option value="' . $key . '" ' . selected( $key, $theme, false ) . '>' . $option . '</option>';
	}
	echo '</select>&nbsp;';
	echo '<label for="_edd_ap_theme">' . __( 'Pick a skin. To use your theme\'s included jPlayer skin, choose "None".', 'edd_ap' ) . '</label>';
	echo '</p>';
	echo '</div>';

	// downloadable files
	$files = get_post_meta( $post->ID, 'edd_preview_files', true );

	echo '<div id="edd_preview_files_wrap" class="edd_meta_table_wrap">';
	echo '<table class="widefat" width="100%" cellpadding="0" cellspacing="0">';
	echo '<thead>';
	echo '<tr>';
	echo '<th style="width: 3%;"></th>';
	echo '<th style="width: 20%;">' . __( 'Name', 'edd_ap' ) . '</th>';
	echo '<th>' . __( 'File URL', 'edd_ap' ) . '</th>';
	echo '<th style="width: 2%;"></th>';
	echo '</tr>';
	echo '</thead>';
	if ( is_array( $files ) ) {
		foreach ( $files as $key => $value ) {
			echo '<tr class="edd_repeatable_upload_wrapper edd_repeatable_row" data-key="' . esc_attr( $key ) . '">';
			$name = isset( $files[$key]['name'] ) ? $files[$key]['name'] : '';
			$file = isset( $files[$key]['file'] ) ? $files[$key]['file'] : '';
			echo '<td>';
			echo '<img class="edd_ap_drag_handle" src="' . EDD_AP_PLUGIN_URL . 'images/cross-hair.png" style="cursor: move; position: relative; top: 4px;"/>';
			echo '</td>';
			echo '<td>';
			echo '<input type="text"  class="edd_repeatable_name_field regular-text" placeholder="' . __( 'file name', 'edd_ap' ) . '" name="edd_preview_files[' . $key . '][name]" id="edd_preview_files[' . $key . '][name]" value="' . $name . '" size="20"" />';
			echo '</td>';
			echo '<td>';
			echo '<div class="edd_repeatable_upload_field_container">';
			echo '<input type="text" class="edd_repeatable_upload_field edd_upload_field large-text" placeholder="' . __( 'file url', 'edd_ap' ) . '" name="edd_preview_files[' . $key . '][file]" id="edd_preview_files[' . $key . '][file]" value="' . $file . '" size="30"" />';
			echo '<span class="edd_upload_file">';
			echo '<a href="#" class="edd_upload_file_button" onclick="return false;">'. __( 'Upload a File', 'edd' ) . '</a>';
			echo '</span>';
			echo '</div>';
			echo '</td>';
			echo '<td>';
			echo '<a href="#" class="edd_remove_repeatable" data-type="audio" style="background: url(' . admin_url( '/images/xit.gif' ) . ') no-repeat;">&times;</a>';
			echo '</td>';
			echo '</tr>';
		}
	} else {
		echo '<tr class="edd_repeatable_upload_wrapper edd_repeatable_row" id="edd_audio_files">';
		echo '<td>';
		echo '<img class="edd_ap_drag_handle" src="' . EDD_AP_PLUGIN_URL . 'images/cross-hair.png" style="cursor: move; position: relative; top: 4px;"/>';
		echo '</td>';
		echo '<td>';
		echo '<input type="text" class="edd_repeatable_name_field large-text" placeholder="' . __( 'file name', 'edd_ap' ) . '" name="edd_preview_files[1][name]" id="edd_preview_files[1][name]" value="" size="20"/>';
		echo '</td>';
		echo '<td>';
		echo '<div class="edd_repeatable_upload_field_container">';
		echo '<input type="text" class="edd_repeatable_upload_field edd_upload_field large-text" placeholder="' . __( 'file url', 'edd_ap' ) . '" name="edd_preview_files[1][file]" id="edd_preview_files[1][file]" value="" size="30" />';
		echo '<span class="edd_upload_file">';
		echo '<a href="#" class="edd_upload_file_button" onclick="return false;">'. __( 'Upload a File', 'edd' ) . '</a>';
		echo '</span>';
		echo '</div>';
		echo '</td>';
		echo '<td>';
		echo '<a href="#" class="edd_remove_repeatable" data-type="audio" style="background: url(' . admin_url( '/images/xit.gif' ) . ') no-repeat;">&times;</a>';
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr>';
	echo '<td class="submit" colspan="4" style="float:none; clear:both; background: #fff;">';
	echo '<a class="button-secondary edd_add_repeatable" style="margin: 6px 0;">' . __( 'Add New', 'edd_ap' ) . '</a>';
	echo '</td>';
	echo '</tr>';

	echo '</table>';

	echo '<input type="hidden" id="edd_preview_files" class="edd_repeatable_upload_name_field" value=""/>';
	echo '<input type="hidden" class="edd_repeatable_upload_file_field" value=""/>';

	echo '</div>';

	echo '<div id="edd_ap_shortcode">';
	echo '<p>';
	echo '<strong>[edd_audio id="' . $post->ID . '"]</strong>';
	echo '<label> - ' . __( 'This short code can be used to show the audio player for this download anywhere on your site.', 'edd_ap' ) . '</label>';
	echo '</div>';
	echo '</p>';

}

function edd_ap_scripts( $post_id = null, $override_singular = false ) {

	global $post;

	if ( ( is_singular( 'download' ) ) || $override_singular == true ) {

		if ( is_null( $post_id ) || !is_numeric( $post_id ) ) {
			$post_id = get_the_ID();
		}

		if ( ! get_post_meta( $post_id, '_edd_show_audio_player', true ) ) {
			return;
		}

		wp_enqueue_script( 'jquery-jplayer', EDD_AP_PLUGIN_URL . 'js/jquery.jplayer.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'jquery-jplayer-playlist', EDD_AP_PLUGIN_URL . 'js/jplayer.playlist.min.js', array( 'jquery' ) );

		$theme = get_post_meta( $post_id, '_edd_ap_theme', true );
		$theme_url = edd_ap_get_theme_css_url( $theme );
		if ( $theme_url ) {
			wp_enqueue_style( 'jplayer-skin-' . $theme, $theme_url );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'edd_ap_scripts' );

function edd_ap_admin_scripts() {
	global $typenow;

	if ( $typenow != 'download' )
		return;

	wp_enqueue_script( 'edd-ap-scripts', EDD_AP_PLUGIN_URL . 'js/edd-audio.js', array( 'jquery' ) );
	wp_localize_script( 'edd-ap-scripts', 'edd_ap_vars', array(
			'nonce' => wp_create_nonce( 'edd_audio_nonce' )
		)
	);
}
add_action( 'admin_enqueue_scripts', 'edd_ap_admin_scripts' );

function edd_ap_get_theme_css_url( $theme = 'blue' ) {

	if ( $theme == 'none' || empty( $theme ) ) {
		return false;
	}

	$themes = apply_filters( 'edd_ap_theme_urls', array(
			'blue' => EDD_AP_PLUGIN_URL . 'css/blue.monday/jplayer.blue.monday.css',
			'pink' => 'http://jplayer.org/latest/skin/pink.flag/jplayer.pink.flag.css',
			'midnight_black' => EDD_AP_PLUGIN_URL . 'css/midnight.black/jplayer.midnight.black.css',
			'morning_light' => EDD_AP_PLUGIN_URL . 'css/morning.light/jplayer.morning.light.css',
			'premium_pixels' => EDD_AP_PLUGIN_URL . 'css/premium-pixels/premium-pixels.css'
		)
	);
	return $themes[$theme];
}

function edd_ap_show_player( $content ) {
	global $post;
	if ( is_singular( 'download' ) && is_main_query() ) {
		if ( get_post_meta( $post->ID, '_edd_show_audio_player', true ) ) {
			$preview_files = get_post_meta( $post->ID, 'edd_preview_files', true );
			$script = edd_ap_get_playlist_script( $preview_files, $post->ID );
			$player = edd_ap_player( $post->ID );
			$placement = get_post_meta( $post->ID, '_edd_ap_placement', true );
			if ( $placement == 'top' ) {
				$content = $script . $player . $content;
			} else {
				$content = $content . $script . $player;
			}
		}
	}
	return $content;
}
add_filter( 'the_content', 'edd_ap_show_player', 0 );

function edd_ap_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
				'id' => null
			), $atts )
	);
	if ( $id ) {

		$preview_files = get_post_meta( $id, 'edd_preview_files', true );
		edd_ap_scripts( $id, true );
		$script = edd_ap_get_playlist_script( $preview_files, $id );
		$player = edd_ap_player( $id );
		$content = $content . $script . $player;

		return $content;
	}
}
add_shortcode( 'edd_audio', 'edd_ap_shortcode' );

function edd_ap_player( $post_id ) {
	ob_start(); ?>
	<div id="jquery_jplayer_<?php echo $post_id; ?>" class="jp-jplayer"></div>
	<div id="jp_container_<?php echo $post_id; ?>" class="jp-audio" style="margin: 0 0 20px;">
		<div class="jp-type-playlist">
			<div class="jp-gui jp-interface">
				<ul class="jp-controls">
					<li><a href="javascript:;" class="jp-previous" tabindex="1"><?php _e( 'previous', 'edd_ap' ); ?></a></li>
					<li><a href="javascript:;" class="jp-play" tabindex="1"><?php _e( 'play', 'edd_ap' ); ?></a></li>
					<li><a href="javascript:;" class="jp-pause" tabindex="1"><?php _e( 'pause', 'edd_ap' ); ?></a></li>
					<li><a href="javascript:;" class="jp-next" tabindex="1"><?php _e( 'next', 'edd_ap' ); ?></a></li>
					<li><a href="javascript:;" class="jp-stop" tabindex="1"><?php _e( 'stop', 'edd_ap' ); ?></a></li>
					<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute"><?php _e( 'mute', 'edd_ap' ); ?></a></li>
					<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute"><?php _e( 'unmute', 'edd_ap' ); ?></a></li>
					<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume"><?php _e( 'max volume', 'edd_ap' ); ?></a></li>
				</ul>
				<div class="jp-progress">
					<div class="jp-seek-bar">
						<div class="jp-play-bar"></div>
					</div>
				</div>
				<div class="jp-volume-bar">
					<div class="jp-volume-bar-value"></div>
				</div>
				<div class="jp-time-holder">
					<div class="jp-current-time"></div>
					<div class="jp-duration"></div>
				</div>
				<ul class="jp-toggles">
					<li><a href="javascript:;" class="jp-shuffle" tabindex="1" title="shuffle"><?php _e( 'shuffle', 'edd_ap' ); ?></a></li>
					<li><a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="shuffle off"><?php _e( 'shuffle off', 'edd_ap' ); ?></a></li>
					<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat"><?php _e( 'repeat', 'edd_ap' ); ?></a></li>
					<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off"><?php _e( 'repeat off', 'edd_ap' ); ?></a></li>
				</ul>
			</div>
			<div class="jp-playlist">
				<ul>
					<li></li>
				</ul>
			</div>
			<div class="jp-no-solution">
				<span><?php _e( 'Update Required', 'edd_ap' ); ?></span>
				<?php _e( 'To play the media you will need to either update your browser to a recent version or update your', 'edd_ap' ); ?> <a href="http://get.adobe.com/flashplayer/" target="_blank"><?php _e( 'Flash plugin', 'edd_ap' ); ?></a>.
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function edd_ap_get_playlist_script( $preview_files, $post_id ) {
	ob_start();
?>
		<style type="text/css">.jp-audio p { display: none; }</style>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				new jPlayerPlaylist({
					jPlayer: "#jquery_jplayer_<?php echo $post_id; ?>",
					cssSelectorAncestor: "#jp_container_<?php echo $post_id; ?>"
				},[
				<?php
	$playlist = '';
	foreach ( $preview_files as $file ) {
		$playlist .= '{title:"' . $file['name'] . '", mp3: "' . $file['file'] . '"},';
	}
	echo $playlist;
?>
				], {
					swfPath: "<?php echo EDD_AP_PLUGIN_URL; ?>js/Jplayer.swf",
					supplied: "mp3",
					wmode: "window",
					solution: "html,flash"
				});
			});
		</script>
	<?php
	return ob_get_clean();
}

function edd_ap_update_file_order() {
	if ( wp_verify_nonce( $_POST['nonce'], 'edd_audio_nonce' ) ) {
		$edd_preview_files = $_POST['edd_preview_files'];
		update_post_meta( $_POST['post_id'], 'edd_preview_files', $edd_preview_files );
	}
	die();
}
add_action( 'wp_ajax_edd_update_audio_files_order', 'edd_ap_update_file_order' );

// Register field with FES 2.3+
function edd_audio_player_add_fes_functionality(){
	if ( class_exists( 'EDD_Front_End_Submissions' ) ){
		if ( version_compare( fes_plugin_version, '2.3', '>=' ) ) {
			require_once dirname( __FILE__ ) . '/audio-field.php';
			add_filter(  'fes_load_fields_array', 'edd_audio_player_add_field', 10, 1 );
			function edd_audio_player_add_field( $fields ){
				$fields['audio_player'] = 'FES_Audio_Player_Field';
				return $fields;
			}
		}
	}
}
add_action( 'fes_load_fields_require', 'edd_audio_player_add_fes_functionality' );