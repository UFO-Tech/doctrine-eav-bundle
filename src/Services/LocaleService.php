<?php

namespace Ufo\EAV\Services;

use Symfony\Component\Translation\LocaleSwitcher;
use Ufo\EAV\Repositories\SpecRepository;

use function array_merge;

class LocaleService
{

    public function __construct(
        protected string $defaultLocale,
        protected LocaleSwitcher $localeAwareServices,
    ) {}

    public function setLocale(?string $locale): void
    {
        $this->localeAwareServices->setLocale($locale ?? $this->defaultLocale);
    }

    public function getLocale(): string
    {
        return $this->localeAwareServices->getLocale();
    }

    public function isDefaultLocale(): bool
    {
        return $this->getLocale() === $this->defaultLocale;
    }

    public function getLocaleCriteria(bool $defaultNull = true): array
    {
        $locale = $this->getLocale();
        if ($this->isDefaultLocale() && $defaultNull) {
            $locale = null;
        }
        return [
            'locale' => $locale,
        ];
    }

    public function addLocaleToCriteria(array $criteria, bool $defaultNull = true): array
    {
        return array_merge($criteria, $this->getLocaleCriteria($defaultNull));
    }
}