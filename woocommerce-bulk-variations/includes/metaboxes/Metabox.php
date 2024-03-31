<?php
/**
 * Metabox class
 *
 * @package woocommerce-bulk-variations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Metabox' ) ) {
	/**
	 * Metabox class
	 *
	 * This class is able to render a custom metabox with a custom specification of fields.
	 *
	 * @class Metabox
	 */
	class Metabox {


		/**
		 * Name of the meta box
		 *
		 * @var string
		 */
		private $meta_box_name;

		/**
		 * Meta fields of this meta box.
		 *
		 * @var array
		 */
		private $meta_fields;

		/**
		 * Post type meta box should be registered on
		 *
		 * @var string
		 */
		private $post_type;

		/**
		 * Title of the meta box
		 *
		 * @var string
		 */
		private $meta_box_title;

		/**
		 * Metabox constructor.
		 *
		 * @param string $meta_box_name the post type to add this custom meta box to.
		 * @param array  $meta_fields array of (label: Label of meta box, desc: Description of meta box, id: ID of meta box
		 *   (used in database), type: Type of meta box (for input field)).
		 * @param string $post_type post type to register meta box on.
		 * @param string $meta_box_title title of the meta box.
		 */
		public function __construct( string $meta_box_name, array $meta_fields, string $post_type, string $meta_box_title ) {
			$this->meta_box_name = $meta_box_name;
			$this->meta_fields = $meta_fields;
			$this->post_type = $post_type;
			$this->meta_box_title = $meta_box_title;
			$this->actions_and_filters();
		}

		/**
		 * Actions and filters.
		 */
		public function actions_and_filters() {
			add_action( 'add_meta_boxes_' . $this->post_type, array( $this, 'register_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_meta_box' ) );
		}

		/**
		 * Register meta box.
		 */
		public function register_meta_box() {
			add_meta_box(
				$this->meta_box_name,
				$this->meta_box_title,
				array( $this, 'show_custom_meta_box' ),
				$this->post_type
			);
		}

		/**
		 * Get the name of the nonce corresponding to this meta box.
		 *
		 * @return string name of the nonce corresponding to this meta box
		 */
		private function get_nonce_name(): string {
			return $this->meta_box_name . '_nonce';
		}

		/**
		 * Get the name of the action corresponding to the nonce.
		 *
		 * @return string Name of the action used for generating and verifying the nonce.
		 */
		private function get_nonce_action(): string {
			return $this->meta_box_name . '_save_meta_box';
		}

		/**
		 * Creates HTML for the custom meta box
		 */
		public function show_custom_meta_box() {
			global $post;
			wp_nonce_field( $this->get_nonce_action(), $this->get_nonce_name() ); ?>
			<table class="form-table">
				<?php foreach ( $this->meta_fields as $field ) : ?>
					<?php $meta = get_post_meta( $post->ID, $field['id'], true ); ?>
					<tr>
						<th>
							<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
						</th>
						<td>
							<?php
							switch ( $field['type'] ) :
								case 'number':
									?>
									<input type="number"
										<?php
										if ( array_key_exists( 'required', $field ) && $field['required'] ) :
											?>
											required
											<?php
										endif;
										?>
										<?php
										if ( array_key_exists( 'step', $field ) ) :
											?>
											step="<?php echo esc_attr( $field['step'] ); ?>"
											<?php
										endif;
										?>
										<?php
										if ( array_key_exists( 'min', $field ) ) :
											?>
										   min=<?php echo esc_attr( $field['min'] ); ?>
											<?php
										   endif;
										?>
										   <?php
											if ( array_key_exists( 'max', $field ) ) :
												?>
										   max=<?php echo esc_attr( $field['max'] ); ?>
												<?php
									endif;
											?>
										   name="<?php echo esc_attr( $field['id'] ); ?>"
										   id="<?php echo esc_attr( $field['id'] ); ?>"
										   value="<?php echo esc_attr( $meta ); ?>"/>
									<br>
									<span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
									<?php
									break;
								case 'text':
									?>
									<input type="text"
										<?php
										if ( array_key_exists( 'required', $field ) && $field['required'] ) :
											?>
											required
											<?php
										endif;
										?>
										   name="<?php echo esc_attr( $field['id'] ); ?>"
										   id="<?php echo esc_attr( $field['id'] ); ?>"
										   value="<?php echo esc_attr( $meta ); ?>"/>
									<br>
									<span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
									<?php
									break;
								case 'textarea':
									?>
									<textarea style="width: 100%; min-height: 200px;"
													<?php
													if ( array_key_exists( 'required', $field ) && $field['required'] ) :
														?>
														required
														<?php
													endif;
													?>
												   name="<?php echo esc_attr( $field['id'] ); ?>"
											  id="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_textarea( $meta ); ?></textarea>
									<br>
									<span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
									<?php
									break;
								case 'checkbox':
									?>
									<input type="checkbox"
										<?php
										if ( array_key_exists( 'required', $field ) && $field['required'] ) :
											?>
											required
											<?php
										endif;
										?>
										   name="<?php echo esc_attr( $field['id'] ); ?>"
										   id="<?php echo esc_attr( $field['id'] ); ?>"
										<?php
										if ( $meta ) :
											echo 'checked=checked';
										endif;
										?>
									/>
									<br>
									<span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
									<?php
									break;
								case 'select':
									if ( 'string' === gettype( $field['options'] ) ) {
										$field['options'] = $field['options']( $post );
									}
									?>
									<select name="<?php echo esc_attr( $field['id'] ); ?>"
											id="<?php echo esc_attr( $field['id'] ); ?>">
										<?php foreach ( $field['options'] as $option ) : ?>
											<option
												<?php
												if ( $meta == $option['value'] ) :
													echo 'selected="selected"';
												endif
												?>
													value="<?php echo esc_attr( $option['value'] ); ?>">
												<?php echo esc_html( $option['label'] ); ?>
											</option>
										<?php endforeach ?>
									</select>
									<br>
									<span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
									<?php
									break;
							endswitch;
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php
		}

		/**
		 * Sanitize a field.
		 *
		 * @param array $field The field to sanitize.
		 * @param mixed $value The value to sanitize.
		 * @return false|float|string The sanitized value for the field.
		 */
		protected function sanitize_field( array $field, $value ) {
			switch ( $field['type'] ) {
				case 'number':
					return floatval( $value );
				case 'text':
				case 'textarea':
				case 'checkbox':
				case 'select':
					return sanitize_text_field( $value );
			}
			return false;
		}

		/**
		 * Saves custom meta tag data
		 *
		 * @param int $post_id the post id to save the data for.
		 * @return int: post_id
		 */
		public function save_meta_box( int $post_id ): int {
			if ( ! isset( $_POST[ $this->get_nonce_name() ] ) || ! isset( $_POST['post_type'] ) ) {
				return $post_id;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $this->get_nonce_name() ] ) ), $this->get_nonce_action() ) || ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			$post_type = sanitize_text_field( wp_unslash( $_POST['post_type'] ) );

			if ( $this->post_type !== $post_type ) {
				return $post_id;
			}

			foreach ( $this->meta_fields as $field ) {
				$old = get_post_meta( $post_id, $field['id'], true );
				$new = isset( $_POST[ $field['id'] ] ) ? $this->sanitize_field( $field, wp_unslash( $_POST[ $field['id'] ] ) ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- We don't know what the input is here.
				$value_updated = $new != $old;
				if ( $new ) {
					if ( $value_updated ) {
						update_post_meta( $post_id, $field['id'], $new );
					}
                    // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found -- Structure of this else is more clear.
				} else {
					// Value has been removed or is not set.
					if ( $field['required'] ) {
						update_post_meta( $post_id, $field['id'], $field['default'] );
					} elseif ( $value_updated ) {
						delete_post_meta( $post_id, $field['id'], $old );
					}
				}
			}
			return $post_id;
		}
	}
}
