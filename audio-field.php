<?php
class FES_Audio_player_Field extends FES_Field {

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = false;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => false,
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'     => false,
			'submission'       => true,
			'vendor-contact'   => false,
			'profile'          => false,
			'login'            => false,
		),
		'position'    => 'extension',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => false,
			'can_add_to_formbuilder'      => true,
		),
		'template' => 'audio_player',
		'title'    => 'Audio Player',
		'phoenix'  => false,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two text fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'audio_player',
		'template'    => 'audio_player',
		'public'      => false,
		'required'    => true,
		'label'       => 'Audio Player',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'single'      => 'no',
	);


	public function set_title() {
		$title = _x( 'Audio Player', 'FES Field title translation', 'edd_ap' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	public function extending_constructor( ) {
		// exclude from submission form in admin
		add_filter( 'fes_templates_to_exclude_render_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
	}

	public function exclude_field( $fields ) {
		array_push( $fields, 'audio_player' );
		return $fields;
	}

	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		return ''; // don't render in the backend
	}

	/** Returns the HTML to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'fes_render_audio_player_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_audio_player_readonly_frontend', $readonly, $user_id, $this->id );

		// this system of letters should just be replaced with booleans. It would make this whole thing way simpler.
		$post_id  = $this->get_save_id();
		$empty_arr = array();
		$empty_arr[0]['file'] = '';
		$empty_arr[0]['name'] = '';

		$files = $post_id != false ? get_post_meta( $post_id, 'edd_preview_files', true ) : $empty_arr;
		$required = $this->required( $readonly );
		
		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output     .= $this->label( $readonly );
		ob_start(); ?>
		<div class="fes-fields">
			<table class="fes-edd-ap">
				<thead>
					<tr>
						<th class="fes-name-column"><?php _e( 'Audio File Name', 'edd_ap' ); ?></th>
						<th class="fes-file-column" colspan="2"><?php _e( 'File URL', 'edd_ap' ); ?></th>
						<?php if ( ! ( $this->characteristics['single'] === 'yes' ) ) { ?>
							<th class="fes-remove-column">&nbsp;</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody  class="fes-variations-list-multiple">
				<?php
				foreach ( $files as $key => $val ) {
					$name = isset( $val['name'] ) ? $val['name'] : '';
					$url =  isset( $val['file'] ) ? $val['file'] : '';
					$index = $key;
					$required = $required ? 'data-required="yes" data-type="multiple"' : '' ?>

					<tr class="fes-single-variation">
						<td class="fes-name-row">
							<input type="text" class="fes-file-name" name="<?php echo $this->name(); ?>[name][<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $name ); ?>" />
						</td>
						<td class="fes-url-row">
							<?php printf( '<span class="fes-file-validation" data-required="%s" data-type="file"></span>', $required ); ?>
							<input type="text" class="fes-file-value" placeholder="<?php _e( "http://", 'edd_ap' ); ?>" name="<?php echo $this->name(); ?>[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $url ); ?>" />
						</td>
						<td class="fes-url-choose-row" width="1%">
							<a href="#" class="btn btn-sm btn-default upload_file_button" data-choose="<?php _e( 'Choose file', 'edd_ap' ); ?>" data-update="<?php _e( 'Insert file URL', 'edd_ap' ); ?>">
							<?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'edd_ap' ) ); ?></a>
						</td>
						<td width="1%" class="fes-delete-row">
							<a href="#" class="btn btn-sm btn-danger delete">
							<?php _e( 'x', 'edd_ap' ); ?></a>
						</td>
					</tr>
				<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="5">
							<?php if ( ! ( $this->characteristics['single'] === 'yes' ) ) { ?>
							<a href="#" class="edd-submit button insert-file-row"><?php _e( 'Add File', 'edd_ap' ); ?></a>
							<?php } ?>
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable          = $this->can_remove_from_formbuilder();
		ob_start(); ?>
		<li class="audio_player">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name, true ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php FES_Formbuilder_Templates::standard( $index, $this );
				$tpl         = '%s[%d][%s]';
				$single_name = sprintf( $tpl, 'fes_input', $index, 'single' );
				$single      = esc_attr( $this->characteristics['single'] );?>

				<div class="fes-form-rows required-field">
					<label><?php _e( 'Single Audio Preview', 'edd_ap' ); ?></label>
					<div class="fes-form-sub-fields">
						<label><input type="radio" name="<?php echo $single_name; ?>" value="yes"<?php checked( $single, 'yes' ); ?>> <?php _e( 'Yes', 'edd_ap' ); ?> </label>
						<label><input type="radio" name="<?php echo $single_name; ?>" value="no"<?php checked( $single, 'no' ); ?>> <?php _e( 'No', 'edd_ap' ); ?> </label>
					</div>
				</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$return_value = false;

		if ( !empty( $values[ $name ] ) ) {
			foreach ( $values[ $name ] as $file => $url ) {
				if ( empty( $values[ $file ]['file']  ) ){
					return __( 'Please enter a valid URL', 'edd_ap' );
				}

				if ( empty( $values[ $file ]['name']  ) ){
					return __( 'Please enter a valid name', 'edd_ap' );
				}
			}
		} else {
			$return_value = __( 'Please fill out this field.', 'edd_ap' );
		}

		return apply_filters( 'fes_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			foreach ( $values[ $name ] as $file => $url ) {
				isset( $values[ $file ]['file'] ) ? filter_var( trim( $values[ $file ]['file'] ), FILTER_SANITIZE_URL ) : ''; 
				isset( $values[ $file ]['name'] ) ? sanitize_text_field( trim( $values[ $file ]['name'] ) ): '';
			}
		}
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}

	public function display_field( $user_id = -2, $single = false ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		$user_id   = apply_filters( 'fes_display_' . $this->template() . '_field_user_id', $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id );
		ob_start(); ?>

			<?php if ( $single ) { ?>
			<table class="fes-display-field-table">
			<?php } ?>

				<tr class="fes-display-field-row <?php echo $this->template(); ?>" id="<?php echo $this->name(); ?>">
					<td class="fes-display-field-label"><?php echo $this->get_label(); ?></td>
					<td class="fes-display-field-values">
						<?php
						echo '';
						?>
					</td>
				</tr>
			<?php if ( $single ) { ?>
			</table>
			<?php } ?>
		<?php
		return ob_get_clean();
	}

	public function formatted_data( $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$user_id   = apply_filters( 'fes_fomatted_' . $this->template() . '_field_user_id', $user_id, $this->id );
		$values     = $this->get_field_value_frontend( $this->save_id, $user_id );
		$output    = '';
		return $output;
	}

	public function save_field_admin( $save_id = -2, $value = '', $user_id = -2 ) {
		// Don't save in the backend
	}

	public function save_field_frontend( $save_id = -2, $value = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $save_id == -2 || $save_id < 1 ) {
			$save_id = $this->save_id;
		}

		$names = isset( $value['name'] ) ? $value['name'] : array();
		unset( $value['name'] );
		$pairs = array();
		$counter = 0;
		foreach ( $value as $file => $url ) {
			$pairs[$counter]['file'] = $url;
			$pairs[$counter]['name'] = isset( $names[$counter] ) ? $names[$counter] : "";
			$counter++;
		}
		if ( count( $pairs ) > 0 ) {
			update_post_meta( $save_id, 'edd_preview_files', $pairs );
			update_post_meta( $save_id, '_edd_show_audio_player', true );
			$style = apply_filters( 'edd_ap_default_player', 'midnight_black' );
			update_post_meta( $save_id, '_edd_ap_theme', $style );
		}
	
	}

	/** Gets field value for admin */
	public function get_field_value_admin( $save_id = -2, $user_id = -2, $public = -2 ) {
		// Don't get field value in the backend
		return false;
	}

	/** Gets field value for frontend */
	public function get_field_value_frontend( $save_id = -2, $user_id = -2, $public = -2 ) {
		// Don't get field value in the frontend
		return false;
	}
}
