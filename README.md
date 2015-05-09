# simplemap
_simplemap_ is a simple sitemap generator for Drupal 7.


## Features
_simplemap_ has the following features:

- Generate sitemaps conforming to the
  [_sitemaps.org_ specification](http://www.sitemaps.org/).
- Generate image sitemaps, conforming to the
  [_Google_ specification](https://support.google.com/webmasters/answer/178636).
- Support for creating sitemaps for single or multiple content types.
- Support for internationalized sitemaps.
- Automatically update the created sitemaps during Cron execution.
- Drush task for manually launching the sitemap creation.
- Support for extension with custom sitemap generators.


## Installation
Clone this repository into the modules directory of your Drupal site.  The
preferred path for contributed modules is into `sites/all/modules/contrib`.

When _simplemap_ is copied into the modules directory enable it with the
following command:

```shell
drush en simplemap
```

When _simplemap_ is enabled you should rebuild the registry cache so Drupal can
detect the new hooks that it provides.  The simplest way to rebuild the cache
is with the command:

```shell
drush cc all
```


## Configuration

### Adding Content to Sitemaps
In **admin/settings/simplemap/inclusions**, _simplemap_ provides a configuration
panel in which you can create sitemaps and attach content types to them.

You can select one or more content types and the specify the name of the sitemap
that will contain them (the _.xml_ extension will be added automatically).  
If you select the _Internationalize_ option, a sitemap will be created for each
language available in your site.

### Excluding Content from Sitemaps
In **admin/settings/simplemap/exclusions**, _simplemap_ provides a configuration
panel in which you can specify which nodes should not appear in the generated
sitemaps.


## Extensibility
As stated in the features, _simplemap_'s functionality can be extended with
custom sitemap generators.

1. Create your own generator class.  It must extend the _SimplemapGeneratorBase_
   abstract class provided by _simplemap_ and implement the _generate()_ method,
   which will be automatically called during _simplemap_ execution.

    ```php
    <?php
    module_load_include('php', 'simplemap', 'classes/generators/SimplemapGeneratorBase');

    /**
    * Example custom sitemap generator. It is MANDATORY that you extend the
    * SimplemapGeneratorBase abstract class, which provides the following variables:
    *  - $path: the path where simplemap stores the generated sitemaps.  Any
    *    sitemap stored outside of this path will not be automatically detected.
    *  - $writer: XMLWriter instance that you can use to write the sitemap content.
    */
    class MyCustomGenerator extends SimplemapGeneratorBase {

      private $repo_url;

      public function __construct($path) {
        parent::__construct($path);
        $this->repo_url = 'https://github.com/simplelogica/simplemap';
      }

      public function generate() {
        watchdog('mymodule', "I'm being called by Simplemap!");
        $this->writer->openURI("{$path}/my_custom_sitemap.xml");
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->startElement('urlset');
        $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

          $this->writer->startElement('url');
          $this->writer->writeElement('loc', $repo_url);
          $this->writer->endElement();

        $this->writer->endElement();
        $this->writer->endDocument();
        $this->writer->flush();
      }
    }
    ```

2. Implement _hook_register_simplemap_generator_ from your module.  All you
   have to do is return the name of your custom generator class.

   ```php
   <?php
   /**
    * Implements hook_register_simplemap_generator().
    */
   function mymodule_register_simplemap_generator() {
     // Remember to include the file wich contains your generated class.
     return 'MyCustomGenerator';
   }
   ```
