<?php

namespace Ufo\EAV\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Repositories\OptionRepository;

#[ORM\Entity(repositoryClass: OptionRepository::class)]
#[ORM\Table(name: 'eav_options')]
#[ORM\UniqueConstraint(name: "param_option_unique", columns: ["param", "value"])]
class Option
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    protected int $id;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Param::class, cascade: ["persist"])]
        #[ORM\JoinColumn(name: "param", referencedColumnName: "tag", onDelete: 'CASCADE')]
        protected Param $param,

        #[ORM\Column(type: "string", length: 255)]
        protected string $value
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return Param
     */
    public function getParam(): Param
    {
        return $this->param;
    }

}
