<?php

use Phinx\Seed\AbstractSeed;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;

class InitialSeedData extends AbstractSeed {
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run() {
        $referenceDataPath = TRANQUIL_PATH_BASE."/resources/database/seeds/referenceData/";
        $uuid = new UuidBinaryOrderedTimeType();

        // Add reference data for locales from CSV
        $records = [];
        if (($handle = fopen(realpath($referenceDataPath."ref_locales.csv"), "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'code' => $data[0],
                    'description' => $data[1],
                    'ordering' => $data[2],
                    'effectiveFrom' => date('Y-m-d H:i:s')
                );
                $records[] = $row;
            }
            fclose($handle);
        }
        $table = $this->table('ref_locales');
        $table->insert($records)->save();

        // Add reference data for timezones from CSV
        $records = [];
        if (($handle = fopen($referenceDataPath."ref_timezones.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'code' => $data[0],
                    'description' => $data[1],
                    'daylightSavings'=> $data[2],
                    'ordering' => $data[3],
                    'effectiveFrom' => date('Y-m-d H:i:s')
                );
                $records[] = $row;
            }
            fclose($handle);
        }
        $table = $this->table('ref_timezones');
        $table->insert($records)->save();

        // Add reference data for countries from CSV
        $records = [];
        if (($handle = fopen($referenceDataPath."ref_countries.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'code' => $data[0],
                    'description' => $data[1],
                    'ordering' => $data[2],
                    'effectiveFrom' => date('Y-m-d H:i:s')
                );
                $records[] = $row;
            }
            fclose($handle);
        }
        $table = $this->table('ref_countries');
        $table->insert($records)->save();

        // Generate OAuth Client record
        $records = [];
        if (($handle = fopen($referenceDataPath."auth_clients.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'id' => hex2bin($data[0]),
                    'clientName' => $data[1],
                    'clientSecret' => password_hash($data[2], PASSWORD_DEFAULT, ['cost' => 11]),
                    'redirectUri' => $data[3],
                    'grantTypes' => $data[4],
                    'scope' => $data[5],
                    'userId' => $data[6]
                );
                $records[] = $row;
            }
            fclose($handle);
        }
        $table = $this->table('auth_clients');
        $table->insert($records)->save();

        // Add seed script transaction
        $records = [];
        if (($handle = fopen($referenceDataPath."sys_audit_txn.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'id' => hex2bin($data[0]),
                    'clientId' => hex2bin($data[1]),
                    'userId' => hex2bin($data[2]),
                    'timestamp' => date('Y-m-d H:i:s'),
                    'updateReason' => 'database_seed_script'
                );
                $records[] = $row;
            }
            fclose($handle);
        }
        $table = $this->table('sys_audit_txn');
        $table->insert($records)->save();

        // Add initial user data
        $records = [];
        if (($handle = fopen($referenceDataPath."bus_entity.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'id' => hex2bin($data[0]),
                    'version' => $data[1],
                    'type' => $data[2],
                    'subType' => $data[3],
                    'deleted' => $data[4],
                    'transactionId' => hex2bin($data[5])
                );
                $records[] = $row;
            }
            fclose($handle);
        }
        $table = $this->table('bus_entity');
        $table->insert($records)->save();

        $records = [];
        if (($handle = fopen($referenceDataPath."bus_users.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'id' => hex2bin($data[0]),
                    'username' => $data[1],
                    'password' => $data[2],
                    'timezoneCode' => $data[3],
                    'localeCode' => $data[4],
                    'active' => $data[5],
                    'securityGroupId' => $data[6],
                    'registeredDateTime' => date('Y-m-d H:i:s')
                );
                $records[] = $row;
            }
            fclose($handle);
        }
        $table = $this->table('bus_users');  
        $table->insert($records)->save();
    }
}
