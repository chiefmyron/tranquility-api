<?php namespace Tranquility\Data\Entities\SystemObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Tranquility class libraries
use Tranquility\Data\Repositories\SystemObjects\OAuthClientRepository;

class OAuthClientSystemObject extends OAuthAbstractHashableField {
    // Entity properties
    protected $id;
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;

    // Define the set of fields that are publically accessible
    private static $entityPublicFields = array(
        'clientId',
        'clientSecret',
        'redirectUri'
    );

    public static function getPublicFields() {
        return self::$entityPublicFields;
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