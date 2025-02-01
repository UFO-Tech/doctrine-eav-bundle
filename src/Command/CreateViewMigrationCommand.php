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
    const string COMMAND_NAME = 'ufo:eav:create-view-migration';
    

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
             with `SpecHierarchy` as (select `child`.`spec_id` AS `spec_id`, `parent`.`spec_id` AS `parent_id`
                 from ((`test`.`eav_specs_values` `child` join `test`.`eav_values` `pv`
                      on (((`child`.`value_id` = `pv`.`id`) and (`pv`.`param` = 'parent')))) join `test`.`eav_specs_values` `parent`
                      on ((`pv`.`str_val_short` = (select `v`.`str_val_short`
                           from (`test`.`eav_values` `v` join `test`.`eav_specs_values` `sv` on ((`v`.`id` = `sv`.`value_id`)))
                           where ((`v`.`param` = 'barcode') and (`parent`.`spec_id` = `sv`.`spec_id`))
                           limit 1))))
                 union all
                 select `s`.`id` AS `spec_id`, NULL AS `parent_id`
                 from `test`.`eav_spec` `s`
                 where exists(select 1
                     from (`test`.`eav_specs_values` `sv` join `test`.`eav_values` `v` on (((`sv`.`value_id` = `v`.`id`) and (`v`.`param` = 'parent'))))
                     where (`sv`.`spec_id` = `s`.`id`)) is false),
                     `SpecValues` as (select `sh`.`spec_id`      AS `spec_id`,
                         `sh`.`parent_id`    AS `parent_id`,
                         `p`.`tag`           AS `param_tag`,
                         `p`.`name`          AS `param_name`,
                         `v`.`value_type`    AS `value_type`,
                         (case
                              when (`v`.`value_type` = 'number') then (`v`.`num_val` / pow(10, `v`.`num_val_scale`))
                              when (`v`.`value_type` = 'string') then coalesce(`v`.`str_val_short`, `v`.`str_val_long`)
                              when (`v`.`value_type` = 'boolean') then `v`.`bool_val`
                              when (`v`.`value_type` = 'options')
                                   then (select group_concat(`o`.`value` separator ', ')
                                        from (`test`.`eav_value_options` `vo` 
                                        join `test`.`eav_options` `o` on ((`vo`.`param_option_id` = `o`.`id`)))
                                        where (`vo`.`value_option_id` = `v`.`id`))
                              else NULL end) AS `value`
                    from (((`SpecHierarchy` `sh` 
                    left join `test`.`eav_specs_values` `sv` on ((`sh`.`spec_id` = `sv`.`spec_id`))) 
                    left join `test`.`eav_values` `v` on ((`sv`.`value_id` = `v`.`id`))) 
                    left join `test`.`eav_params` `p` on ((`v`.`param` = `p`.`tag`)))),
                     `ParentValues` as (select `sh`.`spec_id`      AS `spec_id`,
                         `p`.`tag`           AS `param_tag`,
                         `p`.`name`          AS `param_name`,
                         `v`.`value_type`    AS `value_type`,
                         (case
                             when (`v`.`value_type` = 'number') then (`v`.`num_val` / pow(10, `v`.`num_val_scale`))
                             when (`v`.`value_type` = 'string') then coalesce(`v`.`str_val_short`, `v`.`str_val_long`)
                             when (`v`.`value_type` = 'boolean') then `v`.`bool_val`
                             when (`v`.`value_type` = 'options')
                                 then (select group_concat(`o`.`value` separator ', ')
                                     from (`test`.`eav_value_options` `vo` 
                                     join `test`.`eav_options` `o` on ((`vo`.`param_option_id` = `o`.`id`)))
                                     where (`vo`.`value_option_id` = `v`.`id`))
                             else NULL end) AS `value`
                                 from (((`SpecHierarchy` `sh` 
                                     left join `test`.`eav_specs_values` `sv` on ((`sh`.`parent_id` = `sv`.`spec_id`))) 
                                     left join `test`.`eav_values` `v` on ((`sv`.`value_id` = `v`.`id`))) 
                                     left join `test`.`eav_params` `p` on ((`v`.`param` = `p`.`tag`)))
             )
            select distinct md5(concat(`sh`.`spec_id`, '-', `p`.`tag`, '-', coalesce(`v`.`value`, `pv`.`value`))) AS `unique_id`,
                `sh`.`spec_id`  AS `spec_id`,
                `s`.`name`      AS `spec_name`,
                `p`.`name`      AS `param_name`,
                `p`.`tag`       AS `param_tag`,
                `p`.`filtered`  AS `param_filtered`,
                coalesce(`v`.`value_type`, `pv`.`value_type`) AS `value_type`,
                coalesce(`v`.`value`, `pv`.`value`)           AS `value`
                from ((((`SpecHierarchy` `sh` 
                left join `test`.`eav_params` `p` on (true)) 
                left join `test`.`eav_spec` `s` on ((`s`.`id` = `sh`.`spec_id`))) 
                left join `SpecValues` `v` on (((`sh`.`spec_id` = `v`.`spec_id`) and (`p`.`tag` = `v`.`param_tag`)))) 
                left join `ParentValues` `pv` on (((`sh`.`spec_id` = `pv`.`spec_id`) and (`p`.`tag` = `pv`.`param_tag`))))
                where (coalesce(`v`.`value_type`, `pv`.`value_type`) is not null)
                order by `sh`.`spec_id`
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