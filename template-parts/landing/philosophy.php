<?php
defined( 'ABSPATH' ) || exit;
$data = brio_get_landing_philosophy_data( get_queried_object_id() );
add_filter( 'brio_philosophy_data', function() use ( $data ) { return $data; } );
get_template_part( 'template-parts/home/philosophy' );
