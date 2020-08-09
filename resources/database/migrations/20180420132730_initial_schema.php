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
        $table = $this->table('ref_locales', ['id' => false, 'primary_key' => 'code']);
        $table->addColumn('code', 'string', ['length' => 30]);
        $table->addColumn('description', 'string', ['length' => 100]);
        $table->addColumn('ordering', 'integer');
        $table->addColumn('effectiveFrom', 'datetime');
        $table->addColumn('effectiveUntil', 'datetime', ['null' => true]);
        $table->create();

        // Timezone reference data
        $table = $this->table('ref_timezones', ['id' => false, 'primary_key' => 'code']);
        $table->addColumn('code', 'string', ['length' => 30]);
        $table->addColumn('description', 'string', ['length' => 100]);
        $table->addColumn('daylightSavings', 'boolean');
        $table->addColumn('ordering', 'integer');
        $table->addColumn('effectiveFrom', 'datetime');
        $table->addColumn('effectiveUntil', 'datetime', ['null' => true]);
        $table->create();

        // Countries reference data
        $table = $this->table('ref_countries', ['id' => false, 'primary_key' => 'code']);
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
        $table = $this->table('bus_entity', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('version', 'integer');
        $table->addColumn('type', 'string', ['length' => 25]);
        $table->addColumn('subType', 'string', ['null' => true]);
        $table->addColumn('deleted', 'boolean');
        $table->addColumn('transactionId', 'binary', ['length' => 16]);
        $table->create();

        // Address details - electronic, social, phone
        /*$table = $this->table('bus_addresses', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('category', 'string', ['length' => 25]);
        $table->addColumn('addressType', 'string', ['length' => 25]);
        $table->addColumn('addressText', 'string', ['length' => 25]);
        $table->addColumn('primaryContact', 'boolean');
        $table->create();

        // Address details - physical
        $table = $this->table('entity_addresses_physical', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('id', 'binary', ['length' => 16]);
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
        $table->create();*/

        // Person
        $table = $this->table('bus_people', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('title', 'string', ['length' => 50, 'null' => true]);
        $table->addColumn('firstName', 'string', ['length' => 255]);
        $table->addColumn('lastName', 'string', ['length' => 255]);
        $table->addColumn('position', 'string', ['length' => 255, 'null' => true]);
        $table->addColumn('userId', 'binary', ['length' => 16, 'null' => true]);
        $table->create();

        // User
        $table = $this->table('bus_users', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('username', 'string', ['length' => 255]);
        $table->addColumn('password', 'string', ['length' => 255]);
        $table->addColumn('timezoneCode', 'string', ['length' => 30]);
        $table->addColumn('localeCode', 'string', ['length' => 30]);
        $table->addColumn('active', 'boolean');
        $table->addColumn('securityGroupId', 'biginteger');
        $table->addColumn('registeredDateTime', 'datetime');
        $table->create();

        // Account
        $table = $this->table('bus_accounts', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->create();

        /*************************************************************************
         * System entity tables                                                  *
         *                                                                       *
         * Used to extend entity objects, but are not actual entities themselves *
         * e.g. Tags, etc...                                                     *
         *************************************************************************/

        // Tags
        $table = $this->table('sys_tags', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->create();

        /*************************************************************************
         * Cross-reference tables                                                *
         *                                                                       *
         * Used to record details of many-to-many relationships between other    *
         * entites.                                                              *
         *************************************************************************/

        // Entity cross-referencing for tags
        $table = $this->table('xref_entity_tags', ['id' => false, 'primary_key' => ['entityId', 'tagId']]);
        $table->addColumn('entityId', 'binary', ['length' => 16]);
        $table->addColumn('tagId', 'binary', ['length' => 16]);
        $table->create();

        // Contacts (linkage of multiple Person entities to an Account)
        $table = $this->table('xref_account_people', ['id' => false, 'primary_key' => ['accountId', 'personId']]);
        $table->addColumn('accountId', 'binary', ['length' => 16]);
        $table->addColumn('personId', 'binary', ['length' => 16]);
        $table->addColumn('primaryContact', 'boolean');
        $table->create();

        /*************************************************************************
         * OAuth tables                                                          *
         *                                                                       *
         * Used for storing OAuth clients, toekens and codes                     *
         *************************************************************************/

        // Authentication clients
        $table = $this->table('auth_clients', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('clientName', 'string', ['length' => 80]);
        $table->addColumn('clientSecret', 'string', ['length' => 80, 'null' => 'true']);
        $table->addColumn('redirectUri', 'text', ['length' => 2000, 'null' => 'true']);
        $table->addColumn('grantTypes', 'string', ['length' => 80, 'null' => 'true']);
        $table->addColumn('scope', 'text', ['length' => 4000, 'null' => 'true']);
        $table->addColumn('userId', 'binary', ['length' => 16, 'null' => 'true']);
        $table->create();

        // Authentication access tokens
        $table = $this->table('auth_tokens_access', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('token', 'string', ['length' => 40]);
        $table->addColumn('clientId', 'binary', ['length' => 16]);
        $table->addColumn('userId', 'binary', ['length' => 16, 'null' => 'true']);
        $table->addColumn('expires', 'timestamp');
        $table->addColumn('scope', 'string', ['length' => 4000, 'null' => 'true']);
        $table->create();

        // Authentication refresh tokens
        $table = $this->table('auth_tokens_refresh', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('token', 'string', ['length' => 40]);
        $table->addColumn('clientId', 'binary', ['length' => 16]);
        $table->addColumn('userId', 'binary', ['length' => 16, 'null' => 'true']);
        $table->addColumn('expires', 'timestamp');
        $table->addColumn('scope', 'string', ['length' => 4000, 'null' => 'true']);
        $table->create();

        // Authentication authorisation codes
        $table = $this->table('auth_authorisation_codes', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('code', 'string', ['length' => 40]);
        $table->addColumn('clientId', 'binary', ['length' => 16]);
        $table->addColumn('userId', 'binary', ['length' => 16, 'null' => 'true']);
        $table->addColumn('redirectUri', 'text', ['length' => 2000, 'null' => 'true']);
        $table->addColumn('expires', 'timestamp');
        $table->addColumn('scope', 'string', ['length' => 4000, 'null' => 'true']);
        $table->create();

        // Authentication scopes
        $table = $this->table('auth_scopes', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('scope', 'string', ['length' => 80]);
        $table->addColumn('isDefault', 'boolean');
        $table->create();

        /*************************************************************************
         * System tables                                                         *
         *                                                                       *
         * Used for audit trail details, system configuration and security roles *
         *************************************************************************/

        // Audit transaction
        $table = $this->table('sys_audit_txn', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'binary', ['length' => 16]);
        $table->addColumn('clientId', 'binary', ['length' => 16]);
        $table->addColumn('userId', 'binary', ['length' => 16]);
        $table->addColumn('timestamp', 'datetime');
        $table->addColumn('updateReason', 'string', ['length' => 100]);
        $table->create();

        // Audit transaction field changes
        $table = $this->table('sys_audit_txn_fields', ['id' => false, 'primary_key' => ['transactionId', 'entityId', 'fieldName']]);
        $table->addColumn('transactionId', 'binary', ['length' => 16]);
        $table->addColumn('entityId', 'binary', ['length' => 16]);
        $table->addColumn('fieldName', 'string', ['length' => 45]);
        $table->addColumn('dataType', 'string', ['length' => 45]);
        $table->addColumn('oldValue', 'string', ['length' => 255]);
        $table->addColumn('newValue', 'string', ['length' => 255]);
        $table->create();

        // Entity locking
        $table = $this->table('sys_locks_entity', ['id' => false, 'primary_key' => 'entityId']);
        $table->addColumn('entityId', 'binary', ['length' => 16]);
        $table->addColumn('lockedByUserId', 'binary', ['length' => 16]);
        $table->addColumn('lockedDateTime', 'datetime');
        $table->create();
    }
}
