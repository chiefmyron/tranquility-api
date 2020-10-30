<?php declare(strict_types=1);
namespace Tranquillity\Seeds\Fixtures\Reference;

// Standard PHP classes
use DateTime;

// Library classes
use Doctrine\Persistence\ObjectManager;

// Application classes
use Tranquillity\Data\Entities\Reference\LocaleReferenceDataEntity;
use Tranquillity\Seeds\Fixtures\AbstractFixture;
use Tranquillity\Utility\ArrayHelper;

class LocaleReferenceDataFixture extends AbstractFixture {

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
        $this->referenceDataFilename = ArrayHelper::get($options, 'referenceDataFilename', 'ref_locales.csv');
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
                    'code' => $data[0],
                    'description' => $data[1],
                    'ordering' => $data[2],
                    'effectiveFrom' => new DateTime()
                );

                // Create entity from data
                $entity = new LocaleReferenceDataEntity($row);
                $manager->persist($entity);
            }
            $manager->flush();
            fclose($handle);
        }
    }
}