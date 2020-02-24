<?php
/**
 * Plugin Name: GV Design Filter & Search
 * Description: Manage and consolidate custom AJAX filter and search forms.
 * Version: 1.0
 * Author: Hannah Reilly
 * Author URI: https://github.com/hreilly
 */


/**
 * Implement scripts for Plan AJAX filter and search
 */
function plan_filter_search_scripts() {

	wp_enqueue_script( 'plan_filter_search', plugin_dir_url( __FILE__ ) . '/js/pfs-script.js', array('jquery'), '1.0', true );

	wp_enqueue_style( 'option_filter_search', plugin_dir_url( __FILE__ ) . '/css/pfs.css', array(), true );

    wp_localize_script( 'plan_filter_search', 'ajax_url', admin_url('admin-ajax.php') );
}

// Shortcode: [planFilterSearch]
function plan_filter_search_shortcode() {
	
	plan_filter_search_scripts();
	
	ob_start(); ?>
  
  	<div id="plan-filter-search" style="direction: ltr;">
        <form action="" method="get">
            <div class="column-wrap">
                <div class="column" id="plan-types">

					<h2 style="margin: 0 0 40px 0; padding: 0; box-shadow: none; outline: none;"><span class="deco-underline">Filter by Plan Type:</span></h2>

					<input type="radio" id="allPlans" name="product" value="allPlans" checked>
					<label for="allPlans">All Plans</label>

					<input type="radio" id="traditional" name="product" value="traditional">
					<label for="traditional">Traditional</label>

					<input type="radio" id="canvas" name="product" value="canvas">
					<label for="canvas">Canvas</label>

					<input type="radio" id="estates" name="product" value="estates">
					<label for="estates">Estates</label>

                </div>
            </div>
        </form>
		<div id="plan-query">

			<div class="split-page-item-container">
				<?php 
						
				query_posts( array(
					'posts_per_page' => -1,
					'post_type'      => 'plan', 
                    'orderby' => array(
                        'sqft' => 'DESC',
                    ),
					'meta_query'     => array(
						'relation'          => 'AND',
                        'sqft'       => array(
                            'key'           => 'square_feet',
                            'compare'       => 'EXISTS'
                        ),
					)
				) );

				$allPlans = ' ';

				while ( have_posts() ) { the_post();
					$title = get_the_title();
					$permalink = get_permalink();
					$rows = get_field( 'available_elevations' );
					$frow = $rows[0];
					$frow_image = $frow['elevation_image'];
					$beds = get_field( 'bedrooms' );
					$baths = get_field( 'bathrooms' );
					$garage = get_field( 'garage_spaces' );
					$sqft = get_field( 'square_feet' );
					$image = wp_get_attachment_image_src( $frow_image, 'thumbnail' );

					$allPlans .= '<a href="'. $permalink .'" class="split-page-item" style="text-decoration: none;">'
									.'<div class="plan-card">'
										.'<div class="plan-card-content">'
											.'<h3>'. $title .'</h3>'
											.'<div class="plan-card-info">'
												.'<div>'
													.'<h4>'. $beds .'</h4>'
													.'<p>Bedrooms</p>'
												.'</div>'
												.'<div>'
													.'<h4>'. $baths .'</h4>'
													.'<p>Bathrooms</p>'
												.'</div>'
												.'<div>'
													.'<h4>'. $garage .'</h4>'
													.'<p>Car Gar.</p>'
												.'</div>'
												.'<div>'
													.'<h4>'. $sqft .'</h4>'
													.'<p>Feet<sup>2</sup></p>'
												.'</div>'
											.'</div>'
										.'</div>'
										.'<div class="plan-card-image" style="background-image: url(' . $image[0] . ')"></div>'
									.'</div>'
								.'</a>';
				}

				$allPlans .= '</div>'
					.'</div><!-- #plan-query -->'
				.'</div><!-- #plan-filter-search -->';

				wp_reset_query();

				return $allPlans;

				?>
      
	<?php
    return ob_get_clean();
}
  
add_shortcode ('planFilterSearch', 'plan_filter_search_shortcode');

