<?php
/**
 * @package Outbound_Links_JSON_Generator
 * @version 0.1
 */
/*
Plugin Name: Outbound Links JSON Generator
Plugin URI: http://danq.me/
Description: A basic implementation of https://blog.jim-nielsen.com/2022/well-known-links-resource/. Run using WP-CLI with `wp outbound-links`.
Author: Dan Q
Version: 0.1
Author URI: http://danq.me/
*/

/**
 * TODO LIST:
 *
 * [ ] Turn into a nice reusable class, not just an WP-CLI script
 * [ ] Allow adding to WP-Cron
 * [ ] Provide an admin interface from the web backend
 * [ ] Consider indexing comments
 * [ ] Consider supporting ?domain= queries
 * [ ] Think about other common links out (syndication links?)
 */

function init_outbound_links_generator() {
	/**
	 * Generates .well-known/links
	 */
	$outbound_links_json_generator = function( $args, $assoc_args ) {
		$output_data = [];
		$site_url = get_site_url();
		$query = new WP_Query( [
			'post_type'      => [ 'post', 'page' ],
			'has_password'   => false,
			'posts_per_page' => -1,
		] );
		$pc_complete = 0;
		while ( $query->have_posts() ) {
			$query->the_post();
			// Parse content
			$content = new DOMDocument();
			$content->loadHTML( get_the_content() );
			$links = $content->getElementsByTagName('a');
			foreach ($links as $link){
				$href = $link->getAttribute('href');
				if( ! $href ) continue; // ignore naked anchors
				if( substr_compare( $href, $site_url, 0, strlen( $site_url ) ) == 0 ) continue; // ignore internal links
				$href_parts = parse_url( $href );
				if( ! $href_parts ) continue; // ignore malformed URLs
				// Add to output data
				$output_data[ $href_parts['host'] ] ??= [
					'domain' => $href_parts['host'],
					'count'  => 0,
					'links'  => [],
				];
				$output_data[ $href_parts['host'] ]['count']++;
				$output_data[ $href_parts['host'] ]['links'][] = [
					'sourceUrl' => get_the_permalink(),
					'targetUrl' => $href,
				];
			}
			// Update progress bar
			$new_pc_complete = floor(($query->current_post + 1) / $query->found_posts * 100);
			if($new_pc_complete > $pc_complete) {
				$pc_complete = $new_pc_complete;
				fwrite(STDOUT, "${pc_complete}% ... ");
			}
		}
		fwrite(STDOUT, 'Finished! ' . PHP_EOL);
		mkdir( ABSPATH . '/.well-known' );
		file_put_contents( ABSPATH . '/.well-known/links', json_encode( array_values( $output_data ) ) );

		WP_CLI::success( 'Created/updated ' . $site_url . '/.well-known/links' );
	};
	if (class_exists('WP_CLI_Command')) {
		WP_CLI::add_command( 'outbound-links', $outbound_links_json_generator );
	}
}

add_action('plugins_loaded', 'init_outbound_links_generator');
