<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\RenderOptions;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\TraitRenderMode;

class RenderOptions
{
    public function __construct(
        public bool $includeDependencies,
        public bool $includeCompositions,
        public bool $includeInheritances,
        public bool $includeRealizations,
        public TraitRenderMode $traitRenderMode = TraitRenderMode::Flatten,
    ) {
    }

    public static function default(): self
    {
        return new self(true, true, true, true, TraitRenderMode::Flatten);
    }
}
