# Mermaid class diagram generater

## Overview
Generate Mermaid-js class diagram from php code.  
This tool focuses on the relationships between classes and omits the details of class internals at this stage.
## Installation
Via Composer
```shell
composer require --dev tasuku43/mermaid-class-diagram
```

## Usage
Here is an example run on a sample project
```shell
$ tree
.
├── composer.json
├── composer.lock
├── src
│   ├── SomeAbstractClass.php
│   ├── SomeClassA.php
│   ├── SomeClassB.php
│   ├── SomeClassC.php
│   ├── SomeClassD.php
│   ├── SomeClassE.php
│   └── SomeInterface.php
└── vendor
```
```php
class SomeClassA extends SomeAbstractClass
{
    private SomeClassB $someClassB;

    public function __construct(private SomeClassC $someClassC, SomeClassD $someClassD, private int $int)
    {
    }
}

class SomeClassB
{
}

class SomeClassC
{
}

class SomeClassD
{
}

class SomeClassE
{
    public function __construct(private SomeClassA $a)
    {
        $b = new SomeClassB;
    }

    public function dependAandC(SomeClassA $a): SomeClassC
    {
    }
}

abstract class SomeAbstractClass implements SomeInterface
{
}

interface SomeInterface
{
}
```
### Execute command by specifying a directory
```shell
$ vendor/bin/mermaid-class-diagram generate --path src
classDiagram
    class SomeAbstractClass {
        <<abstract>>
    }
    class SomeClassA {
    }
    class SomeClassB {
    }
    class SomeClassC {
    }
    class SomeClassD {
    }
    class SomeClassE {
    }
    class SomeInterface {
        <<interface>>
    }

    SomeInterface <|.. SomeAbstractClass: realization
    SomeAbstractClass <|-- SomeClassA: inheritance
    SomeClassA *-- SomeClassB: composition
    SomeClassA *-- SomeClassC: composition
    SomeClassA ..> SomeClassD: dependency
    SomeClassE *-- SomeClassA: composition
    SomeClassE ..> SomeClassB: dependency
    SomeClassE ..> SomeClassC: dependency
```
```mermaid
classDiagram
    class SomeAbstractClass {
        <<abstract>>
    }
    class SomeClassA {
    }
    class SomeClassB {
    }
    class SomeClassC {
    }
    class SomeClassD {
    }
    class SomeClassE {
    }
    class SomeInterface {
        <<interface>>
    }

    SomeInterface <|.. SomeAbstractClass: realization
    SomeAbstractClass <|-- SomeClassA: inheritance
    SomeClassA *-- SomeClassB: composition
    SomeClassA *-- SomeClassC: composition
    SomeClassA ..> SomeClassD: dependency
    SomeClassE *-- SomeClassA: composition
    SomeClassE ..> SomeClassB: dependency
    SomeClassE ..> SomeClassC: dependency
```
### Execute command by specifying a file
```shell
$ vendor/bin/mermaid-class-diagram generate --path src/SomeClassA.php
classDiagram
    class SomeClassA {
    }

    SomeAbstractClass <|-- SomeClassA: inheritance
    SomeClassA *-- SomeClassB: composition
    SomeClassA *-- SomeClassC: composition
    SomeClassD <.. SomeClassA: dependency
```
```mermaid
classDiagram
    class SomeClassA {
    }

    SomeAbstractClass <|-- SomeClassA: inheritance
    SomeClassA *-- SomeClassB: composition
    SomeClassA *-- SomeClassC: composition
    SomeClassD <.. SomeClassA: dependency
```

### Filter relationships
You can hide specific relationship types via CSV with `--exclude-relationships`.

- Allowed values (case-insensitive, aliases supported):
  - `dependency` | `dependencies` | `dep` | `deps`
  - `composition` | `compositions` | `comp`
  - `inheritance` | `inheritances` | `extends`
  - `realization` | `realizations` | `implements`

Examples
```shell
# Hide dependencies and compositions
$ vendor/bin/mermaid-class-diagram generate --path src --exclude-relationships dependency,composition

# Hide only dependencies
$ vendor/bin/mermaid-class-diagram generate --path src --exclude-relationships dependency
```

## License
The MIT License (MIT). Please see [LICENSE](https://github.com/tasuku43/php-mermaid-class-diagram/blob/main/LICENSE) for more information.
