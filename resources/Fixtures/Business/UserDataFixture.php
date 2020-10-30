<?php declare(strict_types=1);
namespace Tranquillity\Seeds\Fixtures\Reference;

// Standard PHP classes
use DateTime;

// Library classes
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

// Application classes
use Tranquillity\Data\Entities\Business\UserEntity;
use Tranquillity\Seeds\Fixtures\AbstractFixture;
use Tranquillity\Utility\ArrayHelper;

class UserDataFixture extends AbstractFixture implements DependentFixtureInterface {

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
        $this->referenceDataFilename = ArrayHelper::get($options, 'referenceDataFilename', 'bus_users.csv');
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
                    'username' => $data[0],
                    'password' => $data[1],
                    'timezoneCode' => $data[2],
                    'localeCode' => $data[3],
                    'active' => 1,
                    'securityGroupId' => 1,
                    'registeredDateTime' => new DateTime(),
                    'transaction' => $this->getReference('dataloader-txn')
                );

                // Create entity from data
                $entity = new UserEntity($row);
                $manager->persist($entity);
            }
            $manager->flush();
            fclose($handle);

            // If a client has been flagged as the data loader client, make it available to other fixtures
            if (is_null($dataLoaderClient) === false) {
                $this->addReference('dataloader-client', $dataLoaderClient);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies() {
        return [
            AuditTransactionDataFixture::class
        ];
    }
}