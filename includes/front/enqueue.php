<?php

function ju_enqueue(){
    $uri                =   get_theme_file_uri();
    $ver                =   JU_DEV_MODE ? time() : false;

    wp_register_style( 'ju_fonts', $uri . '/assets/css/fonts.css', [], $ver );
    wp_register_style( 'ju_variables', $uri . '/assets/css/variables.css', [ 'ju_fonts' ], $ver );
    wp_register_style( 'ju_font_icons', $uri . '/assets/css/font-icons.css', [], $ver );
    wp_register_style( 'ju_header', $uri . '/assets/css/header.css', [ 'ju_variables' ], $ver );
    wp_register_style( 'ju_header_responsive', $uri . '/assets/css/header-responsive.css', [ 'ju_header' ], $ver );
    wp_register_style( 'ju_footer', $uri . '/assets/css/footer.css', [ 'ju_variables' ], $ver );

    wp_enqueue_style( 'ju_fonts' );
    wp_enqueue_style( 'ju_variables' );
    wp_enqueue_style( 'ju_font_icons' );
    wp_enqueue_style( 'ju_header' );
    wp_enqueue_style( 'ju_header_responsive' );
    wp_enqueue_style( 'ju_footer' );
    
    wp_register_script( 'ju_plugins', $uri . '/assets/js/plugins.js', [], $ver, true );
    wp_register_script( 'ju_functions', $uri . '/assets/js/functions.js', [], $ver, true );

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'ju_plugins' );
    wp_enqueue_script( 'ju_functions' );
}