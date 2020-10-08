<?php namespace Tranquillity\System\Enums;

// Tranquillity class libraries
use Tranquillity\System\Enums\AbstractEnum as AbstractEnum;
use Tranquillity\Documents\ErrorCollectionDocument;
use Tranquillity\Documents\RelationshipDocument;
use Tranquillity\Documents\ResourceCollectionDocument;
use Tranquillity\Documents\ResourceDocument;

/**
 * Enumeration of entity types
 *
 * @package Tranquillity\System\Enums
 * @author  Andrew Patterson <patto@live.com.au>
 */

class DocumentTypeEnum extends AbstractEnum {
    // Document types
    const ErrorCollection    = 'errors';
    const Relationship       = 'relationship';
    const Resource           = 'resource';
    const ResourceCollection = 'resources';

    private static $_documentClasses = array(
        // Business objects
        self::ErrorCollection    => ErrorCollectionDocument::class,
        self::Relationship       => RelationshipDocument::class,
        self::Resource        	 => ResourceDocument::class,
        self::ResourceCollection => ResourceCollectionDocument::class
    );

    /**
     * Get the document class name for the specified document type
     *
     * @param string $documentType
     * @return string
     */
    public static function getDocumentClassname($documentType) {
        if (array_key_exists($documentType, self::$_documentClasses) == false) {
            throw new \Exception("Unable to find a document class that represents document type '".$documentType."'.");
        }

        return self::$_documentClasses[$documentType];
    }
}