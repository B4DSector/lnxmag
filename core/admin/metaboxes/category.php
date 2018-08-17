<?php 

/**
 * Save category meta 
 * 
 * Callback function to save category meta data
 * 
 * @since  1.0
 */

add_action( 'edited_category', 'gridlove_save_category_meta_fields', 10, 2 );
add_action( 'create_category', 'gridlove_save_category_meta_fields', 10, 2 );

if ( !function_exists( 'gridlove_save_category_meta_fields' ) ) :
	function gridlove_save_category_meta_fields( $term_id ) {

		if ( isset( $_POST['gridlove'] ) ) {

			$meta = array();

			if( isset( $_POST['gridlove']['color'] ) ) { 
				if( $_POST['gridlove']['color']['type'] != 'inherit' ){
					$meta['color']['type'] = $_POST['gridlove']['color']['type'];
					$meta['color']['value'] = $_POST['gridlove']['color']['value'];
				}

				gridlove_update_cat_colors( $term_id, $_POST['gridlove']['color']['value'], $_POST['gridlove']['color']['type'] );
			}

			
			if( !empty( $meta) ){
				update_term_meta( $term_id, '_gridlove_meta', $meta);
			} else {
				delete_term_meta( $term_id, '_gridlove_meta');
			}
			
		}

	}
endif;


/**
 * Add category meta 
 * 
 * Callback function to load category meta fields on "new category" screen
 * 
 * @since  1.0
 */

add_action( 'category_add_form_fields', 'gridlove_category_add_meta_fields', 10, 2 );

if ( !function_exists( 'gridlove_category_add_meta_fields' ) ) :
	function gridlove_category_add_meta_fields() {
		$meta = gridlove_get_category_meta();
?>
	 
	<div class="form-field">
		<label><?php esc_html_e( 'Color', 'gridlove' ); ?></label>
		<label><input type="radio" name="gridlove[color][type]" value="inherit" class="color-type" <?php checked( $meta['color']['type'], 'inherit' );?>> <?php esc_html_e( 'Inherit from accent color', 'gridlove' ); ?></label>
		<label><input type="radio" name="gridlove[color][type]" value="custom" class="color-type" <?php checked( $meta['color']['type'], 'custom' );?>> <?php esc_html_e( 'Set custom color', 'gridlove' ); ?></label>
		<div id="gridlove-color-wrap">
			<p>
			   <input name="gridlove[color][value]" type="text" class="gridlove-colorpicker" value="<?php echo esc_attr($meta['color']['value']); ?>" data-default-color="<?php echo esc_attr($meta['color']['value']); ?>"/>
			</p>

			<?php $recent_colors = get_option( 'gridlove_recent_cat_colors' ); ?>
			<?php if(!empty($recent_colors)) : ?>
				<p class="description"><?php esc_html_e( 'Recently used', 'gridlove' ); ?>:<br/>
					<?php foreach($recent_colors as $color) : ?>
						<a href="javascript:void(0);" style="background: <?php echo esc_attr($color); ?>;" class="gridlove-rec-color" data-color="<?php echo esc_attr($color); ?>"></a>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
		</div>
		<br/>	
	</div>

	<?php
	}
endif;




/**
 * Edit category meta 
 * 
 * Callback function to load category meta fields on edit screen
 * 
 * @since  1.0
 */

add_action( 'category_edit_form_fields', 'gridlove_category_edit_meta_fields', 10, 2 );

if ( !function_exists( 'gridlove_category_edit_meta_fields' ) ) :
	function gridlove_category_edit_meta_fields( $term ) {
		$meta = gridlove_get_category_meta( $term->term_id );
?>
	  
	 <tr class="form-field">
		<th scope="row" valign="top"><label><?php esc_html_e( 'Color', 'gridlove' ); ?></label></th>
			<td>
				<label><input type="radio" name="gridlove[color][type]" value="inherit" class="color-type" <?php checked( $meta['color']['type'], 'inherit' );?>> <?php esc_html_e( 'Inherit from accent color', 'gridlove' ); ?></label><br/>
				<label><input type="radio" name="gridlove[color][type]" value="custom" class="color-type" <?php checked( $meta['color']['type'], 'custom' );?>> <?php esc_html_e( 'Set custom color', 'gridlove' ); ?></label>
				<div id="gridlove-color-wrap">
					<p>
					   <input name="gridlove[color][value]" type="text" class="gridlove-colorpicker" value="<?php echo esc_attr($meta['color']['value']); ?>" data-default-color="<?php echo esc_attr($meta['color']['value']); ?>"/>
					</p>

					<?php $recent_colors = get_option( 'gridlove_recent_cat_colors' ); ?>
					<?php if(!empty($recent_colors)) : ?>
						<p class="description"><?php esc_html_e( 'Recently used', 'gridlove' ); ?>:<br/>
							<?php foreach($recent_colors as $color) : ?>
								<a href="javascript:void(0);" style="background: <?php echo esc_attr($color); ?>;" class="gridlove-rec-color" data-color="<?php echo esc_attr($color); ?>"></a>
							<?php endforeach; ?>
						</p>
					<?php endif; ?>
				</div>
			</td>
		</tr>

	<?php
	}
endif;


/**
 * Delete category meta 
 * 
 * Delete our custom category meta from database on category deletion
 * 
 * @return  void 
 * @since  1.0
 */

add_action( 'delete_category', 'gridlove_delete_category_meta' );

if ( !function_exists( 'gridlove_delete_category_meta' ) ):
	function gridlove_delete_category_meta( $term_id ) {
		
		//Check for category colors deletion
		$colors = get_option( 'gridlove_cat_colors' );

		if ( !empty($colors) && array_key_exists( $term_id, $colors ) ) {
				unset( $colors[$term_id] );
				update_option( 'gridlove_cat_colors', $colors );
		}
	}	
endif;


/**
 * Update category colors 
 * 
 * Function checks for category color and updates two fields
 * in options table. One for list of category colors and second
 * for recently picked colors.
 * 
 * @param   int $cat_id
 * @param   string $color Hexadecimal color value
 * @param   string $type inherit|custom
 * @return  void 
 * @since  1.0
 */
if ( !function_exists( 'gridlove_update_cat_colors' ) ):
	function gridlove_update_cat_colors( $cat_id, $color, $type ) {

		/* Update category color */
		
		$colors = get_option( 'gridlove_cat_colors' );

		if(empty($colors)) {
			$colors = array();
		}

		if ( array_key_exists( $cat_id, $colors ) ) {

			if ( $type == 'inherit' ) {
				unset( $colors[$cat_id] );
			} elseif ( $colors[$cat_id] != $color ) {
				$colors[$cat_id] = $color;
			}

		} else {

			if ( $type != 'inherit' ) {
				$colors[$cat_id] = $color;
			}
		}

		update_option( 'gridlove_cat_colors', $colors );


		/* Store recent category colors */
		if ( $type != 'inherit' ) {

			$num_col = 10;
			$current = get_option( 'gridlove_recent_cat_colors' );
			if(empty($current)) {
				$current = array();
			}
			$update = false;

			if ( !in_array( $color, $current ) ) {
				$current[] = $color;
				if ( count( $current ) > $num_col ) {
					$current = array_slice( $current, ( count( $current ) - $num_col ), ( count( $current ) - 1 ) );
				}
				$update = true;
			}

			if ( $update ) {
				update_option( 'gridlove_recent_cat_colors', $current );
			}
		
		}

	}
endif;

?>