<?php
defined( 'ABSPATH' ) || exit;
$data = brio_get_landing_showcase_data( get_queried_object_id() );
add_filter( 'brio_showcase_data', function() use ( $data ) { return $data; } );
get_template_part( 'template-parts/home/showcase' );
