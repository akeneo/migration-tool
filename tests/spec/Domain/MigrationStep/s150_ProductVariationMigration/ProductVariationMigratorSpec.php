<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidInnerVariationTypeException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidProductVariationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidVariantGroupException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductVariationMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductVariationMigratorSpec extends ObjectBehavior
{
    public function let(
        ChainedConsole $console,
        InnerVariationTypeMigrator $innerVariantTypeMigrator,
        VariantGroupMigrator $variantGroupMigrator,
        VariantGroupRepository $variantGroupRepository,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($console, $innerVariantTypeMigrator, $variantGroupMigrator, $variantGroupRepository, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductVariationMigrator::class);
    }

    public function it_successfully_migrates_product_variations(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $console,
        $innerVariantTypeMigrator,
        $variantGroupMigrator,
        $variantGroupRepository
    ) {
        $sourcePim->hasIvb()->willReturn(true);
        $innerVariantTypeMigrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $variantGroupRepository->retrieveNumberOfVariantGroups($destinationPim)->willReturn(2);
        $variantGroupMigrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $console->execute(new SymfonyCommand('pim:product:index --all', SymfonyCommand::PROD), $destinationPim)->shouldBeCalled();
        $console->execute(new SymfonyCommand('pim:product-model:index --all', SymfonyCommand::PROD), $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_throws_an_exception_if_there_are_invalid_product_variations(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $console,
        $innerVariantTypeMigrator,
        $variantGroupMigrator,
        $variantGroupRepository
    ) {
        $sourcePim->hasIvb()->willReturn(true);
        $invalidInnerVariationTypeException = new InvalidInnerVariationTypeException();
        $innerVariantTypeMigrator->migrate($sourcePim, $destinationPim)->willThrow($invalidInnerVariationTypeException);

        $variantGroupRepository->retrieveNumberOfVariantGroups($destinationPim)->willReturn(2);
        $invalidVariantGroupException = new InvalidVariantGroupException(1);
        $variantGroupMigrator->migrate($sourcePim, $destinationPim)->willThrow($invalidVariantGroupException);

        $console->execute(new SymfonyCommand('pim:product:index --all', SymfonyCommand::PROD), $destinationPim)->shouldBeCalled();
        $console->execute(new SymfonyCommand('pim:product-model:index --all', SymfonyCommand::PROD), $destinationPim)->shouldBeCalled();

        $this->shouldThrow(new InvalidProductVariationException([$invalidInnerVariationTypeException->getMessage(), $invalidVariantGroupException->getMessage()]))
            ->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_does_nothing_if_there_are_no_product_variations(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $variantGroupRepository,
        $logger
    ) {
        $sourcePim->hasIvb()->willReturn(false);
        $logger->info('There is no InnerVariationType to migrate.')->shouldBeCalled();

        $variantGroupRepository->retrieveNumberOfVariantGroups($destinationPim)->willReturn(0);
        $logger->info("There are no variant groups to migrate")->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}