<?php

/**
 * The slide admin-specific functionality of the plugin.
 *
 * @since		1.0.0
 * @since		1.3.2	Refactored class from object to static methods.
 *
 * @package		Foyer
 * @subpackage	Foyer/admin
 * @author		Menno Luitjes <menno@mennoluitjes.nl>
 */
class Foyer_Admin_Slide {

	/**
	 * Adds a Slide Format column to the Slides admin table, just after the title column.
	 *
	 * @since	1.0.0
	 * @since	1.3.2	Changed method to static.
	 *
	 * @param 	array	$columns	The current columns.
	 * @return	array				The new columns.
	 */
	static function add_slide_format_column( $columns ) {
		$new_columns = array();

		foreach( $columns as $key => $title ) {
			$new_columns[$key] = $title;

			if ( 'title' == $key ) {
				// Add slides count column after the title column
				$new_columns['slide_format'] = __( 'Slide format', 'foyer' );
			}
		}
		return $new_columns;
	}

	/**
	 * Adds the channel editor meta box to the display admin page.
	 *
	 * @since	1.0.0
	 * @since	1.3.1	Updated the slide_default_meta_box callback, after method was moved to Foyer_Admin_Slide_Format_Default.
	 * @since	1.3.2	Changed method to static.
	 * @since	1.4.0	Removed value for $meta_box_callback for default slide format, as this value is now defined in the
	 *					slide format properties, same as for the other slide formats.
	 *					Switched to a single metabox holding format and background selects and content.
	 */
	static function add_slide_editor_meta_boxes() {
		add_meta_box(
			'foyer_slide_content',
			__( 'Slide content' , 'foyer' ),
			array( __CLASS__, 'slide_content_meta_box' ),
			Foyer_Slide::post_type_name,
			'normal',
			'low'
		);
	}

	/**
	 * Outputs the Slide Format column.
	 *
	 * @since	1.0.0
	 * @since	1.3.2	Changed method to static.
	 *
	 * @param 	string	$column		The current column that needs output.
	 * @param 	int 	$post_id 	The current display ID.
	 * @return	void
	 */
	static function do_slide_format_column( $column, $post_id ) {
		if ( 'slide_format' == $column ) {

			$slide = new Foyer_Slide( get_the_id() );
			$format_slug = $slide->get_format();
			$format = Foyer_Slides::get_slide_format_by_slug( $format_slug );
			echo $format['title'];
	    }
	}

	/**
	 * Localizes the JavaScript for the slide admin area.
	 *
	 * @since	1.0.0
	 * @since	1.0.1	Escaped the output.
	 * @since	1.1.3	Fixed a Javascript issue where adding an image to a slide was only possible when
	 *					the image was already in the media library. Removed 'photo' default as it is no
	 *					longer needed by our Javascript.
	 * @since	1.3.1	Changed handle of script to {plugin_name}-admin.
	 * @since	1.3.2	Changed method to static.
	 * @since	1.4.0	Renamed slide_format_default to slide_image_defaults.
	 *					Added the slide formats backgrounds.
	 *
	 */
	static function localize_scripts() {
		$slide_image_defaults = array(
			'text_select_photo' => esc_html__( 'Select an image', 'foyer' ),
			'text_use_photo' => esc_html__( 'Use this image', 'foyer' ),
		);
		wp_localize_script( Foyer::get_plugin_name() . '-admin', 'foyer_slide_image_defaults', $slide_image_defaults );

		$slide_formats_backgrounds = Foyer_Slides::get_slide_formats_backgrounds();
		wp_localize_script( Foyer::get_plugin_name() . '-admin', 'foyer_slide_formats_backgrounds', $slide_formats_backgrounds );
	}

	/**
	 * Removes the sample permalink from the Slide edit screen.
	 *
	 * @since	1.0.0
	 * @since	1.3.2	Changed method to static.
	 *
	 * @param 	string	$sample_permalink
	 * @return 	string
	 */
	static function remove_sample_permalink( $sample_permalink ) {

		$screen = get_current_screen();

		// Bail if not on Slide edit screen.
		if ( empty( $screen ) || Foyer_Slide::post_type_name != $screen->post_type ) {
			return $sample_permalink;
		}

		return '';
	}

