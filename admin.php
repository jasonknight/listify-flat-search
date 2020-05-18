<?php
namespace ListifyFlatSearch\Admin;
/*
	We only want the plugin to run on certain pages.
*/
function should_run() {
    global $pagenow;
    return true;
}

function init() {
   if ( !should_run() ) 
       return;
}
\add_action('admin_init', __NAMESPACE__ . '\init');

