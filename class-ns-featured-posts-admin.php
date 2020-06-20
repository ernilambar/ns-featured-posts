<?php
/**
 * NS Featured Posts
 *
 * @package   NS_Featured_Posts_Admin
 * @author    Nilambar Sharma <nilambar@outlook.com>
 * @license   GPL-2.0+
 * @link      https://www.nilambar.net
 * @copyright 2013 Nilambar Sharma
 */

use Nilambar\Optioner\Optioner;

/**
 * NS Featured Posts Admin class.
 *
 * @package NS_Featured_Posts_Admin
 * @author  Nilambar Sharma <nilambar@outlook.com>
 */
class NS_Featured_Posts_Admin
{

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

  	protected $options = array();

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     */
    private function __construct()
    {

        /*
         * Call $plugin_slug from public plugin class.
         *
         */
        $plugin = NS_Featured_Posts::get_instance();
        $this->plugin_slug = $plugin->get_plugin_slug();
    		$this->options = $plugin->ns_featured_posts_get_options_array();

        /*
         * Add an action link pointing to the options page.
         */
        $plugin_basename = plugin_basename(plugin_dir_path(__FILE__) . 'ns-featured-posts.php');
        add_filter('plugin_action_links_' . $plugin_basename, array($this, 'ns_featured_posts_add_action_links'));

        /*
         * Define custom functionality.
         */

        add_action( 'admin_init', array($this, 'ns_featured_posts_add_columns_head'));
        add_action( 'admin_enqueue_scripts', array($this, 'load_assets'));
        // add_action( 'admin_head', array( $this,'add_script_to_admin_head') );
        // add_action( 'admin_head', array( $this,'add_style_to_admin_head') );
        add_action( 'wp_ajax_nsfeatured_posts', array( $this, 'nsfp_ajax_featured_post' ) );

        add_action( 'restrict_manage_posts', array( $this, 'nsfp_table_filtering' ) );
        add_filter( 'parse_query', array( $this, 'nsfp_query_filtering' ) );

        add_filter( 'pre_get_posts', array( $this, 'nsfp_filtering_query_for_listing' ) );

        add_action( 'widgets_init', array( $this, 'nsfp_custom_widgets' ) );

        // Metabox stuffs.
        add_action( 'add_meta_boxes', array( $this, 'add_featured_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'nsfp_save_meta_box' ) );

        add_action( 'init', array( $this, 'setup_admin_page' ) );
    }

    public function setup_admin_page() {
        $obj = new Optioner();

        $obj->set_page(
        	array(
        		'page_title'  => esc_html__( 'NS Featured Posts', 'ns-featured-posts' ),
        		'menu_title'  => esc_html__( 'NS Featured Posts', 'ns-featured-posts' ),
        		'capability'  => 'manage_options',
        		'menu_slug'   => 'ns-featured-posts',
        		'option_slug' => 'nsfp_plugin_options',
        	)
        );

        // Tab: nsfp_settings_tab.
        $obj->add_tab(
        	array(
        		'id'    => 'nsfp_settings_tab',
        		'title' => esc_html__( 'Settings', 'ns-featured-posts' ),
        	)
        );

        // Field: nsfp_posttypes.
        $obj->add_field(
        	'nsfp_settings_tab',
        	array(
				'id'      => 'nsfp_posttypes',
				'type'    => 'multicheck',
				'title'   => esc_html__( 'Enable Featured for', 'ns-featured-posts' ),
				'choices' => $this->get_post_types_options(),
        	)
        );

        // Sidebar.
        $obj->set_sidebar(
        	array(
        		'render_callback' => array( $this, 'render_sidebar' ),
        		'width'           => 30,
        	)
        );

        // Run now.
        $obj->run();
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance()
    {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance )
        {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function get_post_types_options() {
    	$output = array(
    		'post' => esc_html__( 'Post', 'ns-featured-posts' ),
    		'page' => esc_html__( 'Page', 'ns-featured-posts' ),
    	);

    	$args = array(
    		'public'   => true,
    		'_builtin' => false,
    	);

    	$custom_types = get_post_types( $args, 'objects' );

    	if ( ! empty( $custom_types ) ) {
    		foreach ( $custom_types as $item ) {
    			$output[ $item->name ] = $item->labels->{'singular_name'};
    		}
    	}

    	return $output;
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */
    public function ns_featured_posts_add_action_links( $links ) {

    	return array_merge(
    		array(
    			'settings' => '<a href="' . esc_url( admin_url( 'options-general.php?page=' . $this->plugin_slug ) ) . '">' . __( 'Settings', 'ns-featured-posts' ) . '</a>'
    			),
    		$links
		);
    }

    /**
     * Add columns to the listing.
     *
     * @since    1.0.0
     */
    function ns_featured_posts_add_columns_head() {
        foreach ( $this->options['nsfp_posttypes'] as $post_type ) {
            add_filter('manage_edit-'.$post_type.'_columns', array( $this,'add_featured_column_heading'), 2);
            add_action('manage_'.$post_type.'_posts_custom_column', array( $this,'add_featured_column_content'), 10, 2);
        }
    }

    /**
     * Add heading in the featured column.
     *
     * @since    1.0.0
     */
    function add_featured_column_heading( $columns ) {
        $columns['ns_featured_posts_col'] = __( 'Featured', 'ns-featured-posts' );

        return $columns;
    }

    /**
     * Add column content in the featured column.
     *
     * @since    1.0.0
     */
    function add_featured_column_content( $column, $id ){
        if ( $column == 'ns_featured_posts_col' ){
          $class = '';
          $ns_featured = get_post_meta( $id, '_is_ns_featured_post', true );
          $classes = array('ns_featured_posts_icon');
          if ('yes' == $ns_featured) {
              $classes[] = 'selected';
          }
          echo  '<a id="btn-post-featured_'.$id.'" class="'.implode(' ', $classes).'"></a>';
        }
    }

    /**
     * Function to handle AJAX request.
     *
     * @since    1.0.0
     */
    function nsfp_ajax_featured_post(){
        $ns_featured = $_POST['ns_featured'];
        $id = (int)$_POST['post'];
        if( !empty( $id ) && $ns_featured !== NULL ) {
            if ( $ns_featured == 'no' ){
                delete_post_meta( $id, "_is_ns_featured_post" );
            }
            else {
                update_post_meta( $id, "_is_ns_featured_post", 'yes' );
            }
        }
        wp_send_json_success();
    }

    public function load_assets() {
    	global $pagenow;

    	if ( 'edit.php' !== $pagenow ) {
    	    return;
    	}

    	if ( ! current_user_can( 'unfiltered_html' ) ) {
    	    return;
    	}

    	wp_enqueue_style( 'nspf-admin', NS_FEATURED_POSTS_URL . '/assets/css/admin.css' , array(), '1.0.0' );
    	wp_enqueue_script( 'nspf-admin', NS_FEATURED_POSTS_URL . '/assets/js/admin.js' , array( 'jquery' ), '1.0.0', true );
    }

    /**
     * Add meta box in posts.
     *
     * @since 1.1.0
     */
    function add_featured_meta_boxes() {
      global $typenow;

      $allowed = array();

      foreach ( $this->options['nsfp_posttypes'] as $post_type ) {
          $allowed[] = $post_type;
      }

      if ( ! in_array($typenow,  $allowed )  ) {
          return;
      }

      $screens = $allowed;

      foreach ( $screens as $screen ) {
        add_meta_box( 'nsfp_meta_box_featured', __( 'Featured', 'ns-featured-posts' ), array( $this, 'nsfp_meta_box_featured_callback' ), $screen, 'side' );
      }
    }

    /**
     * Featured meta box callback.
     *
     * @since 1.0.0
     */
    function nsfp_meta_box_featured_callback( $post ){

		$is_ns_featured_post = get_post_meta( $post->ID, '_is_ns_featured_post', true );

      wp_nonce_field( plugin_basename( __FILE__ ), 'nsfp_featured_metabox_nonce' );
      ?>
      <p>
      <label>
	      <input type="hidden" name="nsfp_settings[make_this_featured]" value="0" />
	      <input type="checkbox" name="nsfp_settings[make_this_featured]" value="yes" <?php checked( $is_ns_featured_post, 'yes', true); ?> />
	      <span class="small"><?php _e( 'Check this to make featured.', 'ns-featured-posts' ); ?></span>
      </label>
      </p>
      <?php

    }

    function nsfp_save_meta_box( $post_id ){

      $allowed = array();
      foreach ( $this->options['nsfp_posttypes'] as $post_type ) {
        $allowed[] = $post_type;
      }
      if ( ! in_array( get_post_type( $post_id ),  $allowed )  ) {
        return $post_id;
      }

      // Bail if we're doing an auto save
      if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

      // if our nonce isn't there, or we can't verify it, bail
      if ( ! isset( $_POST['nsfp_featured_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['nsfp_featured_metabox_nonce'], plugin_basename( __FILE__ ) ) )
          return $post_id;

      // if our current user can't edit this post, bail
      if( ! current_user_can( 'edit_post' , $post_id ) )
        return $post_id;

      $featured_value = '';
      if ( isset( $_POST['nsfp_settings']['make_this_featured'] ) && 'yes' == $_POST['nsfp_settings']['make_this_featured'] ) {
        $featured_value = 'yes';
      }
      if ( 'yes' == $featured_value ) {
        update_post_meta( $post_id, '_is_ns_featured_post', $featured_value );
      }
      else{
        delete_post_meta( $post_id, '_is_ns_featured_post' );
      }
      return $post_id;

    }



    /**
     * Filtering dropdown in the post listing.
     *
     * @since    1.0.0
     */
    function nsfp_table_filtering(){
        global $wpdb, $typenow ;
        $allowed = array();
        foreach ( $this->options['nsfp_posttypes'] as $post_type ) {
            $allowed[]= $post_type;
        }
        if ( ! in_array($typenow,  $allowed )  ) {
            return;
        }

        $selected_now = '';

        if ( isset( $_GET['filter-ns-featured-posts'] ) ) {
          $selected_now = esc_attr( $_GET['filter-ns-featured-posts'] );
        }

        echo '<select name="filter-ns-featured-posts" id="filter-ns-featured-posts">';
        echo '<option value="">'. __( 'Show All', 'ns-featured-posts' ) .'</option>';
        echo '<option value="yes" '.selected( $selected_now, 'yes', false ) .'>'. __( 'Featured', 'ns-featured-posts' ) .'</option>';
        echo '<option value="no" '.selected( $selected_now, 'no', false ) .'>'. __( 'Not Featured', 'ns-featured-posts' ) .'</option>';
        echo '</select>';
    }

    /**
     * Query filtering in the post listing.
     *
     * @since    1.0.0
     */
    function nsfp_query_filtering($query){
        global $pagenow;

        $qv = &$query->query_vars;

        if ( is_admin() && $pagenow == 'edit.php'){

            if ( ! isset( $qv['meta_query'] ) ) {
              $qv['meta_query'] = array();
            }

            if( !empty( $_GET['filter-ns-featured-posts'] ) ) {

                if ('yes' == $_GET['filter-ns-featured-posts'] ) {
                    $qv['meta_query'][] = array(
                       'key' => '_is_ns_featured_post',
                       'compare' => '=',
                       'value' => 'yes',
                    );
                } // end if yes

                if ('no' == $_GET['filter-ns-featured-posts'] ) {
                    $qv['meta_query'][] = array(
                       'key' => '_is_ns_featured_post',
                       'compare' => 'NOT EXISTS',
                       'value' => '',
                    );
                } // end if no

            } // end if not empty

            // for filter link
            if ( isset($_GET['post_status']) && 'nsfp' == $_GET['post_status']  ) {
                if ( isset($_GET['featured']) && 'yes' == $_GET['featured']  ) {

                    $qv['meta_query'][] = array(
                       'key' => '_is_ns_featured_post',
                       'compare' => '=',
                       'value' => 'yes',
                    );

                }
            }

        } // end if

    }

    /**
     * Adding filtering link
     */
    function nsfp_filtering_query_for_listing( $wp_query ){

        if( is_admin()) {
            $allowed_posttypes = array();
            foreach ( $this->options['nsfp_posttypes'] as $post_type ) {
                $allowed_posttypes[]= $post_type;
            }
            if ( ! empty( $allowed_posttypes ) ) {
                foreach ( $allowed_posttypes as $val ) {
                    add_filter( 'views_edit-' . $val, array( $this,
                        'nsfp_add_views_link'
                    ));
                }
            }
        }
    }

    /**
     * Adding views link
     */
    function nsfp_add_views_link( $views ){

        $post_type = ( (isset($_GET['post_type']) && $_GET['post_type'] != "" ) ? $_GET['post_type'] : 'post');
        $count = $this->get_total_featured_count($post_type);
        $class = ( isset( $_GET['featured'] ) &&  $_GET['featured'] == 'yes' )  ? "current" : '';
        $args = array(
            'post_type'   => $post_type,
            'post_status' => 'nsfp',
            'featured'    => 'yes',
            );
        $url = esc_url( add_query_arg( $args,  admin_url('edit.php') ) );
        $views['featured'] = '<a href="' . $url . '" class="' . $class . '" >'
            .__('Featured','ns-featured-posts')
            .'<span class="count">'
            . ' ('.$count.') '
            .'</span>'
            .'</a>';

        return $views;
    }

    /**
     * Get total featured count
     */
    function get_total_featured_count( $post_type ) {
        $args = array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'meta_key'       => '_is_ns_featured_post',
			'meta_value'     => 'yes',
        );

        $postlist = get_posts( $args );

        return count( $postlist );
    }

    /**
     * NSFP Widgets
     */
    function nsfp_custom_widgets(){
        register_widget( 'NSFP_Featured_Post_Widget' );
    }

	/**
	 * Render sidebar.
	 *
	 * @since 3.1.1
	 */
	public function render_sidebar() {
		?>
		<div class="sidebox">
			<h3 class="box-heading">Help &amp; Support</h3>
			<div class="box-content">
				<ul>
					<li><strong>Questions, bugs or great ideas?</strong></li>
					<li><a href="http://wordpress.org/support/plugin/ns-featured-posts" target="_blank">Visit our plugin support page</a></li>
					<li><strong>Wanna help make this plugin better?</strong></li>
					<li><a href="http://wordpress.org/support/view/plugin-reviews/ns-featured-posts" target="_blank">Review and rate this plugin on WordPress.org</a></li>
				</ul>
			</div>
		</div><!-- .sidebox -->
		<div class="sidebox">
			<h3 class="box-heading">My Blog</h3>
			<div class="box-content">
				<?php
				$rss = fetch_feed( 'https://www.nilambar.net/category/wordpress/feed' );

				$maxitems = 0;

				$rss_items = array();

				if ( ! is_wp_error( $rss ) ) {
					$maxitems  = $rss->get_item_quantity( 5 );
					$rss_items = $rss->get_items( 0, $maxitems );
				}
				?>

				<?php if ( ! empty( $rss_items ) ) : ?>

					<ul>
						<?php foreach ( $rss_items as $item ) : ?>
							<li><a href="<?php echo esc_url( $item->get_permalink() ); ?>" target="_blank"><?php echo esc_html( $item->get_title() ); ?></a></li>
						<?php endforeach; ?>
					</ul>

				<?php endif; ?>
			</div>
		</div><!-- .sidebox -->
		<?php
	}


} // End class.
