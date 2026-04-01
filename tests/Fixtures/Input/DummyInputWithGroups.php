<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Fixtures\Input;

use Sfmok\RequestInput\Attribute\AsInput;
use Sfmok\RequestInput\Metadata\SerializationMetadata;
use Sfmok\RequestInput\Metadata\ValidationMetadata;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[AsInput(
    validation: new ValidationMetadata(groups: ['foo']),
    serialization: new SerializationMetadata(context: ['groups' => 'foo']),
)]
class DummyInputWithGroups
{
    #[Assert\NotBlank]
    private string $title;

    #[Assert\NotBlank]
    private string $content;

    #[Assert\Type(type: 'array')]
    private array $tags = [];

    #[SerializedName('author')]
    #[Assert\NotBlank]
    private string $name;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
