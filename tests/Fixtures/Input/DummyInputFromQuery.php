<?php

declare(strict_types=1);

namespace Sfmok\RequestInput\Tests\Fixtures\Input;

use Sfmok\RequestInput\Attribute\AsInput;
use Sfmok\RequestInput\Enum\Source;
use Symfony\Component\Validator\Constraints as Assert;

#[AsInput(source: Source::QueryString)]
class DummyInputFromQuery
{
    #[Assert\NotBlank]
    private string $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
