<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Fixtures\Input;

use Sfmok\RequestInput\InputInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;

class DummyInput implements InputInterface
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
     * @Assert\Type(type="array")
     */
    private array $tags = [];

    /**
     * @SerializedName('author')
     * @Assert\NotBlank()
     */
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