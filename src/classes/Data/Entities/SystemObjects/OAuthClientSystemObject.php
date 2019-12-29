<?php namespace Tranquility\Data\Entities\SystemObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;
use Tranquility\Data\Repositories\SystemObjects\OAuthClientRepository;

class OAuthClientSystemObject extends OAuthAbstractHashableField {
    // Entity properties
    protected $id;
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;

    // Define the set of fields that are publicly accessible
    private static $entityPublicFields = array(
        'id',
        'type',
        'clientId',
        'clientSecret',
        'redirectUri'
    );

    /**
     * Retrieves the type code used to describe the entity throughout the system
     *
     * @return string
     */
    public static function getEntityType() {
        return EntityTypeEnum::OAuthClient;
    }

    /** Retrieves the set of publicly accessible fields for the entity extension object
     * 
     * @return array
     */
    public static function getPublicFields() {
        return self::$entityPublicFields;
    }

    /** 
     * Retrieves the an array describing the related entities or entity collections for the entity
     * 
     * @return array
     */
    public static function getPublicRelationships() {
        return [];
    }

    public function setClientId($clientId) {
        $this->clientId = $clientId;
        return $this;
    }

    public function setClientSecret($clientSecret) {
        $this->clientSecret = $this->hashField($clientSecret);
        return $this;
    }

    public function setRedirectUri($redirectUri) {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    public function verifyClientSecret($clientSecret) {
        return $this->verifyHashedFieldValue($this->clientSecret, $clientSecret);
    }

    public function toArray() {
        return [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $this->redirectUri
        ];
    }

    /**
     * Metadata used to define object relationship to database
     *
     * @var \Doctrine\ORM\Mapping\ClassMetadata $metadata  Metadata to be passed to Doctrine
     * @return void
     */
    public static function loadMetadata(ClassMetadata $metadata) {
        $builder = new ClassMetadataBuilder($metadata);

        // Define table name
        $builder->setTable('sys_auth_clients');
        $builder->setCustomRepositoryClass(OAuthClientRepository::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('clientId', 'string');
        $builder->addField('clientSecret', 'string');
        $builder->addField('redirectUri', 'string');
    }
}