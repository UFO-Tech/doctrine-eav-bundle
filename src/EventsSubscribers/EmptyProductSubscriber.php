<?php

namespace Ufo\EAV\EventsSubscribers;

use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\EavEntity;

class EmptyProductSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::preRemove
        ];
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        $em = $args->getObjectManager();

        if ($entity instanceof Spec) {
            $product = $entity->getEav();

            if ($product->getSpecifications()->count() === 1) {
                $em->remove($product);
                $em->flush();
            }
        }
    }
}