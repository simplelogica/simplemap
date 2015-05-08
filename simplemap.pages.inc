<?php


/**
 * Outputs the sitemap.xml file.
 */
function simplemap_output_xml() {
  $filepath = simplemap_get_basepath() . "/sitemap.xml";

  // Transfer file in 16 KB chunks to save memory usage.
  if ($fd = fopen($filepath, 'rb')) {
    drupal_add_http_header('Content-type', 'text/xml; charset=utf-8');
    drupal_add_http_header('Cache-Control', 'must-revalidate');
    drupal_add_http_header('X-Robots-Tag', 'noindex, follow');
    drupal_send_headers();
    while (!feof($fd)) {
      print fread($fd, 1024*16);
    }
    fclose($fd);
  } else {
    drupal_not_found();
  }
  drupal_exit();
}

