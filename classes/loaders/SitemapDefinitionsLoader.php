<?php
module_load_include('php', 'simplemap', 'classes/loaders/ExcludedNodesLoader');


class SitemapDefinitionsLoader {

  private $excluded_loader;

  function __construct() {
    $this->excluded_loader = new ExcludedNodesLoader();
  }

  function loadCTsInSitemap($sitemap = null) {
    $query = db_select('simplemap_content_types', 's');
    $query->join('node_type', 't', 's.content_type = t.type');
    $query->fields('t', ['type', 'name', 'description']);
    if (isset($sitemap)) {
      $query->condition('s.sitemap_name', $sitemap);
    }
    $result = $query->execute();
    return $result->fetchAllAssoc('type');
  }

  function loadDefinitions() {
    $query = db_select('simplemap_content_types', 't')
      ->fields('t', ['content_type', 'sitemap_name', 'internationalize'])
      ->groupBy('t.sitemap_name')
      ->execute();
    return $query->fetchAllAssoc('sitemap_name');
  }


  function loadSitemapNodes($sitemap, $language = null) {
    $excluded_nids = $this->excluded_loader->loadExcludedNids();

    $query = db_select('node', 'n');
    $query->join('simplemap_content_types', 's', 'n.type = s.content_type');
    $query->fields('n');
    $query->condition('s.sitemap_name', $sitemap, '=');
    // Filter nodes by language.  Nodes that are not suposed to be translated
    // must show in all languages.
    if (isset($language)) {
      $query->condition('n.language', [$language->language, LANGUAGE_NONE], 'IN');
    }
    // Avoid excluded nodes.
    if (!empty($excluded_nids)) {
      $query->condition('n.nid', $excluded_nids, 'NOT IN');
    }
    $result = $query->execute();
    return $result->fetchAllAssoc('nid');
  }

}
