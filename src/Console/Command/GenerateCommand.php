<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\Console\Command;

use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\ClassDiagramBuilder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\NodeParser;

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $path = $input->getOption('path');

        $builder = new ClassDiagramBuilder(new NodeParser(
            (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1)),
            new NodeFinder()
        ));

        $symfonyStyle->write($builder->build($path)->render());

        return self::SUCCESS;
    }
}
