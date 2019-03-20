<?php namespace Tranquility\Data\Entities\SystemObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Tranquility class libraries
use Tranquility\Data\Entities\SystemObjects\OAuthClientSystemObject as Client;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

// Entity repository
use Tranquility\Data\Repositories\SystemObjects\OAuthAuthorisationCodeRepository;

class OAuthAuthorisationCodeSystemObject extends AbstractSystemObject {
    // Entity properties
    protected $id;
    protected $code;
    protected $client;
    protected $user;
    protected $expires;
    protected $redirectUri;
    protected $scope;

    // Define the set of fields that are publically accessible
    private static $entityPublicFields = array(
        'code',
        'client',
        'user',
        'expires',
        'redirectUri',
        'scope'
    );

    public static function getPublicFields() {
        return self::$entityPublicFields;
    }

    public function setCode($code) {
        $this->code = $code;
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

    public function setRedirectUri($redirectUri) {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    public function setScope($scope) {
        $this->scope = $scope;
        return $this;
    }

    public function toArray() {
        return [
            'code' => $this->code,
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
        $builder->setTable('sys_auth_authorisation_codes');
        $builder->setCustomRepositoryClass(OAuthAuthorisationCodeRepository::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('code', 'string');
        $builder->addField('expires', 'datetime');
        $builder->addField('scope', 'string');
        $builder->addField('redirectUri', 'string');

        // Define relationships
        $builder->createManyToOne('client', Client::class)->addJoinColumn('clientId', 'id')->mappedBy('id')->build();
        $builder->createManyToOne('user', User::class)->addJoinColumn('userId', 'id')->mappedBy('id')->build();
    }
}