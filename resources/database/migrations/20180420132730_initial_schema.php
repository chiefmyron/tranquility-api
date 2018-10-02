<?php

use Phinx\Migration\AbstractMigration;

class InitialSchema extends AbstractMigration {
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change() {
        /*************************************************************************
         * Reference data tables                                                 *
         *                                                                       *
         * Used for application lookups                                          *
         *************************************************************************/
        
        // Locale reference data
        $table = $this->table('cd_locales', ['id' => false, 'primary_key' => 'code']);
        $table->addColumn('code', 'string', ['length' => 30]);
        $table->addColumn('description', 'string', ['length' => 100]);
        $table->addColumn('ordering', 'integer');
        $table->addColumn('effectiveFrom', 'datetime');
        $table->addColumn('effectiveUntil', 'datetime', ['null' => true]);
        $table->create();

        // Timezone reference data
        $table = $this->table('cd_timezones', ['id' => false, 'primary_key' => 'code']);
        $table->addColumn('code', 'string', ['length' => 30]);
        $table->addColumn('description', 'string', ['length' => 100]);
        $table->addColumn('daylightSavings', 'boolean');
        $table->addColumn('ordering', 'integer');
        $table->addColumn('effectiveFrom', 'datetime');
        $table->addColumn('effectiveUntil', 'datetime', ['null' => true]);
        $table->create();

        // Countries reference data
        $table = $this->table('cd_countries', ['id' => false, 'primary_key' => 'code']);
        $table->addColumn('code', 'string', ['length' => 30]);
        $table->addColumn('description', 'string', ['length' => 100]);
        $table->addColumn('ordering', 'integer');
        $table->addColumn('effectiveFrom', 'datetime');
        $table->addColumn('effectiveUntil', 'datetime', ['null' => true]);
        $table->create();

        /*************************************************************************
         * Business object tables                                                *
         *                                                                       *
         * Used to store currently active business data                          *
         *************************************************************************/

        // Base business object entity
        $table = $this->table('entity', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'biginteger', ['identity' => true]);
        $table->addColumn('version', 'integer');
        $table->addColumn('type', 'string', ['length' => 25]);
        $table->addColumn('subType', 'string', ['null' => true]);
        $table->addColumn('deleted', 'boolean');
        $table->addColumn('transactionId', 'biginteger');
        $table->create();

        // Address details - electronic, social, phone
        $table = $this->table('entity_addresses', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'biginteger');
        $table->addColumn('parentId', 'biginteger');
        $table->addColumn('category', 'string', ['length' => 25]);
        $table->addColumn('addressType', 'string', ['length' => 25]);
        $table->addColumn('addressText', 'string', ['length' => 25]);
        $table->addColumn('primaryContact', 'boolean');
        $table->create();

        // Address details - physical
        $table = $this->table('entity_addresses_physical', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'biginteger');
        $table->addColumn('parentId', 'biginteger');
        $table->addColumn('addressType', 'string', ['length' => 25]);
        $table->addColumn('addressLine1', 'string', ['length' => 255]);
        $table->addColumn('addressLine2', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('addressLine3', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('addressLine4', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('city', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('state', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('postcode', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('country', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('latitude', 'float', ['default' => 0.0]);
        $table->addColumn('longitude', 'float', ['default' => 0.0]);
        $table->create();

        // Person
        $table = $this->table('entity_people', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'biginteger');
        $table->addColumn('title', 'string', ['length' => 50, 'null' => true]);
        $table->addColumn('firstName', 'string', ['length' => 255]);
        $table->addColumn('lastName', 'string', ['length' => 255]);
        $table->addColumn('position', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('userId', 'biginteger', ['null' => true]);
        $table->create();

        // User
        $table = $this->table('entity_users', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'biginteger');
        $table->addColumn('username', 'string', ['length' => 255]);
        $table->addColumn('password', 'string', ['length' => 255]);
        $table->addColumn('timezoneCode', 'string', ['length' => 30]);
        $table->addColumn('localeCode', 'string', ['length' => 30]);
        $table->addColumn('active', 'boolean');
        $table->addColumn('securityGroupId', 'biginteger');
        $table->addColumn('registeredDateTime', 'datetime');
        $table->create();

        // Account
        $table = $this->table('entity_accounts', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'biginteger');
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->create();

        // Contact (linkage of Person to Account)
        $table = $this->table('entity_contacts', ['id' => false, 'primary_key' => ['personId', 'accountId']]);
        $table->addColumn('personId', 'biginteger');
        $table->addColumn('accountId', 'biginteger');
        $table->addColumn('primaryContact', 'boolean');
        $table->create();

        // Entity cross-referencing for tags
        $table = $this->table('entity_tags_xref', ['id' => false, 'primary_key' => ['entityId', 'tagId']]);
        $table->addColumn('entityId', 'biginteger');
        $table->addColumn('tagId', 'biginteger');
        $table->create();

        /*************************************************************************
         * Historical business object tables                                     *
         *                                                                       *
         * Used to store previous versions of business objects for audit         *
         * purposes                                                              *
         *************************************************************************/

        // Base business object entity
        $table = $this->table('history_entity', ['id' => false, 'primary_key' => ['id', 'version']]);
        $table->addColumn('id', 'biginteger');
        $table->addColumn('version', 'integer');
        $table->addColumn('type', 'string', ['length' => 25]);
        $table->addColumn('subType', 'string', ['null' => true]);
        $table->addColumn('deleted', 'boolean');
        $table->addColumn('transactionId', 'biginteger');
        $table->create();

        // Address details - electronic, social, phone
        $table = $this->table('history_entity_addresses', ['id' => false, 'primary_key' => ['id', 'version']]);
        $table->addColumn('id', 'biginteger');
        $table->addColumn('parentId', 'biginteger');
        $table->addColumn('version', 'integer');
        $table->addColumn('category', 'string', ['length' => 25]);
        $table->addColumn('addressType', 'string', ['length' => 25]);
        $table->addColumn('addressText', 'string', ['length' => 25]);
        $table->addColumn('primaryContact', 'boolean');
        $table->create();

        // Address details - physical
        $table = $this->table('history_entity_addresses_physical', ['id' => false, 'primary_key' => ['id', 'version']]);
        $table->addColumn('id', 'biginteger');
        $table->addColumn('parentId', 'biginteger');
        $table->addColumn('version', 'integer');
        $table->addColumn('addressType', 'string', ['length' => 25]);
        $table->addColumn('addressLine1', 'string', ['length' => 255]);
        $table->addColumn('addressLine2', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('addressLine3', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('addressLine4', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('city', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('state', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('postcode', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('country', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('latitude', 'float', ['default' => 0.0]);
        $table->addColumn('longitude', 'float', ['default' => 0.0]);
        $table->create();

        // Person
        $table = $this->table('history_entity_people', ['id' => false, 'primary_key' => ['id', 'version']]);
        $table->addColumn('id', 'biginteger');
        $table->addColumn('version', 'integer');
        $table->addColumn('title', 'string', ['length' => 50, 'null' => true]);
        $table->addColumn('firstName', 'string', ['length' => 255]);
        $table->addColumn('lastName', 'string', ['length' => 255]);
        $table->addColumn('position', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('userId', 'biginteger', ['null' => true]);
        $table->create();

        // User
        $table = $this->table('history_entity_users', ['id' => false, 'primary_key' => ['id', 'version']]);
        $table->addColumn('id', 'biginteger');
        $table->addColumn('version', 'integer');
        $table->addColumn('username', 'string', ['length' => 255]);
        $table->addColumn('password', 'string', ['length' => 255]);
        $table->addColumn('timezoneCode', 'string', ['length' => 30]);
        $table->addColumn('localeCode', 'string', ['length' => 30]);
        $table->addColumn('active', 'boolean');
        $table->addColumn('securityGroupId', 'biginteger');
        $table->addColumn('registeredDateTime', 'datetime');
        $table->create();

        // Account
        $table = $this->table('history_entity_accounts', ['id' => false, 'primary_key' => ['id', 'version']]);
        $table->addColumn('id', 'biginteger');
        $table->addColumn('version', 'integer');
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->create();

        // Contact (linkage of Person to Account)
        $table = $this->table('history_entity_contacts', ['id' => false, 'primary_key' => ['personId', 'accountId', 'version']]);
        $table->addColumn('personId', 'biginteger');
        $table->addColumn('accountId', 'biginteger');
        $table->addColumn('version', 'integer');
        $table->addColumn('primaryContact', 'boolean');
        $table->create();

        /*************************************************************************
         * Entity data extension tables                                          *
         *                                                                       *
         * Used to extend entity objects, but are not actual entities themselves *
         * e.g. Tags, etc...                                                     *
         *************************************************************************/

        // Tags
        $table = $this->table('ext_tags', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'biginteger', ['identity' => true]);
        $table->addColumn('text', 'string', ['length' => 255]);
        $table->create();

        /*************************************************************************
         * System tables                                                         *
         *                                                                       *
         * Used for audit trail details, system configuration and security roles *
         *************************************************************************/

        // Audit trail
        $table = $this->table('sys_trans_audit', ['id' => false, 'primary_key' => 'transactionId']);
        $table->addColumn('transactionId', 'biginteger', ['identity' => true]);
        $table->addColumn('transactionSource', 'string', ['length' => 100]);
        $table->addColumn('updateUserId', 'biginteger');
        $table->addColumn('updateDateTime', 'datetime');
        $table->addColumn('updateReason', 'string', ['length' => 100]);
        $table->create();

        // Entity locking
        $table = $this->table('sys_entity_locks', ['id' => false, 'primary_key' => 'entityId']);
        $table->addColumn('entityId', 'biginteger');
        $table->addColumn('lockedByUserId', 'biginteger');
        $table->addColumn('lockedDateTime', 'datetime');
        $table->create();

        // Authentication clients
        $table = $this->table('sys_auth_clients', ['id' => false, 'primary_key' => 'client_id']);
        $table->addColumn('client_id', 'string', ['length' => 80]);
        $table->addColumn('client_secret', 'string', ['length' => 80, 'null' => 'true']);
        $table->addColumn('redirect_uri', 'text', ['length' => 2000, 'null' => 'true']);
        $table->addColumn('grant_types', 'string', ['length' => 80, 'null' => 'true']);
        $table->addColumn('scope', 'text', ['length' => 4000, 'null' => 'true']);
        $table->addColumn('user_id', 'string', ['length' => 80, 'null' => 'true']);
        $table->create();

        // Authentication access tokens
        $table = $this->table('sys_auth_access_tokens', ['id' => false, 'primary_key' => 'access_token']);
        $table->addColumn('access_token', 'string', ['length' => 40]);
        $table->addColumn('client_id', 'string', ['length' => 80]);
        $table->addColumn('user_id', 'string', ['length' => 80, 'null' => 'true']);
        $table->addColumn('expires', 'timestamp');
        $table->addColumn('scope', 'string', ['length' => 4000, 'null' => 'true']);
        $table->create();

        // Authentication refresh tokens
        $table = $this->table('sys_auth_refresh_tokens', ['id' => false, 'primary_key' => 'refresh_token']);
        $table->addColumn('refresh_token', 'string', ['length' => 40]);
        $table->addColumn('client_id', 'string', ['length' => 80]);
        $table->addColumn('user_id', 'string', ['length' => 80, 'null' => 'true']);
        $table->addColumn('expires', 'timestamp');
        $table->addColumn('scope', 'string', ['length' => 4000, 'null' => 'true']);
        $table->create();

        // Authentication authorisation codes
        $table = $this->table('sys_auth_authorisation_codes', ['id' => false, 'primary_key' => 'authorisation_code']);
        $table->addColumn('authorisation_code', 'string', ['length' => 40]);
        $table->addColumn('client_id', 'string', ['length' => 80]);
        $table->addColumn('user_id', 'string', ['length' => 80, 'null' => 'true']);
        $table->addColumn('redirect_uri', 'text', ['length' => 2000, 'null' => 'true']);
        $table->addColumn('expires', 'timestamp');
        $table->addColumn('scope', 'string', ['length' => 4000, 'null' => 'true']);
        $table->addColumn('id_token', 'string', ['length' => 1000, 'null' => 'true']);
        $table->create();

        // Authentication scopes
        $table = $this->table('sys_auth_scopes', ['id' => false, 'primary_key' => 'scope']);
        $table->addColumn('scope', 'string', ['length' => 80]);
        $table->addColumn('is_default', 'boolean');
        $table->create();

        // Authentication JSON Web Tokens (JWT)
        $table = $this->table('sys_auth_jwt', ['id' => false]);
        $table->addColumn('client_id', 'string', ['length' => 80]);
        $table->addColumn('subject', 'string', ['length' => 80, 'null' =>'true']);
        $table->addColumn('public_key', 'text', ['length' => 2000]);
        $table->create();
    }
}
