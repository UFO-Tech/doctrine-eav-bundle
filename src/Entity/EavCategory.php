<?php

namespace Ufo\EAV\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ufo\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use Ufo\DoctrineBehaviors\Model\Tree\TreeNodeTrait;

use function array_merge;
use function str_pad;

#[ORM\MappedSuperclass]
abstract class EavCategory implements TreeNodeInterface
{
    const string PREFIX = 'category_';
    use TreeNodeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected int $id;

    #[ORM\OneToMany(targetEntity: EavEntity::class, mappedBy: 'category', cascade: ['persist', 'remove'], fetch: 'LAZY')]
    protected Collection $entities;

    public function __construct(
       #[ORM\Column(type: Types::JSON)]
        protected array $filters,

        #[ORM\Column(type: Types::STRING, length: 30, unique: true, nullable: true)]
        protected ?string $slug = null,
    )
    {
        $this->entities = new ArrayCollection();
    }

   public function getEntities(): Collection
    {
        return $this->entities;
    }

    public function addEntity(EavEntity $entity): static
    {
        if (!$this->entities->contains($entity)) {
            $this->entities->add($entity);
            $entity->setCategory($this);
        }

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug ?? static::PREFIX . str_pad((string)$this->id, 6, '0', STR_PAD_LEFT);
    }

    public function removeEntity(EavEntity $entity): static
    {
        if ($this->entities->removeElement($entity)) {
            $entity->setCategory(null);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function changeFilters(array $filters): static
    {
        $this->filters = $filters;
        return $this;
    }

    public function getFilters(): array
    {
        /**
         * @var static $parent
         */
        $parent = $this->parentNode;
        $parentFilter = $parent?->getFilters() ?? [];
        return array_merge($parentFilter, $this->filters);
    }

}