<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidInnerVariationTypeException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationCleaner;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationFamilyMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeValidator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationTypeMigratorSpec extends ObjectBehavior
{
    public function let(
        InnerVariationTypeRepository $innerVariationTypeRepository,
        InnerVariationFamilyMigrator $innerVariationFamilyMigrator,
        InnerVariationProductMigrator $innerVariationProductMigrator,
        InnerVariationCleaner $innerVariationCleaner,
        InnerVariationTypeValidator $innerVariationTypeValidator,
        LoggerInterface $logger
    )
    {
        $this->beConstructedWith(
            $innerVariationTypeRepository,
            $innerVariationFamilyMigrator,
            $innerVariationProductMigrator,
            $innerVariationCleaner,
            $innerVariationTypeValidator,
            $logger
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(InnerVariationTypeMigrator::class);
    }

    public function it_successfully_migrates_inner_variation_types(
        $innerVariationTypeRepository,
        $innerVariationFamilyMigrator,
        $innerVariationProductMigrator,
        $innerVariationCleaner,
        $innerVariationTypeValidator,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    )
    {
        $firstVariationFamily = new Family(10, 'an_inner_variation_family', []);
        $secondVariationFamily = new Family(11, 'another_inner_variation_family', []);

        $firstInnerVariationType = new InnerVariationType(
            1, 'ivt_with_two_axes', $firstVariationFamily, [
                ['code' => 'axis_1', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axis_2', 'attribute_type' => 'pim_catalog_metric']
            ]
        );

        $secondInnerVariationType = new InnerVariationType(
            2, 'ivt_with_one_axis', $secondVariationFamily, [['code' => 'axis_1', 'attribute_type' => 'pim_catalog_simpleselect']]
        );

        $innerVariationTypeRepository->findAll($destinationPim)->willReturn([$firstInnerVariationType, $secondInnerVariationType]);

        $innerVariationTypeValidator->canInnerVariationTypeBeMigrated($firstInnerVariationType)->willReturn(true);
        $innerVariationTypeValidator->canInnerVariationTypeBeMigrated($secondInnerVariationType)->willReturn(true);

        $innerVariationFamilyMigrator->migrate($firstInnerVariationType, $destinationPim)->shouldBeCalled();
        $innerVariationProductMigrator->migrate($firstInnerVariationType, $destinationPim)->shouldBeCalled();

        $innerVariationFamilyMigrator->migrate($secondInnerVariationType, $destinationPim)->shouldBeCalled();
        $innerVariationProductMigrator->migrate($secondInnerVariationType, $destinationPim)->shouldBeCalled();

        $innerVariationCleaner->cleanInnerVariationTypes([$firstInnerVariationType, $secondInnerVariationType], $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_does_not_migrate_the_invalid_inner_variation_types(
        $innerVariationTypeRepository,
        $innerVariationFamilyMigrator,
        $innerVariationProductMigrator,
        $innerVariationCleaner,
        $innerVariationTypeValidator,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    )
    {
        $firstVariationFamily = new Family(10, 'an_inner_variation_family', []);
        $secondVariationFamily = new Family(11, 'another_inner_variation_family', []);

        $validInnerVariationType = new InnerVariationType(
            1, 'valid_ivt', $firstVariationFamily, [
                ['code' => 'axis_1', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axis_2', 'attribute_type' => 'pim_reference_data_simpleselect'],
                ['code' => 'axis_3', 'attribute_type' => 'pim_catalog_metric'],
                ['code' => 'axis_4', 'attribute_type' => 'pim_catalog_boolean'],
            ]
        );

        $invalidInnerVariationType = new InnerVariationType(
            2, 'invalid_ivt', $secondVariationFamily, [
                ['code' => 'axis_1', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'invalid_axis', 'attribute_type' => 'pim_catalog_identifier'],
            ]
        );

        $innerVariationTypeRepository->findAll($destinationPim)->willReturn([$validInnerVariationType, $invalidInnerVariationType]);

        $innerVariationTypeValidator->canInnerVariationTypeBeMigrated($validInnerVariationType)->willReturn(true);
        $innerVariationTypeValidator->canInnerVariationTypeBeMigrated($invalidInnerVariationType)->willReturn(false);

        $innerVariationFamilyMigrator->migrate($validInnerVariationType, $destinationPim)->shouldBeCalled();
        $innerVariationProductMigrator->migrate($validInnerVariationType, $destinationPim)->shouldBeCalled();

        $innerVariationFamilyMigrator->migrate($invalidInnerVariationType, $destinationPim)->shouldNotBeCalled();
        $innerVariationProductMigrator->migrate($invalidInnerVariationType, $destinationPim)->shouldNotBeCalled();

        $innerVariationCleaner->cleanInnerVariationTypes([$validInnerVariationType, $invalidInnerVariationType], $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_continues_to_migrate_if_an_exception_is_thrown(
        $innerVariationTypeRepository,
        $innerVariationFamilyMigrator,
        $innerVariationProductMigrator,
        $innerVariationCleaner,
        $innerVariationTypeValidator,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    )
    {
        $firstVariationFamily = new Family(10, 'an_inner_variation_family', []);
        $secondVariationFamily = new Family(11, 'another_inner_variation_family', []);

        $firstInnerVariationType = new InnerVariationType(
            1, 'ivt_with_two_axes', $firstVariationFamily, [
                ['code' => 'axis_1', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axis_2', 'attribute_type' => 'pim_catalog_metric']
            ]
        );

        $secondInnerVariationType = new InnerVariationType(
            2, 'ivt_with_one_axis', $secondVariationFamily, [['code' => 'axis_1', 'attribute_type' => 'pim_catalog_simpleselect']]
        );

        $innerVariationTypeRepository->findAll($destinationPim)->willReturn([$firstInnerVariationType, $secondInnerVariationType]);

        $innerVariationTypeValidator->canInnerVariationTypeBeMigrated($firstInnerVariationType)->willReturn(true);
        $innerVariationTypeValidator->canInnerVariationTypeBeMigrated($secondInnerVariationType)->willReturn(true);

        $innerVariationFamilyMigrator->migrate($firstInnerVariationType, $destinationPim)->shouldBeCalled();
        $innerVariationProductMigrator
            ->migrate($firstInnerVariationType, $destinationPim)
            ->willThrow(new \Exception());

        $innerVariationFamilyMigrator->migrate($secondInnerVariationType, $destinationPim)->shouldBeCalled();
        $innerVariationProductMigrator->migrate($secondInnerVariationType, $destinationPim)->shouldBeCalled();

        $innerVariationCleaner->cleanInnerVariationTypes([$firstInnerVariationType, $secondInnerVariationType], $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}
