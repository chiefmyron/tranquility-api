<?php declare(strict_types=1);
namespace Tranquillity\Seeds\Fixtures\Reference;

// Library classes
use Doctrine\Persistence\ObjectManager;

// Application classes
use Tranquillity\Data\Entities\OAuth\ScopeEntity;
use Tranquillity\Seeds\Fixtures\AbstractFixture;
use Tranquillity\Utility\ArrayHelper;

class ScopeDataFixture extends AbstractFixture {

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
        $this->referenceDataFilename = ArrayHelper::get($options, 'referenceDataFilename', 'oauth_scopes.csv');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        // Load reference data from CSV file
        $path = $this->referenceDataPath.$this->referenceDataFilename;
        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'scope' => $data[0],
                    'isDefault' => (bool)$data[1]
                );

                // Create entity from data
                $entity = new ScopeEntity($row);
                $manager->persist($entity);
            }
            $manager->flush();
            fclose($handle);
        }
    }
}