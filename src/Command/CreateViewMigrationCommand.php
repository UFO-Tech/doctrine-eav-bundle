<?php

namespace Ufo\EAV\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: CreateViewMigrationCommand::COMMAND_NAME,
    description: 'Creates a migration file with view creation SQL.'
)]
class CreateViewMigrationCommand extends Command
{
    const COMMAND_NAME = 'ufo:eav:create-view-migration';
    
//    public function __construct(protected string $migrationPath) { }


    protected function configure()
    {
        $this
            ->setHidden(true)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $migrationName = shell_exec('php bin/console doctrine:migrations:generate');

        $matches = [];
        preg_match('/to "(.+?)"/', $migrationName, $matches);

        if (!empty($matches)) {
            $migrationFile = $matches[1];
            $fs = new Filesystem();
            try {
                $originalContent = file_get_contents($migrationFile);
                $newContent = str_replace('// this up() migration is auto-generated, please modify it to your needs', $this->getUpSql(), $originalContent);
                $newContent = str_replace('// this down() migration is auto-generated, please modify it to your needs', $this->getDownSql(), $newContent);
                $fs->dumpFile($migrationFile, $newContent);
                $io->success('Migration file created and SQL added successfully.');
            } catch (IOExceptionInterface $exception) {
            }
        } else {
            $io->error('Error parse migration name');
        }
        return Command::SUCCESS;
    }


    public function getUpSql(): string
    {
        return "\$this->addSql(\"CREATE VIEW eav_spec_details_view AS
            SELECT
                s.id AS spec_id,
                s.name AS spec_name,
                p.name AS param_name,
                p.tag AS param_tag,
                v.value_type,
            
                CASE
                    WHEN v.value_type = 'number' THEN v.num_val / POWER(10, v.num_val_scale)
                    WHEN v.value_type = 'string' THEN COALESCE(v.str_val_short, v.str_val_long)
                    WHEN v.value_type = 'boolean' THEN v.bool_val
                    WHEN v.value_type = 'file' THEN JSON_OBJECT(
                        'name', v.file_val_name,
                        'mime_type', v.file_val_mime_type,
                        'size', v.file_val_size,
                        'url', v.file_val_url,
                        'storage', v.file_val_storage
                    )
                    WHEN v.value_type = 'options' THEN (SELECT GROUP_CONCAT(o.value SEPARATOR ', ') FROM eav_value_options vo
                                                        JOIN eav_options o ON vo.param_option_id = o.id
                                                        WHERE vo.value_option_id = v.id)
                    ELSE NULL
                END AS value
            FROM
                eav_spec s
            LEFT JOIN eav_specs_values sv ON s.id = sv.spec_id
            LEFT JOIN eav_values v ON sv.value_id = v.id
            LEFT JOIN eav_params p ON v.param = p.tag;\"
        );

        \$this->addSql(\"CREATE VIEW eav_spec_details_json_view AS
            SELECT
                s.id AS spec_id,
                s.name AS spec_name,
                JSON_OBJECTAGG(
                    p.tag,
                    JSON_OBJECT(
                        'name', p.name,
                        'tag', p.tag,
                        'value', sd.value
                    )
                ) AS spec_values
            FROM
                eav_spec s
            LEFT JOIN eav_specs_values sv ON s.id = sv.spec_id
            LEFT JOIN eav_values v ON sv.value_id = v.id
            LEFT JOIN eav_params p ON v.param = p.tag
            LEFT JOIN eav_spec_details_view sd ON s.id = sd.spec_id AND p.tag = sd.param_tag
            GROUP BY s.id, s.name;
        \");";
    }

    public function getDownSql(): string
    {
        return "\$this->addSql('DROP VIEW `eav_spec_details`;');
        \$this->addSql('DROP VIEW `eav_spec_details_json`;');";
    }
}