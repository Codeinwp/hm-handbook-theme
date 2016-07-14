<?php

namespace HM_Handbook;

use \DateTime;
use \WP_Query;
use \WP_REST_Request;

add_action( 'wp_enqueue_scripts', function() {

	if ( ! is_singular() ) {
		return;
	}

	wp_localize_script( 'hm-handbook', 'HMHandbookPageHistory', [
		'strings' => [
			'listTitle' => __( 'Page History', 'hm-handbook' ),
			'loadMore'  => __( 'Load more revisions', 'hm-handbook' ),
		],
		'post_id'   => get_the_ID(),
		'api_base'  => home_url( '/wp-json/hm-handbook/v1/' ),
		'api_nonce' => wp_create_nonce( 'wp_rest' ),
	] );

}, 20 );

add_action( 'rest_api_init', function () {
	register_rest_route(
		'hm-handbook/v1',
		'/posts/(?P<id>\d+)/revisions',
		[
			'methods' => 'GET',
			'callback' => __NAMESPACE__ . '\\get_revisions_response',
			'args' => [
				'id' => [
					'sanitize_callback' => 'absint',
					'validate_callback' => function( $param, $request, $key ) {
						return is_numeric( $param );
					}
				],
				'paged' => [
					'sanitize_callback' => 'absint',
					'validate_callback' => function( $param, $request, $key ) {
						return is_numeric( $param );
					}
				],
			],
		]
	);

} );

/**
 * Get post revisions API request response.
 *
 * @param  \WP_REST_Request $request Request
 *
 * @return \WP_REST_Response Response
 */
function get_revisions_response( WP_REST_Request $request ) {

	$revisions = [];

	$query = new WP_Query( [
		'post_parent'    => $request->get_param( 'id' ),
		'post_type'      => 'revision',
		'post_status'    => 'inherit',
		'posts_per_page' => 5,
		'paged'          => $request->get_param( 'paged' ),
	] );

	foreach ( $query->posts as $revision ) {

		$date   = new DateTime( $revision->post_modified_gmt );
		$author = get_userdata( $revision->post_author );

		$revisions[] = [
			'id'      => $revision->ID,
			'content' => $revision->post_content,
			'date'    => $date->format( 'j M y @ H:i' ),
			'author'  => get_the_author_meta( 'display_name', $revision->post_author ),
		];

	};

	return rest_ensure_response( array(
		'revisions' => $revisions,
		'hasMore'   => $request->get_param( 'paged' ) < $query->max_num_pages,
	) );

}
