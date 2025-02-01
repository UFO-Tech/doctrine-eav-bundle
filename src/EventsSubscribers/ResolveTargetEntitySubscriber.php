<?php

namespace Ufo\EAV\EventsSubscribers;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Ufo\EAV\Entity\EavCategory;
use Ufo\EAV\Entity\EavEntity;

class ResolveTargetEntitySubscriber implements EventSubscriber
{
    const array METADATA_CLASSES = [
        EavEntity::class => true,
        EavCategory::class => true,
    ];

    /**
     * @var ClassMetadata[]
     */
    protected array $mapping = [];

    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {}

    public function getSubscribedEvents(): array
    {
        return [];
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();

        foreach ($this->mapping as $abstractClass => $data) {
            $eavTableName = $data['eavTable'] ?? null;
            $realTableName = $data['realTable'] ?? null;

            if (!$eavTableName || !$realTableName) continue;

            foreach ($schema->getTables() as $tName => $table) {
                if ($table->getName() === $eavTableName) {
                    $schema->renameTable($table->getName(), $realTableName);
                }

                foreach ($table->getForeignKeys() as $key => $foreignKeyObj) {
                    if ($foreignKeyObj->getForeignTableName() === $eavTableName) {
                        $table->removeForeignKey($key);

                        $table->addForeignKeyConstraint(
                            $realTableName,
                            $foreignKeyObj->getLocalColumns(),
                            $foreignKeyObj->getForeignColumns(),
                            $foreignKeyObj->getOptions()
                        );
                    }
                }
            }
        }
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $classMetadata = $args->getClassMetadata();
        $refClass = $classMetadata->getReflectionClass();
        $refParent = $refClass->getParentClass();

        if (static::METADATA_CLASSES[$refClass->getName()] ?? false) {
            $this->mapping[$refClass->getName()]['eavTable'] = $this->getTableNameFromClass($classMetadata);
            $this->mapping[$refClass->getName()]['eavMetadata'] = $classMetadata;
        }

        if ($refParent && (static::METADATA_CLASSES[$refParent->getName()] ?? false)) {
            $this->mapping[$refParent->getName()]['realTable'] = $this->getTableNameFromClass($classMetadata);
            $this->mapping[$refParent->getName()]['realMetadata'] = $classMetadata;
        }
    }

    private function getTableNameFromClass(ClassMetadata $targetClass): string
    {
        return $targetClass->getTableName();
    }

}