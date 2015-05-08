<?php
module_load_include('php', 'simplemap', 'classes/loaders/ContentTypesLoader');
module_load_include('php', 'simplemap', 'classes/loaders/SitemapDefinitionsLoader');


function simplemap_inclusions_form($form, &$form_state) {
  $cthelper = new ContentTypesLoader();
  $smhelper = new SitemapDefinitionsLoader();

  // Loads the available Content-Types (those who don't have a sitemap defined).
  $load_available_cts = function () use ($cthelper) {
    $ct_without_sitemap = $cthelper->loadNotInSitemap();
    return array_reduce($ct_without_sitemap, function ($result, $ctype) {
        $result[$ctype->type] = $ctype->name;
      return $result;
    }, []);
  };

  // Creates a row for the Content-Types list.
  $create_rows = function () use ($cthelper, $smhelper) {
    $sitemap_defs = $smhelper->loadDefinitions();
    return array_reduce($sitemap_defs, function ($res, $sitemap_def) use ($smhelper) {
      // Load the names of all the content types in this sitemap.
      $content_types = $smhelper->loadCTsInSitemap($sitemap_def->sitemap_name);
      $ct_names = array_map(function ($ct) {
        return $ct->name;
      }, $content_types);

      $internationalize = $sitemap_def->internationalize ? t('Yes') : t('No');
      $delete_link = l(t('Delete Sitemap'), "admin/settings/simplemap/inclusions/{$sitemap_def->sitemap_name}/delete");
      $row = [implode(', ', $ct_names), $sitemap_def->sitemap_name, $internationalize, $delete_link];
      array_push($res, $row);
      return $res;
    }, []);
  };

  $form = [
    'new_inclusion' => [
      '#type'       => 'fieldset',
      '#title'      => t('Add a New Content Type to the Sitemap'),

      'content_type' => [
        '#type'     => 'select',
        '#title'    => t('Content Type'),
        '#options'  => $load_available_cts(),
        '#multiple' => true,
        '#required' => true,
      ],
      'sitemap_name'  => [
        '#type'     => 'textfield',
        '#title'    => t('Name of the Sitemap File'),
        '#required' => true,
      ],
      'internationalize'  => [
        '#type'     => 'checkbox',
        '#title'    => t('Internationalize'),
      ],

      'submit' => [
        '#type' => 'submit',
        '#value' => t('Add to the Sitemap'),
      ],
    ],

    'existing_sitemaps' => [
      '#type'   => 'fieldset',
      '#title'  => t('Already Defined Sitemaps'),
      'list'    => [
        '#theme'  => 'table',
        '#header' => [t('Content Types'), t('Sitemap'), t('Internationalize'), ''],
        '#rows'   => $create_rows(),
      ],
    ],
  ];
  return $form;
}


function simplemap_inclusions_form_submit($form, $form_state) {
  $input = $form_state['input'];

  foreach ($input['content_type'] as $ctype) {
  db_insert('simplemap_content_types')
    ->fields([
      'content_type'      => $ctype,
      'sitemap_name'      => $input['sitemap_name'],
      'internationalize'  => $input['internationalize'] ? 1 : 0,
    ])->execute();
  }

  drupal_set_message(t("The sitemap '@sm' has been created successfuly", [
    '@sm' => $input['sitemap_name'],
    ]));
}


function simplemap_inclusions_form_validate($form, $form_state) {
  // It would be cool to validate the sitemap name...
}


function delete_sitemap($sitemap_name) {
  db_delete('simplemap_content_types')
    ->condition('sitemap_name', $sitemap_name)
    ->execute();
  drupal_set_message(t("The sitemap '@sm' has been deleted", [
    '@sm' => $sitemap_name,
    ]));
  drupal_goto('admin/settings/simplemap/inclusions');
}
