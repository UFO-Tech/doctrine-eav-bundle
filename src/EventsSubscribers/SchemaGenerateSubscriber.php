<?php

namespace Ufo\EAV\EventsSubscribers;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Ufo\EAV\Command\CreateViewMigrationCommand;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Entity\Views\SpecDetailsJson;

class SchemaGenerateSubscriber implements EventSubscriber
{
    const VIEWS_FOR_CREATE = [
        SpecDetail::VIEW_NAME,
        SpecDetailsJson::VIEW_NAME,
        self::TABLE_BLOCKER
    ];
    
    const TABLE_BLOCKER = Spec::TABLE_NAME;

    public function getSubscribedEvents()
    {
        return ['postGenerateSchema'];
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args)
    {
        $schema = $args->getSchema();

        // Перевірка наявності таблиці, якщо вона вже існує, не додаємо view
        if (!$schema->hasTable('eav_spec')) {
            return;
        }
        
        $views = $args->getEntityManager()->getConnection()
            ->executeQuery("SHOW FULL TABLES")
            ->fetchAllNumeric()
        ;

        $issetView = array_filter($views, function ($res) {
            return in_array($res[0], static::VIEWS_FOR_CREATE);
        });
        
        if (count($issetView) > 0) {
            return;
        }

        shell_exec('php bin/console ' . CreateViewMigrationCommand::COMMAND_NAME);
    }
}