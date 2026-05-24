<?php
defined( 'ABSPATH' ) || exit;
$data = brio_get_landing_faqs_data( get_queried_object_id() );
add_filter( 'brio_faqs_data', function() use ( $data ) { return $data; } );
get_template_part( 'template-parts/home/faqs' );
