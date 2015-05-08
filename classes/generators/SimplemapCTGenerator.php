<?php
module_load_include('php', 'simplemap', 'classes/generators/SimplemapGeneratorBase');
module_load_include('php', 'simplemap', 'classes/helpers/SitemapDefinitionsLoader');


class SimplemapCTGenerator extends SimplemapGeneratorBase {

  private $language;
  private $sitemap_definition_loader;
  private $filename;
  
  function __construct($path, $filename, $language = null) {
    parent::__construct($path);
    $this->filename = $filename;
    $this->language = $language;
    $this->sitemap_definition_loader = new SitemapDefinitionsLoader();
  }

  public function generate() {
    $nodes = $this->sitemap_definition_loader->loadSitemapNodes($this->filename, $this->language);
    if ($nodes) {
      $this->writeSitemap($nodes);
    }
  }

  private function writeSitemap(array $nodes) {
    // Create the subfolder for the selected language if it does not exist.
    $path = $this->getFilePath();
    if (!realpath($path)) {
      mkdir($path);
    }

    $this->writer->openURI("{$path}/{$this->filename}.xml");
    $this->writer->startDocument('1.0', 'UTF-8');
    $this->writer->setIndent(4);
    $this->writer->startElement('urlset');
    $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

      foreach ($nodes as $node) {
        $this->generateNodeEntry($node);
      }

    $this->writer->endElement();
    $this->writer->endDocument();
    $this->writer->flush();
  }

  private function generateNodeEntry($node) {
    $date = function () use ($node) {
      return date('Y-m-d', $node->changed);
    };
    $nodeurl = function () use ($node) {
      return url("node/{$node->nid}", [
        'absolute'  => true,
        'language'  => $this->language,
      ]);
    };
    $this->writer->startElement('url');
      $this->writer->writeElement('loc', $nodeurl());
      $this->writer->writeElement('lastmod', $date());
    $this->writer->endElement();
  }


  /**
   * Gets the path where the generated sitemap must be stored.
   * @return string Absolute path where the sitemap file must be stored.
   */
  private function getFilepath() {
    // If this sitemap is for a specific language the generated file will be
    // stored in a subdirectory named as the language code.
    $lang_path = ($this->language) ? "/{$this->language->language}": "";
    return "{$this->path}{$lang_path}";
  }
}
