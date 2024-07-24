<?php

namespace Ufo\EAV\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Ufo\EAV\Entity\Views\CommonParamsFilter;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Entity\Views\SpecDetailsJson;

#[AsCommand(
    name: CreateViewMigrationCommand::COMMAND_NAME,
    description: 'Creates a migration file with view creation SQL.'
)]
class CreateViewMigrationCommand extends Command
{
    const COMMAND_NAME = 'ufo:eav:create-view-migration';
    

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
        $specDetail = SpecDetail::VIEW_NAME;
        $specDetailJson = SpecDetailsJson::VIEW_NAME;
        $commonParams = CommonParamsFilter::VIEW_NAME;
        return "\$this->addSql(\"CREATE VIEW $specDetail AS
             WITH RECURSIVE SpecHierarchy AS (
                SELECT
                    s.id,
                    s.parent_id,
                    s.name
                FROM
                    eav_spec s
                UNION ALL
                SELECT
                    s.id,
                    s.parent_id,
                    s.name
                FROM
                    eav_spec s
                    INNER JOIN SpecHierarchy sh ON s.parent_id = sh.id
            ),
            SpecValues AS (
                SELECT
                    sh.id AS spec_id,
                    p.tag AS param_tag,
                    p.name AS param_name,
                    v.value_type,
                    CASE
                        WHEN v.value_type = 'number' THEN v.num_val / POWER(10, v.num_val_scale)
                        WHEN v.value_type = 'string' THEN COALESCE(v.str_val_short, v.str_val_long)
                        WHEN v.value_type = 'boolean' THEN v.bool_val
                        WHEN v.value_type = 'options' THEN (SELECT GROUP_CONCAT(o.value SEPARATOR ', ') FROM eav_value_options vo
                                                                JOIN eav_options o ON vo.param_option_id = o.id
                                                            WHERE vo.value_option_id = v.id)
                        ELSE NULL
                    END AS value
                FROM
                    SpecHierarchy sh
                    LEFT JOIN eav_specs_values sv ON sh.id = sv.spec_id
                    LEFT JOIN eav_values v ON sv.value_id = v.id
                    LEFT JOIN eav_params p ON v.param = p.tag
            ),
            ParentValues AS (
                SELECT
                    sh.id AS spec_id,
                    p.tag AS param_tag,
                    p.name AS param_name,
                    v.value_type,
                    CASE
                        WHEN v.value_type = 'number' THEN v.num_val / POWER(10, v.num_val_scale)
                        WHEN v.value_type = 'string' THEN COALESCE(v.str_val_short, v.str_val_long)
                        WHEN v.value_type = 'boolean' THEN v.bool_val
                        WHEN v.value_type = 'options' THEN (SELECT GROUP_CONCAT(o.value SEPARATOR ', ') FROM eav_value_options vo
                                                                JOIN eav_options o ON vo.param_option_id = o.id
                                                            WHERE vo.value_option_id = v.id)
                        ELSE NULL
                    END AS value
                FROM
                    SpecHierarchy sh
                    LEFT JOIN eav_specs_values sv ON sh.parent_id = sv.spec_id
                    LEFT JOIN eav_values v ON sv.value_id = v.id
                    LEFT JOIN eav_params p ON v.param = p.tag
            )
            SELECT DISTINCT
                MD5(CONCAT(sh.id, '-', p.tag, '-', COALESCE(v.value, pv.value))) AS unique_id,
                sh.id AS spec_id,
                sh.name AS spec_name,
                p.name AS param_name,
                p.tag AS param_tag,
                p.filtered AS param_filtered,
                COALESCE(v.value_type, pv.value_type) AS value_type,
                COALESCE(v.value, pv.value) AS value
            FROM
                SpecHierarchy sh
                LEFT JOIN eav_params p ON TRUE
                LEFT JOIN SpecValues v ON sh.id = v.spec_id AND p.tag = v.param_tag
                LEFT JOIN ParentValues pv ON sh.id = pv.spec_id AND p.tag = pv.param_tag
             WHERE
                COALESCE(v.value_type, pv.value_type) IS NOT NULL
            ORDER BY
                sh.id
            ;\");
        
        \$this->addSql(\"CREATE VIEW $commonParams AS
            SELECT
                MD5(CONCAT(s.param_tag, '-', CAST(s.value AS CHAR))) AS unique_id,
                s.param_tag,
                s.param_name,
                CAST(s.value AS CHAR) AS value,
                s.value_type,
                s.spec_count,
                s.total_count
            FROM (
                SELECT
                    s.param_tag,
                    s.param_name,
                    CAST(s.value AS CHAR) AS value,
                    s.value_type,
                    COUNT(DISTINCT s.spec_id) AS spec_count,
                    SUM(COUNT(DISTINCT s.spec_id)) OVER (PARTITION BY s.param_tag) AS total_count
                FROM
                    eav_spec_details_view s
                LEFT JOIN eav_params p ON s.param_tag = p.tag
                WHERE
                    s.value_type != 'file'
                    AND p.filtered = TRUE
                GROUP BY
                    s.param_tag, s.param_name, s.value, s.value_type
            ) AS s
            WHERE
                s.total_count >= (SELECT COUNT(DISTINCT id) FROM eav_spec)
            ORDER BY s.param_tag;
        \");

        \$this->addSql(\"CREATE VIEW $specDetailJson AS
            SELECT 
                spec_id,
                spec_name,
                JSON_OBJECTAGG(
                    param_tag, 
                    JSON_OBJECT(
                        'tag', param_tag,
                        'filter', param_filtered,
                        'name', param_name,
                        'type', value_type,
                        'value', value
                    )
                ) AS spec_values
            FROM 
                eav_spec_details_view
            GROUP BY 
                spec_id, spec_name
            ORDER BY 
                spec_id;
        \");";
    }

    public function getDownSql(): string
    {
        $specDetail = SpecDetail::VIEW_NAME;
        $specDetailJson = SpecDetailsJson::VIEW_NAME;
        $commonParams = CommonParamsFilter::VIEW_NAME;
        return "\$this->addSql('DROP VIEW `$specDetail`;');
        \$this->addSql('DROP VIEW `$specDetailJson`;');
        \$this->addSql('DROP VIEW `$commonParams`;');";
    }
}