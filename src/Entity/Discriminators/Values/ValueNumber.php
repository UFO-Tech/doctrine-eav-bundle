<?php

namespace Ufo\EAV\Entity\Discriminators\Values;


use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Value;

#[ORM\Entity]
class ValueNumber extends Value
{
    #[ORM\Column(name: "num_val", type: Types::INTEGER)]
    protected int $content = 0;

    #[ORM\Column(name: "num_val_scale", type: Types::INTEGER)]
    protected int $scale = 0;

    public function __construct(
        Param $param,
        int|float $number = 0,
        ?string $locale = null,
        ?Value $baseValue = null,
    )
    {
        parent::__construct($param, $locale, $baseValue);
        $this->setContent($number);
    }

    public function setContent(int|float $number): void
    {
        $this->content = match (gettype($number)) {
            "double" => $this->parseFloat($number),
            "integer" => $number
        };
    }

    protected function parseFloat(float $number): int
    {
        $parts = explode('.', (string)$number);
        $this->scale = isset($parts[1]) ? strlen($parts[1]) : 0;
        return (int)str_replace('.', '', (string)$number);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContent(): float
    {
        return $this->content / (10 ** $this->scale);
    }

    public function getRawContent(): int
    {
        return $this->content;
    }

    public function getScale(): int
    {
        return $this->scale;
    }
}