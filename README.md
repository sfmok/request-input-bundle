## RequestInputBundle
[![Test](https://github.com/sfmok/request-input/actions/workflows/php.yml/badge.svg)](https://github.com/sfmok/request-input/actions/workflows/php.yml)
[![Latest Stable Version](http://poser.pugx.org/sfmok/request-input-bundle/v/stable)](https://packagist.org/packages/sfmok/request-input-bundle)
[![License](http://poser.pugx.org/sfmok/request-input-bundle/license)](https://packagist.org/packages/sfmok/request-input-bundle)

**RequestInputBundle** converts request data into DTO inputs objects with validation.

- Request data supported: `json`, `xml` and `form` based on header content type.
- Resolve inputs arguments for controllers actions.
- Validate DTO inputs objects (An option to enable or disable it).
- Global YAML configuration
- Custom Configuration via Input Attribute per controller action

### Installation
Require the bundle with composer:
```bash
composer require sfmok/request-input-bundle
```

### How to use

- Create DTO input and implements `Sfmok\RequestInput\InputInterface`
```php
use Sfmok\RequestInput\InputInterface;

class PostInput implements InputInterface
{
    #[Assert\NotBlank]
    private string $title;

    #[Assert\NotBlank]
    private string $content;

    #[Assert\NotBlank]
    private array $tags;

    #[SerializedName('author')]
    #[Assert\NotBlank]
    private string $name;
    
    # getters and setters or make properties public
}
```
- Use DTO input in your controller action as an argument:
```php
class PostController
{
    # Example with global config
    #[Route(path: '/posts', name: 'create')]
    public function create(PostInput $input): Response
    {
        dd($input);
    }
    
    # Example with specific config
    #[Route(path: '/posts', name: 'create')]
    #[Input(format: 'json', groups: ['create'], context: ['groups' => ['create']])]
    public function create(PostInput $input): Response
    {
        dd($input);
    }
}
```

### Validations
- Response header
```
Content-Type: application/problem+json; charset=utf-8
```
- Response body
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

### Configuration
* In case you want to serve a specific input data format for all your DTO inputs with skip validation:
```yaml
# config/packages/request_input.yaml
request_input:
  enabled: true # default value true
  formats: ['json'] # default value ['json', 'xml', 'form']
  skip_validation: true # default value false
```
- With above configuration RequestInputBundle will convert JSON request data only and skip validation process.
- You can also use a format even if disabled globally by using attribute input and specify the format explicitly.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
