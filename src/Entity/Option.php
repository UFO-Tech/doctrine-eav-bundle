<?php

namespace Ufo\EAV\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Types\Types as EntityTypes;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Repositories\OptionRepository;

#[ORM\Entity(repositoryClass: OptionRepository::class)]
#[ORM\Table(name: 'eav_options')]
#[ORM\Index(name: "option_id_idx", columns: ["id"])]
#[ORM\Index(name: "option_locale_idx", columns: ["locale"])]
#[ORM\UniqueConstraint(name: "param_option_unique", columns: ["param", "value", "locale"])]
class Option
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected int $id;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Param::class, cascade: ["persist"])]
        #[ORM\JoinColumn(name: "param", referencedColumnName: "tag", onDelete: 'CASCADE')]
        protected Param $param,

        #[ORM\Column(type: Types::STRING, length: 255)]
        protected string $value,

        #[ORM\Column(type: EntityTypes::STRING, length: 5, nullable: true)]
        protected ?string $locale = null,

        #[ORM\ManyToOne(targetEntity: Option::class, fetch: 'LAZY')]
        #[ORM\JoinColumn(name: "base_option_id", referencedColumnName: "id", nullable: true, onDelete: 'SET NULL')]
        protected ?self $baseOption = null
    ) {}

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

    public function getBaseOption(): ?Option
    {
        return $this->baseOption;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
