<?php namespace Tranquility\Data\Entities\BusinessObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity repository
use Tranquility\Data\Repositories\BusinessObjects\BusinessObjectRepository;
use Tranquility\Data\Repositories\OAuth\UserOAuthRepository;

class UserBusinessObject extends AbstractBusinessObject {

    // Entity properties
    protected $username;
    protected $password;
    protected $timezoneCode;
    protected $localeCode;
    protected $active;
    protected $securityGroupId;
    protected $registeredDateTime;

    // Related entities
    protected $person;

    // Related extension data objects
    protected $userTokens;

    // Define the set of fields that are publically accessible
    protected $publicFields = array(
        'username',
        'timezoneCode',
        'localeCode',
        'active',
        'securityGroupId',
        'registeredDateTime'
    );
    
    /**
     * Metadata used to define object relationship to database
     *
     * @var \Doctrine\ORM\Mapping\ClassMetadata $metadata  Metadata to be passed to Doctrine
     * @return void
     */
   public static function loadMetadata(ClassMetadata $metadata) {
        $builder = new ClassMetadataBuilder($metadata);
        
        // Define table name
        $builder->setTable('entity_users');
        $builder->setCustomRepositoryClass(UserOAuthRepository::class);
        
        // Define fields
        $builder->addField('username', 'string');
        $builder->addField('password', 'string');
        $builder->addField('timezoneCode', 'string');
        $builder->addField('localeCode', 'string');
        $builder->addField('active', 'boolean');
        $builder->addField('securityGroupId', 'integer');
        $builder->addField('registeredDateTime', 'datetime');
        
        // Add relationships
        //$builder->createOneToOne('person', Person::class)->mappedBy('user')->build();
        //$builder->createOneToMany('userTokens', UserToken::class)->mappedBy('user')->build();
    }

    public function getPublicFields() {
        return array_merge($this->entityPublicFields, $this->publicFields);
    }

    public function getPassword() {
        return $this->password;
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    public function toArray() {
        return [
            'user_id' => $this->id
        ];
    }
}