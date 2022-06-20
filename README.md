# PSR-4 Autoloading without Composer

The MaxieSystems\PSR4Autoloader class implements PSR-4 autoloading.

This class is PSR-12 compliant.

Basic Usage
-----------
~~~php
<?php
require 'path-to-includes/PSR4Autoloader.php';
spl_autoload_register(new \MaxieSystems\PSR4Autoloader([
    'VendorNamespace' => ['directory'],
    'AnotherVendor\\SubNamespace' => ['another/dir'],
], false));
~~~

First argument defines a mapping from namespaces to paths, relative to the autoloader directory.

Namespace prefixes must NOT end in \ (backslash).
