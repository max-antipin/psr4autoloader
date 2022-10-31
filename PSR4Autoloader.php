<?php

/*
 * Autoloader class (https://www.php-fig.org/psr/psr-4/).
 *
 * (c) Max Antipin <max.v.antipin@gmail.com>
 *
 */

namespace MaxieSystems;

final class PSR4Autoloader
{
    public function __construct(array $conf, bool $use_map, string $base_dir = null)
    {
        $this->base_dir = ($base_dir ?: __DIR__) . DIRECTORY_SEPARATOR;
        foreach ($conf as $prefix => $c) {
            $this->addNamespace($prefix, $c);
        }
        if ($use_map) {
            $this->map = (include $this->getClassMapFileName()) ?: [];
        }
    }

    public function __invoke(string $class_name): void
    {
        if (null === $this->map) {
            foreach ($this->conf as $prefix => $conf) {
                if (strncmp($prefix, $class_name, $conf['len']) === 0) {
                    $f = str_replace('\\', DIRECTORY_SEPARATOR, substr($class_name, $conf['len'])) . '.php';
                    foreach ($conf['dirs'] as $dir) {
                        $file = $dir . $f;
                        if (file_exists($file)) {
                            require $file;
                            return;
                        }
                    }
                }
            }
        } elseif (isset($this->map[$class_name])) {
            require $this->map[$class_name];
            return;
        }
    }

    public function addNamespace(string $prefix, array $conf): self
    {
        $prefix .= '\\';
        if (!isset($this->conf[$prefix])) {
            $this->conf[$prefix] = ['len' => strlen($prefix), 'dirs' => [], ];
        }
        $k = 'base_dir';
        if (isset($conf[$k])) {
            $base_dir = $conf[$k] . DIRECTORY_SEPARATOR;
            unset($conf[$k]);
        } else {
            $base_dir = $this->base_dir;
        }
        foreach ($conf as $v) {
            $this->conf[$prefix]['dirs'][] = $base_dir . ('' === $v ? '' : $v . DIRECTORY_SEPARATOR);
        }
        return $this;
    }

    public function createClassMap(): array
    {
        $files = [];
        foreach ($this->conf as $conf) {
            foreach ($conf['dirs'] as $dir) {
                $directory = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
                $iterator = new \RecursiveIteratorIterator($directory);
                foreach ($iterator as $info) {
                    $p = $info->getRealPath();
                    if ('.php' === substr($p, -4)) {
                        try {
                            require_once($p);
                            $files[$p] = str_replace('/', '\\', substr($iterator->getSubPathname(), 0, -4));
                        } catch (\Error $e) {
                            print($e->getMessage());
                        }
                    }
                }
            }
        }
        $map = [];
        foreach (get_declared_classes() as $class_name) {
            $rc = new \ReflectionClass($class_name);
            $fname = $rc->getFileName();
            foreach ($this->conf as $prefix => $conf) {
                if (
                    strncmp($prefix, $class_name, $conf['len']) === 0
                    && isset($files[$fname])
                    && substr($class_name, $conf['len']) === $files[$fname]
                ) {
                    $map[$class_name] = $fname;
                }
            }
        }
        return $map;
    }

    public static function updateClassMap(): void
    {
        $map = [];
        foreach (spl_autoload_functions() as $f) {
            if ($f instanceof self) {
                # We must unload map as we renew it.
                $f->map = null;
                foreach ($f->createClassMap() as $k => $v) {
                    if (isset($map[$k])) {
                        throw new \Exception("Class $k already mapped");
                    }
                    $map[$k] = $v;
                }
            }
        }
        // file_put_contents(self::getClassMapFileName(), '<?php return ' . var_export($map, true) . ';');
        var_dump($map);
    }

    public static function getClassMapFileName(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'psr4classmap.php';
    }

    public function __debugInfo()
    {
        return [];
    }

    private array $conf = [];
    private ?array $map = null;
    private string $base_dir;
}
