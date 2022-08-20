<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\Console\Command;

use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\ClassDiagramBuilder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\NodeBuilder;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this->setName('generate')
            ->setDescription('Generate class diagram from PHP code.')
            ->addOption(
                'path',
                null,
                InputOption::VALUE_REQUIRED,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $path = $input->getOption('path');

        $builder = new ClassDiagramBuilder(new NodeBuilder(
            (new ParserFactory)->create(ParserFactory::PREFER_PHP7),
            new NodeFinder()
        ));

        $symfonyStyle->write($builder->build($path)->render());

        return self::SUCCESS;
    }
}
