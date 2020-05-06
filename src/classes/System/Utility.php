<?php namespace Tranquility\System;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;

// Utility library classes
use Ramsey\Uuid\Uuid as UuidGenerator;

/**
 * Utility class containing useful shortcut methods
 *
 * @package \Tranquility
 * @author  Andrew Patterson <patto@live.com.au>
 */
 
class Utility {
	/**
	 * Constructor
	 * Cannot be instantiated - should be static only
	 */
	final public function __construct() {
		throw new \Exception('\Tranquility\System\Utility class may not be instantiated');
	}
	
	/**
	 * Extracts the value for a specified key from an array or object
	 *
	 * @param mixed   $object    The array or object the value is stored in
	 * @param string  $key       The identifier for the value
	 * @param mixed   $default   [Optional] The value to return if no value is found in $object (defaults to null)
	 * @param string  $dataType  [Optional] The datatype to cast the returned value to
	 * @return mixed             The extracted value
	 */
	public static function extractValue($object, $key, $default = null, $dataType = null) {
		$value = null;
		
		// Determine if the object is an array or an actual object
		if (is_array($object) && isset($object[$key])) {
			$value = $object[$key];
		} elseif (is_object($object) && isset($object->$key)) {
			$value = $object->$key;
		}
		
		// If no value was extracted, return the default value
		if (is_null($value)) {
			return $default;
		}
		
		// Perform type casting on return value
		$dataType = strtolower($dataType);
		switch($dataType) {
			case 'string':
			case 'str':
				// Cast to string
				$value = strval($value);
				break;
			case 'integer':
			case 'int':
				// Cast to integer
				$value = intval($value);
				break;
			case 'float':
			case 'double':
				// Cast to decimal
				$value = floatval($value);
				break;
			case 'boolean':
			case 'bool':
				// Cast to boolean
				$value = (bool)$value;
				break;
			default:
				// No type cast necessary
				break;
		}
		
		// Return extracted value
		return $value;
	}

	/**
	 * Represents a universally unique identifier (UUID), according to RFC 4122. Generate a version 4 (random) UUID.
	 *
	 * @return string
	 */
	public static function generateUuidV4() {
		return UuidGenerator::uuid4()->toString();
	}

	/**
     * Generate a fully qualified base URL (including custom base path, if applicable)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return string
     */
    public static function getBaseUrl(ServerRequestInterface $request) {
        $uri = $request->getUri();
        $scheme = $uri->getScheme();
        $authority = $uri->getAuthority();
        $basePath = $request->getAttribute('basePath', '');

        $urlString = ($scheme !== '' ? $scheme.':' : '') . ($authority ? '//'.$authority : '') . rtrim($basePath, '/');
        return $urlString;
    }

	/**
     * Generate fully qualified URL from a route
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request            PSR7 request
     * @param string                                    $routeName          Name of the route to be generated
     * @param array                                     $routeParams        Route parameter values
	 * @param array                                     $queryStringParams  Query string parameter values
     * @return string
     */
    public static function getRouteUrl(ServerRequestInterface $request, string $routeName, array $routeParams = [], array $queryStringParams = []) {
        $uri = $request->getUri();
        $routeParser = $request->getAttribute('routeParser');
        return $routeParser->fullUrlFor($uri, $routeName, $routeParams, $queryStringParams);
    }
}