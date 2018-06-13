<?php namespace Tranquility\System\ORM\Extensions\TablePrefix;

use \Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use \Doctrine\ORM\Mapping\ClassMetaDataInfo;

class TablePrefixExtension {
    /**
     * String to use as database table name prefix
     * 
     * @var string
     */
    protected $prefix = '';

    /** 
     * Create a Doctrine extension for handling table prefixes
     * 
     * @param  string  $prefix  String to use as database table name prefix
     * @return void
     */
    public function __construct($prefix = '') {
        $this->prefix = (string)$prefix;
    }

    /**
     * Inject table prefix into requests for table metadata
     * 
     * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs  $eventArgs
     * @return void
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs) {
        $classMetadata = $eventArgs->getClassMetadata();

        // Apply the table prefix to the main table
        if ($classMetadata->isInheritanceTypeSingleTable() == false || $classMetadata->getName() === $classMetadata->rootEntityName) {
            $tableName = $this->prefix.$classMetadata->getTableName();
            $classMetadata->setPrimaryTable(['name' => $tableName]);
        }

        // If the event involves associations, apply the table prefix to associated tables as well
        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide'] == true) {
                $mappedTableName = $mapping['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix.$mappedTableName;
            }
        }
    }
}
