<?php declare(strict_types=1);
namespace Tranquillity\Seeds\Fixtures\Reference;

// Standard PHP classes
use DateTime;

// Library classes
use Doctrine\Persistence\ObjectManager;

// Application classes
use Tranquillity\Data\Entities\OAuth\ClientEntity;
use Tranquillity\Seeds\Fixtures\AbstractFixture;
use Tranquillity\Utility\ArrayHelper;

class ClientDataFixture extends AbstractFixture {

    /**
     * @var string
     */
    private $referenceDataPath;

    /**
     * @var string
     */
    private $referenceDataFilename;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = []) {
        $this->referenceDataPath = ArrayHelper::get($options, 'referenceDataPath', APP_BASE_PATH.'/resources/ReferenceData/');
        $this->referenceDataFilename = ArrayHelper::get($options, 'referenceDataFilename', 'oauth_clients.csv');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        // Load reference data from CSV file
        $path = $this->referenceDataPath.$this->referenceDataFilename;
        if (($handle = fopen($path, "r")) !== FALSE) {
            $dataLoaderClient = null;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'clientName' => $data[0],
                    'clientSecret' => $data[1],
                    'redirectUrl' => $data[2]
                );

                // Create entity from data
                $entity = new ClientEntity($row);
                $manager->persist($entity);

                // Check if this client has been flagged as the client to use for the data loader audit trail
                if (boolval($data[3]) == true) {
                    $dataLoaderClient = $entity;
                }
            }
            $manager->flush();
            fclose($handle);

            // If a client has been flagged as the data loader client, make it available to other fixtures
            if (is_null($dataLoaderClient) === false) {
                $this->addReference('dataloader-client', $dataLoaderClient);
            }
        }
    }
}