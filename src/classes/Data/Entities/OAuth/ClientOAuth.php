<?php namespace Tranquility\Data\Entities\OAuth;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Tranquility class libraries
use Tranquility\Data\Entities\OAuth\HashableFieldOAuth as HashableFieldOAuth;
use Tranquility\Data\Repositories\OAuthRepository as OAuthRepository;

class ClientOAuthEntity extends HashableFieldOAuth {
    // Entity properties
    private $id;
    private $clientIdentifier;
    private $clientSecret;
    private $redirectUri;

    // Define the set of fields that are publically accessible
    private $entityPublicFields = array(
        'clientIdentifier',
        'clientSecret',
        'redirectUri'
    );

    private function _getId() {
        return $this->id;
    }

    public function setClientIdentifier($clientIdentifier) {
        $this->clientIdentifier = $clientIdentifier;
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
            'clientId' => $this->clientIdentifier,
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
        $builder->setCustomRepositoryClass(OAuthRepository::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('clientIdentifier', 'string');
        $builder->addField('clientSecret', 'string');
        $builder->addField('redirectUri', 'string');
    }
}