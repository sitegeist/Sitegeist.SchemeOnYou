# Sitegeist.SchemeOnYou
## API Documentation that is always complete, accurate and up to date. For humans and machines

SchemOnYou makes the implementation of REST/JSON APIs simple and easy. It enforces strong conventions and
modern type safe php code for data transfer objects (DTOs). SchemOnYou generates OpenApi-Specs directy 
from the controller code wich ensures that the documentation is always complete, up to date and accurate.

Since OpenApi-Specs are human and machine readable SchemeOnYou allows to integrate with tools like swagger-ui 
but also with code generators that allow to generate type safe client code to consume the apis. 

This allows type safe and static analyzable code from the backend through JSON/Rest apis to the frontend and back.
For faster development pace while maintaining high quality and end to end type safety.

## TLDR: 

#### Sitegeist.SchemeOnYou provides:
- A strong convention for PHP DataTransferObjects based on modern PHP code
- Serializers and deserializers for those DTOs
- A base class for REST/JSON endpoint controllers where each endpoint recieves and returns DTOs
- A generator for OpenAPI schemas describing the DTOs and endpoints

#### Differences:
- Controllers extend `Sitegeist\SchemeOnYou\Application\OpenApiController`
- ControllerActions must accept and return supported types.
- Supported Types are scalar Values, DTOs and Collections of DTOs plus an exception for PHP Date objects.

#### Advantages:
- Simpler Controller logic!
- Controllers can be fully analyzed!
- Controllers can be tested via UnitTests!
- OpenApi Documentation is generated on the fly and cannot deviate from the facts.
- Return types and other details can be defined via Attributes.

#### Caveats:
- Everything going in an out must use named and supported types.
- You must not use plain arrays as type.
- Other than in method returns union types are not yet supported.

### Authors & Sponsors

* Bernhard Schmitt - schmitt@sitegeist.de
* Martin Ficzel - ficzel@sitegeist.de

*The development and the public-releases of this package is generously sponsored
by our employer http://www.sitegeist.de.*

## Build APIs 

### Configure OpenAPI Documents

The package allows to specify multiple OpenAPI documents. Each class that extends `\Sitegeist\SchemeOnYou\Application\OpenApiController`
and matches one of the configured `classNames` patterns will be included into the api document.

```yaml
Sitegeist:
  SchemeOnYou:
    documents:
      example:
        name: "Example OpenApi document"
        classNames:
          - 'Vendor\Example\Controller\*'
```

The configured OpenApi Documents spec can than be rendered via cli `./flow openapidocument:render {name}`
or via url-path `/openapi/document/{name}`.

### Create OpenApi Controllers 

OpenApi endpoints that are included in the generated documents are all `*Action` methods inside controllers that
extends the `Sitegeist\SchemeOnYou\Application\OpenApiController` and are reachable via Routing. 
Controllers must specify the type of each parameter and also the return type. 

_!!! For now union-types are only allowed in return values of Action methods. !!!_


```php
<?php
declare(strict_types=1);
namespace Vendor\Example\Controller;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Application\OpenApiController;
use Vendor\Example\Dto;

class ExampleOpenApiController extends OpenApiController
{
    public function indexAction(Query $query, string $language = 'de'): Dto\AddressCollection|Dto\NotFoundResponse {
        ... 
    }
}
```

### PHP Attributes

The following PHP Attributes allow to specify the details of the parameter and schema handling.

- ControllerAction Parameters
  - `\Sitegeist\SchemeOnYou\Domain\Metadata\RequestBody` a single method parameter can be marked a being the request body
  - `\Sitegeist\SchemeOnYou\Domain\Metadata\Parameter` all other parameters can be marked as being transferred via `query`, `path`, `header` or `cookie`. If a parameter has no attributes it is handled as `query` parameter.
- DTO Classes 
  - `\Sitegeist\SchemeOnYou\Domain\Metadata\Response` the response Attribute allows to mark a dto with a status-code other than 200.
  - `\Sitegeist\SchemeOnYou\Domain\Metadata\Schema` the schema Attribute specifies a name and description for a DTO if that is not to be derived from the PHP name.

## Supported Types

The following property types are supported by this package. You will notice the absence of arrays here but data transfer objects (DTOs)
and collections which allow much finer control about property conversion.

### Scalar Values

Values of type `string`, `int`, `float`, and `bool` are allowed directly by OpenApi and need no transformation.

_!!! `null` is not allowed as a single type. However, nullable values are allowed !!!_

### PHP Date Objects

Objects of type `\DateTime`, `\DateTimeImmutable`, `\DateInterval` are allowed as exceptions. 
The values are serialized as string with a predefined format.

### Backed Enums

Value backed enums are supported by converting to and from the underlying value.

### Data Transfer Objects (DTOs)

A supported data transfer object has to adhere to the following rules:
- The class is `readonly`
- The class has a public constructor
- All parameters in the constructor are `public`, `promoted` and of a supported type
- The number of properties equals the number of constructor arguments

```php
#[Flow\Proxy(false)]
final readonly class Address
{
    public function __construct(
        public string $streetAddress,
        public ?string $addressRegion,
        public ?string $addressCountry = 'DE',
        public ?string $postOfficeBoxNumber = null
    ) {
    }
}
```

If the DTO has a single property of name `value` it is serialized as that single value. 
In all other cases the DTO is serialized as an array of all constructor properties.


```php
#[Flow\Proxy(false)]
final readonly class Identifier
{
    public function __construct(
        public string $value,
    ) {
    }
}
```

### Collection of DTO Objects

A supported collection object has to adhere to the following rules:
- The class is `readonly`
- The class has a public constructor
- The constructor has a single variadic parameter of a supported type
- The class has a single `public`, `readonly` property

```php
#[Flow\Proxy(false)]
final readonly class AddressCollection
{
    /**
     * @var Address[]
     */
    public array $items;

    public function __construct(Address ...$items)
    {
        $this->items = array_values($items);
    }
}
```

_!!! There is a small chance the arguments passed to the constructor are not stored in the class property. We have to accept that until variadic arguments can be promoted. !!!_
  
## Installation

Sitegeist.SchemeOnYou is available via packagist. Run `composer require sitegeist/schemeonyou` to require this package. 
You may also want to install `flowpack/cors` or any other CORS package if you are using the endpoints from different urls .

We use semantic versioning, so every breaking change will increase the major version number.

## Contribution

We will gladly accept contributions. Please send us pull requests.
