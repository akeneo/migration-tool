<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Symfony\Component\Workflow\Event\Event;

/**
 * Finish Migration !.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MigrationFinished extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.transporteo.transition.finish_migration' => 'onFinishMigration',
        ];
    }

    public function onFinishMigration(Event $event): void
    {
        $transPrefix = 'migration_finished.';

        $this->printerAndAsker->section($this->translator->trans($transPrefix.'end_message'));
    }
}
