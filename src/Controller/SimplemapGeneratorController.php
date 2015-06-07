<?php
module_load_include('php', 'simplemap', 'src/Generator/SimplemapCTGenerator');
module_load_include('php', 'simplemap', 'src/Generator/SimplemapImageGenerator');
module_load_include('php', 'simplemap', 'src/Generator/SimplemapIndexGenerator');
module_load_include('php', 'simplemap', 'src/Loader/SitemapDefinitionsLoader');


class SimplemapGeneratorController {

  private $external_generators;
  private $base_path;

  function __construct($external_generators = []) {
    $this->external_generators = $external_generators;
    $this->base_path = simplemap_get_basepath();
  }

  function generate() {
    $this->createSitemapsDir();
    $this->generateCTsSitemap();
    $this->generateImagesSitemap($this->base_path);

    foreach ($this->external_generators as $generator_name) {
      // We instantiate an object using its classname and make sure it extends
      // the SimplemapGeneratorBase class.
      $generator = new $generator_name($this->base_path);
      if (!is_subclass_of($generator, 'SimplemapGeneratorBase')) {
        throw new Exception("The class {$generator_name} must extend SimplemapGeneratorBase");
      }
      $generator->generate();
    }

    $this->generateIndexSitemap();
  }


  private function generateCTsSitemap() {
    $generateSitemap = function($definition, $lang = null) {
      $filename = $definition->sitemap_name;
      $path = $this->base_path;
      $generator = new SimplemapCTGenerator($path, $filename, $lang);
      $generator->generate();
    };

    $sm_loader = new SitemapDefinitionsLoader();
    foreach ($sm_loader->loadDefinitions() as $sm_definition) {
      if ($sm_definition->internationalize) {
        foreach ($this->getEnabledLanguages() as $lang) {
          $generateSitemap($sm_definition, $lang);
        }
      } else {
        $generateSitemap($sm_definition);
      }
    }
  }

  private function generateImagesSitemap() {
    $generator = new SimplemapImageGenerator($this->base_path);
    $generator->generate();
  }

  private function generateIndexSitemap() {
    $generator = new SimplemapIndexGenerator($this->base_path);
    $generator->generate();
  }

  private function createSitemapsDir() {
    // If the folder already exists, delete it before creating a new one.
    $dir = $this->base_path;
    if (realpath($dir)) {
      $this->rrmdir($dir);
    }
    mkdir($dir);
  }


  /**
   * Recursively delete a directory that is not empty
   * Taken from http://php.net/rmdir
   */
  private function rrmdir($dir) {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
        }
      }
      reset($objects);
      rmdir($dir);
    }
  }

  /**
   * Returns an array containing only the enabled languages.
   */
  private function getEnabledLanguages() {
    return array_filter(language_list(), function ($lang) {
      return $lang->enabled;
    });
  }


}
