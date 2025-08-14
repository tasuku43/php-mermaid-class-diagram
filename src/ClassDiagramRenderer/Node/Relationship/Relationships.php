<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\RenderOptions\RenderOptions;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Trait_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;

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

    public function optimize(RenderOptions $options): self
    {
        // Delegate to clearer, private implementations per mode
        $relationships = $options->traitRenderMode->isWithTraits()
            ? $this->optimizeWithTraitsInternal()
            : $this->optimizeFlattenInternal();

        return new self($this->filterByOptions($relationships, $options));
    }

    /**
     * Flatten mode: reassign trait-origin relationships to using classes,
     * hide TraitUsage edges, and deduplicate by (type, from, to).
     * @return Relationship[]
     */
    private function optimizeFlattenInternal(): array
    {
        $traitUsers = $this->buildTraitClassUsersMap();
        $flattened = [];
        $seen = [];

        foreach ($this->relationships as $rel) {
            if ($rel instanceof TraitUsage) {
                continue; // hide trait usage edges in flatten mode
            }

            if ($rel->from instanceof Trait_) {
                $users = $traitUsers[$rel->from->nodeName()] ?? [];
                if (!empty($users)) {
                    foreach ($users as $userNode) {
                        $className = get_class($rel);
                        /** @var Relationship $new */
                        $new = new $className($userNode, $rel->to);
                        $k = $this->generateKey($new);
                        if (!isset($seen[$k])) {
                            $seen[$k] = true;
                            $flattened[] = $new;
                        }
                    }
                }
                // drop original trait-origin edge
                continue;
            }

            $k = $this->generateKey($rel);
            if (!isset($seen[$k])) {
                $seen[$k] = true;
                $flattened[] = $rel;
            }
        }

        return $flattened;
    }

    /**
     * WithTraits mode: keep trait and `use` edges, and suppress class-level
     * duplicates for composition/dependency that are already provided by traits.
     * @return Relationship[]
     */
    private function optimizeWithTraitsInternal(): array
    {
        $traitProvides = $this->buildTraitProvidesMap();
        $classUses = $this->buildClassUsesMap();

        $result = [];
        $seen = [];
        foreach ($this->relationships as $rel) {
            $from = $rel->from;

            $isSuppressedType = $rel instanceof Dependency || $rel instanceof Composition;
            if ($isSuppressedType && !($from instanceof Trait_)) {
                $traits = $classUses[$from->nodeName()] ?? [];
                $suppress = false;
                foreach ($traits as $tName) {
                    if (!empty($traitProvides[get_class($rel)][$rel->to->nodeName()] ?? false)) {
                        $suppress = true;
                        break;
                    }
                }
                if ($suppress) {
                    continue;
                }
            }

            $k = $this->generateKey($rel);
            if (!isset($seen[$k])) {
                $seen[$k] = true;
                $result[] = $rel;
            }
        }

        return $result;
    }

    /**
     * Build a map: traitName => [ className => Node ].
     * @return array<string, array<string, Node>>
     */
    private function buildTraitUsersMap(): array
    {
        $traitUsers = [];
        foreach ($this->relationships as $rel) {
            if ($rel instanceof TraitUsage) {
                $traitName = $rel->to->nodeName();
                $traitUsers[$traitName] ??= [];
                $traitUsers[$traitName][$rel->from->nodeName()] = $rel->from;
            }
        }
        return $traitUsers;
    }

    /**
     * Build a map from trait name to transitive class users (flattening trait->trait chains).
     * Only final class-like nodes (non-trait) are included as users.
     * @return array<string, array<string, Node>> traitName => [className => Node]
     */
    private function buildTraitClassUsersMap(): array
    {
        $directUsers = $this->buildTraitUsersMap();
        $result = [];

        foreach ($directUsers as $traitName => $usersMap) {
            $finals = [];
            $queue = array_values($usersMap);
            $visitedTraits = [$traitName => true];

            while (!empty($queue)) {
                /** @var Node $user */
                $user = array_shift($queue);
                if ($user instanceof Trait_) {
                    $tName = $user->nodeName();
                    if (!empty($visitedTraits[$tName])) {
                        continue;
                    }
                    $visitedTraits[$tName] = true;
                    foreach ($directUsers[$tName] ?? [] as $next) {
                        $queue[] = $next;
                    }
                    continue;
                }

                $finals[$user->nodeName()] = $user;
            }

            $result[$traitName] = $finals;
        }

        return $result;
    }

    /**
     * Build a map of trait-provided targets by relationship type:
     * type(FQCN) => [ toNodeName => true ].
     * @return array<string, array<string, bool>>
     */
    private function buildTraitProvidesMap(): array
    {
        $traitProvides = [];
        foreach ($this->relationships as $rel) {
            if ($rel->from instanceof Trait_) {
                $type = get_class($rel);
                $traitProvides[$type] ??= [];
                $traitProvides[$type][$rel->to->nodeName()] = true;
            }
        }
        return $traitProvides;
    }

    /**
     * Build a map: className => [ traitName, ... ].
     * @return array<string, string[]>
     */
    private function buildClassUsesMap(): array
    {
        $classUses = [];
        foreach ($this->relationships as $rel) {
            if ($rel instanceof TraitUsage) {
                $classUses[$rel->from->nodeName()] ??= [];
                $classUses[$rel->from->nodeName()][] = $rel->to->nodeName();
            }
        }
        return $classUses;
    }

    private function generateKey(Relationship $r): string
    {
        return get_class($r) . '|' . $r->from->nodeName() . '|' . $r->to->nodeName();
    }

    /**
     * @param Relationship[] $relationships
     * @return Relationship[]
     */
    private function filterByOptions(array $relationships, RenderOptions $options): array
    {
        $filtered = array_filter($relationships, function (Relationship $relationship) use ($options) {
            if ($relationship instanceof TraitUsage && !$options->traitRenderMode->isWithTraits()) {
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

        return array_values($filtered);
    }
}
