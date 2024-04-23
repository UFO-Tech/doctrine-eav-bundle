<?php

namespace Ufo\EAV\EventsListeners;

use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Value;

class RemoveListener implements EventSubscriber
{
    /**
     * @var object[]
     */
    protected static array $forRemove = [];

    public function preFlush(PreFlushEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();
        foreach (static::$forRemove as $i => $entity) {
            $entityManager->remove($entity);
            unset(static::$forRemove[$i]);
        }
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::preFlush
        ];
    }

    /**
     * @param object $forRemove
     */
    public static function add(object $forRemove): void
    {
        static::$forRemove[] = $forRemove;
    }
}
