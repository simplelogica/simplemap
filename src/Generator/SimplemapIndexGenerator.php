<?php
module_load_include('php', 'simplemap', 'src/Generator/SimplemapGeneratorBase');


class SimplemapIndexGenerator extends SimplemapGeneratorBase {

  private $filename;

  function __construct($path) {
    parent::__construct($path);
    $this->filename = 'sitemap';
  }

  /**
   * Generates the sitemap index file.
   */
  function generate() {
    $this->writer->openURI("{$this->path}/{$this->filename}.xml");
    $this->writer->startDocument('1.0', 'UTF-8');
    $this->writer->setIndent(4);
    $this->writer->startElement('sitemapindex');
    $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

      foreach ($this->getSitemaps() as $sitemap) {
        // Avoid the index to contain itself.  We don't want to break
        // Google, do we?
        if ($sitemap->filename != "{$this->filename}.xml") {
          $this->writer->startElement('sitemap');
            $this->writer->writeElement('loc', file_create_url($sitemap->uri));
          $this->writer->endElement();
        }
      }

    $this->writer->endElement();
    $this->writer->endDocument();
    $this->writer->flush();
  }


  private function getSitemaps() {
    return file_scan_directory('public://simplemap', '/.*\.xml$/');
  }
}
