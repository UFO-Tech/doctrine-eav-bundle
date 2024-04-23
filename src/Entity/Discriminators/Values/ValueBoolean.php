<?php

namespace Ufo\EAV\Entity\Discriminators\Values;

use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Value;

#[ORM\Entity]
#[ORM\Table(name: 'eav_value_bool')]
class ValueBoolean extends Value
{

    public function __construct(
        Param $param,
        #[ORM\Column(name: "bool_val", type: "boolean")]
        protected bool $content
    )
    {
        parent::__construct($param);
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
