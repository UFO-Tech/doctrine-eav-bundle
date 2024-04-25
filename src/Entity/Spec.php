<?php

namespace Ufo\EAV\Entity;

use App\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Interfaces\IHaveParamsAccess;
use Ufo\EAV\Interfaces\IHaveValuesAccess;
use Ufo\EAV\Traits\SpecValuesAccessors;

#[ORM\Table(name: Spec::TABLE_NAME)]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Spec implements IHaveParamsAccess, IHaveValuesAccess
{
    const TABLE_NAME = 'eav_spec';
    
    use SpecValuesAccessors;

    const DEFAULT = 'noname';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    protected int $id;

    #[ORM\ManyToMany(targetEntity: Value::class, inversedBy: "specs", cascade: ["persist"], fetch: 'LAZY')]
    #[ORM\JoinTable(name: 'eav_specs_values',
        joinColumns: [new ORM\JoinColumn(name: 'spec_id', referencedColumnName: 'id', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'value_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    )]
    protected Collection $values;

    #[ORM\ManyToMany(targetEntity: Param::class, inversedBy: "specs", cascade: ["persist"], fetch: 'LAZY')]
    #[ORM\JoinTable(name: 'eav_specs_params',
        joinColumns: [new ORM\JoinColumn(name: 'spec_id', referencedColumnName: 'id', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'param', referencedColumnName: 'tag', onDelete: 'CASCADE')]
    )]
    protected Collection $params;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Product::class, cascade: ['persist', 'remove'], inversedBy: "specifications")]
        #[ORM\JoinColumn(name: "eav_id", referencedColumnName: "id", onDelete: 'CASCADE')]
        protected EavEntity $eav,

        #[ORM\Column(type: "string", length: 255)]
        protected string    $name = self::DEFAULT
    )
    {
        $this->values = new ArrayCollection();
        $this->params = new ArrayCollection();
        $this->onPostLoad();
    }
    #[ORM\PostLoad]
    public function onPostLoad(): void
    {
        $this->state = $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEav(): EavEntity
    {
        return $this->eav;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function rename(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    #[ORM\PostPersist]
    public function setDefaultNameForMainSpecification(): void
    {
        if ($this->getName() == Spec::DEFAULT) {
            $this->rename('sp:' . $this->eav->getId() . '.' . $this->getId());
        }
    }
}
