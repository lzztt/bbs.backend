<?php declare(strict_types=1);

namespace lzx\core;

use ErrorException;
use Exception;
use InvalidArgumentException;

class ClassLoader
{
    private $namespaces = [];

    private function __construct()
    {
        if (spl_autoload_register([$this, 'loadClass']) === false) {
            throw new Exception('failed to register autoload function');
        }
    }

    // Singleton methord for each database

    public static function getInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    public function getNamespaces()
    {
        return $this->namespaces;
    }

    public function registerNamespaces(array $namespaces)
    {
        foreach ($namespaces as $namespace => $path) {
            $this->registerNamespace($namespace, $path);
        }
    }

    public function registerNamespace($namespace, $path)
    {
        if (!is_string($namespace)) {
            throw new InvalidArgumentException('invalid namespace name for autoload registration');
        }
        //if(!is_string($path) || substr($, $start))
        $this->namespaces[trim($namespace, '\\')] = DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR);
    }

    public function loadClass($class)
    {
        $pos = strpos($class, '\\');
        if ($pos === false || $pos == 0) {
            //$class = substr($class, 1);
            // we do not define and use user classes in global scope. please use namespace.
            throw new Exception('autoloader is trying to load a global class: ' . $class . ' (this should not happen)');
        }

        // namespaced class name
        $namespace = substr($class, 0, $pos);

        if (!array_key_exists($namespace, $this->namespaces)) {
            throw new Exception('unregistered namespace : ' . $namespace);
        }
        $file = $this->namespaces[$namespace] . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $pos)) . '.php';

        if (is_file($file) && is_readable($file)) {
            require $file;
        } else {
            throw new ErrorException('failed to load class : ' . $class);
        }
    }
}
