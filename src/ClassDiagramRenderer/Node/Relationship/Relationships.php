<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Trait_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\RenderOptions\RenderOptions;

class Relationships
{
    /**
     * @param Relationship[] $relationships
     */
    private function __construct(private array $relationships)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function add(Relationship $relationship): void
    {
        $this->relationships[] = $relationship;
    }

    /**
     * @return Relationship[]
     */
    public function getAll(): array
    {
        return $this->relationships;
    }

    public function sort(): self
    {
        usort($this->relationships, function (Relationship $a, Relationship $b) {
            $aKey = $a->from->nodeName() . ' ' . $a->to->nodeName();
            $bKey = $b->from->nodeName() . ' ' . $b->to->nodeName();
            return strcmp($aKey, $bKey);
        });

        return $this;
    }

    public function filter(RenderOptions $options): self
    {
        $filtered = array_filter($this->relationships, function (Relationship $relationship) use ($options) {
            if (!$options->includeTraits && ($relationship->from instanceof Trait_ || $relationship->to instanceof Trait_)) {
                return false;
            }
            if ($relationship instanceof Dependency && !$options->includeDependencies) {
                return false;
            }
            if ($relationship instanceof Composition && !$options->includeCompositions) {
                return false;
            }
            if ($relationship instanceof Inheritance && !$options->includeInheritances) {
                return false;
            }
            if ($relationship instanceof Realization && !$options->includeRealizations) {
                return false;
            }

            return true;
        });

        return new self(array_values($filtered));
    }
}
