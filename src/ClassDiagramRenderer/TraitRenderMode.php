<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer;

enum TraitRenderMode
{
    // Render trait nodes and trait use relationships
    case WithTraits;

    // Hide trait nodes and use lines; flatten trait deps to using classes
    case Flatten;

    public function isWithTraits(): bool
    {
        return $this === self::WithTraits;
    }

    public function isFlatten(): bool
    {
        return $this === self::Flatten;
    }
}