	/**
	 * Saves all custom fields for a display.
	 *
	 * Triggered when a display is submitted from the display admin form.
	 *
	 * @since	1.0.0
	 * @since	1.3.1	Updated the save_slide_default call, after method was moved to Foyer_Admin_Slide_Format_Default.
	 * @since	1.3.2	Changed method to static.
	 * @since	1.4.0	Removed call_user_func_array() for default slide format, as the callback is now defined in the
	 *					slide format properties, same as for the other slide formats.
	 *					Saves the slide background value, and invokes saving the background's fields through a callback.
	 *
	 * @param 	int		$post_id	The channel id.
	 * @return	void
	 */
	static function save_slide( $post_id ) {

		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		/* Check if our nonce is set */
		if ( ! isset( $_POST[Foyer_Slide::post_type_name.'_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST[Foyer_Slide::post_type_name.'_nonce'];

		/* Verify that the nonce is valid */
		if ( ! wp_verify_nonce( $nonce, Foyer_Slide::post_type_name ) ) {
			return $post_id;
		}

		/* If this is an autosave, our form has not been submitted, so we don't want to do anything */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		/* Check the user's permissions */
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		/* Slide format */
		$slide_format_slug = sanitize_title( $_POST['slide_format'] );
		$slide_format = Foyer_Slides::get_slide_format_by_slug( $slide_format_slug );

		if ( ! empty( $slide_format ) ) {
			update_post_meta( $post_id, 'slide_format', $slide_format_slug );
		}

		if ( ! empty( $slide_format['save_post'] ) ) {
			call_user_func_array( $slide_format['save_post'], array( $post_id ) );
		}

		/* Slide background */
		$slide_background_slug = sanitize_title( $_POST['slide_background'] );
		$slide_background = Foyer_Slides::get_slide_background_by_slug( $slide_background_slug );

		if ( ! empty( $slide_background ) ) {
			update_post_meta( $post_id, 'slide_background', $slide_background_slug );
		}

		if ( ! empty( $slide_background['save_post'] ) ) {
			call_user_func_array( $slide_background['save_post'], array( $post_id ) );
		}
	}

	/**
	 * Outputs the content of the meta box holding all slide background choices.
	 *
	 * @since	1.4.0
	 *
	 * @param	WP_Post		$post	The post object of the current slide.
	 * @return	void
	 */
	static function slide_background_meta_box( $post ) {

		wp_nonce_field( Foyer_Slide::post_type_name, Foyer_Slide::post_type_name.'_nonce' );

		$slide = new Foyer_Slide( $post->ID );

		?><input type="hidden" id="foyer_slide_editor_<?php echo Foyer_Slide::post_type_name; ?>"
			name="foyer_slide_editor_<?php echo Foyer_Slide::post_type_name; ?>" value="<?php echo intval( $post->ID ); ?>"><?php

		foreach( Foyer_Slides::get_slide_backgrounds() as $slide_background_key => $slide_background_data ) {
			?><label>
				<input type="radio" value="<?php echo esc_attr( $slide_background_key ); ?>" name="slide_background" <?php checked( $slide->get_background(), $slide_background_key, true ); ?> />
				<span><?php echo esc_html( $slide_background_data['title'] ); ?></span>
			</label><?php
		}
	}

	/**
	 * Outputs the content of the meta box holding all slide content.
	 *
	 * @since	1.0.0
	 * @since	1.0.1	Escaped and sanitized the output.
	 * @since	1.3.2	Changed method to static.
	 * @since	1.4.0	Renamed from slide_format_meta_box() to slide_content_meta_box().
	 *					Rebuild into a single metabox holding format and background selects and content.
	 *					Displayed a slide format description and slide background description and a message
	 *					'No settings.' when both description and meta_box are empty.
	 *
	 * @param	WP_Post		$post	The post object of the current slide.
	 * @return	void
	 */
	static function slide_content_meta_box( $post ) {

		wp_nonce_field( Foyer_Slide::post_type_name, Foyer_Slide::post_type_name.'_nonce' );

		$slide = new Foyer_Slide( $post->ID );

		?><div class="foyer_slide_select_format_background">

			<input type="hidden" id="foyer_slide_editor_<?php echo Foyer_Slide::post_type_name; ?>"
				name="foyer_slide_editor_<?php echo Foyer_Slide::post_type_name; ?>" value="<?php echo intval( $post->ID ); ?>">

			<div class="foyer_slide_select_format">
				<p><?php _e( 'Format', 'foyer' ); ?></p>
				<select name="slide_format">
					<?php foreach( Foyer_Slides::get_slide_formats() as $slide_format_key => $slide_format_data ) { ?>
						<option value="<?php echo esc_attr( $slide_format_key ); ?>" <?php selected( $slide->get_format(), $slide_format_key, true ); ?>>
							<?php echo esc_html( $slide_format_data['title'] ); ?>
						</option>
					<?php } ?>
				</select>
			</div>

			<div class="foyer_slide_select_background">
				<p><?php _e( 'Background', 'foyer' ); ?></p>
				<select name="slide_background">
					<?php foreach( Foyer_Slides::get_slide_backgrounds() as $slide_background_key => $slide_background_data ) { ?>
						<option value="<?php echo esc_attr( $slide_background_key ); ?>" <?php selected( $slide->get_background(), $slide_background_key, true ); ?>>
							<?php echo esc_html( $slide_background_data['title'] ); ?>
						</option>
					<?php } ?>
				</select>
			</div>
		</div>


		<div class="foyer_slide_formats"><?php

			foreach( Foyer_Slides::get_slide_formats() as $slide_format_key => $slide_format_data ) {

				?><div id="<?php echo 'foyer_slide_format_' . $slide_format_key; ?>">
					<h3><?php echo sprintf( __( 'Slide format: %s ', 'foyer'), $slide_format_data['title'] ); ?></h3>

					<?php if ( ! empty( $slide_format_data['description'] ) ) { ?>
						<p class="foyer_slide_admin_description"><?php echo esc_html( $slide_format_data['description'] ); ?></p>
					<?php } ?>

					<?php if ( ! empty( $slide_format_data['meta_box'] ) ) { ?>
						<?php call_user_func_array( $slide_format_data['meta_box'], array( get_post( $slide->ID ) ) ); ?>
					<?php } ?>

					<?php if ( empty( $slide_format_data['description'] ) && empty( $slide_format_data['meta_box'] ) ) { ?>
						<p class="foyer_slide_admin_description"><?php _e( 'No settings.', 'foyer' ); ?></p>
					<?php } ?>

				</div><?php
			} ?>

		</div>

		<div class="foyer_slide_backgrounds"><?php

			foreach( Foyer_Slides::get_slide_backgrounds() as $slide_background_key => $slide_background_data ) {

				?><div id="<?php echo 'foyer_slide_background_' . $slide_background_key; ?>">
					<h3><?php echo sprintf( __( 'Slide background: %s ', 'foyer'), $slide_background_data['title'] ); ?></h3>

					<?php if ( ! empty( $slide_background_data['description'] ) ) { ?>
						<p class="foyer_slide_admin_description"><?php echo esc_html( $slide_background_data['description'] ); ?></p>
					<?php } ?>

					<?php if ( ! empty( $slide_background_data['meta_box'] ) ) { ?>
						<?php call_user_func_array( $slide_background_data['meta_box'], array( get_post( $slide->ID ) ) ); ?>
					<?php } ?>

					<?php if ( empty( $slide_background_data['description'] ) && empty( $slide_background_data['meta_box'] ) ) { ?>
						<p class="foyer_slide_admin_description"><?php _e( 'No settings.', 'foyer' ); ?></p>
					<?php } ?>

				</div><?php
			} ?>

		</div><?php
	}
}
