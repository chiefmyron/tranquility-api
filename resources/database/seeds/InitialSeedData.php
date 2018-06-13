<?php

use Phinx\Seed\AbstractSeed;

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
        $referenceDataPath = "./resources/database/seeds/referenceData/";

        // Add reference data for locales from CSV
        $records = [];
        if (($handle = fopen(realpath($referenceDataPath."cd_locales.csv"), "r")) !== FALSE) {
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
        $table = $this->table('cd_locales');
        $table->insert($records)->save();

        // Add reference data for timezones from CSV
        $records = [];
        if (($handle = fopen($referenceDataPath."cd_timezones.csv", "r")) !== FALSE) {
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
        $table = $this->table('cd_timezones');
        $table->insert($records)->save();

        // Add reference data for countries from CSV
        $records = [];
        if (($handle = fopen($referenceDataPath."cd_countries.csv", "r")) !== FALSE) {
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
        $table = $this->table('cd_countries');
        $table->insert($records)->save();
    }
}
