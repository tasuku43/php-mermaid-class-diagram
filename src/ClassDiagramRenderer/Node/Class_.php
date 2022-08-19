<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

class Class_ extends Node
{
    public function render(): string
    {
        return <<<EOM
            class $this->name {
                }
            EOM;
    }
}
