<?php namespace Tranquility\App;

use \Tranquility\Support\ArrayHelper as Arr;
use \Symfony\Component\Finder\Finder as Finder;

class Config implements \ArrayAccess {
    /**
     * Set of configuration items
     * 
     * @var array
     */
    protected $items = [];

    /**
     * Create a new configuration repository
     * 
     * @param  array  $items
     * @return void
     */
    public function __construct(array $items = []) {
        $this->items = $items;
    }
    
    /**
     * Loads configuration files into object
     * 
     * @param string $path Relative path to the folder containing config files to load.
     * @return void
     */
    public function load(string $path) {
        // If no path is specified, use the default path
        if (is_null($path) || file_exists($path) == false) {
            throw new \Exception("Unable to find specified path for configuration files: ".$path);
        }
        $configPath = realpath($path);

        // Load all PHP files in the specified path
        $files = [];
        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            // Check if file has been found in a nested directory
            $directory = $file->getPath();
            $directory = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR);

            // Add file to list
            $key = $directory.basename($file->getRealPath(), '.php');
            $files[$key] = $file->getRealPath();
        }

        foreach ($files as $key => $path) {
            $arrayValues = require($path);
            $this->set($key, $arrayValues);
        }
    }

    /** 
     * Determine if the given configuration value exists
     * 
     * @param  string  $key
     * @return bool
     */
    public function has($key) {
        return Arr::has($this->items, $key);
    }

    /** 
     * Get the specified configuration value
     * 
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null) {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Set a given configuration value
     * 
     * @param  array|string  $key
     * @param  mixed         $value
     * @return void
     */
    public function set($key, $value = null) {
        // If configuration value is supplied as a single item, convert it into an array
        $keys = $key;
        if (is_array($key) == false) {
            $keys = [$key => $value];
        }

        foreach ($keys as $key => $value) {
            Arr::set($this->items, $key, $value);
        }
    }

    /** 
     * Prepend a value onto an array configuration value
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function prepend($key, $value) {
        $array = $this->get($key);
        array_unshift($array, $value);
        $this->set($key, $array);
    }

    /**
     * Push a value onto an array configuration value
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function push($key, $value) {
        $array = $this->get($key);
        $array[] = $value;
        $this->set($key, $array);
    }

    /**
     * Get all configuration items 
     * 
     * @return array
     */
    public function all() {
        return $this->items;
    }

    /**
     * Determine if the given key exists in the configuration item set
     * 
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key) {
        return $this->has($key);
    }

    /**
     * Get a configuration option
     * 
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key) {
        return $this->get($key);
    }

    /**
     * Set a configuration option
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function offsetSet($key, $value) {
        return $this->set($key, $value);
    }

    /**
     * Unset a configuration option
     * 
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key) {
        $this->set($key, null);
    }
}