<?php

namespace Ufo\EAV\Entity;

use App\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Interfaces\IHaveParamsAccess;
use Ufo\EAV\Interfaces\IHaveValuesAccess;
use Ufo\EAV\Repositories\SpecRepository;
use Ufo\EAV\Traits\SpecValuesAccessors;

#[ORM\Table(name: Spec::TABLE_NAME)]
#[ORM\Index(columns: ["id"], name: "spec_id_idx", )]
#[ORM\Entity(repositoryClass: SpecRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Spec implements IHaveParamsAccess, IHaveValuesAccess
{

    use SpecValuesAccessors;

    const TABLE_NAME = 'eav_spec';
    const DEFAULT = 'noname';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected int $id;

    #[ORM\OneToMany(targetEntity: Spec::class, mappedBy: 'parent', cascade: ["persist"])]
    protected Collection $children;

    #[ORM\ManyToOne(targetEntity: Spec::class, inversedBy: 'children',  cascade: ["persist"])]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Spec $parent = null;

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
        #[ORM\ManyToOne(targetEntity: Product::class, cascade: ['persist', 'remove'], fetch: 'LAZY', inversedBy: "specifications")]
        #[ORM\JoinColumn(name: "eav_id", referencedColumnName: "id", onDelete: 'CASCADE')]
        protected EavEntity $eav,

        #[ORM\Column(type: Types::STRING, length: 255)]
        protected string $name = self::DEFAULT
    )
    {
        $this->values = new ArrayCollection();
        $this->params = new ArrayCollection();
        $this->children = new ArrayCollection();
        if (($mainSpec = $this->eav->getMainSpecification()) !== $this) {
            $this->parent = $mainSpec;
        }
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

    /**
     * @return Collection
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @return Spec|null
     */
    public function getParent(): ?Spec
    {
        return $this->parent;
    }

    /**
     * @param Collection $children
     * @return static
     */
    public function setChildren(Collection $children): static
    {
        $this->children = $children;

        return $this;
    }

    public function addChildren(string $name = self::DEFAULT): Spec
    {
        $child = new Spec($this->getEav(), $name);
        $this->children->add($child);
        return $child;
    }

}
