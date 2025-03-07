<?php

namespace Ufo\EAV\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ufo\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Ufo\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use Ufo\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Ufo\DoctrineBehaviors\Model\Tree\TreeNodeTrait;
use Ufo\EAV\Entity\Translations\EavCategoryTranslation;

use function array_merge;
use function is_nan;
use function is_null;
use function str_pad;

use const STR_PAD_LEFT;

#[ORM\MappedSuperclass]
abstract class EavCategory implements TreeNodeInterface, TranslatableInterface
{
    const string DEFAULT_LANG = 'en';
    const string PREFIX = 'category_';
    use TreeNodeTrait, TranslatableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected int $id;

    #[ORM\OneToMany(targetEntity: EavEntity::class, mappedBy: 'category', cascade: ['persist', 'remove'], fetch: 'LAZY')]
    protected Collection $entities;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 255)]
        protected string $name,

        #[ORM\Column(type: Types::JSON)]
        protected array $filters,

        #[ORM\Column(type: Types::STRING, length: 30, unique: true, nullable: true)]
        protected ?string $slug = null,

        string $defaultLocale = self::DEFAULT_LANG
    )
    {
        $this->setDefaultLocale($defaultLocale);
        $this->entities = new ArrayCollection();
    }

    public static function getTranslationEntityClass(): string
    {
        return EavCategoryTranslation::class;
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

    public function changeFilters(array $filters, ?string $locale = null): static
    {
        is_null($locale) || $this->getDefaultLocale() === $locale
            ? $this->filters = $filters
            : $this->translate($locale)->changeFilters($filters);
        
        return $this;
    }

    public function getFilters(?string $locale = null): array
    {
        /**
         * @var static $parent
         */
        $parent = $this->parentNode;
        $parentFilter = $parent?->getFilters($locale) ?? [];

        $thisFilter = $this->translate($locale)?->getFilters() ?? $this->filters;
        return array_merge($parentFilter, $thisFilter);
    }

    public function getName(?string $locale = null): string
    {
        return $this->translate($locale)?->getName() ?? $this->name;
    }

    public function rename(string $name, ?string $locale = null): static
    {
        is_null($locale) || $this->getDefaultLocale() === $locale
            ? $this->name = $name
            : $this->translate($locale)->rename($name);
        return $this;
    }
}