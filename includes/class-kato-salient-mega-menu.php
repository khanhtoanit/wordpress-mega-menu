<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Kato_Salient_Mega_Menu {

    const META_ENABLE     = '_kato_enable_srs_mega_menu';
    const META_PREVIEW_ID = '_kato_mega_preview_image_id';
    const META_PREVIEW_T  = '_kato_mega_preview_title';
    const META_PREVIEW_D  = '_kato_mega_preview_desc';
    const META_PREVIEW_C  = '_kato_mega_preview_cta_text';

    /** @var self|null */
    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter( 'wp_nav_menu_args', array( $this, 'filter_nav_menu_args' ), 20 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'render_menu_fields' ), 10, 5 );
        add_action( 'wp_update_nav_menu_item', array( $this, 'save_menu_fields' ), 10, 3 );
        add_filter( 'manage_nav-menus_columns', array( $this, 'register_menu_columns' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function filter_nav_menu_args( $args ) {
        if ( is_admin() ) {
            return $args;
        }

        $supported_locations = array( 'top_nav', 'top_nav_pull_left', 'top_nav_pull_right' );
        $theme_location      = isset( $args['theme_location'] ) ? (string) $args['theme_location'] : '';

        if ( ! in_array( $theme_location, $supported_locations, true ) ) {
            return $args;
        }

        if ( wp_is_mobile() ) {
            return $args;
        }

        $walker_base = class_exists( 'Nectar_Arrow_Walker_Nav_Menu' ) ? 'Nectar_Arrow_Walker_Nav_Menu' : 'Walker_Nav_Menu';

        if ( ! class_exists( 'Kato_Salient_Mega_Menu_Walker' ) ) {
            eval('class Kato_Salient_Mega_Menu_Walker extends ' . $walker_base . ' { use Kato_Salient_Mega_Menu_Walker_Trait; }');
        }

        $args['walker'] = new Kato_Salient_Mega_Menu_Walker();
        return $args;
    }

    public function enqueue_assets() {
        if ( is_admin() || wp_is_mobile() ) {
            return;
        }

        wp_register_style(
            'kato-salient-mega-menu',
            KATO_SALIENT_MEGA_MENU_URL . 'assets/kato-salient-mega-menu.css',
            array(),
            '1.0.0'
        );

        wp_register_script(
            'kato-salient-mega-menu',
            KATO_SALIENT_MEGA_MENU_URL . 'assets/kato-salient-mega-menu.js',
            array(),
            '1.0.0',
            true
        );

        wp_enqueue_style( 'kato-salient-mega-menu' );
        wp_enqueue_script( 'kato-salient-mega-menu' );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( 'nav-menus.php' !== $hook ) {
            return;
        }

        wp_enqueue_media();
        wp_add_inline_script(
            'jquery-core',
            "(function($){
                $(document).on('click', '.kato-menu-media-upload', function(e){
                    e.preventDefault();
                    var button = $(this);
                    var wrap = button.closest('.kato-menu-field-group');
                    var input = wrap.find('.kato-menu-media-id');
                    var preview = wrap.find('.kato-menu-media-preview');
                    var frame = wp.media({ title: 'Select image', button: { text: 'Use image' }, multiple: false });
                    frame.on('select', function(){
                        var attachment = frame.state().get('selection').first().toJSON();
                        input.val(attachment.id);
                        preview.html('<img src="'+ attachment.url +'" alt="" style="max-width:100%;height:auto;display:block;" />');
                    });
                    frame.open();
                });
                $(document).on('click', '.kato-menu-media-remove', function(e){
                    e.preventDefault();
                    var wrap = $(this).closest('.kato-menu-field-group');
                    wrap.find('.kato-menu-media-id').val('');
                    wrap.find('.kato-menu-media-preview').empty();
                });
            })(jQuery);"
        );
    }

    public function register_menu_columns( $columns ) {
        $columns['kato_srs_mega_menu'] = esc_html__( 'Kato Mega Menu', 'kato-salient-mega-menu' );
        return $columns;
    }

    public function render_menu_fields( $item_id, $item, $depth, $args, $id ) {
        $enabled = get_post_meta( $item_id, self::META_ENABLE, true );
        $image_id = (int) get_post_meta( $item_id, self::META_PREVIEW_ID, true );
        $title = (string) get_post_meta( $item_id, self::META_PREVIEW_T, true );
        $desc = (string) get_post_meta( $item_id, self::META_PREVIEW_D, true );
        $cta = (string) get_post_meta( $item_id, self::META_PREVIEW_C, true );
        $preview_html = $image_id ? wp_get_attachment_image( $image_id, 'medium', false, array( 'style' => 'max-width:100%;height:auto;display:block;' ) ) : '';
        ?>
        <div class="description description-wide kato-menu-field-group" style="margin-top:12px; padding:12px; border:1px solid #dcdcde; background:#fff;">
            <p style="margin-top:0;"><strong><?php echo esc_html__( 'Kato 3-column mega menu', 'kato-salient-mega-menu' ); ?></strong></p>

            <?php if ( 0 === (int) $depth ) : ?>
                <p class="field-kato-enable description description-thin">
                    <label for="edit-menu-item-kato-enable-<?php echo esc_attr( $item_id ); ?>">
                        <input type="checkbox"
                               id="edit-menu-item-kato-enable-<?php echo esc_attr( $item_id ); ?>"
                               name="menu-item-kato-enable[<?php echo esc_attr( $item_id ); ?>]"
                               value="1" <?php checked( $enabled, '1' ); ?> />
                        <?php echo esc_html__( 'Enable Kato SRS mega menu for this level 1 item', 'kato-salient-mega-menu' ); ?>
                    </label>
                </p>
                <p class="description" style="clear:both;">
                    <?php echo esc_html__( 'This option should be enabled on a top-level item that is already configured as a Salient mega menu. The first level 2 child becomes the default active item when the panel opens.', 'kato-salient-mega-menu' ); ?>
                </p>
            <?php else : ?>
                <p class="description" style="margin-bottom:8px;">
                    <?php echo esc_html__( 'Optional preview overrides for this level 2 item. If left empty, the menu item title, description, linked object excerpt, and featured image will be used as fallback.', 'kato-salient-mega-menu' ); ?>
                </p>

                <p class="field-kato-preview-title description description-wide">
                    <label for="edit-menu-item-kato-preview-title-<?php echo esc_attr( $item_id ); ?>">
                        <?php echo esc_html__( 'Preview title override', 'kato-salient-mega-menu' ); ?><br />
                        <input type="text" class="widefat" id="edit-menu-item-kato-preview-title-<?php echo esc_attr( $item_id ); ?>" name="menu-item-kato-preview-title[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $title ); ?>" />
                    </label>
                </p>

                <p class="field-kato-preview-desc description description-wide">
                    <label for="edit-menu-item-kato-preview-desc-<?php echo esc_attr( $item_id ); ?>">
                        <?php echo esc_html__( 'Preview description override', 'kato-salient-mega-menu' ); ?><br />
                        <textarea class="widefat edit-menu-item-description" rows="3" id="edit-menu-item-kato-preview-desc-<?php echo esc_attr( $item_id ); ?>" name="menu-item-kato-preview-desc[<?php echo esc_attr( $item_id ); ?>]"><?php echo esc_textarea( $desc ); ?></textarea>
                    </label>
                </p>

                <p class="field-kato-preview-cta description description-wide">
                    <label for="edit-menu-item-kato-preview-cta-<?php echo esc_attr( $item_id ); ?>">
                        <?php echo esc_html__( 'CTA text override', 'kato-salient-mega-menu' ); ?><br />
                        <input type="text" class="widefat" id="edit-menu-item-kato-preview-cta-<?php echo esc_attr( $item_id ); ?>" name="menu-item-kato-preview-cta[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $cta ); ?>" placeholder="<?php echo esc_attr__( 'Read more', 'kato-salient-mega-menu' ); ?>" />
                    </label>
                </p>

                <p class="field-kato-preview-image description description-wide">
                    <label>
                        <?php echo esc_html__( 'Preview image override', 'kato-salient-mega-menu' ); ?>
                    </label>
                    <input type="hidden" class="kato-menu-media-id" name="menu-item-kato-preview-image-id[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $image_id ); ?>" />
                    <div class="kato-menu-media-preview" style="margin:8px 0;"><?php echo $preview_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    <p>
                        <button type="button" class="button kato-menu-media-upload"><?php echo esc_html__( 'Select image', 'kato-salient-mega-menu' ); ?></button>
                        <button type="button" class="button-link kato-menu-media-remove"><?php echo esc_html__( 'Remove image', 'kato-salient-mega-menu' ); ?></button>
                    </p>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    public function save_menu_fields( $menu_id, $menu_item_db_id, $args ) {
        if ( ! current_user_can( 'edit_theme_options' ) ) {
            return;
        }

        $enabled = isset( $_POST['menu-item-kato-enable'][ $menu_item_db_id ] ) ? '1' : '';
        $this->update_meta( $menu_item_db_id, self::META_ENABLE, $enabled );

        $title = isset( $_POST['menu-item-kato-preview-title'][ $menu_item_db_id ] ) ? sanitize_text_field( wp_unslash( $_POST['menu-item-kato-preview-title'][ $menu_item_db_id ] ) ) : '';
        $desc  = isset( $_POST['menu-item-kato-preview-desc'][ $menu_item_db_id ] ) ? sanitize_textarea_field( wp_unslash( $_POST['menu-item-kato-preview-desc'][ $menu_item_db_id ] ) ) : '';
        $cta   = isset( $_POST['menu-item-kato-preview-cta'][ $menu_item_db_id ] ) ? sanitize_text_field( wp_unslash( $_POST['menu-item-kato-preview-cta'][ $menu_item_db_id ] ) ) : '';
        $img   = isset( $_POST['menu-item-kato-preview-image-id'][ $menu_item_db_id ] ) ? absint( $_POST['menu-item-kato-preview-image-id'][ $menu_item_db_id ] ) : 0;

        $this->update_meta( $menu_item_db_id, self::META_PREVIEW_T, $title );
        $this->update_meta( $menu_item_db_id, self::META_PREVIEW_D, $desc );
        $this->update_meta( $menu_item_db_id, self::META_PREVIEW_C, $cta );
        $this->update_meta( $menu_item_db_id, self::META_PREVIEW_ID, $img );
    }

    private function update_meta( $menu_item_id, $meta_key, $value ) {
        if ( '' === $value || 0 === $value ) {
            delete_post_meta( $menu_item_id, $meta_key );
            return;
        }
        update_post_meta( $menu_item_id, $meta_key, $value );
    }
}

