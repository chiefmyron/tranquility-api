<?php declare(strict_types=1);
namespace Tranquillity\Console\App;

// Library classes
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;

class ServerStartCommand extends Command {

    /**
     * @var string|null The default command name
     */
    protected static $defaultName = 'app:start';

    /**
     * {@inheritDoc}
     */
    protected function configure() {
        $this->setDescription('Serve the application using the built-in PHP development server')
             ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on.', 'localhost')
             ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on.', 8080);
    }
    
    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $host = $input->getOption('host');
        $port = $input->getOption('port');

        // Determine path to './public/index.php'
        $path = escapeshellarg(APP_BASE_PATH.'/public');
        $php = escapeshellarg((new PhpExecutableFinder)->find(false));

        // Start the server
        $output->writeln("<info>Tranquillity API server started on http://{$host}:{$port}/</info>");
        passthru("{$php} -S {$host}:{$port} -t {$path}");
        return Command::SUCCESS;
    }
}