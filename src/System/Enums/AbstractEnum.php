<?php namespace Tranquillity\System\Enums;

abstract class AbstractEnum {
	
	/**
	 * Constructor
	 * Cannot be instantiated - Enums should be static only
	 */
	final public function __construct() {
		throw new Exception('\Tranquillity\System\Enums classes may not be instantiated');
	}
	
	/**
	 * Retrieve the set of values defined in the enumeration
	 *
	 * @param string $className [Optional] If a class is not supplied, will check the calling class
	 * @return array            Array of defined labels and values in the enumeration
	 * @throws \Tranquillity\Exception
	 */
	final public static function getValues($className = null) {
		// If no class name is specified, use the current class
		if ($className === null) {
			$className = get_called_class();
		}
		
		// Check that enumeration class exists
		if (!class_exists($className)) {
			throw new Exception('Specified Enum class "'.$className.'" does not exist');
		}
		
		$reflector = new \ReflectionClass($className);
		if (!$reflector->isSubclassOf('\Tranquillity\System\Enums\AbstractEnum')) {
			throw new Exception('Specified class "'.$className.'" does not extend \Tranquillity\System\Enums\AbstractEnum');
		}

		// Return the set of values
		$values = array_values($reflector->getConstants());
		return $values;
	}
	
	/**
	 * Determines whether the supplied value is a defined value in the enumeration
	 *
	 * @param string $enumValue Enum value to verify
	 * @param string $className [Optional] If a class is not specified, will check the calling class
	 * @return boolean          True if value is defined in the enumeration, otherwise false
	 */
	final public static function isValidValue($enumValue, $className = null) {
		return self::_validateEnumAspect('value', $enumValue, $className);
	}
	
	/**
	 * Determines whether the supplied label is a defined label in the enumeration
	 *
	 * @param string $enumLabel Enum label to verify
	 * @param string $className [Optional] If a class is not specified, will check the calling class
	 * @return boolean          True if label is defined in the enumeration, otherwise false
	 */
	final public static function isValidLabel($enumLabel, $className = null) {
		return self::_validateEnumAspect('label', $enumLabel, $className);
	}
	
	/**
	 * Determines whether the supplied label or value is a defined label in the enumeration
	 *
	 * @param string $mode       Either 'value' or 'label'
	 * @param string $searchTerm Enum value or label to verify
	 * @param string $className  [Optional] If a class is not specified, will check the calling class
	 * @return boolean           True if value or label is defined in the enumeration, otherwise false
	 * @throws \Tranquillity\Exception
	 */
	final private static function _validateEnumAspect($mode, $searchTerm, $className = null) {
		// If no class name is specified use the current class
		if ($className === null) {
			$className = get_called_class();
		}
		
		// Check that enumeration class exists
		if (!class_exists($className)) {
			throw new Exception('Specified Enum class "'.$className.'" does not exist');
		}
		
		$reflector = new \ReflectionClass($className);
		if (!$reflector->isSubclassOf('\Tranquillity\System\Enums\AbstractEnum')) {
			throw new Exception('Specified class "'.$className.'" does not extend \\Tranquillity\\System\\Enums\\AbstractEnum');
		}
		
		// Attempt to match values
		foreach ($reflector->getConstants() as $label => $value) {
			if ($mode == 'value' && $value == $searchTerm) {
				return true;
			}
			
			if ($mode == 'label' && $label == $searchTerm) {
				return true;
			}
		}
		return false;
	}
}