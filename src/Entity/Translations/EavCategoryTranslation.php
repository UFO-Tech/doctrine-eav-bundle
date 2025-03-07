<?php

namespace Ufo\EAV\Entity\Translations;


use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ufo\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Ufo\DoctrineBehaviors\Model\Translatable\TranslationTrait;
use Ufo\EAV\Entity\EavCategory;

use function array_merge;

#[ORM\Entity]
class EavCategoryTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::JSON)]
    protected array $filters = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function changeFilters(array $filters): static
    {
        $this->filters = $filters;
        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public static function getTranslatableEntityClass(): string
    {
        return EavCategory::class;
    }

}