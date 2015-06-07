<?php
module_load_include('php', 'simplemap', 'src/Loader/ExcludedNodesLoader');


function simplemap_exclusions_form() {
  $create_rows = function () {
    $loader = new ExcludedNodesLoader();
    $excluded = $loader->loadExcludedNodes();
    $available_langs = language_list();
    return array_map(function ($node) use ($available_langs) {
      // The link must be created in the original node language, not in the
      // current user language.
      $node_link = l($node->title, "node/{$node->nid}", [
        'language' => $available_langs[$node->language]
      ]);
      $delete_link = l(t('Delete from Exclusion'), "admin/settings/simplemap/exclusions/{$node->nid}/delete");
      return [$node->nid, $node_link, $delete_link];
    }, $excluded);
  };

  return [
    'new_exclusion' => [
      '#type' => 'fieldset',
      '#title'  => t('Add a new node to the exclusion list'),

      'nid' => [
        '#type'     => 'textfield',
        '#title'    => t('NID'),
        '#required' => true,
      ],
      'submit'  => [
        '#type'   => 'submit',
        '#value'  => t('Add to the Exclusion List'),
      ],
    ],

    'existing_exclusions' => [
      '#type'   => 'fieldset',
      '#title'  => t('Nodes Excluded from Sitemaps'),
      'list'    => [
        '#theme'  => 'table',
        '#header' => [t('NID'), t('Title'), ''],
        '#rows'   => $create_rows(),
      ],
    ],
  ];
}


function simplemap_exclusions_form_submit($form, &$form_state) {
  $input = $form_state['input'];
  db_insert('simplemap_exclusions')
    ->fields(['nid' => $input['nid']])
    ->execute();
  drupal_set_message(t('The node @nid has been excluded from the sitemap', [
    '@nid' => $input['nid'],
  ]));
}

function simplemap_exclusions_form_validate($form, &$form_state) {
  $input = $form_state['input'];
  if (!is_numeric($input['nid'])) {
    form_set_error('nid', t('The NID must be a number'));
  }
}

function delete_from_exclusion($nid) {
  db_delete('simplemap_exclusions')
    ->condition('nid', $nid)
    ->execute();
  drupal_set_message(t('The node @nid has been removed from the exclusion list', [
    '@nid' => $nid,
  ]));
  drupal_goto('admin/settings/simplemap/exclusions');
}
