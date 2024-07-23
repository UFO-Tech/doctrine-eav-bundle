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
                        INNER JOIN SpecHierarchy sh ON s.id = sh.parent_id
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
                                   sh.parent_id AS spec_id,
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
                COALESCE(v.value_type, pv.value_type) AS value_type,
                COALESCE(v.value, pv.value) AS value
            FROM
                SpecHierarchy sh
                    LEFT JOIN eav_params p ON TRUE
                    LEFT JOIN SpecValues v ON sh.id = v.spec_id AND p.tag = v.param_tag
                    LEFT JOIN ParentValues pv ON sh.parent_id = pv.spec_id AND p.tag = pv.param_tag
            WHERE
                pv.param_tag IS NOT NULL
            ORDER BY
                spec_id
            ;\");
        
        \$this->addSql(\"CREATE VIEW $commonParams AS
            SELECT
                MD5(CONCAT(e.param_tag, '-', e.value)) AS unique_id,
                e.param_tag,
                e.param_name,
                e.value,
                e.value_type,
                e.spec_count,
                e.total_count
            FROM (
                 SELECT
                     e.param_tag,
                     e.param_name,
                     e.value,
                     e.value_type,
                     COUNT(DISTINCT e.spec_id) AS spec_count,
                     SUM(COUNT(DISTINCT e.spec_id)) OVER (PARTITION BY e.param_tag) AS total_count
                 FROM (
                      SELECT
                          s.id AS spec_id,
                          p.tag AS param_tag,
                          p.name AS param_name,
                          v.value_type as value_type,
                          CASE
                              WHEN v.value_type = 'number' THEN CAST(v.num_val / POWER(10, v.num_val_scale) AS CHAR)
                              WHEN v.value_type = 'string' THEN COALESCE(v.str_val_short, v.str_val_long)
                              WHEN v.value_type = 'boolean' THEN CAST(v.bool_val AS CHAR)
                              WHEN v.value_type = 'options' THEN o.value
                              ELSE NULL
                              END AS value
                      FROM
                          eav_spec s
                              LEFT JOIN eav_specs_values sv ON s.id = sv.spec_id
                              LEFT JOIN eav_values v ON sv.value_id = v.id
                              LEFT JOIN eav_params p ON v.param = p.tag
                              LEFT JOIN eav_value_options vo ON vo.value_option_id = v.id
                              LEFT JOIN eav_options o ON vo.param_option_id = o.id
                      WHERE v.value_type != 'file'
                  ) AS e
                 GROUP BY
                     e.param_tag, e.param_name, e.value, e.value_type
                 ) AS e
            WHERE e.total_count >= (SELECT COUNT(DISTINCT id) FROM eav_spec where parent_id is not null)
            ORDER BY e.param_tag;\"
        );

        \$this->addSql(\"CREATE VIEW $specDetailJson AS
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
        $specDetail = SpecDetail::VIEW_NAME;
        $specDetailJson = SpecDetailsJson::VIEW_NAME;
        $commonParams = CommonParamsFilter::VIEW_NAME;
        return "\$this->addSql('DROP VIEW `$specDetail`;');
        \$this->addSql('DROP VIEW `$specDetailJson`;');
        \$this->addSql('DROP VIEW `$commonParams`;');";
    }
}