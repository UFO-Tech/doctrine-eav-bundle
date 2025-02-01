<?php

namespace Ufo\EAV\EventsSubscribers;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Static_;
use Ufo\EAV\Command\CreateViewMigrationCommand;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Views\CommonParamsFilter;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Entity\Views\SpecDetailsJson;

class SchemaGenerateSubscriber implements EventSubscriber
{
    const array VIEWS_FOR_CREATE = [
        SpecDetail::VIEW_NAME,
        SpecDetailsJson::VIEW_NAME,
        CommonParamsFilter::VIEW_NAME,
    ];
    
    const string TABLE_BLOCKER = Spec::TABLE_NAME;

    public function getSubscribedEvents(): array
    {
        return ['postGenerateSchema'];
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();

        if (!$schema->hasTable(static::TABLE_BLOCKER)) {
            return;
        }
        
        $views = $args->getEntityManager()->getConnection()
            ->executeQuery("SHOW FULL TABLES")
            ->fetchAllNumeric()
        ;

        $issetView = array_filter($views, function ($res) {
            return in_array($res[0], static::VIEWS_FOR_CREATE);
        });
        $schema->dropTable(SpecDetail::VIEW_NAME);
        $schema->dropTable(SpecDetailsJson::VIEW_NAME);
        $schema->dropTable(CommonParamsFilter::VIEW_NAME);

        if (count($issetView) > 0) {
            return;
        }

        shell_exec('php bin/console ' . CreateViewMigrationCommand::COMMAND_NAME);
    }
}