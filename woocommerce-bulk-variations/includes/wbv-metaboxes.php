<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WidCol Metabox class
 *
 * This class is able to render a custom metabox with a custom specification of fields.
 *
 * @class WidColMetabox
 */
if (!class_exists("WBVMetabox")) {
    class WBVMetabox
    {
        private string $meta_box_name;
        private array $meta_fields;
        private string $post_type;
        private string $meta_box_title;
        private string $context;

        /**
         * WBVMetabox constructor.
         *
         * @param string $post_type : the post type to add this custom meta box to.
         * @param array $meta_fields: array of (label: Label of meta box, desc: Description of meta box, id: ID of meta box
         * (used in database), type: Type of meta box (for input field)).
         * @param string $meta_box_name: meta box name
         */
        public function __construct(string $meta_box_name, array $meta_fields, string $post_type, string $meta_box_title, string $context = 'advanced')
        {
            $this->meta_box_name = $meta_box_name;
            $this->meta_fields = $meta_fields;
            $this->post_type = $post_type;
            $this->meta_box_title = $meta_box_title;
            $this->context = $context;
            $this->actions_and_filters();
        }

        /**
         * Actions and filters.
         */
        public function actions_and_filters()
        {
            add_action('add_meta_boxes_' . $this->post_type, array($this, 'register_meta_box'));
            add_action('save_post', array($this, 'save_meta_box'));
        }

        /**
         * Register meta box.
         */
        public function register_meta_box()
        {
            add_meta_box(
                $this->meta_box_name,
                $this->meta_box_title,
                array($this, 'show_custom_meta_box'),
                $this->post_type,
                $this->context
            );
        }

        /**
         * Get the name of the nonce corresponding to this meta box.
         *
         * @return string name of the nonce corresponding to this meta box
         */
        private function get_nonce_name(): string
        {
            return $this->meta_box_name . '_nonce';
        }

        /**
         * Creates HTML for the custom meta box
         */
        public function show_custom_meta_box()
        {
            global $post;
            // Use nonce for verification
            wp_nonce_field(basename(__FILE__), $this->get_nonce_name()); ?>
                <table class="form-table">
                    <?php foreach ($this->meta_fields as $field) : ?>
                        <?php $meta = get_post_meta($post->ID, $field['id'], true); ?>
                        <tr>
                            <th><label for="<?php echo $field['id']; ?>"><?php echo $field['label']; ?></label></th>
                            <td>
                                <?php
                                switch ($field['type']) :
                                    case 'number':
                                        ?>
                                                <input type="number"
                                                   <?php if (array_key_exists('required', $field) && $field['required']) :
                                                        ?>required<?php
                                                   endif; ?>
                                                   <?php if (array_key_exists('any', $field) && $field['any']) :
                                                        ?>step="any"<?php
                                                   endif; ?>
                                                   <?php if (array_key_exists('min', $field)) :
                                                        ?>min=<?php echo $field['min']; ?><?php
                                                   endif; ?>
                                                   <?php if (array_key_exists('max', $field)) :
                                                        ?>max=<?php echo $field['max']; ?><?php
                                                   endif; ?>
                                                       name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" value="<?php echo $meta; ?>" />
                                                <br>
                                                <span class="description"><?php echo $field['desc']; ?></span>
                                            <?php
                                        break;
            case 'text':
                                        ?>
                                            <input type="text"
                                                   <?php if (array_key_exists('required', $field) && $field['required']) :
                                                        ?>required<?php
                                                   endif; ?>
                                                   name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" value="<?php echo $meta; ?>" />
                                            <br>
                                            <span class="description"><?php echo $field['desc']; ?></span>
                                                                    <?php
                                        break;
            case 'textarea':
                                        ?>
                                            <textarea style="width: 100%; min-height: 200px;"
                                                    <?php if (array_key_exists('required', $field) && $field['required']) :
                                                        ?>required<?php
                                                    endif; ?>
                                                   name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>"><?php echo $meta; ?></textarea>
                                            <br>
                                            <span class="description"><?php echo $field['desc']; ?></span>
                                                                    <?php
                                        break;
            case 'checkbox':
                                        ?>
                                            <input type="checkbox"
                                                   <?php if (array_key_exists('required', $field) && $field['required']) :
                                                        ?>required<?php
                                                   endif; ?>
                                                   name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" <?php if ($meta) :
                                                        echo "checked=checked";
            endif; ?> />
                                            <br>
                                            <span class="description"><?php echo $field['desc']; ?></span>
                                            <?php
                                        break;
            case 'select':
                if (function_exists($field['options'])) {
                    $field['options'] = $field['options']($post);
                } ?>
                                            <select name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>">
                                                <?php foreach ($field['options'] as $option) : ?>
                                                    <option <?php if ($meta == $option['value']) :
                                                        echo 'selected="selected"';
            endif ?> value="<?php echo $option['value']; ?>">
                                                        <?php echo $option['label']; ?>
                                                    </option>
                                                <?php endforeach ?>
                                            </select>
                                            <br>
                                            <span class="description"><?php echo $field['desc']; ?></span>
                                            <?php
                                        break;
            endswitch; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php
        }

        /**
         * Saves custom meta tag data
         * @param int $post_id : the post id to save the data for
         * @return int: post_id
         */
        public function save_meta_box(int $post_id): int
        {
            if (!wp_verify_nonce($_POST[$this->get_nonce_name()], basename(__FILE__))) {
                return $post_id;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            if ($_POST['post_type'] == $this->post_type && current_user_can('edit_post')) {
                foreach ($this->meta_fields as $field) {
                    $old = get_post_meta($post_id, $field['id'], true);
                    $new = $_POST[$field['id']];
                    if ($new && $new != $old) {
                        update_post_meta($post_id, $field['id'], $new);
                    } elseif (!$new && $old) {
                        delete_post_meta($post_id, $field['id'], $old);
                    }
                }
            }
            return $post_id;
        }
    }
}
