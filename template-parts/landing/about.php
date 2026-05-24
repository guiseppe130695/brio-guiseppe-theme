<?php
defined( 'ABSPATH' ) || exit;
$data = brio_get_landing_about_data( get_queried_object_id() );
// Inject into the homepage template via filter
add_filter( 'brio_about_data', function() use ( $data ) { return $data; } );
get_template_part( 'template-parts/home/about' );
