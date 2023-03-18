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
    public function __construct(array $namespaces, string $base_dir = '')
    {
        $this->base_dir = ($base_dir ?: __DIR__) . DIRECTORY_SEPARATOR;
        foreach ($namespaces as $prefix => $dirs) {
            if (is_array($dirs)) {
                $this->addNamespace($prefix, ...$dirs);
            } else {
                $this->addNamespace($prefix, $dirs);
            }
        }
        spl_autoload_register($this);
    }

    public function __invoke(string $fqcn): void
    {
        foreach ($this->prefixes as $prefix => $p) {
            if (strncmp($prefix, $fqcn, $p['len']) === 0) {
                $p['require'](
                    str_replace('\\', DIRECTORY_SEPARATOR, substr($fqcn, $p['len'])) . '.php',
                    ...$p['dirs']
                );
                return;
            }
        }
    }

    final public function addNamespace(string $prefix, string ...$dirs): self
    {
        $prefix .= '\\';
        if (!isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = ['len' => strlen($prefix), 'dirs' => [], ];
        }
        foreach ($dirs as $dir) {
            if ('' === $dir) {
                throw new \ValueError('Empty directory name');
            }
            $this->prefixes[$prefix]['dirs'][] = $this->base_dir . $dir . DIRECTORY_SEPARATOR;
        }
        $this->prefixes[$prefix]['require'] = [
            $this,
            count($this->prefixes[$prefix]['dirs']) > 1 ? 'requireIfExists' : 'requireFile'
        ];
        return $this;
    }

    public function __debugInfo()
    {
        return [];
    }

    private function requireFile(string $f, string $dir): void
    {
        require $dir . $f;
    }

    private function requireIfExists(string $f, string ...$dirs): void
    {
        foreach ($dirs as $dir) {
            $file = $dir . $f;
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }

    private array $prefixes = [];
    private readonly string $base_dir;
}
