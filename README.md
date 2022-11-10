## RequestInputBundle
[![Test](https://github.com/sfmok/request-input/actions/workflows/php.yml/badge.svg)](https://github.com/sfmok/request-input/actions/workflows/php.yml)
[![Latest Stable Version](http://poser.pugx.org/sfmok/request-input-bundle/v/stable)](https://packagist.org/packages/sfmok/request-input-bundle)
[![License](http://poser.pugx.org/sfmok/request-input-bundle/license)](https://packagist.org/packages/sfmok/request-input-bundle)

RequestInput bundle provides auto-transform request data into DTO input objects
- Request data supported: `json`, `xml` and `form`
- Resolve inputs arguments for controllers actions

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
    /**
     * @Assert\NotBlank()
     */
    private string $title;

    /**
     * @Assert\NotBlank()
     */
    private string $content;

    /**
     * @Assert\NotBlank()
     */
    private array $tags = [];

    /**
     * @SerializedName('author')
     * @Assert\NotBlank()
     */
    private string $name;
    
    # getters and setters or make properties public
}
```
- Use DTO input in your controller action as an argument:
```php
class PostController
{
    /**
     * @Route("/posts", methods={"POST"})
     */
    public function create(PostInput $input): Response
    {
        # dump input
        dd($input);
        
        # set entity data and store
        $post = (new Post())
            ->setTitle($input->getTitle())
            ->setContent($input->getContent())
            ->setTags($input->getTags())
            ->setName($input->getName())
        ;
            
        $em->persist($post);
        $em->flush();
        
        ...
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
* In case you want to serve a specific format for your inputs:
```yaml
# config/packages/request_input.yaml
request_input:
  enabled: true # default value true
  formats: ['json'] # default value ['json', 'xml', 'form']
```
with above configuration RequestInputBundle will transform JSON request data only.
