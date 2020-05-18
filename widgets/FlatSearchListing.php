<?php
/**
 * This is a flat search widget, that does not use AJAX, it is meant to work with shoots.video,
 * so it does not implement all searching features, just the region and category
 *
 * @since Listify 1.0.0
 */
class __FlatSearchWP_Query extends \WP_Query {

  /**
   * Extend order clause with own columns.
   *
   * @param string $order_by
   *
   * @return bool|false|string
   */
  protected function parse_orderby( $order_by ) {
    $parent_orderby = parent::parse_orderby( $order_by );

    if ( $parent_orderby ) {
      // WordPress knew what to do => keep it like that
      return $parent_orderby;
    }

    // whitelist some fields we extended
    $additional_allowed = array(
      'last_comment_date',
    );

    if ( ! in_array( $order_by, $additional_allowed, true ) ) {
      // not allowed column => early exit here
      return false;
    }

    // Default: order by post field.
    global $wpdb;
    return $wpdb->posts . '.' . sanitize_key( $order_by );
  }
}
class FlatSearchListing extends Listify_Widget {
	public function __construct() {
		$this->widget_description = __( 'Display Listings and Search as without AJAX', 'listify' );
		$this->widget_id          = 'listify_flat_search_flat_listing_widget';
		$this->widget_name        = __( 'Flat Search Listings', 'listify' );
		//$this->widget_areas       = array( 'widget-area-home','widget-area-page' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Title:', 'Flat Search Listings' ),
			),
			'icon'  => array(
				'type'  => 'text',
				'std'   => 'ion-reply',
				'label' => '<a href="http://ionicons.com/" target="_blank">' . __( 'Icon Class:', 'listify' ) . '</a>',
			),
		);
		parent::__construct();
	}

	function widget( $args, $instance ) {
		global $wpdb, $job_preview, $post;
		if ( 'publish' != $post->post_status ) {
			return;
		}
        global  $job_manager, 
                $comments_widget_title, 
                $comments_widget_icon, 
                $comments_widget_before_title, 
                $comments_widget_after_title;
		extract( $args );
        $widget_title = apply_filters( 
            'widget_title', 
            isset( $instance['title'] ) ? $instance['title'] : '', 
            $instance, 
            $this->id_base 
        );
        $result = $this->get_listings();
        if ( $result->have_posts() ) {
            $listings = $result->get_posts();
        } else {
            $listings = [];
        } 
		ob_start();
        $widget_id = uniqid();
        ?>
            <h1 class="listify-flat-search-widget-title"><?php echo apply_filters('page_title','Search Results'); ?></h1>
            <div class="listify-flatsearch-widget-container" id="<?php echo $widget_id; ?>">
                <aside id="listify_flatsearch_aside">
                    <div class="flatsearch-cover">
				        <div class="cover-wrapper container">
                            <?php 
                                ob_start();
                                $atts = [
                                    'per_page' => 25,
                                    'show_categories' => true,
                                    'categories' => $_REQUEST['search_categories'],
                                    'selected_category' => $_REQUEST['search_categories'],
                                    'location' => $_REQUEST['search_location'],
                                    'show_category_multiselect' => true,
                                ];
                                get_job_manager_template(
                                    'job-filters.php',
                                    array(
                                        'per_page'                  => $atts['per_page'],
                                        'show_categories'           => $atts['show_categories'],
                                        'categories'                => $atts['categories'],
                                        'selected_category'         => $atts['selected_category'],
                                        'job_types'                 => $atts['job_types'],
                                        'atts'                      => $atts,
                                        'location'                  => $atts['location'],
                                        'show_category_multiselect' => $atts['show_category_multiselect'],
                                        'show_filters' => false,
                                    )
                                );
                                $partial = ob_get_contents();
                                ob_end_clean();
                                echo $partial;
                            ?>                    
                            <style type="text/css">
                                div.archive-job_listing-filter-title {
                                    display: none;
                                }
                            </style>
                            <script type="text/javascript">
                                (function ($) {
                                    let l = '<?php echo isset($_REQUEST['search_location']) ? $_REQUEST['search_location'] : ''; ?>'; 
                                    let c = '<?php echo isset($_REQUEST['search_categories']) ? json_encode($_REQUEST['search_categories']) : '[]'; ?>'; 
                                    $(function () {
                                        let root = $('#<?php echo $widget_id; ?>'); 
                                        root.find('#search_location').val(l);
                                    });
                                })(jQuery);
                            </script>
                        </div>
                    </div>
                    <?php foreach ( $listings as $p ) { ?>
                        <div class="listify-flat-search-listing">
                            <?php
                                echo \ListifyFlatSearch\render_template('widgets/views/listing.php',['p' => $p]);
                            ?>
                        </div>
                    <?php } ?> 
                    <div class="pagination">
                        <?php 
                            echo paginate_links( array(
                                'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                                'total'        => $result->max_num_pages,
                                'current'      => max( 1, get_query_var( 'paged' ) ),
                                'format'       => '?paged=%#%',
                                'show_all'     => false,
                                'type'         => 'plain',
                                'end_size'     => 2,
                                'mid_size'     => 1,
                                'prev_next'    => true,
                                'prev_text'    => sprintf( '<i></i> %1$s', __( 'Newer Referrals', 'text-domain' ) ),
                                'next_text'    => sprintf( '%1$s <i></i>', __( 'Older Referrals', 'text-domain' ) ),
                                'add_args'     => false,
                                'add_fragment' => '',
                            ) );
                        ?>
                    </div>
                </aside>
            </div>
        <?php
		echo apply_filters( $this->widget_id, ob_get_clean() );
	}
    public function get_listings() {
		global $wpdb;
        $search_locations = isset($_REQUEST['search_location']) ? $_REQUEST['search_location'] : [];
        if ( ! is_array($search_locations) ) 
            $search_locations = [$search_locations];
        $search_categories = isset($_REQUEST['search_categories']) ? $_REQUEST['search_categories'] : []; 
        $offset = isset($_REQUEST['offset']) ? intval($_REQUEST['offset']) : 0;
        $args = [
            'post_type' => 'job_listing',
            'post_status' => 'publish',
            'posts_per_page' => 25,
            'paged' => max(1,get_query_var('paged')),
            'orderby' => 'last_comment_date',
            'order' => 'desc',
            'tax_query' => [],
            'meta_query' => [],
            'cache_results' => false,
            'fields' => 'all',
        ];
        if ( ! empty($search_locations) ) {
            $location_meta_keys = array( 'geolocation_formatted_address', '_job_location', 'geolocation_state_long', );
            $location_search = [ 'relation' => 'OR' ];
            foreach ( $location_meta_keys as $meta_key ) {
                $location_search[] = array(
                      'key'     => $meta_key,
                      'value'   => $search_locations[0],
                      'compare' => 'like',
                );
            }
			$location_by_ids_meta_keys = ['_job_region_region_0','_job_region_region_1','_job_region_region_2','_job_region_region_3','_job_region_region_4'];
			$tid = $wpdb->get_results("SELECT term_id FROM {$wpdb->terms} WHERE name LIKE '%{$search_locations[0]}%' LIMIT 1");
			if ( !empty($tid) ) {
				$tid = $tid[0]->term_id;
				foreach ( $location_by_ids_meta_keys as $meta_key ) {
					$location_search[] = array(
						  'key'     => $meta_key,
						  'value'   => $tid,
						  'compare' => 'like',
					);
				}
			}
            $args['meta_query'][] = $location_search;
        }
        if ( ! empty($search_categories) ) {
            $field = is_numeric( $search_categories[0] ) ? 'term_id' : 'slug';
            $operator = 'all' === get_option( 'job_manager_category_filter_type', 'all' ) && count( $search_categories ) > 1 ? 'AND' : 'IN';
            $args['tax_query'][] = array(
                 'taxonomy'         => 'job_listing_category',
                 'field'            => $field,
                 'terms'            => array_values( $search_categories ),
                 'include_children' => 'AND' !== $operator,
                 'operator'         => $operator,
            );
        }
        do_action('before_get_job_listings',$args,[]);
        $result = new __FlatSearchWP_Query($args);
        do_action('after_get_job_listings',$args,[]);
        return $result;
    }
}
