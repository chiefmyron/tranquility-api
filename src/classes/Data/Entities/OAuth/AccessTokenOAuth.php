<?php namespace Tranquility\Data\Entities\OAuth;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Tranquility class libraries
use Tranquility\Data\Entities\AbstractEntity;
use Tranquility\Data\Entities\OAuth\ClientOAuth;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject;
use Tranquility\Data\Repositories\OAuth\AccessTokenOAuthRepository;

class AccessTokenOAuth extends AbstractEntity {
    // Entity properties
    private $id;
    private $token;
    private $clientId;
    private $userId;
    private $expires;
    private $scope;
    private $client;
    private $user;

    // Define the set of fields that are publically accessible
    private $entityPublicFields = array(
        'token',
        'clientId',
        'userId',
        'expires',
        'scope',
        'client',

    );

    public function getPublicFields() {
        return $this->entityPublicFields;
    }

    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    public function setClient(ClientOAuthEntity $client = null) {
        $this->client = $client;
        return $this;
    }

    public function setClientId($clientId) {
        $this->clientId = $clientId;
        return $this;
    }

    public function setUser(UserBusinessObject $user = null) {
        $this->user = $user;
        return $this;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
        return $this;
    }

    public function setExpires($expires) {
        return $this->expires = $expires;
    }

    public function setScope($scope) {
        return $this->scope = $scope;
    }

    public function toArray() {
        return [
            'token' => $this->token,
            'clientId' => $this->clientId,
            'userId' => $this->userId,
            'expires' => $this->expires,
            'scope' => $this->scope
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
        $builder->setTable('sys_auth_access_tokens');
        $builder->setCustomRepositoryClass(AccessTokenOAuthRepository::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('token', 'string');
        $builder->addField('clientId', 'string');
        $builder->addField('userId', 'string');
        $builder->addField('expires', 'datetime');
        $builder->addField('scope', 'string');

        // Define relationships
        $builder->createManyToOne('client', ClientOAuth::class)->mappedBy('id')->build();
        $builder->createManyToOne('user', UserBusinessObject::class)->mappedBy('id')->build();
    }
}