<?php declare(strict_types=1);
namespace Tranquillity\Seeds\Fixtures\Reference;

// Standard PHP classes
use DateTime;

// Library classes
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

// Application classes
use Tranquillity\Data\Entities\System\AuditTransactionEntity;
use Tranquillity\Seeds\Fixtures\AbstractFixture;

class AuditTransactionDataFixture extends AbstractFixture implements DependentFixtureInterface {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        // Create new audit transaction
        $data = [
            'timestamp' => new DateTime(),
            'updateReason' => 'dataloader_seed_data',
            'client' => $this->getReference('dataloader-client')
        ];

        $entity = new AuditTransactionEntity($data);
        $manager->persist($entity);
        $manager->flush();

        // Make audit transaction available to other fixtures
        $this->addReference('dataloader-txn', $entity);
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies() {
        return [
            ClientDataFixture::class
        ];
    }
}