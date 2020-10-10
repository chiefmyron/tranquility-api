<?php declare(strict_types=1);
namespace Tranquillity\Console\Auth;

// Library classes
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// Application classes
use Tranquillity\Data\Entities\OAuth\ClientEntity;

class ClientAddCommand extends Command {

    protected static $defaultName = 'auth:client:add';

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
        $this->setDescription('Add a new OAuth Client')
             ->addArgument('name', InputArgument::REQUIRED, 'Identifier for the client')
             ->addOption('secret', 's', InputOption::VALUE_OPTIONAL, 'Custom secret to use for the client. If not provided, a secret will be automatically generated.');
    }
    
    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        // Get repository for client entity
        $repository = $this->em->getRepository(ClientEntity::class);

        // Get inputs
        $name = $input->getArgument('name');
        $secret = $input->getOption('secret');

        // Check if client already exists with that name
        if ($repository->getClientDetails($name) !== null) {
            $output->writeln('<error>An existing OAuth Client with the same name already exists!</error>');
            $output->writeln('');
            return Command::FAILURE;
        }

        // Generate secret if one has not been supplied
        $displaySecret = false;
        if ($secret === null) {
            $displaySecret = true;
            $secret = $this->generateSecret();
        }

        // Create client record
        try {
            $client = $repository->createClient($name, $secret);
        } catch (\Exception $ex) {
            $output->writeln('<error>Unable to create a new OAuth Client!</error>');
            $output->writeln('<error>'.$ex->getMessage().'</error>');
            $output->writeln('');
            return Command::FAILURE;
        }

        // Display success message
        if ($displaySecret === true) {
            $output->writeln('');
            $output->writeln('<comment>IMPORTANT! Make a note of the client secret - you will not be able to see this value again.</comment>');
            $output->writeln('<comment>Client secret:  '.$secret.'</comment>');
        }
        $output->writeln('');
        $output->writeln('<info>New OAuth Client created successfully!</info>');
        $output->writeln('');
        return Command::SUCCESS;
    }

    /**
     * Generates a random string of characters to the specified length (default 20 characters)
     *
     * @param integer $length
     * @return string
     */
    private function generateSecret(int $length = 20) : string {
        $secret = '';
        while (($len = strlen($secret)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $secret .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return $secret;
    }
}