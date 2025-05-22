<?php
/*
* @Package: MindCat
*/
class MindCat{
    private $current_cat;
    function __construct(){
        load_plugin_textdomain( 'mindcat', false, 'mindcat/languages' );
        add_action('init', array(&$this,'register_blocks'));
        add_action('widgets_init', array(&$this,'register_widgets'));
        add_action('admin_init', array(&$this, 'register_settings'));
        add_action('plugins_loaded', array(&$this,'init'));

        // Styles & scripts
        add_action('wp_print_styles', array(&$this,'enqueue_styles'));
        add_action('admin_print_styles', array(&$this,'enqueue_styles'));
        add_action('admin_enqueue_scripts', array(&$this, 'load_media_scripts'));

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true )
            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_frontend_scripts'));

        add_action('wp_enqueue_scripts', array(&$this, 'localize_scripts'), 99);
        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_frontend_styles'));

        // Settings
        add_action('admin_menu', array(&$this,'settings_page'));

        //Rest API
        add_action('rest_api_init', array($this,'rest_routes'), 10, 1);

        // Shortcode
        add_shortcode('mindcat', array(&$this,'block_mindmap'));

        add_filter('pre_render_block', array(&$this, 'pre_render_block'), 10, 3);

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true )
            add_filter('register_block_type_args', array(&$this, 'register_block_type_args'), 10, 2);
    }
    /**
     * PHP4 Constructor
     */
    public function MindCat(){
        $this->__construct();
    }

    function inc($path){
        include plugin_dir_path(__DIR__).'inc/'.$path;
    }

    function require_once($path){
        require_once plugin_dir_path(__DIR__).'inc/'.$path;
    }

    function register_widgets(){
        $this->require_once('widgets/class-mindcat-widget.php');
        register_widget('MindCat_Widget');
    }

    public function init(){
        $taxonomies = ['category','post_tag'];
        $taxonomies = apply_filters('mindcat_additional_taxonomies', $taxonomies);
        foreach($taxonomies as $taxonomy){
            add_action($taxonomy.'_edit_form_fields', array(&$this, 'edit_term'));
            add_action('delete_'.$taxonomy, array(&$this, 'delete_term'));
            add_action('edited_'.$taxonomy, array(&$this, 'save_term'));
        }
    }

    /**
     * Register the blocks
     * 
     * @return void
     */
    function register_blocks(){
        wp_register_script('mindcat-mindmap-front', plugins_url('build/mindmap/mindmap.js', __DIR__), array( 'jquery' ), false, true);
        register_block_type(plugin_dir_path(__DIR__) . 'build/mindmap', [
            'render_callback' => [$this, 'block_mindmap'],
            'script' => 'mindcat-mindmap-front',
        ]);
        register_block_type(plugin_dir_path(__DIR__) . 'build/list', array(
            'render_callback' => [$this,'render_mindcategory_block'],
        ));
    }
    
    /**
     * Enqueue styles
     * 
     * @return void
     */
    public function enqueue_styles(){
        $colors = get_option('MindCatColors',array());
        $vars  = '';
        $rules = '';
        if(is_array($colors)){
            foreach($colors as $id=>$color){
                $vars .= '--mindcat-color-'.$id.'-bg:'.$color['bg'].';';
                $vars .= '--mindcat-color-'.$id.'-txt:'.$color['txt'].';';
                $rules.= '.mindcat-use-bg-color .mindcat-term-'.$id.'-bg{background-color:var(--mindcat-color-'.$id.'-bg);}';
                $rules.= '.mindcat-use-txt-color .mindcat-term-'.$id.'-txt{color:var(--mindcat-color-'.$id.'-txt);}';
            }
        }
        echo '<style id="mindcat-css-vars">:root{'.$vars.'}</style>'."\n";
        echo '<style id="mindcat-css-rules">'.$rules.'</style>'."\n";
    }

    /**
     * For developing and forcing WordPress to load changed JavaScript
     * 
     * @return void
     */
    public function enqueue_frontend_scripts() {
        wp_deregister_script( 'mindcat-mindmap-front' );
        wp_register_script(
            'mindcat-mindmap-front',
            plugins_url( 'mindcat/build/mindmap/mindmap.umin.js' ),
            array( 'jquery' ),
            false,
            true
        );
        wp_enqueue_script( 'mindcat-mindmap-front-umin' );
    }

    /**
     * Localize scripts
     * 
     * @return void
     */
    public function localize_scripts() {
        $translation_array = array(
            'save' => __( 'Save changes', 'mindcat' ),
            'cancel' => __( 'Cancel changes', 'mindcat' ),
            'rename' => __( 'Rename this term', 'mindcat' ),
            'add' => __( 'Add a sub-term', 'mindcat' ),
            'remove' => __( 'Remove this term', 'mindcat' ),
        );
        wp_localize_script( 'mindcat-mindmap-front', 'Mindmap', $translation_array );
    }

    /**
     * Enqueue frontend styles
     * 
     * @return void
     */
    public function enqueue_frontend_styles() {
        wp_enqueue_style( 'dashicons' );

        // For developing and forcing WordPress to load changed CSS
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true )
            wp_register_style(
                'mindcat-mindmap-umin-style',
                plugins_url( 'mindcat/build/mindmap/mindmap.umin.css' ),
                array(),
                rand( 1, 99 )
            );

    }

    /**
     * Load wp.media scripts in admin
     *
     * @return void
     */
    function load_media_scripts()
    {
        wp_enqueue_media();
    }

    /**
     * Delete term meta fields
     * 
     * @param int $id
     */
    function delete_term($id) {
        $MindCatColors = get_option('MindCatColors',array());
        if(isset($MindCatColors[$id])){
            unset($MindCatColors[$id]);
        }
        update_option('MindCatColors',$MindCatColors);
    }
    
    /**
     * Save upload image term meta fields
     *
     * @param mixed $term_id
     *
     * @return void
     */
    function save_term($term_id)
    {
        if (isset($_POST['mindcat_image_primary'])) {
            update_term_meta($term_id, 'mindcat_image_primary', $_POST['mindcat_image_primary']);
        }
        if (isset($_POST['mindcat_image_secondary'])) {
            update_term_meta($term_id, 'mindcat_image_secondary', $_POST['mindcat_image_secondary']);
        }

        if(isset($_POST['MindCatColor'])) {
            $MindCatColors = get_option('MindCatColors', array());
            if(!is_array($MindCatColors)) {
                $MindCatColors = array();
            }
            $MindCatColors[$term_id] = (isset($_POST['MindCatColor']) && is_array($_POST['MindCatColor'])) ? $_POST['MindCatColor'] : array('bg' => '#CCCCCC','txt' => '#333333');
            update_option('MindCatColors', $MindCatColors);
        }

    }

    /**
     * Render the mindmap block
     * 
     * @param mixed $attributes
     * @param mixed $content
     * 
     * @return string
     */
    function block_mindmap($attributes, $content=''){
        $this->require_once('views/mindmap.php');
        return \Mindcat\mindmap($attributes);
    }
    /**
     * Render the mindcategory block List
     * 
     * @param mixed $attributes
     * 
     * @return string
     */
    function render_mindcategory_block($attributes, $content='', $block_class='')
    {
        $categories = $attributes['selectedCategories'] ?? [];
        $displayMode = $attributes['displayMode'] ?? 'grid';
        $imageChoice = $attributes['imageChoice'] ?? 'primary';
        $className = $attributes['className'] ?? '';
        $useBGColor = $attributes['useBGColor'] ?? false;
        $useTextColor = $attributes['useTextColor'] ?? false;
        $imageSizeChoice = $attributes['imageSizeChoice'] ?? 'thumbnail';
        $showPublications = $attributes['showPublications'] ?? false;
        $noImageURL = get_option('mindcat_image_noimage');
        $square_logo = false;

        $item_class='';
        $titleBalise = 'h6';
        if ($displayMode === 'grid') {
            $className.= ' mindcategorygrid';
        } elseif ($displayMode === 'column') {
            $item_class = "mindcategorycolumn";
        } elseif ($displayMode === 'mindcat-card') {
            $item_class = "mindcat-card-1";
        } elseif ($displayMode === 'mindcat-round-card') {
            $item_class = "mindcat-round-card";
        } elseif ($displayMode === 'mindcat-brand-card') {
            $item_class = "mindcat-brand-card";
        } elseif ($displayMode === 'mindcat-h2') {
            $item_class = "mindcat-h2";
            $titleBalise = 'h2';
        } elseif ($displayMode === 'mindcat-h3') {
            $item_class = "mindcat-h3";
            $titleBalise = 'h3';
        } elseif ($displayMode === 'mindcat-h4') {
            $item_class = "mindcat-h4";
            $titleBalise = 'h4';
        } elseif($displayMode === 'mindcat-square-card') {
            $item_class = "mindcat-square-card";
            $square_logo = true;
        }

        if($useBGColor){
            $className.= ' mindcat-use-bg-color';
        }
        if($useTextColor){
            $className.= ' mindcat-use-txt-color';
        }

        // Image choice
        if($imageChoice === 'none'){
            $className.= ' mindcat-no-image';
        }
        else{
            $className.= ' mindcat-has-image';
        }

        $category_class = $showPublications ? 'mindcat-has-publications' : 'mindcat-no-publications';
        $output = '<div class="mindcategory '.esc_attr($className) . ' ' . $category_class . '">';

        foreach($categories as $category) {
            $taxonomy = get_taxonomy($category['slug']);
            $term_id = $category['value'];
            $term = get_term($term_id);
            $category_link = '';
            if ($term) {
                $taxonomy = $term->taxonomy;
                if (taxonomy_exists($taxonomy)) {
                    $category_link = get_term_link($category['value'], $taxonomy);
                    if(isset($_REQUEST['context']) && $_REQUEST['context']==='edit'){
                        // $output.='Term error '.$term_id.'@'.$taxonomy.'<br>'.$category_link->get_error_message().'<hr>';
                        $category_link = '#';
                    }
                    elseif (is_wp_error($category_link)) {
                        continue;
                    }
                }
            }
            
            $image_key = get_option('mindcat_image_' . $imageChoice);
            $category_image = '';
            $url = '';
            if($image_key){
                $category_image = get_term_meta($category['value'], $image_key, true);
                $url = wp_get_attachment_url($category_image);
            } else {
                $category_image = get_term_meta($term_id, 'mindcat_image_' . $imageChoice, true);
                $url = wp_get_attachment_url($category_image);
                if($square_logo) {
                    $logo_image = get_term_meta($term_id, 'mindcat_image_secondary', true);
                    $logo_url = wp_get_attachment_url($logo_image);
                }
            }
            if ($url) {
                $image_html = '<a href="'.esc_attr($category_link).'">' . wp_get_attachment_image($category_image, $imageSizeChoice, false, ['class' => 'category-image']) . '</a>';
            } else {
                $image_html = '<a class="entry-featured-image-url img_archive_default" href="'.esc_attr($category_link).'">';
                if($noImageURL){
                    $image_html .= '<img src="' . esc_url($noImageURL) . '" class="category-image" /></a>';
                } else {
                    $image_html .= '<img src="' . esc_url(plugins_url('build/thumbnail.webp', __DIR__)) . '" class="category-image" /></a>';
                }
            }
            if(isset($logo_url) && $logo_url) {
                $logo_html = '<a class="mindcat-square-logo" href="'.esc_attr($category_link).'">' . wp_get_attachment_image($logo_image, 'medium', false, ['class' => 'category-image']) . '</a>';
            } else {
                $logo_html = false;
            }

            $output .= '<div class="'.esc_attr($item_class.' mindcat-term mindcat-term-'.$term_id).'-bg ' . $category_class . '">';
            $output .= $imageChoice !== 'none' ? $image_html : '';
            $output .= $logo_html ? $logo_html : '';
            $output .= '<' . $titleBalise . ' class="card1-title '.esc_attr('mindcat-term-'.$term_id).'-txt">';
            $output .= '<a href="'.esc_attr($category_link).'" class="category-item">'.$category['label'].'</a>';
            $output .= '</'. $titleBalise . '>';


            if ($showPublications) {
                $this->current_cat = $term;
                foreach ($block_class->inner_blocks as $inner_block) {
                    $output .= apply_filters('pre_render_block', null, $inner_block->parsed_block, $block_class);
                }
            }
            $output .= '</div>';
        }
       $output .= "</div>";
        return $output;
    }

    function pre_render_block($pre_render, $parsed_block, $parent_block)
    {   
        if ($parsed_block['blockName'] === 'core/query') {
            if (isset($parent_block) && isset($parent_block->parsed_block['blockName']) && $parent_block->parsed_block['blockName'] === 'mindcat/list'){
                if (isset($this->current_cat) && is_object($this->current_cat)) {
                    // get all post types associated to taxonomy $this->current_cat->taxonomy
                    $post_types = get_taxonomy($this->current_cat->taxonomy)->object_type;
                    $parsed_block['attrs']['query']['postType'] = implode(',', $post_types);
                    $parsed_block['attrs']['query']['taxQuery'] = [
                        $this->current_cat->taxonomy => [$this->current_cat->term_id]
                    ];
                    return render_block($parsed_block);
                }
            }
        }

        return $pre_render; 
    }

    /**
     * For developing and forcing WordPress to load changed CSS
     * 
     * @param array $args
     * @param string $block_type
     * 
     * @return string
     */
    function register_block_type_args( $args, $block_type )
    {
        // List of block types to modify
        $block_types_to_modify = [
            'mindcat/mindmap',
        ];
    
        // Check if the current block type is in the list
        if ( in_array( $block_type, $block_types_to_modify, true ) ) {
            // Edit arguments
            $args[ 'style' ] = [
                'mindcat-mindmap-umin-style',
                '',
                rand( 1, 99 )
            ];
        }
    
        return $args;
    }

    /**
     * Add settings page
     * 
     * @return void
     */
    function settings_page() {
        add_options_page(__('Mindcat Settings', 'mindcat'), __('Mindcat', 'mindcat'), 'manage_options', 'mindcat-settings', array(&$this, 'settings_page_callback'));
    }
    
    /**
     * Display the settings page
     * 
     * @return void
     */
    function settings_page_callback() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('mindcat_settings_group'); ?>
                <?php do_settings_sections('mindcat-settings'); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register settings
     * 
     * @return void
     */
    function register_settings() {
        register_setting('mindcat_settings_group', 'mindcat_image_primary');
        register_setting('mindcat_settings_group', 'mindcat_image_secondary');
        register_setting('mindcat_settings_group', 'mindcat_image_noimage');
    
        add_settings_section('mindcat_image_section', __('Image Settings', 'mindcat'), array(&$this, 'mindcat_image_section_callback'), 'mindcat-settings');
        add_settings_field('mindcat_image_primary', __('Primary Image meta name', 'mindcat'), array(&$this, 'mindcat_image_primary_callback'), 'mindcat-settings', 'mindcat_image_section');
        add_settings_field('mindcat_image_secondary', __('Secondary Image meta name', 'mindcat'), array(&$this, 'mindcat_image_secondary_callback'), 'mindcat-settings', 'mindcat_image_section');
        add_settings_field('mindcat_image_noimage', __('No-Image URL', 'mindcat'), array(&$this, 'mindcat_image_noimage_callback'), 'mindcat-settings', 'mindcat_image_section');
    }
    
    /**
     * Description of the taxonomies images in the settings page
     * 
     * @return void
     */
    function mindcat_image_section_callback() {
        echo '<p>' . __('Enter meta name for primary and secondary images.', 'mindcat') . '</p>';
    }
   
    /**
     * Primary image field in the settings page
     * 
     * @return void
     */
    function mindcat_image_primary_callback() {
        $value = get_option('mindcat_image_primary');
        echo '<input type="text" name="mindcat_image_primary" value="' . esc_attr($value) . '" />';
    }
    /**
     * Secondary image field in the settings page
     * 
     * @return void
     */
    function mindcat_image_secondary_callback() {
        $value = get_option('mindcat_image_secondary');
        echo '<input type="text" name="mindcat_image_secondary" value="' . esc_attr($value) . '" />';
    }

    /**
     * No image field in the settings page
     * 
     * @return void
     */
    function mindcat_image_noimage_callback()
    {
        $value = get_option('mindcat_image_noimage');
        echo '<input type="text" name="mindcat_image_noimage" value="' . esc_attr($value) . '" />';
    }

    /**
     * Add custom term meta fields to term edit page
     * 
     * @param mixed $term
     * 
     * @return void
     */
    public function edit_term($term) {
        $taxonomy = $term->taxonomy;
        $primary_image_key = get_option('mindcat_image_primary');
        $secondary_image_key = get_option('mindcat_image_secondary');
    
        $primary_image_id = get_term_meta($term->term_id, 'mindcat_image_primary', true);
        $secondary_image_id = get_term_meta($term->term_id, 'mindcat_image_secondary', true);

        $id = $term->term_id;
        $MindCatColors = get_option('MindCatColors', array());

        if(!is_array($MindCatColors)) {
            $MindCatColors = array();
        }
        $bgcolor = isset($MindCatColors[$id]['bg']) ? $MindCatColors[$id]['bg'] : '#CCCCCC';
        $color = isset($MindCatColors[$id]['txt']) ? $MindCatColors[$id]['txt'] : '#333333';
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('mindcat-mindmap-front');
        ?>
        <table id="sortableTableNaN" class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th valign="top" scope="row">
                        <label for="name"><?php _e('Background color', 'mindcat'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="MindCatColor[bg]" value="<?=$bgcolor?>" class="mindcat-color-field" data-default-color="#cccccc" />
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th valign="top" scope="row">
                        <label for="name"><?php _e('Color', 'mindcat'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="MindCatColor[txt]" value="<?=$color?>" class="mindcat-color-field" data-default-color="#333333" />
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="mindcat_image_primary"><?php _e('Primary Image', 'mindcat'); ?></label>
                    </th>
                    <td>
                        <?php if ($primary_image_key) : ?>
                            <p><?php
                                // translators: %1$s represents the term meta key, %2$s represents the URL to Mindcat settings page
                                echo sprintf(__('The primary image is managed by %1$s term meta. You can change it <a href="%2$s">in mindcat settings page</a>.', 'mindcat'), $primary_image_key, esc_url(add_query_arg(array('page' => 'mindcat-settings'), admin_url('options-general.php'))));?></p>
                        <?php else : ?>
                            <div class="mindcat-image-upload">
                                <input type="hidden" name="mindcat_image_primary" id="mindcat_image_primary" value="<?php echo $primary_image_id; ?>">
                                <button type="button" class="mindcat-upload-button button"><?php _e('Upload Primary Image', 'mindcat'); ?></button>
                                <div class="mindcat-image-preview"><?php if ($primary_image_id) echo '<img src="' . wp_get_attachment_url($primary_image_id) . '" style="max-width:100px;">'; ?></div>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="mindcat_image_secondary"><?php _e('Secondary Image', 'mindcat'); ?></label>
                    </th>
                    <td>
                        <?php if ($secondary_image_key) : ?>
                            <p><?php
                                // translators: %1$s represents the term meta key and %2$s represents the URL to Mindcat settings page
                                echo sprintf(__('The secondary image is managed by %1$s term meta. You can change it <a href="%2$ss">in mindcat settings page</a>.', 'mindcat'), $secondary_image_key, admin_url('options-general.php?page=mindcat-settings')); ?></p>
                        <?php else : ?>
                            <div class="mindcat-image-upload">
                                <input type="hidden" name="mindcat_image_secondary" id="mindcat_image_secondary" value="<?php echo $secondary_image_id; ?>">
                                <button type="button" class="mindcat-upload-button button"><?php _e('Upload Secondary Image', 'mindcat'); ?></button>
                                <div class="mindcat-image-preview"><?php if ($secondary_image_id) echo '<img src="' . wp_get_attachment_url($secondary_image_id) . '" style="max-width:100px;">'; ?></div>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <script>
            jQuery(document).ready(function($) {
                $('#mindcat_image_primary').siblings('.mindcat-upload-button').click(function(e) {
                    e.preventDefault();
                    var button = $(this);
                    var imageUploader = wp.media({
                        title: '<?php _e('Upload Primary Image', 'mindcat'); ?>',
                        button: { text: '<?php _e('Use Primary Image', 'mindcat'); ?>' },
                        multiple: false
                    }).on('select', function() {
                        var attachment = imageUploader.state().get('selection').first().toJSON();
                        button.siblings('#mindcat_image_primary').val(attachment.id);
                        button.siblings('.mindcat-image-preview').html('<img src="' + attachment.url + '" style="max-width:100px;">');
                    }).open();
                });
    
                $('#mindcat_image_secondary').siblings('.mindcat-upload-button').click(function(e) {
                    e.preventDefault();
                    var button = $(this);
                    var imageUploader = wp.media({
                        title: '<?php _e('Upload Secondary Image', 'mindcat'); ?>',
                        button: { text: '<?php _e('Use Secondary Image', 'mindcat'); ?>' },
                        multiple: false
                    }).on('select', function() {
                        var attachment = imageUploader.state().get('selection').first().toJSON();
                        button.siblings('#mindcat_image_secondary').val(attachment.id);
                        button.siblings('.mindcat-image-preview').html('<img src="' + attachment.url + '" style="max-width:100px;">');
                    }).open();
                });
            });
        </script>
        <?php
    }

  


  /**
     * Rest routes for taxonomies
     * 
     * @return void
     */
    function rest_routes() {
        register_rest_route('mindcat', 'taxonomies', array(
            'methods' => 'GET',
            'permission_callback' => '__return_true',
            'callback' => [$this, 'mindcat_get_taxonomies'],
          ));
    }

    /**
     * Get wp taxonomies and mindcat_additional_taxonomies filter
     * 
     * @return void
     */
    function mindcat_get_taxonomies() {
        $wordpress_taxonomies = array('category', 'post_tag');
        $additional_taxonomies = apply_filters('mindcat_additional_taxonomies', array());
        $selected_taxonomies = array_merge($wordpress_taxonomies, $additional_taxonomies);
        $taxonomies_details = array();
        foreach ($selected_taxonomies as $taxonomy) {
            $taxonomy_details = get_taxonomy($taxonomy);
            $taxonomies_details[$taxonomy] = array(
                'name'         => $taxonomy_details->labels->name,
                'slug'         => $taxonomy_details->name,
                'types'        => $taxonomy_details->object_type,
                'hierarchical' => $taxonomy_details->hierarchical,
            );
        }
        return rest_ensure_response($taxonomies_details);
    }
    
}