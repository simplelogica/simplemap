<?php


/**
 * This class provides the basis for all Sitemap Generators.  If you want to
 * create a custom generator it must extend this class and implement the
 * 'generate' method.
 */
abstract class SimplemapGeneratorBase {

  protected $path;
  protected $writer;


  function __construct($path) {
    $this->path = $path;
    $this->writer = new XMLWriter();
  }

  /**
   * Generates the sitemap file.
   */
  abstract function generate();
}
