<?php

namespace Ufo\EAV\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Utils\DiscriminatorMapper;
use Ufo\EAV\Utils\Types;
use Ufo\EAV\Utils\ValueEntityMap;
use Doctrine\DBAL\Types\Types as EntityTypes;

#[ORM\Entity]
#[ORM\Table(name: 'eav_values')]
#[ORM\Index(columns: ["id", "param", "value_type"], name: "value_id_param_value_type_idx")]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "value_type", type: EntityTypes::STRING, enumType: Types::class)]
#[ORM\DiscriminatorMap([
    Types::BOOLEAN->value   => ValueEntityMap::BOOLEAN->value,
    Types::FILE->value      => ValueEntityMap::FILE->value,
    Types::NUMBER->value    => ValueEntityMap::NUMBER->value,
    Types::OPTIONS->value   => ValueEntityMap::OPTIONS->value,
    Types::STRING->value    => ValueEntityMap::STRING->value,
])]
abstract class Value
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: EntityTypes::INTEGER)]
    protected int $id;

    #[ORM\ManyToMany(targetEntity: Spec::class, mappedBy: "values", cascade: ['persist'], fetch: 'LAZY')]
    protected Collection $specs;

    /**
     * @param Param $param
     */
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Param::class, cascade: ['persist'], fetch: 'LAZY', inversedBy: "values")]
        #[ORM\JoinColumn(name: "param", referencedColumnName: "tag", onDelete: 'CASCADE')]
        protected Param $param
    )
    {
        $this->specs = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Param
     */
    public function getParam(): Param
    {
        return $this->param;
    }

    /**
     * @return Collection
     */
    public function getSpecs(): Collection
    {
        return $this->specs;
    }

    /**
     * @return mixed
     */
    abstract public function getContent(): mixed;

    /**
     * Creates a value entity based on the value type determined by the provided value.
     *
     * @param Param $param The parameter associated with the value.
     * @param mixed $value The value itself.
     * @return Value Returns an instance of the appropriate value class.
     */
    public static function create(Param $param, mixed $value): Value
    {
        return new (DiscriminatorMapper::valueClass($value))($param, $value);
    }

}