// Ajax Callback

add_action('wp_ajax_plan_filter_search', 'plan_filter_search_callback');
add_action('wp_ajax_nopriv_plan_filter_search', 'plan_filter_search_callback');
  
function plan_filter_search_callback() {
  
	header("Content-Type: application/json"); 
	
	$meta_query = array('relation' => 'AND', 'sqft' => array('key' => 'square_feet', 'compare' => 'EXISTS'),);

	if(isset($_GET['product'])) {
		if($_GET['product'] == "allPlans") {
			$meta_query[] = array(
				'key' => 'product_line',
				'compare' => '='
			);
		} else if($_GET['product'] == "traditional") {
			$meta_query[] = array(
				'key' => 'product_line',
				'value' => 'traditional',
				'compare' => '='
			);
		} else if($_GET['product'] == "canvas") {
			$meta_query[] = array(
				'key' => 'product_line',
				'value' => 'canvas',
				'compare' => '='
			);
		} else if($_GET['product'] == "estates") {
			$meta_query[] = array(
				'key' => 'product_line',
				'value' => 'estates',
				'compare' => '='
			);
		}
		
	}
  
    $args = array(
        'post_type'      => 'plan',
		'posts_per_page' => -1,
		'orderby' => array(
			'sqft' => 'DESC',
		),
        'meta_query'     => $meta_query,
    );
  
    $search_query = new WP_Query( $args );

    if ( $search_query->have_posts() ) {
  
        $result = array();
  
        while ( $search_query->have_posts() ) {
			$search_query->the_post();
			$rows = get_field( 'available_elevations' );
			$frow = $rows[0];
			$frow_image = $frow['elevation_image'];
			$image = wp_get_attachment_image_src( $frow_image, 'thumbnail' );
  
            $result[] = array(
                "id" => get_the_ID(),
                "title" => get_the_title(),
                "permalink" => get_permalink(),
				"product" => get_field('product_line'),
				"image" => $image[0],
				"beds" => get_field( 'bedrooms' ),
				"baths" => get_field( 'bathrooms' ),
				"garage" => get_field( 'garage_spaces' ),
				"sqft" => get_field( 'square_feet' ),
			);
        }
        wp_reset_query();
  
        echo json_encode($result);
  
    } else {
        // no posts found
    }
    wp_die();
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Implement scripts for Option AJAX filter and search
 */
function option_filter_search_scripts() {

	wp_enqueue_script( 'option_filter_search', plugin_dir_url( __FILE__ ) . '/js/ofs-script.js', array('jquery'), '1.0', true );

	wp_enqueue_style( 'option_filter_search', plugin_dir_url( __FILE__ ) . '/css/pfs.css', array(), true );

    wp_localize_script( 'option_filter_search', 'ajax_url', admin_url('admin-ajax.php') );
}

// Shortcode: [optionFilterSearch]
function option_filter_search_shortcode() {
	
	option_filter_search_scripts();

	global $post;
	$taxo = $post->post_name;
	
	ob_start(); ?>
  
  	<div id="option-filter-search" style="direction: ltr;">
        <form action="" method=GET>
            <div class="column-wrap">
                <div class="column" id="plan-types">

					<h2 style="margin: 0 0 40px 0; padding: 0; box-shadow: none; outline: none;"><span class="deco-underline">Filter by Plan Type:</span></h2>

					<input type="radio" id="allPlans" name="product" value="allPlans" checked>
					<label for="allPlans">All Homes</label>

					<input type="radio" id="traditional" name="product" value="traditional">
					<label for="traditional">Traditional</label>

					<input type="radio" id="canvas" name="product" value="canvas">
					<label for="canvas">Canvas</label>

					<input type="radio" id="estates" name="product" value="estates">
					<label for="estates">Estates</label>

					<input type="hidden" id="hidden-option" name="option" value="<?php echo $taxo; ?>">

                </div>
            </div>
        </form>
		<div id="options-query">

			<?php 
				
				$terms = get_terms([
					'taxonomy' => $taxo,
					'hide_empty' => false,
					'meta_query' => array(
						array(
							'key' => 'product_lines',
							'compare' => 'EXISTS'
						)
					)
				]);
									
				if ($terms) {
					foreach ( $terms as $term_obj ) {

						$url = get_term_link($term_obj);
						$img = get_field('option_image', $term_obj);
						$tax = $term_obj->taxonomy;
						$taxName = get_taxonomy($tax)->labels->singular_name;
						$termName = $term_obj->name;
						$standardtoggle = get_field('available_as_standard_feature', $term_obj);
						$thedesc = $term_obj->description;
						$getlength = strlen($thedesc);
						$thelength = 45;
						$truncdesc = rtrim(substr($thedesc, 0, $thelength));
						$lines = get_field('product_lines', $term_obj);
						$linesString = implode(", ", $lines);

						echo '<div class="single-option option-breakout" style="position: relative;">
							<a href="'.$url.'">';
						if ($img != '') {
							echo '<img src="'.$img['sizes']['mini-thumb'].'" width="150px" height="150px" alt=""/>';
						} else {
							echo '<div></div>';
						}
						echo '<div>';
						echo '<p>';
						echo $termName;
						if ($standardtoggle != 'true') {
							echo '<sup>★</sup>';
						} 
						echo '</p>';
						echo '<p>';
						echo $truncdesc;
						if ($getlength > $thelength) echo "...";
						echo '</p>';
						echo '<p style="font-style: italic;">Available in: '.ucwords($linesString).'</p>';
						echo	'</div>
							</a>
						</div>';
					}
				} else {
					echo 'No options found.';
				}

			?>
		</div>
      
	<?php
    return ob_get_clean();
}
  
add_shortcode ('optionFilterSearch', 'option_filter_search_shortcode');

// Ajax Callback

add_action('wp_ajax_option_filter_search', 'option_filter_search_callback');
add_action('wp_ajax_nopriv_option_filter_search', 'option_filter_search_callback');
  
function option_filter_search_callback() {
  
	header("Content-Type: application/json"); 
	
	$meta_query = array('relation' => 'AND',);

	if(isset($_GET['product'])) {
		if($_GET['product'] == "allPlans") {
			$meta_query[] = array(
				'key' => 'product_lines',
				'compare' => 'EXISTS'
			);
		} else if($_GET['product'] == "traditional") {
			$meta_query[] = array(
				'key' => 'product_lines',
				'value' => 'traditional',
				'compare' => 'LIKE'
			);
		} else if($_GET['product'] == "canvas") {
			$meta_query[] = array(
				'key' => 'product_lines',
				'value' => 'canvas',
				'compare' => 'LIKE'
			);
		} else if($_GET['product'] == "estates") {
			$meta_query[] = array(
				'key' => 'product_lines',
				'value' => 'estates',
				'compare' => 'LIKE'
			);
		}
		
	}
	
	$terms = get_terms([
		'taxonomy' => $_GET['option'],
		'hide_empty' => false,
		'meta_query' => $meta_query,
	]);

	if ( $terms ) {

		$result = array();

		foreach ( $terms as $term_obj ) {

			$url = get_term_link($term_obj);
			$img = get_field('option_image', $term_obj);
			$tax = $term_obj->taxonomy;
			$termName = $term_obj->name;
			$standardtoggle = get_field('available_as_standard_feature', $term_obj);
			$thedesc = $term_obj->description;
			$getlength = strlen($thedesc);
			$thelength = 45;
			$truncdesc = rtrim(substr($thedesc, 0, $thelength));
			$lines = get_field('product_lines', $term_obj);
			$linesString = implode(", ", $lines);
			$ucLines = ucwords($linesString);

			$result[] = array(
				"url" => $url,
				"img" => $img['sizes']['mini-thumb'],
				"termName" => $termName,
				"standardtoggle" => $standardtoggle,
				"thedesc" => $thedesc,
				"truncdesc" => $truncdesc,
				"getlength" => $getlength,
				"thelength" => $thelength,
				"ucLines" => $ucLines,
			);
		}
		
		wp_reset_query();

		echo json_encode($result);
  
    } else {
        // no posts found
    }
    wp_die();
}