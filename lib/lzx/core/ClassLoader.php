<?php

namespace lzx\core;

class ClassLoader
{

    private $namespaces = array();

    private function __construct()
    {
        if ( \spl_autoload_register( array($this, 'loadClass') ) === FALSE )
        {
            throw new \Exception( 'failed to register autoload function' );
        }
    }

    // Singleton methord for each database
    /**
     * @return ClassLoader
     */
    public static function getInstance()
    {
        static $instance;

        if ( !isset( $instance ) )
        {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Gets the configured namespaces.
     *
     * @return array A hash with namespaces as keys and directories as values
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Registers an array of namespaces
     *
     * @param array $namespaces An array of namespaces (namespaces as keys and locations as values)
     *
     * @api
     */
    public function registerNamespaces( array $namespaces )
    {
        foreach ( $namespaces as $namespace => $path )
        {
            $this->registerNamespace( $namespace, $path );
        }
    }

    /**
     * Registers a namespace.
     *
     * @param string       $namespace The namespace
     * @param array|string $paths     The location(s) of the namespace
     *
     * @api
     */
    public function registerNamespace( $namespace, $path )
    {
        if ( !\is_string( $namespace ) )
        {
            throw new \InvalidArgumentException( 'invalid namespace name for autoload registration' );
        }
        //if(!is_string($path) || substr($, $start))
        $this->namespaces[\trim( $namespace, '\\' )] = DIRECTORY_SEPARATOR . \trim( $path, DIRECTORY_SEPARATOR );
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class The name of the class
     */
    public function loadClass( $class )
    {
        $pos = \strpos( $class, '\\' );
        if ( $pos === FALSE || $pos == 0 )
        {
            //$class = substr($class, 1);
            // we do not define and use user classes in global scope. please use namespace.
            throw new \Exception( 'autoloader is trying to load a global class : this should not happen' );
        }

        // namespaced class name
        $namespace = \substr( $class, 0, $pos );

        if ( !\array_key_exists( $namespace, $this->namespaces ) )
        {
            throw new \Exception( 'unregistered namespace : ' . $namespace );
        }
        $file = $this->namespaces[$namespace] . \str_replace( '\\', DIRECTORY_SEPARATOR, \substr( $class, $pos ) ) . '.php';

        if ( \is_file( $file ) && \is_readable( $file ) )
        {
            require $file;
        }
        else
        {
            throw new \ErrorException( 'failed to load class : ' . $class );
        }
    }

}
