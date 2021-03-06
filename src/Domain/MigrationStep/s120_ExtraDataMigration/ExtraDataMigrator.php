<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s120_ExtraDataMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Migrator for extra data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ExtraDataMigrator implements DataMigrator
{
    /** @var TableMigrator */
    private $tableMigrator;

    /** @var ChainedConsole */
    private $chainedConsole;

    public function __construct(TableMigrator $tableMigrator, ChainedConsole $chainedConsole)
    {
        $this->tableMigrator = $tableMigrator;
        $this->chainedConsole = $chainedConsole;
    }

    /**
     * {@inheritdoc}
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        try {
            $tablesInSourcePimResults = $this->chainedConsole->execute(new MySqlQueryCommand('SHOW TABLES'), $sourcePim)->getOutput();

            $tablesInSourcePim = array_map(function ($element) {
                return reset($element);
            }, $tablesInSourcePimResults);

            $extraTables = array_diff($tablesInSourcePim, $this->getSourcePimStandardTables());

            foreach ($extraTables as $extraTable) {
                $this->tableMigrator->migrate($sourcePim, $destinationPim, $extraTable);
            }
        } catch (\Exception $exception) {
            throw new ExtraDataMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    protected function getSourcePimStandardTables(): array
    {
        return [
            'acl_classes',
            'acl_entries',
            'acl_object_identities',
            'acl_object_identity_ancestors',
            'acl_security_identities',
            'akeneo_batch_job_execution',
            'akeneo_batch_job_instance',
            'akeneo_batch_step_execution',
            'akeneo_batch_warning',
            'akeneo_file_storage_file_info',
            'oro_access_group',
            'oro_access_role',
            'oro_config',
            'oro_config_value',
            'oro_navigation_history',
            'oro_navigation_item',
            'oro_navigation_item_pinbar',
            'oro_navigation_pagestate',
            'oro_navigation_title',
            'oro_user',
            'oro_user_access_group',
            'oro_user_access_group_role',
            'oro_user_access_role',
            'pim_api_access_token',
            'pim_api_auth_code',
            'pim_api_client',
            'pim_api_refresh_token',
            'pim_catalog_association',
            'pim_catalog_association_group',
            'pim_catalog_association_product',
            'pim_catalog_association_type',
            'pim_catalog_association_type_translation',
            'pim_catalog_attribute',
            'pim_catalog_attribute_group',
            'pim_catalog_attribute_group_translation',
            'pim_catalog_attribute_locale',
            'pim_catalog_attribute_option',
            'pim_catalog_attribute_option_value',
            'pim_catalog_attribute_requirement',
            'pim_catalog_attribute_translation',
            'pim_catalog_category',
            'pim_catalog_category_product',
            'pim_catalog_category_translation',
            'pim_catalog_channel',
            'pim_catalog_channel_currency',
            'pim_catalog_channel_locale',
            'pim_catalog_channel_translation',
            'pim_catalog_completeness',
            'pim_catalog_currency',
            'pim_catalog_family',
            'pim_catalog_family_attribute',
            'pim_catalog_family_translation',
            'pim_catalog_group',
            'pim_catalog_group_attribute',
            'pim_catalog_group_product',
            'pim_catalog_group_translation',
            'pim_catalog_group_type',
            'pim_catalog_group_type_translation',
            'pim_catalog_locale',
            'pim_catalog_metric',
            'pim_catalog_product',
            'pim_catalog_product_template',
            'pim_catalog_product_value',
            'pim_catalog_product_value_option',
            'pim_catalog_product_value_price',
            'pim_comment_comment',
            'pim_datagrid_view',
            'pim_enrich_sequential_edit',
            'pim_notification_notification',
            'pim_notification_user_notification',
            'pim_session',
            'pim_user_default_datagrid_view',
            'pim_versioning_version',
            'pimee_product_asset_asset',
            'pimee_product_asset_asset_category',
            'pimee_product_asset_asset_tag',
            'pimee_product_asset_category',
            'pimee_product_asset_category_translation',
            'pimee_product_asset_channel_variation_configuration',
            'pimee_product_asset_file_metadata',
            'pimee_product_asset_reference',
            'pimee_product_asset_tag',
            'pimee_product_asset_variation',
            'pimee_security_asset_category_access',
            'pimee_security_attribute_group_access',
            'pimee_security_job_profile_access',
            'pimee_security_locale_access',
            'pimee_security_product_category_access',
            'pimee_teamwork_assistant_completeness_per_attribute_group',
            'pimee_teamwork_assistant_project',
            'pimee_teamwork_assistant_project_product',
            'pimee_teamwork_assistant_project_status',
            'pimee_teamwork_assistant_project_user_group',
            'pimee_workflow_category_published_product',
            'pimee_workflow_group_published_product',
            'pimee_workflow_product_draft',
            'pimee_workflow_published_product',
            'pimee_workflow_published_product_association',
            'pimee_workflow_published_product_association_published_group',
            'pimee_workflow_published_product_association_published_product',
            'pimee_workflow_published_product_completeness',
            'pimee_workflow_published_product_metric',
            'pimee_workflow_published_product_value',
            'pimee_workflow_published_product_value_asset',
            'pimee_workflow_published_product_value_option',
            'pimee_workflow_published_product_value_price',
        ];
    }
}
