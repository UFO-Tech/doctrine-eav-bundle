<?php

namespace Ufo\EAV\Entity\Discriminators\Values;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Value;

#[ORM\Entity]
#[ORM\Table(name: 'eav_value_bool')]
class ValueBoolean extends Value
{

    public function __construct(
        Param $param,
        #[ORM\Column(name: "bool_val", type: Types::BOOLEAN)]
        protected bool $content,
        ?string $locale = null,
        ?Value $baseValue = null,
    )
    {
        parent::__construct($param, $locale, $baseValue);
    }

    public function getContent(): bool
    {
        return $this->content;
    }

    public function setContent(bool $boolVal): void
    {
        $this->content = $boolVal;
    }
}
