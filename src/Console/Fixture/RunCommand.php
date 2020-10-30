<?php declare(strict_types=1);
namespace Tranquillity\Console\Fixture;

// Library classes

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// Application classes
use Tranquillity\Data\Entities\OAuth\ClientEntity;

class RunCommand extends Command {

    protected static $defaultName = 'fixture:run';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Loader
     */
    private $loader;

    /**
     * Constructor
     *
     * @param Loader $loader
     */
    public function __construct(EntityManagerInterface $em, Loader $loader) {
        parent::__construct();
        $this->em = $em;
        $this->loader = $loader;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure() {
        $this->setDescription('Execute seed data fixtures to seed database.')
             ->addOption('fixture', 'f', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Name of the fixture to load as seed data.', [])
             ->addOption('append', 'a', InputOption::VALUE_NONE, 'If specified, fixtures will be appended to existing data already present in tables.')
             ->setHelp(<<<EOT
The <info>fixture:run</info> command runs all available fixtures by default. If one or more individual fixtures are specified as options, only those fixtures will be run.

<info>%command.full_name% fixture:run</info>                                   Runs all fixtures
<info>%command.full_name% fixture:run -f UserFixture</info>                    Runs only the UserFixture fixture
<info>%command.full_name% fixture:run -f UserFixture -s AccountFixture</info>  Runs only the UserFixture and AccountFixture fixtures
<info>%command.full_name% fixture:run -f UserFixture -a</info>                 Runs only the UserFixture fixture, but keeps any existing user data already present

EOT
             );
    }
    
    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('');

        $fixtureNames = $input->getOption('fixture');
        $append = $input->getOption('append');
        if ($append !== false) {
            $append = true;
        }

        // Get the list of fixture classes to execute
        $fixtures = [];
        if (count($fixtureNames) > 0) {
            // Run only individually specified fixtures
            $loadedFixtures = $this->loader->getFixtures();
            foreach ($fixtureNames as $fixtureName) {
                $fixtureFound = false;
                foreach ($loadedFixtures as $classname => $fixture) {
                    if (str_ends_with($classname, $fixtureName) === true) {
                        $fixtureFound = true;
                        $fixtures[] = $fixture;
                        
                    }
                }
                if ($fixtureFound == false) {
                    $output->writeln('<comment>Unable to find a loaded fixture named "'.$fixtureName.'".</comment>');
                }
            }
        } else {
            // Run all loaded fixtures
            $fixtures = $this->loader->getFixtures();
        }

        // If not fixtures have been loaded, finish early
        if (count($fixtures) <= 0) {
            $output->writeln('<error>No fixtures are available to run!</error>');
            $output->writeln('');
            return Command::FAILURE;
        }

        // Execute the list of fixtures
        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->em, $purger);
        $startTime = microtime(true);
        $output->writeln('<info>'.count($fixtures).' fixtures have been selected to run.</info>');
        if ($output->isVerbose() === true) {
            foreach ($fixtures as $classname => $fixture) {
                $output->writeln('    '.get_class($fixture));
            }
            $output->writeln('');
        }
        try {
            $executor->execute($fixtures, $append);
        } catch (Exception $e) {
            $output->writeln('');
            $output->writeln('<error>An error occurred while executing a fixture!</error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');
            $output->writeln('');
            return Command::FAILURE;
        }
        
        $finishTime = microtime(true);
        $output->writeln('<info>Fixtures were executed successfully! Total execution time: '.number_format(($finishTime - $startTime), 2).' seconds.</info>');
        $output->writeln('');
        return Command::SUCCESS;
    }
}