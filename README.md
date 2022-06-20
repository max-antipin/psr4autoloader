# PSR-4 Autoloading without Composer
Basic Usage
-----------
~~~php
<?php
require 'path-to-includes/PSR4Autoloader.php';
spl_autoload_register(new \MaxieSystems\PSR4Autoloader([
    'VendorNamespace' => ['directory'],
    'AnotherVendor\\SubNamespace' => ['another/dir'],
]));
~~~
