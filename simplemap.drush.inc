<?php


/**
 * Implements hook_drush_command().
 */
function simplemap_drush_command() {
  return [
    'simplemap-generate' => [
      'description' => 'Launch the Sitemap Generation',
      'aliases'     => ['sg'],
      'callback'    => 'simplemap_drush_generate',
    ],
  ];
}

function simplemap_drush_generate() {
  echo "Simplemap is working it's magic...\n";
  simplemap_launch_generation();
  echo "Done!\n";
  echo "Sitemaps generated in public://simplemap\n";
}
