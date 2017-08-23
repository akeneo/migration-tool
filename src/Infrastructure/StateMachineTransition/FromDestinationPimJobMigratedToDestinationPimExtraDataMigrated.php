<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\ExtraDataMigration\ExtraDataMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate extra data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromDestinationPimJobMigratedToDestinationPimExtraDataMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var ExtraDataMigrator */
    private $extraDataMigrator;

    public function __construct(
        Translator $translator,
        ExtraDataMigrator $extraDataMigrator
    ) {
        parent::__construct($translator);
        $this->extraDataMigrator = $extraDataMigrator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.destination_pim_extra_data_migration' => 'onDestinationPimExtraDataMigration',
        ];
    }

    public function onDestinationPimExtraDataMigration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage($this->translator->trans('from_destination_pim_job_migrated_to_destination_pim_extra_data_migrated.message'));

        $this->extraDataMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
    }
}
