<?php namespace Tranquility\Data\Entities\OAuth;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Tranquility class libraries
use Tranquility\Data\Entities\AbstractEntity;
use Tranquility\Data\Entities\OAuth\ClientOAuth;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject;
use Tranquility\Data\Repositories\OAuth\RefreshTokenOAuthRepository;

class RefreshTokenOAuth extends AbstractEntity {
    // Entity properties
    protected $id;
    protected $token;
    protected $expires;
    protected $scope;
    protected $client;
    protected $user;

    // Define the set of fields that are publically accessible
    private $entityPublicFields = array(
        'token',
        'expires',
        'scope',
        'client',
        'user'
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

    public function setUser(UserBusinessObject $user = null) {
        $this->user = $user;
        return $this;
    }

    public function setExpires($expires) {
        $this->expires = $expires;
        return $this;
    }

    public function setScope($scope) {
        $this->scope = $scope;
        return $this;
    }

    public function toArray() {
        return [
            'refresh_token' => $this->token,
            'client_id' => $this->client->clientId,  // Key needs to be in underscore format for OAuth library
            'user_id' => $this->user->id,            // Key needs to be in underscore format for OAuth library
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
        $builder->setTable('sys_auth_refresh_tokens');
        $builder->setCustomRepositoryClass(RefreshTokenOAuthRepository::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('token', 'string');
        $builder->addField('expires', 'datetime');
        $builder->addField('scope', 'string');

        // Define relationships
        $builder->createManyToOne('client', ClientOAuth::class)->addJoinColumn('clientId', 'id')->mappedBy('id')->build();
        $builder->createManyToOne('user', UserBusinessObject::class)->addJoinColumn('userId', 'id')->mappedBy('id')->build();
    }
}