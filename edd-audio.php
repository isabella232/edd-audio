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
if(!defined('EDD_AP_PLUGIN_URL')) {
	define('EDD_AP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

define( 'EDD_AP_STORE_API_URL', 'http://easydigitaldownloads.com' );
define( 'EDD_AP_PRODUCT_NAME', 'Audio Player' );
define( 'EDD_AP_VERSION', '1.4' );

if( class_exists( 'EDD_License' ) ) {
	$edd_ap_license = new EDD_License( __FILE__, EDD_AP_PRODUCT_NAME, EDD_AP_VERSION, 'Pippin Williamson', 'edd_ap_license_key' );
}

/*****************************************
load the languages
*****************************************/

function edd_ap_load_textdomain() {
	load_plugin_textdomain( 'edd_ap', false, dirname( plugin_basename( EDD_PLUGIN_FILE ) ) . '/languages/' );
}
add_action('init', 'edd_ap_load_textdomain');

function edd_ap_meta_fields_save($fields) {
	$fields[] = '_edd_show_audio_player';
	$fields[] = '_edd_ap_placement';
	$fields[] = 'edd_preview_files';
	$fields[] = '_edd_ap_theme';
	return $fields;
}
add_filter('edd_metabox_fields_save', 'edd_ap_meta_fields_save');

function edd_ap_sanitize_file_save( $files ) {

	foreach( $files as $id => $file ) {

		if( empty( $file['file'] ) && empty( $file['name'] ) ) {

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

	$show_button = get_post_meta( $post->ID, '_edd_show_audio_player', true);
	echo '<div id="edd_show_audio_player"><strong>' . __('Audio Player', 'edd_ap') . '</strong></div>';
	echo '<p>';
		echo '<input type="checkbox" name="_edd_show_audio_player" id="_edd_show_audio_player" value="1" ' . checked(1, $show_button, false) . '/>&nbsp;';
		echo '<label for="_edd_show_audio_player">' . __('Include a preview audio player for each music file included below? For complete placement control, use the short code below.', 'edd_ap') . '</label>';
	echo '</p>';

	$placement = get_post_meta( $post->ID, '_edd_ap_placement', true);

	$show_button = get_post_meta( $post->ID, '_edd_show_audio_player', true);

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
			$placements = array('top' => __('Top', 'edd_ap'), 'bottom' => __('Bottom', 'edd_ap'));
			foreach($placements as $key => $placement) {
				echo '<option value="' . $key . '" ' . selected($key, $placement, false) . '>' . $placement . '</option>';
			}
		echo '</select>&nbsp;';
		echo '<label for="_edd_ap_placement">' . __('Place the audio player above or below the download content?', 'edd_ap') . '</label>';
	echo '</p>';

	//  button color
	$theme = get_post_meta( $post->ID, '_edd_ap_theme', true);
	echo '<div id="edd_ap_theme">';
		echo '<p>';
			echo '<select name="_edd_ap_theme" id="_edd_ap_theme">';
				$themes = apply_filters('edd_ap_audio_themes',
					array(
						'premium_pixels' => __('Premium Pixels', 'edd_ap'),
						'midnight_black' => __('Midnight Black', 'edd_ap'),
						'morning_light'  => __('Morning Light', 'edd_ap'),
						'blue' => __('jPlayer Blue', 'edd_ap'),
						'pink' => __('jPlayer Pink', 'edd_ap'),
						'none' => __('None', 'edd_ap')
					)
				);
				foreach($themes as $key => $option) {
					echo '<option value="' . $key . '" ' . selected($key, $theme, false) . '>' . $option . '</option>';
				}
			echo '</select>&nbsp;';
			echo '<label for="_edd_ap_theme">' . __('Pick a skin. To use your theme\'s included jPlayer skin, choose "None".', 'edd_ap') . '</label>';
		echo '</p>';
	echo '</div>';

	// downloadable files
	$files = get_post_meta( $post->ID, 'edd_preview_files', true);

	echo '<div id="edd_preview_files_wrap" class="edd_meta_table_wrap">';
		echo '<table class="widefat" width="100%" cellpadding="0" cellspacing="0">';
			echo '<thead>';
				echo '<tr>';
					echo '<th style="width: 3%;"></th>';
					echo '<th style="width: 20%;">' . __('Name', 'edd_ap') . '</th>';
					echo '<th>' . __('File URL', 'edd_ap') . '</th>';
					echo '<th style="width: 2%;"></th>';
				echo '</tr>';
			echo '</thead>';
			if(is_array($files)) {
				foreach($files as $key => $value) {
					echo '<tr class="edd_repeatable_upload_wrapper edd_repeatable_row" data-key="' . esc_attr( $key ) . '">';
						$name = isset($files[$key]['name']) ? $files[$key]['name'] : '';
						$file = isset($files[$key]['file']) ? $files[$key]['file'] : '';
						echo '<td>';
							echo '<img class="edd_ap_drag_handle" src="' . EDD_AP_PLUGIN_URL . 'images/cross-hair.png" style="cursor: move; position: relative; top: 4px;"/>';
						echo '</td>';
						echo '<td>';
							echo '<input type="text"  class="edd_repeatable_name_field regular-text" placeholder="' . __('file name', 'edd_ap') . '" name="edd_preview_files[' . $key . '][name]" id="edd_preview_files[' . $key . '][name]" value="' . $name . '" size="20"" />';
						echo '</td>';
						echo '<td>';
							echo '<div class="edd_repeatable_upload_field_container">';
								echo '<input type="text" class="edd_repeatable_upload_field edd_upload_field large-text" placeholder="' . __('file url', 'edd_ap') . '" name="edd_preview_files[' . $key . '][file]" id="edd_preview_files[' . $key . '][file]" value="' . $file . '" size="30"" />';
								echo '<span class="edd_upload_file">';
									echo '<a href="#" class="edd_upload_file_button" onclick="return false;">'. __( 'Upload a File', 'edd' ) . '</a>';
								echo '</span>';
							echo '</div>';
						echo '</td>';
						echo '<td>';
							echo '<a href="#" class="edd_remove_repeatable" data-type="audio" style="background: url(' . admin_url('/images/xit.gif') . ') no-repeat;">&times;</a>';
						echo '</td>';
					echo '</tr>';
				}
			} else {
				echo '<tr class="edd_repeatable_upload_wrapper edd_repeatable_row" id="edd_audio_files">';
					echo '<td>';
						echo '<img class="edd_ap_drag_handle" src="' . EDD_AP_PLUGIN_URL . 'images/cross-hair.png" style="cursor: move; position: relative; top: 4px;"/>';
					echo '</td>';
					echo '<td>';
						echo '<input type="text" class="edd_repeatable_name_field large-text" placeholder="' . __('file name', 'edd_ap') . '" name="edd_preview_files[1][name]" id="edd_preview_files[1][name]" value="" size="20"/>';
					echo '</td>';
					echo '<td>';
						echo '<div class="edd_repeatable_upload_field_container">';
							echo '<input type="text" class="edd_repeatable_upload_field edd_upload_field large-text" placeholder="' . __('file url', 'edd_ap') . '" name="edd_preview_files[1][file]" id="edd_preview_files[1][file]" value="" size="30" />';
							echo '<span class="edd_upload_file">';
								echo '<a href="#" class="edd_upload_file_button" onclick="return false;">'. __( 'Upload a File', 'edd' ) . '</a>';
							echo '</span>';
						echo '</div>';
					echo '</td>';
					echo '<td>';
						echo '<a href="#" class="edd_remove_repeatable" data-type="audio" style="background: url(' . admin_url('/images/xit.gif') . ') no-repeat;">&times;</a>';
					echo '</td>';
				echo '</tr>';
			}
			echo '<tr>';
				echo '<td class="submit" colspan="4" style="float:none; clear:both; background: #fff;">';
					echo '<a class="button-secondary edd_add_repeatable" style="margin: 6px 0;">' . __('Add New', 'edd_ap') . '</a>';
				echo '</td>';
			echo '</tr>';

		echo '</table>';

		echo '<input type="hidden" id="edd_preview_files" class="edd_repeatable_upload_name_field" value=""/>';
		echo '<input type="hidden" class="edd_repeatable_upload_file_field" value=""/>';

	echo '</div>';

	echo '<div id="edd_ap_shortcode">';
		echo '<p>';
			echo '<strong>[edd_audio id="' . $post->ID . '"]</strong>';
			echo '<label> - ' . __('This short code can be used to show the audio player for this download anywhere on your site.', 'edd_ap') . '</label>';
		echo '</div>';
	echo '</p>';

}

function edd_ap_scripts($post_id = null, $override_singular = false) {

	global $post;

	if( ( is_singular('download') ) || $override_singular == true) {

		if(is_null($post_id) || !is_numeric($post_id)) {
			$post_id = get_the_ID();
		}

		wp_enqueue_script('jquery-jplayer', EDD_AP_PLUGIN_URL . 'js/jquery.jplayer.min.js', array('jquery'));
		wp_enqueue_script('jquery-jplayer-playlist', EDD_AP_PLUGIN_URL . 'js/jplayer.playlist.min.js', array('jquery'));

		$theme = get_post_meta($post_id, '_edd_ap_theme', true);
		$theme_url = edd_ap_get_theme_css_url($theme);
		if( $theme_url ) {
			wp_enqueue_style('jplayer-skin-' . $theme, $theme_url);
		}
	}
}
add_action('wp_enqueue_scripts', 'edd_ap_scripts');

function edd_ap_admin_scripts() {
	global $typenow;

	if( $typenow != 'download' )
		return;

	wp_enqueue_script('edd-ap-scripts', EDD_AP_PLUGIN_URL . 'js/edd-audio.js', array('jquery'));
	wp_localize_script('edd-ap-scripts', 'edd_ap_vars', array(
			'nonce' => wp_create_nonce('edd_audio_nonce')
		)
	);
}
add_action('admin_enqueue_scripts', 'edd_ap_admin_scripts');

function edd_ap_get_theme_css_url($theme = 'blue') {

	if( $theme == 'none')
		return false;

	$themes = apply_filters('edd_ap_theme_urls', array(
			'blue' => EDD_AP_PLUGIN_URL . 'css/blue.monday/jplayer.blue.monday.css',
			'pink' => 'http://jplayer.org/latest/skin/pink.flag/jplayer.pink.flag.css',
			'midnight_black' => EDD_AP_PLUGIN_URL . 'css/midnight.black/jplayer.midnight.black.css',
			'morning_light' => EDD_AP_PLUGIN_URL . 'css/morning.light/jplayer.morning.light.css',
			'premium_pixels' => EDD_AP_PLUGIN_URL . 'css/premium-pixels/premium-pixels.css'
		)
	);
	return $themes[$theme];
}

function edd_ap_show_player($content) {
	global $post;
	if(is_singular('download') && is_main_query()) {
		if(get_post_meta($post->ID, '_edd_show_audio_player', true)) {
			$preview_files = get_post_meta($post->ID, 'edd_preview_files', true);
			$script = edd_ap_get_playlist_script($preview_files, $post->ID);
			$player = edd_ap_player($post->ID);
			$placement = get_post_meta($post->ID, '_edd_ap_placement', true);
			if( $placement == 'top') {
				$content = $script . $player . $content;
			} else {
				$content = $content . $script . $player;
			}
		}
	}
	return $content;
}
add_filter('the_content', 'edd_ap_show_player', 0);

function edd_ap_shortcode($atts, $content = null) {
	extract( shortcode_atts( array(
			'id' => null
		), $atts )
	);
	if($id) {

		$preview_files = get_post_meta($id, 'edd_preview_files', true);
		edd_ap_scripts($id, true);
		$script = edd_ap_get_playlist_script($preview_files, $id);
		$player = edd_ap_player($id);
		$content = $content . $script . $player;

		return $content;
	}
}
add_shortcode('edd_audio', 'edd_ap_shortcode');

function edd_ap_player($post_id) {
	ob_start(); ?>
	<div id="jquery_jplayer_<?php echo $post_id; ?>" class="jp-jplayer"></div>
	<div id="jp_container_<?php echo $post_id; ?>" class="jp-audio" style="margin: 0 0 20px;">
		<div class="jp-type-playlist">
			<div class="jp-gui jp-interface">
				<ul class="jp-controls">
					<li><a href="javascript:;" class="jp-previous" tabindex="1"><?php _e('previous', 'edd_ap'); ?></a></li>
					<li><a href="javascript:;" class="jp-play" tabindex="1"><?php _e('play', 'edd_ap'); ?></a></li>
					<li><a href="javascript:;" class="jp-pause" tabindex="1"><?php _e('pause', 'edd_ap'); ?></a></li>
					<li><a href="javascript:;" class="jp-next" tabindex="1"><?php _e('next', 'edd_ap'); ?></a></li>
					<li><a href="javascript:;" class="jp-stop" tabindex="1"><?php _e('stop', 'edd_ap'); ?></a></li>
					<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute"><?php _e('mute', 'edd_ap'); ?></a></li>
					<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute"><?php _e('unmute', 'edd_ap'); ?></a></li>
					<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume"><?php _e('max volume', 'edd_ap'); ?></a></li>
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
					<li><a href="javascript:;" class="jp-shuffle" tabindex="1" title="shuffle"><?php _e('shuffle', 'edd_ap'); ?></a></li>
					<li><a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="shuffle off"><?php _e('shuffle off', 'edd_ap'); ?></a></li>
					<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat"><?php _e('repeat', 'edd_ap'); ?></a></li>
					<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off"><?php _e('repeat off', 'edd_ap'); ?></a></li>
				</ul>
			</div>
			<div class="jp-playlist">
				<ul>
					<li></li>
				</ul>
			</div>
			<div class="jp-no-solution">
				<span><?php _e('Update Required', 'edd_ap'); ?></span>
				<?php _e('To play the media you will need to either update your browser to a recent version or update your', 'edd_ap'); ?> <a href="http://get.adobe.com/flashplayer/" target="_blank"><?php _e('Flash plugin', 'edd_ap'); ?></a>.
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function edd_ap_get_playlist_script($preview_files, $post_id) {
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
				foreach($preview_files as $file) {
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
	if(wp_verify_nonce( $_POST['nonce'], 'edd_audio_nonce')) {
		$edd_preview_files = $_POST['edd_preview_files'];
		update_post_meta( $_POST['post_id'], 'edd_preview_files', $edd_preview_files);
	}
	die();
}
add_action('wp_ajax_edd_update_audio_files_order', 'edd_ap_update_file_order');

// Begin FES integration
// add button to the post button listing
add_action('fes_custom_post_button', 'edd_ap_custom_post_button');
function edd_ap_custom_post_button( $title ){
	echo  '<button class="fes-button button" data-name="edd_ap" data-type="action" title="' . $title . '">'. __('Audio Player', 'edd_ap') . '</button>';
}

add_action( 'fes_admin_field_edd_ap', 'fes_admin_field_save', 10, 3);
function fes_admin_field_save( $field_id, $label = "", $values = array() ){
		if( !isset( $values['label'] ) ){
			$values['label'] = __('Audio Player', 'edd_ap');
		}

		$values['no_css'] = true;
		$values['is_meta'] = true;
		$values['name'] = 'edd_ap';
        ?>
        <li class="edd_ap">
            <?php FES_Formbuilder_Templates::legend( $values['label'] ); ?>
            <?php FES_Formbuilder_Templates::hidden_field( "[$field_id][input_type]", 'edd_ap' ); ?>
            <?php FES_Formbuilder_Templates::hidden_field( "[$field_id][template]", 'edd_ap' ); ?>

            <div class="fes-form-holder">
                <?php FES_Formbuilder_Templates::common( $field_id, 'edd_ap', false, $values ); ?>
            </div> <!-- .fes-form-holder -->
        </li>
        <?php
}

add_filter('fes_formbuilder_custom_field', 'edd_ap_formbuilder_is_custom_field', 10, 2);

function edd_ap_formbuilder_is_custom_field( $bool, $template_field ){
	if ( $bool ){
		return $bool;
	}
	else if ( isset( $template_field['template'] ) && $template_field['template'] == 'edd_ap' ){
		return true;
	}
	else{
		return $bool;
	}
}

add_action( 'fes_submission_form_save_custom_fields', 'edd_ap_save_custom_fields' );
function edd_ap_save_custom_fields( $post_id ){
	if ( isset( $_POST ['edd_ap'] ) ){
		$files = $_POST ['edd_ap'];
		$names = isset( $_POST['edd_ap']['name']) ? $_POST['edd_ap']['name'] : array();
		unset($files['name']);
		$pairs = array();
		$counter = 0;
		foreach( $files as $file => $url ){
			$pairs[$counter]['file'] = $url;
			$pairs[$counter]['name'] = isset( $names[$counter] ) ? $names[$counter] : "";
			$counter++;
		}
		if ( count( $pairs) > 0 ){
			update_post_meta( $post_id, 'edd_preview_files', $pairs );
			update_post_meta( $post_id, '_edd_show_audio_player', true);
			$style = apply_filters( 'edd_ap_default_player', 'midnight_black');
			update_post_meta( $post_id, '_edd_ap_theme', $style );
		}
	}
}

add_action('fes_render_field_edd_ap', 'edd_ap_file_upload', 10, 3 );
function edd_ap_file_upload( $attr, $post_id, $type ) {
		$empty_arr = array();
		$empty_arr[0]['file'] = '';
		$empty_arr[0]['name'] = '';
		$files = $post_id != false ? get_post_meta( $post_id, 'edd_preview_files', true ) : $empty_arr;
?>
        <div class="fes-fields">
			<table class="<?php echo sanitize_key($attr['name']); ?>">
				<thead>
					<tr>
						<td class="fes-edd-ap">
							<?php _e( 'Audio File Name', 'edd_ap' ); ?>
						</td>
						<td class="fes-file-column" colspan="2"><?php _e( 'File URL', 'edd_fes' ); ?></td>
						<td class="fes-remove-column">&nbsp;</td>
					</tr>
				</thead>
				<tbody class="fes-variations-list-<?php echo sanitize_key($attr['name']); ?>">
			<?php
          	
			foreach ( $files as $key => $val ) {
				$name = isset( $val['name'] ) ? $val['name'] : '';
				$url =  isset( $val['file'] ) ? $val['file'] : '';
				$index = $key;
				?>
				<tr class="fes-single-variation">
					<td class="fes-name-row">
						<input type="text" class="fes-file-name" name="<?php echo $attr['name']; ?>[name][<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $name ); ?>" />
					</td>
					<td class="fes-url-row">
						<?php printf( '<span class="fes-file-validation" data-required="%s" data-type="file"></span>', $attr['required'] ); ?>
						<input type="text" class="fes-file-value" placeholder="<?php _e( "http://", 'edd_fes' ); ?>" name="<?php echo $attr['name']; ?>[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $url ); ?>" />
					</td>
					<td class="fes-url-choose-row" width="1%">
						<a href="#" class="btn btn-sm btn-default upload_file_button" data-choose="<?php _e( 'Choose file', 'edd_fes' ); ?>" data-update="<?php _e( 'Insert file URL', 'edd_fes' ); ?>">
						<?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'edd_fes' ) ); ?></a>
					</td>
					<td width="1%" class="fes-delete-row">
						<a href="#" class="btn btn-sm btn-danger delete">
						<?php _e( 'x', 'edd_fes' ); ?></a>
					</td>
				</tr>
				<?php
			}
			?>
					<tr class="add_new" style="display:none !important;" id="<?php echo sanitize_key($attr['name']); ?>"></tr>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="5">
							<a href="#" class="insert-file-row" id="<?php echo sanitize_key($attr['name']); ?>"><?php _e( 'Add File', 'edd_fes' ); ?></a>
						</th>
					</tr>
				</tfoot>
		</table>
       </div> <!-- .fes-fields -->
        <?php
	}