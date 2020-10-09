<?php declare(strict_types=1);
namespace Tranquillity\Console;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabasePingCommand extends Command {

    protected static $defaultName = 'db:ping';

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection) {
        parent::__construct();
        $this->connection = $connection;
    }

    protected function configure() {
        $this->setDescription('Ping the database to test for a connection');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('');
        $output->writeln('Database connection details');
        $output->writeln('---------------------------');

        // Get database connection details
        $config = $this->connection->getParams();
        foreach ($config as $name => $value) {
            $output->writeln(str_pad(ucfirst($name).': ', 10).$value);
        }
        $output->writeln('');

        // Attempt connection
        try {
            $this->connection->ping();
        } catch (\Exception $ex) {
            $output->writeln('<error>Unable to connect to database!</error>');
            $output->writeln('<error>'.$ex->getMessage().'</error>');
            $output->writeln('');
            return Command::FAILURE;
        }

        $output->writeln('<info>Database pinged successfully!</info>');
        $output->writeln('');
        return Command::SUCCESS;
    }
}