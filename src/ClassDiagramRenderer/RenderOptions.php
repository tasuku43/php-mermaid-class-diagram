<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer;

class RenderOptions
{
    public function __construct(
        public bool $includeDependencies,
        public bool $includeCompositions,
        public bool $includeInheritances,
        public bool $includeRealizations,
        public bool $includeTraits = false,
    ) {
    }

    public static function default(): self
    {
        return new self(true, true, true, true, false);
    }
}
