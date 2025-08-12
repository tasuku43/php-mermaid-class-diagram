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
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\RenderOptions\RenderOptions;

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
        $this->addOption(
            'exclude-relationships',
            null,
            InputOption::VALUE_REQUIRED,
            'Comma-separated relationship types to exclude: dependency,composition,inheritance,realization'
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

        $options = $this->buildRenderOptions($input);

        $symfonyStyle->write($builder->build($path)->render($options));

        return self::SUCCESS;
    }

    private function buildRenderOptions(InputInterface $input): RenderOptions
    {
        $options = RenderOptions::default();

        $exclude = (string)($input->getOption('exclude-relationships') ?? '');
        if ($exclude === '') {
            return $options;
        }

        $tokens = array_filter(array_map('trim', explode(',', $exclude)));
        foreach ($tokens as $token) {
            switch (strtolower($token)) {
                case 'dependency':
                case 'dependencies':
                case 'dep':
                case 'deps':
                    $options->includeDependencies = false;
                    break;
                case 'composition':
                case 'compositions':
                case 'comp':
                    $options->includeCompositions = false;
                    break;
                case 'inheritance':
                case 'inheritances':
                case 'extends':
                    $options->includeInheritances = false;
                    break;
                case 'realization':
                case 'realizations':
                case 'implements':
                    $options->includeRealizations = false;
                    break;
                default:
                    // Ignore unknown tokens silently for now
                    break;
            }
        }

        return $options;
    }
}
