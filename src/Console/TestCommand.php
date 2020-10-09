<?php declare(strict_types=1);
namespace Tranquillity\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command {
    protected function configure() {
        $this->setName('test')
             ->setDescription('This is a test command');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln("This is a test");
    }
}