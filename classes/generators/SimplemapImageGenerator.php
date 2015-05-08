<?php
module_load_include('php', 'simplemap', 'classes/generators/SimplemapGeneratorBase');
module_load_include('php', 'simplemap', 'classes/loaders/NodeImagesLoader');


class SimplemapImageGenerator extends SimplemapGeneratorBase {

  private $node_image_loader;
  private $available_langs;
  private $filename;

  function __construct($path) {
    parent::__construct($path);
    $this->filename = 'sitemap_images';
    $this->node_image_loader = new NodeImagesLoader();
    $this->available_langs = language_list();
  }

  /**
   * Generates the image sitemap file.
   */
  function generate () {
    $nodes = $this->node_image_loader->loadNodesWithImages();
    if (!empty($nodes)) {
      $this->writeSitemap($nodes);
    }
  }

  /**
   * Writes the sitemap file to disk.
   * @param $nodes array Array of nodes with images associated.
   */
  private function writeSitemap(array $nodes) {
    $this->writer->openURI("{$this->path}/{$this->filename}.xml");
    $this->writer->startDocument('1.0', 'UTF-8');
    $this->writer->setIndent(4);
    $this->writer->startElement('urlset');
    $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    $this->writer->writeAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');

      foreach ($nodes as $node) {
        $this->generateURLEntry($node);
      }

    $this->writer->endElement();
    $this->writer->endDocument();
    $this->writer->flush();
  }


  /**
   * Generates a <url> entry in sitemap file.  Each entry contains a <loc>
   * child and multiple <image:image> childs.
   * @param $node stdClass Node object to generate a <url> entry for.
   */
  private function generateURLEntry(stdClass $node) {
    $node_images = $this->node_image_loader->loadImagesFromNode($node->nid);
    $node_url = function () use ($node) {
      $url_options = ['absolute'  => true];
      // The language option must only be included if the node specifies a
      // language.
      if ($node->language !== LANGUAGE_NONE) {
        $url_options['language'] = $this->available_langs[$node->language];
      }
      return url("node/{$node->nid}", $url_options);
    };

    $this->writer->startElement('url');
      $this->writer->writeElement('loc', $node_url());
      foreach ($node_images as $image) {
        $this->writer->startElement('image:image');
        $this->writer->writeElement('image:loc', file_create_url($image->uri));
        $this->writer->endElement();
      }
    $this->writer->endElement();
  }

}