trait Kato_Salient_Mega_Menu_Walker_Trait {

    /** @var array<int,array<int,WP_Post>> */
    protected $kato_children_map = array();

    public function walk( $elements, $max_depth, ...$args ) {
        $this->kato_children_map = array();
        foreach ( (array) $elements as $element ) {
            if ( ! empty( $element->menu_item_parent ) ) {
                $this->kato_children_map[ (int) $element->menu_item_parent ][] = $element;
            }
        }
        return parent::walk( $elements, $max_depth, ...$args );
    }

    public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
        if ( $element instanceof WP_Post && 0 === (int) $depth && $this->is_kato_srs_menu( $element, $args ) ) {
            $output .= $this->render_kato_mega_menu( $element, $args );
            $this->unset_children_recursive( (int) $element->ID, $children_elements );
            return;
        }

        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }

    protected function is_kato_srs_menu( $item, $args ) {
        if ( ! $item instanceof WP_Post ) {
            return false;
        }

        $args_obj        = is_array( $args ) && isset( $args[0] ) ? $args[0] : null;
        $theme_location  = ( $args_obj && isset( $args_obj->theme_location ) ) ? (string) $args_obj->theme_location : '';
        $allowed         = array( 'top_nav', 'top_nav_pull_left', 'top_nav_pull_right' );
        $kato_enabled    = get_post_meta( $item->ID, Kato_Salient_Mega_Menu::META_ENABLE, true );
        $nectar_options  = maybe_unserialize( get_post_meta( $item->ID, 'nectar_menu_options', true ) );
        $salient_enabled = is_array( $nectar_options ) && isset( $nectar_options['enable_mega_menu'] ) && 'on' === $nectar_options['enable_mega_menu'];

        return '1' === (string) $kato_enabled && $salient_enabled && in_array( $theme_location, $allowed, true );
    }

    protected function render_kato_mega_menu( WP_Post $top_item, $args ) {
        $level2_items = isset( $this->kato_children_map[ $top_item->ID ] ) ? $this->kato_children_map[ $top_item->ID ] : array();
        $classes      = empty( $top_item->classes ) ? array() : (array) $top_item->classes;
        $classes[]    = 'menu-item-' . $top_item->ID;
        $classes[]    = 'menu-item-has-children';
        $classes[]    = 'sf-with-ul';
        $classes[]    = 'megamenu';
        $classes[]    = 'nectar-megamenu-menu-item';
        $classes[]    = 'kato-mega-menu';

        $nectar_options = maybe_unserialize( get_post_meta( $top_item->ID, 'nectar_menu_options', true ) );
        if ( is_array( $nectar_options ) ) {
            if ( ! empty( $nectar_options['mega_menu_alignment'] ) ) {
                $classes[] = 'align-' . sanitize_html_class( $nectar_options['mega_menu_alignment'] );
            }
            if ( ! empty( $nectar_options['mega_menu_width'] ) ) {
                $classes[] = 'width-' . sanitize_html_class( $nectar_options['mega_menu_width'] );
            }
        }

        $class_attr = implode( ' ', array_map( 'sanitize_html_class', array_unique( array_filter( $classes ) ) ) );
        $title_html = sprintf(
            '<span class="menu-title-text"><span class="nectar-text-reveal-button"><span class="nectar-text-reveal-button__text" data-text="%1$s">%2$s</span></span></span><span class="sf-sub-indicator"><i class="fa fa-angle-down icon-in-menu" aria-hidden="true"></i></span>',
            esc_attr( wp_strip_all_tags( $top_item->title ) ),
            esc_html( $top_item->title )
        );

        $link_atts = array(
            'href' => ! empty( $top_item->url ) ? $top_item->url : '#',
            'class' => 'sf-with-ul',
        );

        $output  = '<li id="menu-item-' . esc_attr( $top_item->ID ) . '" class="' . esc_attr( $class_attr ) . '">';
        $output .= '<a' . $this->kato_build_atts( $link_atts ) . '>' . $title_html . '</a>';

        if ( empty( $level2_items ) ) {
            $output .= '</li>';
            return $output;
        }

        $first_preview = $this->get_menu_preview_data( $level2_items[0] );
        $output .= '<div class="kato-mega-menu__panel" role="group" aria-label="' . esc_attr( $top_item->title ) . '">';
        $output .= $this->render_preview_column( $first_preview );
        $output .= '<div class="kato-mega-menu__col kato-mega-menu__col--level2">';
        $output .= '<div class="kato-mega-menu__heading kato-mega-menu__heading--level1">' . esc_html( $top_item->title ) . '</div>';
        $output .= '<ul class="kato-mega-menu__level2-list">';

        foreach ( $level2_items as $index => $level2_item ) {
            $preview = $this->get_menu_preview_data( $level2_item );
            $active  = 0 === $index ? ' is-active' : '';
            $output .= '<li class="kato-mega-menu__level2-item' . esc_attr( $active ) . '"';
            $output .= ' data-kato-key="' . esc_attr( $level2_item->ID ) . '"';
            $output .= ' data-preview-image="' . esc_url( $preview['image_url'] ) . '"';
            $output .= ' data-preview-title="' . esc_attr( $preview['title'] ) . '"';
            $output .= ' data-preview-desc="' . esc_attr( $preview['desc'] ) . '"';
            $output .= ' data-preview-link="' . esc_url( $preview['link'] ) . '"';
            $output .= ' data-preview-cta="' . esc_attr( $preview['cta'] ) . '">';
            $output .= '<a href="' . esc_url( $preview['link'] ) . '">' . esc_html( $level2_item->title ) . '</a>';
            $output .= '</li>';
        }

        $output .= '</ul></div>';
        $output .= '<div class="kato-mega-menu__col kato-mega-menu__col--level3">';
        $output .= '<div class="kato-mega-menu__level3-target">' . $this->render_level3_group( $level2_items[0] ) . '</div>';
        $output .= '</div>';
        $output .= '<div class="kato-mega-menu__templates" hidden>';
        foreach ( $level2_items as $level2_item ) {
            $output .= '<template data-kato-key="' . esc_attr( $level2_item->ID ) . '">';
            $output .= $this->render_level3_group( $level2_item );
            $output .= '</template>';
        }
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</li>';

        return $output;
    }

    protected function render_preview_column( array $preview ) {
        $has_image = ! empty( $preview['image_url'] );
        $class     = 'kato-mega-menu__col kato-mega-menu__col--preview';
        if ( ! $has_image ) {
            $class .= ' has-no-image';
        }

        $output  = '<div class="' . esc_attr( $class ) . '">';
        $output .= '<a class="kato-mega-menu__preview-link" href="' . esc_url( $preview['link'] ) . '">';
        $output .= '<span class="kato-mega-menu__preview-image-wrap">';
        if ( $has_image ) {
            $output .= '<img class="kato-mega-menu__preview-image" src="' . esc_url( $preview['image_url'] ) . '" alt="' . esc_attr( $preview['title'] ) . '" />';
        } else {
            $output .= '<span class="kato-mega-menu__preview-image kato-mega-menu__preview-image--placeholder" aria-hidden="true"></span>';
        }
        $output .= '</span>';
        $output .= '<span class="kato-mega-menu__preview-content">';
        $output .= '<span class="kato-mega-menu__heading kato-mega-menu__heading--preview">' . esc_html( $preview['title'] ) . '</span>';
        if ( ! empty( $preview['desc'] ) ) {
            $output .= '<span class="kato-mega-menu__preview-desc">' . esc_html( $preview['desc'] ) . '</span>';
        }
        $output .= '<span class="kato-mega-menu__preview-cta">' . esc_html( $preview['cta'] ) . ' <span aria-hidden="true">→</span></span>';
        $output .= '</span>';
        $output .= '</a>';
        $output .= '</div>';
        return $output;
    }

    protected function render_level3_group( WP_Post $level2_item ) {
        $level3_items = isset( $this->kato_children_map[ $level2_item->ID ] ) ? $this->kato_children_map[ $level2_item->ID ] : array();
        $output  = '<div class="kato-mega-menu__level3-group">';
        $output .= '<div class="kato-mega-menu__heading kato-mega-menu__heading--level2">' . esc_html( $level2_item->title ) . '</div>';

        if ( empty( $level3_items ) ) {
            $output .= '<div class="kato-mega-menu__empty">' . esc_html__( 'No sub items', 'kato-salient-mega-menu' ) . '</div>';
            $output .= '</div>';
            return $output;
        }

        $output .= '<ul class="kato-mega-menu__level3-list">';
        foreach ( $level3_items as $level3_item ) {
            $output .= '<li class="kato-mega-menu__level3-item"><a href="' . esc_url( $level3_item->url ) . '">' . esc_html( $level3_item->title ) . '</a></li>';
        }
        $output .= '</ul>';
        $output .= '</div>';
        return $output;
    }

    protected function get_menu_preview_data( WP_Post $menu_item ) {
        $title = (string) get_post_meta( $menu_item->ID, Kato_Salient_Mega_Menu::META_PREVIEW_T, true );
        $desc  = (string) get_post_meta( $menu_item->ID, Kato_Salient_Mega_Menu::META_PREVIEW_D, true );
        $cta   = (string) get_post_meta( $menu_item->ID, Kato_Salient_Mega_Menu::META_PREVIEW_C, true );
        $img_id = (int) get_post_meta( $menu_item->ID, Kato_Salient_Mega_Menu::META_PREVIEW_ID, true );

        if ( '' === $title ) {
            $title = $menu_item->title;
        }

        if ( '' === $desc ) {
            $desc = isset( $menu_item->description ) ? trim( (string) $menu_item->description ) : '';
        }

        if ( '' === $desc ) {
            $desc = $this->get_object_excerpt( $menu_item );
        }

        if ( '' === $cta ) {
            $cta = __( 'Read more', 'kato-salient-mega-menu' );
        }

        $image_url = '';
        if ( $img_id > 0 ) {
            $image = wp_get_attachment_image_src( $img_id, 'large' );
            if ( $image ) {
                $image_url = $image[0];
            }
        }

        if ( '' === $image_url ) {
            $image_url = $this->get_object_image_url( $menu_item );
        }

        return array(
            'title'     => $title,
            'desc'      => wp_trim_words( wp_strip_all_tags( $desc ), 24, '…' ),
            'cta'       => $cta,
            'image_url' => $image_url,
            'link'      => ! empty( $menu_item->url ) ? $menu_item->url : '#',
        );
    }

    protected function get_object_excerpt( WP_Post $menu_item ) {
        if ( 'post_type' === $menu_item->type ) {
            $post = get_post( (int) $menu_item->object_id );
            if ( $post ) {
                if ( has_excerpt( $post ) ) {
                    return (string) $post->post_excerpt;
                }
                return wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 28, '…' );
            }
        }

        if ( 'taxonomy' === $menu_item->type ) {
            $term = get_term( (int) $menu_item->object_id, $menu_item->object );
            if ( $term && ! is_wp_error( $term ) ) {
                return (string) $term->description;
            }
        }

        return '';
    }

    protected function get_object_image_url( WP_Post $menu_item ) {
        if ( 'post_type' === $menu_item->type ) {
            $thumbnail_id = get_post_thumbnail_id( (int) $menu_item->object_id );
            if ( $thumbnail_id ) {
                $image = wp_get_attachment_image_src( $thumbnail_id, 'large' );
                return $image ? $image[0] : '';
            }
        }

        return '';
    }

    protected function kato_build_atts( array $atts ) {
        $result = '';
        foreach ( $atts as $name => $value ) {
            if ( '' === $value || null === $value ) {
                continue;
            }
            if ( 'href' === $name ) {
                $value = esc_url( $value );
            } else {
                $value = esc_attr( $value );
            }
            $result .= ' ' . $name . '="' . $value . '"';
        }
        return $result;
    }

    protected function unset_children_recursive( $item_id, &$children_elements ) {
        if ( empty( $children_elements[ $item_id ] ) ) {
            return;
        }

        foreach ( $children_elements[ $item_id ] as $child ) {
            $this->unset_children_recursive( (int) $child->ID, $children_elements );
        }

        unset( $children_elements[ $item_id ] );
    }
}
