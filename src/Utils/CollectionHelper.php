<?php

namespace Ufo\EAV\Utils;

use Doctrine\ORM\PersistentCollection;

class CollectionHelper
{
    /**
     * @param array<array-key, mixed> $values
     */
    public static function prePopulateEntityCollection(
        object $entity,
        string $collectionName,
        array $values,
    ): void {
        $method = sprintf('get%s', ucfirst($collectionName));
        $collection = $entity->$method();

        if (!$collection instanceof PersistentCollection) {
            return;
        }

        $collection->setInitialized(true);
        $collection->setDirty(true);

        foreach ($values as $value) {
            $collection->add($value);
        }

        $collection->setDirty(false);
        $collection->setInitialized(true);
    }
}