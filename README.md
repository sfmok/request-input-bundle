## RequestInputBundle

[![CI](https://github.com/sfmok/request-input/actions/workflows/ci.yml/badge.svg)](https://github.com/sfmok/request-input/actions/workflows/ci.yml)
[![codecov](https://codecov.io/github/sfmok/request-input-bundle/branch/main/graph/badge.svg?token=9EDGRUPYCB)](https://codecov.io/github/sfmok/request-input-bundle)
[![Latest Stable Version](http://poser.pugx.org/sfmok/request-input-bundle/v/stable)](https://packagist.org/packages/sfmok/request-input-bundle)
[![License](http://poser.pugx.org/sfmok/request-input-bundle/license)](https://packagist.org/packages/sfmok/request-input-bundle)

**RequestInputBundle** deserializes and validates HTTP request data into typed DTO objects, resolvable directly as controller arguments.

---

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [The `#[AsInput]` Attribute](#the-asinput-attribute)
  - [Body Payload (JSON / XML)](#body-payload-json--xml)
  - [Query String](#query-string)
- [Validation](#validation)
- [Deserialization Errors](#deserialization-errors)
- [Configuration](#configuration)
  - [Global YAML defaults](#global-yaml-defaults)
  - [Per-DTO overrides with `ValidationMetadata`](#per-dto-overrides-with-validationmetadata)
  - [Per-DTO overrides with `SerializationMetadata`](#per-dto-overrides-with-serializationmetadata)
  - [Combining both overrides](#combining-both-overrides)
- [Using `InputFactoryInterface` outside controllers](#using-inputfactoryinterface-outside-controllers)
- [License](#license)

---

## Installation

```bash
composer require sfmok/request-input-bundle
```

---

## Quick Start

Decorate your DTO class with `#[AsInput]` and type-hint it as a controller argument — that's all.

```php
use Sfmok\RequestInput\Attribute\AsInput;
use Symfony\Component\Validator\Constraints as Assert;

#[AsInput]
class CreatePostInput
{
    #[Assert\NotBlank]
    public string $title;

    #[Assert\NotBlank]
    public string $content;
}
```

```php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController
{
    #[Route('/posts', methods: ['POST'])]
    public function create(CreatePostInput $input): Response
    {
        // $input is already deserialized and validated
        echo $input->title;
    }
}
```

Send a JSON request:

```http
POST /posts
Content-Type: application/json

{"title": "Hello", "content": "World"}
```

---

## The `#[AsInput]` Attribute

```php
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsInput
{
    public function __construct(
        public ?Source $source = null,           // Source::BodyPayload (default) or Source::QueryString
        public ?ValidationMetadata $validation = null,
        public ?SerializationMetadata $serialization = null,
    ) {}
}
```

All parameters are optional. `#[AsInput]` with no arguments uses the global YAML defaults.

### Body Payload (JSON / XML)

The default source is `Source::BodyPayload`. The serialization format is determined from the request `Content-Type` header.

Supported content types:

| Content-Type | Format |
|---|---|
| `application/json`, `application/x-json` | JSON |
| `application/xml`, `text/xml` | XML |

```php
#[AsInput]
class CreatePostInput
{
    #[Assert\NotBlank]
    public string $title;

    #[Assert\NotBlank]
    #[SerializedName('body')]   // maps JSON key "body" → $content
    public string $content;
}
```

```http
POST /posts
Content-Type: application/xml

<request><title>Hello</title><body>World</body></request>
```

### Query String

Set `source: Source::QueryString` to populate the DTO from query parameters instead of the request body. No `Content-Type` header is required.

```php
use Sfmok\RequestInput\Attribute\AsInput;
use Sfmok\RequestInput\Enum\Source;
use Symfony\Component\Validator\Constraints as Assert;

#[AsInput(source: Source::QueryString)]
class SearchInput
{
    #[Assert\NotBlank]
    public string $query;

    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 20;

    public int $page = 1;
}
```

```php
class SearchController
{
    #[Route('/search', methods: ['GET'])]
    public function search(SearchInput $input): Response
    {
        // populated from ?query=foo&limit=10&page=2
    }
}
```

---

## Validation

When validation fails the bundle throws a `ValidationException` (an `HttpException`) which is caught by the built-in `ExceptionListener` and converted to a structured JSON response.

**Response headers:**

```
HTTP/1.1 400 Bad Request
Content-Type: application/json
```

**Response body:**

```json
{
  "type": "https://symfony.com/errors/validation",
  "title": "Validation Failed",
  "detail": "title: This value should not be blank.",
  "violations": [
    {
      "propertyPath": "title",
      "title": "This value should not be blank.",
      "parameters": {
        "{{ value }}": "\"\""
      },
      "type": "urn:uuid:c1051bb4-d103-4f74-8988-acbcafc7fdc3"
    }
  ]
}
```

Validation groups and the HTTP status code are configurable (see [Configuration](#configuration)).

---

## Deserialization Errors

Both type mismatches and syntax errors produce a structured 400 response.

**Type mismatch** — sending an integer where a string is expected:

```json
{ "title": 42 }
```

```json
{
  "title": "Deserialization Failed",
  "detail": "Data error",
  "violations": [
    {
      "propertyPath": "title",
      "message": "This value should be of type string",
      "currentType": "int"
    }
  ]
}
```

**Syntax error** — malformed JSON:

```json
{ "title": "foo", }
```

```json
{
  "title": "Deserialization Failed",
  "detail": "Syntax error",
  "violations": []
}
```

---

## Configuration

### Global YAML defaults

```yaml
# config/packages/request_input.yaml
request_input:
  enabled: true           # disable the bundle entirely (default: true)
  validation:
    skip: false           # skip validation for all inputs (default: false)
    status_code: 400      # HTTP status code for validation errors (default: 400)
  serialization:
    context: []           # Symfony Serializer context applied to all inputs (default: [])
```

### Per-DTO overrides with `ValidationMetadata`

`ValidationMetadata` lets you override `skip`, `status_code`, and `groups` for a specific DTO. Per-DTO values take precedence over global YAML; unset fields fall back to the global value.

| Property | Type | Description |
|---|---|---|
| `skip` | `bool\|null` | Skip validation for this DTO |
| `statusCode` | `int\|null` | HTTP status code on validation failure |
| `groups` | `string[]\|null` | Validation groups to apply |

```php
use Sfmok\RequestInput\Attribute\AsInput;
use Sfmok\RequestInput\Metadata\ValidationMetadata;
use Symfony\Component\Validator\Constraints as Assert;

#[AsInput(
    validation: new ValidationMetadata(
        groups: ['create'],
        statusCode: 422,
    )
)]
class CreatePostInput
{
    #[Assert\NotBlank(groups: ['create'])]
    public string $title;
}
```

To skip validation entirely for one DTO while keeping it enabled globally:

```php
#[AsInput(validation: new ValidationMetadata(skip: true))]
class RawWebhookInput
{
    public string $payload;
}
```

### Per-DTO overrides with `SerializationMetadata`

`SerializationMetadata` lets you pass a Symfony Serializer context for a specific DTO. The per-DTO context is **merged on top of** the global context (per-DTO keys win on conflict).

| Property | Type | Description |
|---|---|---|
| `context` | `array<string, mixed>` | Symfony Serializer context entries |

```php
use Sfmok\RequestInput\Attribute\AsInput;
use Sfmok\RequestInput\Metadata\SerializationMetadata;

#[AsInput(
    serialization: new SerializationMetadata(
        context: ['groups' => ['create']]
    )
)]
class CreatePostInput
{
    #[Groups(['create'])]
    public string $title;

    #[Groups(['edit'])]       // excluded when deserializing with group 'create'
    public string $slug;
}
```

### Combining both overrides

```php
use Sfmok\RequestInput\Attribute\AsInput;
use Sfmok\RequestInput\Metadata\SerializationMetadata;
use Sfmok\RequestInput\Metadata\ValidationMetadata;

#[AsInput(
    validation: new ValidationMetadata(groups: ['create'], statusCode: 422),
    serialization: new SerializationMetadata(context: ['groups' => ['create']]),
)]
class CreatePostInput
{
    #[Assert\NotBlank(groups: ['create'])]
    #[Groups(['create'])]
    public string $title;
}
```

---

## Using `InputFactoryInterface` outside controllers

Inject `InputFactoryInterface` anywhere in your application to resolve inputs manually.

```php
use Sfmok\RequestInput\Factory\InputFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class PostService
{
    public function __construct(private InputFactoryInterface $inputFactory) {}

    public function handle(Request $request): void
    {
        /** @var CreatePostInput $input */
        $input = $this->inputFactory->createFromRequest($request, CreatePostInput::class);

        // $input is deserialized and validated, or null if CreatePostInput
        // is not decorated with #[AsInput]
    }
}
```

`createFromRequest` returns `null` when the given class does not carry the `#[AsInput]` attribute, making it safe to call speculatively.

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
