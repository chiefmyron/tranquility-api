<?php namespace Tranquility\Data\Entities\SystemObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;
use Tranquility\Data\Entities\SystemObjects\OAuthClientSystemObject as Client;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

// Entity repository
use Tranquility\Data\Repositories\SystemObjects\OAuthRefreshTokenRepository;

class OAuthRefreshTokenSystemObject extends AbstractSystemObject {
    // Entity properties
    protected $id;
    protected $token;
    protected $expires;
    protected $scope;
    protected $client;
    protected $user;

    // Define the set of fields that are publicly accessible
    private static $entityPublicFields = array(
        'token',
        'expires',
        'scope',
        'client',
        'user'
    );

    /**
     * Retrieves the type code used to describe the entity throughout the system
     *
     * @return string
     */
    public static function getEntityType() {
        return EntityTypeEnum::OAuthTokenRefresh;
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

    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    public function setClient(Client $client = null) {
        $this->client = $client;
        return $this;
    }

    public function setUser(User $user = null) {
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
        $builder->setCustomRepositoryClass(OAuthRefreshTokenRepository::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('token', 'string');
        $builder->addField('expires', 'datetime');
        $builder->addField('scope', 'string');

        // Define relationships
        $builder->createManyToOne('client', Client::class)->addJoinColumn('clientId', 'id')->mappedBy('id')->build();
        $builder->createManyToOne('user', User::class)->addJoinColumn('userId', 'id')->mappedBy('id')->build();
    }
}