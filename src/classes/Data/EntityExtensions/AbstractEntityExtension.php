<?php namespace Tranquility\Data\EntityExtensions;

abstract class AbstractEntityExtension {

    /** Retrieves the set of publically accessible fields for the entity extension object
     * 
     * @return array
     * @abstract
     */
    abstract public function getPublicFields();

    /**
     * Retrieves the value for an entity extension property
     * 
     * @param string $name  Property name
     * @throws Exception
     * @return mixed
     */
    public function __get($name) {
        $methodName = '_get'.ucfirst($name);
        if (method_exists($this, $methodName)) {
            // Use custom function to retrieve value
            return $this->{$methodName}();
        } elseif (in_array($name, $this->getPublicFields())) {
            // Retrieve value directly
            return $this->$name;
        } else {
            throw new \Exception('Cannot get property value - class "'.get_class($this).'" does not have a property named "'.$name.'"');
        }
    }

    /**
     * Set the value for an entity extension property
     * 
     * @param string $name  Property name
     * @param mixed $value  Property value
     * @throws Exception 
     * @return void
     */
    public function __set($name, $value) {
        $methodName = '_set'.ucfirst($name);
        if (method_exists($this, $methodName)) {
            // Use custom function to set value
            $this->{$methodName}($value);
        } elseif (in_array($name, $this->getPublicFields())) {
            // Store value directly
            if ($value !== '') {
                $this->$name = $value;
            }
        } else {
            throw new \Exception('Cannot set property - class "'.get_class($this).'" does not have a property named "'.$name.'"');
        }
    }
}