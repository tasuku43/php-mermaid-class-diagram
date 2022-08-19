<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

class Interface_ extends Node
{
    public function render(): string
    {
        return <<<EOM
            class $this->name {
                    <<interface>>
                }
            EOM;
    }
}
