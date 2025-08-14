<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

class Trait_ extends Node
{
    public function render(): string
    {
        return <<<EOM
            class $this->name {
                    <<trait>>
                }
            EOM;
    }
}

