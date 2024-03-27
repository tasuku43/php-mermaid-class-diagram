<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;

abstract class Relationship
{
    protected const FORMAT = "%s %s %s: %s";
    public function __construct(protected Node $from, protected Node $to)
    {
    }

    abstract protected function render(): string;

    public static function sortRelationships(array &$relationships): void
    {
        usort($relationships, function (Relationship $a, Relationship $b) {
            $aKey = $a->from->nodeName() . ' ' . $a->to->nodeName();
            $bKey = $b->from->nodeName() . ' ' . $b->to->nodeName();
            return strcmp($aKey, $bKey);
        });
    }
}
