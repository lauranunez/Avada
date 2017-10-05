<?php

function theme_enqueue_styles() {
 wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'parent-style' ) );
}

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 99 );


function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );


function my_search_form($html)
{
    return str_replace('Search ...', 'Buscador ...', $html);
}
add_filter('get_search_form', 'my_search_form');




 
register_nav_menus( array(
	'social' => __( 'Social', 'avada' )
) );



if ( ! function_exists( 'avada_render_post_metadata' ) ) {
	/**
	 * Render the full meta data for blog archive and single layouts.
	 *
	 * @param string $layout    The blog layout (either single, standard, alternate or grid_timeline).
	 * @param string $settings HTML markup to display the date and post format box.
	 * @return  string
	 */
	function avada_render_post_metadata( $layout, $settings = array() ) {

		$html = $author = $date = $metadata = '';

		$settings = ( is_array( $settings ) ) ? $settings : array();

		$default_settings = array(
			'post_meta'          => Avada()->settings->get( 'post_meta' ),
			'post_meta_author'   => Avada()->settings->get( 'post_meta_author' ),
			'post_meta_date'     => Avada()->settings->get( 'post_meta_date' ),
			'post_meta_cats'     => Avada()->settings->get( 'post_meta_cats' ),
			'post_meta_tags'     => Avada()->settings->get( 'post_meta_tags' ),
			'post_meta_comments' => Avada()->settings->get( 'post_meta_comments' ),
		);

		$settings = wp_parse_args( $settings, $default_settings );

		// Check if meta data is enabled.
		if ( ( $settings['post_meta'] && 'no' != get_post_meta( get_queried_object_id(), 'pyre_post_meta', true ) ) || ( ! $settings['post_meta'] && 'yes' == get_post_meta( get_queried_object_id(), 'pyre_post_meta', true ) ) ) {

			// For alternate, grid and timeline layouts return empty single-line-meta if all meta data for that position is disabled.
			if ( in_array( $layout, array( 'alternate', 'grid_timeline' ) ) && ! $settings['post_meta_author'] && ! $settings['post_meta_date'] && ! $settings['post_meta_cats'] && ! $settings['post_meta_tags'] && ! $settings['post_meta_comments'] ) {
				return '';
			}



			// Render author meta data.
			if ( $settings['post_meta_author'] ) {
				ob_start();
				the_author_posts_link();
				$author_post_link = ob_get_clean();

				// Check if rich snippets are enabled.
				if ( ! Avada()->settings->get( 'disable_date_rich_snippet_pages' ) ) {
					$metadata .= sprintf( esc_html__( '%s', 'Avada' ), '<span>' . $author_post_link . '</span>' );
				} else {
					$metadata .= sprintf( esc_html__( '%s', 'Avada' ), '<span class="vcard"><span class="fn">' . $author_post_link . '</span></span>' );
				}
				$metadata .= '<span class="fusion-inline-sep">|</span>';
			} else { // If author meta data won't be visible, render just the invisible author rich snippet.
				$author .= avada_render_rich_snippets_for_pages( false, true, false );
			}

			 $post_type = get_post_type( get_the_ID() );
    if  ($post_type == 'noticias' )
        $metadata .= '<span class="meta-post"><a href=" ' . get_post_type_archive_link($post_type) .' ">Noticias</a></span>';
   

      $post_type = get_post_type( get_the_ID() );
    if  ($post_type == 'servicios' )
        $metadata .= '<span class="meta-post"><a href="http://cognicase.com.mx/servicios/">Servicios</a></span>';
    // Servicios lo tengo que meter así, ya que al poner has archive=false, aquí no lo coge

     $post_type = get_post_type( get_the_ID() );
    if  ($post_type == 'page' )
        $metadata .= '<span class="meta-post"><a href=" ' . get_page_link(ID) .' ">' . get_the_title($postID) .'</a></span>';




			// Render the updated meta data or at least the rich snippet if enabled.
			if ( $settings['post_meta_date'] ) {
				$metadata .= avada_render_rich_snippets_for_pages( false, false, true );

				$formatted_date = get_the_time( Avada()->settings->get( 'date_format' ) );
				$date_markup = '<span class="meta-date">' . $formatted_date . '</span><span class="fusion-inline-sep">|</span>';
				$metadata .= apply_filters( 'avada_post_metadata_date', $date_markup, $formatted_date );
			} else {
				$date .= avada_render_rich_snippets_for_pages( false, false, true );
			}

			// Render rest of meta data.
			// Render categories.
			if ( $settings['post_meta_cats'] ) {
				ob_start();
				the_category( ' , ' );
				$categories = ob_get_clean();

				if ( $categories ) {
					$metadata .= 'BLOG: ';
					$metadata .= ( $settings['post_meta_tags'] ) ? sprintf( esc_html__( 'BLOG: %s', 'Avada' ), $categories ) : $categories;
					$metadata .= '<span class="fusion-inline-sep">|</span>';
				}
			}



			// Render tags.
			if ( $settings['post_meta_tags'] ) {
				ob_start();
				the_tags( '' );
				$tags = ob_get_clean();

				if ( $tags ) {
					$metadata .= '<span class="meta-tags">' . sprintf( esc_html__( 'Tags: %s', 'Avada' ), $tags ) . '</span><span class="fusion-inline-sep">|</span>';
				}
            }

 
            
			// Render comments.
			if ( $settings['post_meta_comments'] && 'grid_timeline' !== $layout ) {
				ob_start();
				comments_popup_link( esc_html__( '0 Comments', 'Avada' ), esc_html__( '1 Comment', 'Avada' ), esc_html__( '% Comments', 'Avada' ) );
				$comments = ob_get_clean();
				$metadata .= '<span class="fusion-comments">' . $comments . '</span>';
			}

			// Render the HTML wrappers for the different layouts.
			if ( $metadata ) {
				$metadata = $author . $date . $metadata;

				if ( 'single' == $layout ) {
					$html .= '<div class="fusion-meta-info"><div class="fusion-meta-info-wrapper">' . $metadata . '</div></div>';
				} elseif ( in_array( $layout, array( 'alternate', 'grid_timeline' ) ) ) {
					$html .= '<p class="fusion-single-line-meta">' . $metadata . '</p>';
				} else {
					$html .= '<div class="fusion-alignleft">' . $metadata . '</div>';
				}
			} else {
				$html .= $author . $date;
			}
		} else {
			// Render author and updated rich snippets for grid and timeline layouts.
			if ( Avada()->settings->get( 'disable_date_rich_snippet_pages' ) ) {
				$html .= avada_render_rich_snippets_for_pages( false );
			}
		}

		return apply_filters( 'avada_post_metadata_markup', $html );
	}
}