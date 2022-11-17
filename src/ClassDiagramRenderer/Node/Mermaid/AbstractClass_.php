<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid;

class AbstractClass_ extends MermaidDiagramNode
{
    public function render(): string
    {
        return <<<EOM
            class $this->name {
                    <<abstruct>>
                }
            EOM;
    }
}