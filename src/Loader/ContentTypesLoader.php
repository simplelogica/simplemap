<?php
module_load_include('php', 'simplemap', 'src/Loader/SitemapDefinitionsLoader');


class ContentTypesLoader {

  private $sitemap_loader;


  function __construct() {
    $this->sitemap_loader = new SitemapDefinitionsLoader();
  }


  function loadAll() {
    $result = db_select('node_type', 't')
      ->fields('t', ['type', 'name', 'description'])
      ->execute();
    return $result->fetchAllAssoc('type');
  }


  function loadNotInSitemap() {
    $in_sitemap = array_keys($this->sitemap_loader->loadCTsInSitemap());
    return array_filter($this->loadAll(), function ($ctype) use ($in_sitemap) {
      return !in_array($ctype->type, $in_sitemap);
    });
  }
}
