<?php declare(strict_types=1);
namespace Tranquillity\Console\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tranquillity\Data\Entities\OAuth\ClientEntity;

class ClientListCommand extends Command {

    protected static $defaultName = 'auth:client:list';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em) {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure() {
        $this->setDescription('List the current set of OAuth Clients');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        // Get repository for client entity
        $repository = $this->em->getRepository(ClientEntity::class);
        $clients = $repository->findAll();

        // Display in table
        $table = new Table($output);
        $table->setHeaders(['Client', 'Secret']);
        foreach ($clients as $client) {
            $table->addRow([$client->clientName, $client->clientSecret]);
        }
        $table->render();
        return Command::SUCCESS;
    }
}