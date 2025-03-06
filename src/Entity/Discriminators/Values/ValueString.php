<?php

namespace Ufo\EAV\Entity\Discriminators\Values;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Value;

#[ORM\Entity]
class ValueString extends Value
{
    private const int MAX_SHORT_CONTENT_LENGTH = 255;  // Define your length limit for VARCHAR column

    #[ORM\Column(name: "str_val_short", type: Types::STRING, length: ValueString::MAX_SHORT_CONTENT_LENGTH, nullable: true)]
    protected ?string $contentShort = null;

    #[ORM\Column(name: "str_val_long", type: Types::TEXT, nullable: true)]
    protected ?string $contentLong = null;

    public function __construct(
        Param $param,
        string $content,
        ?string $locale = null,
        ?Value $baseValue = null,
    )
    {
        parent::__construct($param, $locale, $baseValue);
        $this->setContent($content);
    }

    public function setContent(string $content): void
    {
        $this->contentShort = null;
        $this->contentLong = $content;

        if (mb_strlen($content, '8bit') <= self::MAX_SHORT_CONTENT_LENGTH) {
            $this->contentShort = $content;
            $this->contentLong = null;
        }
    }

    public function getContent(): string
    {
        return $this->contentShort ?? $this->contentLong;
    }
    public function isEmpty(): bool
    {
        return empty($this->getContent());
    }

    public function isLongContent(): bool
    {
        return !empty($this->textContent);
    }

    public function getOriginalLength(): int
    {
        return mb_strlen($this->getContent(), '8bit');
    }
}
