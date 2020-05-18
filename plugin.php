<?php
/**
 * Plugin Name: Listify Flat Search
 * Plugin URI: https://lycanthropenoir.com
 * Description: Plugin to provide flat listings for listify with no AJAX
 * Version: 1.0
 * Author: Jason Martion <contact@lycanthropenoir.com>
 * Author URI: https://app.codeable.io/tasks/new?preferredContractor=43500&ref=76T6q
 * License: GPL2
 */
namespace ListifyFlatSearch;
require_once(__DIR__ . '/core.php');
require_once(__DIR__ . '/admin.php');

function init() {
    
}
\add_action('init', __NAMESPACE__ . '\init');
function get_listing_data($p) {
    global $wpdb;
    $data = [];
    $data = apply_filters('listify_flat_search_get_listing_data',$data,$p);
    if ( ! empty($data) )
        return $data;
    ob_start();
    listify_the_listing_secondary_image($p,[ 'type' => 'avatar', 'size' => 'thumbnail', 'style' => 'circle']);
    $image = ob_get_contents();
    ob_end_clean();
    $image = str_replace('listing-entry-company-image--style-circle','',$image);
    $image = str_replace('listing-entry-company-image--type-avatar','',$image);
    if ( $js ) {
        if ( preg_match("/src=\"([^\"]+)\"/",$image,$m) ) {
            $image = $m[1];
        } else {
            $image = "";
        }
    }
    $image = apply_filters('listify_flat_search_get_listing_data_image',$image);
    ob_start();
    listify_the_listing_rating($p);
    $rating = ob_get_contents();
    ob_end_clean();
    $lines = array_filter(explode("\n",$rating),function ($l) { return !empty(trim($l));});
    $new_lines = [];
    foreach ( $lines as $l ) { $new_lines[] = $l; }
    $lines = $new_lines;
    $new_rating = '';
    $ref_count = '';
    $found = false;
    for ( $i = 1; $i < count($lines) - 1; $i++ ) {
        $l = $lines[$i];
        if ( preg_match('/listing.rating.count/',$l) )
           $found = true; 
        if ( $found == false )
            $new_rating .= $l;
        if ( $found == true )
            $ref_count .= $l;
    }
    $rating = $lines[0] . $new_rating . $lines[count($lines)-1]; 
    $ref_count = $lines[0] . $ref_count . $lines[count($lines)-1]; 
    if ( $js ) {
        if ( preg_match("/(\d+)/",$ref_count,$m) ) {
            $ref_count = $m[1];
        } else {
            $ref_count = 0;
        }
    }
    $rating = apply_filters('listify_flat_search_get_listing_data_rating',$rating);
    $text = apply_filters('the_job_description',$p->post_content);
    $text = str_replace('class="comment-stars"', 'style="display:none;"',$text);
    $text = wp_kses_post('"'. wp_trim_words($text,30,null));
    if ( $js ) {
        $text = html_entity_decode(strip_tags($text)) . '"';
    } else {
        $text .= '" &nbsp;<a href="'.$permalink.'">More</a>';
    }
    ob_start();
        listify_the_listing_location($p);
        $location = ob_get_contents();
    ob_end_clean();
    if ( $js ) {
        $location = strip_tags($location);
    }
    $data = [
        'image' => $image,
        'rating' => $rating, 
        'ref_count' => $ref_count,
        'text' => $text,
        'location' => $location,
        'permalink' => get_permalink($p),
        'title' => str_replace('&','&amp;',html_entity_decode(get_the_title($p))),
    ];
    $data = apply_filters('listify_flat_search_get_listing_data_end',$data);
    return $data;
}
