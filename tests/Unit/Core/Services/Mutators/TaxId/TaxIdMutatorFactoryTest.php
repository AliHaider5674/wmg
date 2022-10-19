<?php

namespace Tests\Unit\Core\Services\Mutators\TaxId;

use App\Core\Services\Mutators\TaxId\Brazil\BrazilTaxIdFormatter;
use App\Core\Services\Mutators\TaxId\Brazil\BrazilTaxIdValidator;
use App\Core\Services\Mutators\TaxId\PassThrough\PassThroughTaxIdFormatter;
use App\Core\Services\Mutators\TaxId\PassThrough\PassThroughTaxIdValidator;
use App\Core\Services\Mutators\TaxId\TaxIdMutatorFactory;
use Tests\TestCase;

/**
 * Class TaxIdMutatorFactoryTest
 * @package App\Core
 */
class TaxIdMutatorFactoryTest extends TestCase
{
    private const BRAZIL_COUNTRY_CODE = 'BR';
    /**
     * Default formatter when one does not exist for a country code
     */
    private const DEFAULT_FORMATTER = PassThroughTaxIdFormatter::class;

    /**
     * Default validator when one does not exist for a country code
     */
    private const DEFAULT_VALIDATOR = PassThroughTaxIdValidator::class;

    /**
     * @var TaxIdMutatorFactory
     */
    private $taxIdMutatorFactory;

    /**
     * Set up tests
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->taxIdMutatorFactory = app(TaxIdMutatorFactory::class);
    }

    /**
     * Test that passing in "BR" as the country code will return the Brazil
     * formatter and validator
     *
     * @return void
     */
    public function testPassingBrazilCountryCodeAsUppercaseWillReturnBrazilMutatorClasses(): void
    {
        $mutator = $this->taxIdMutatorFactory->create(
            strtoupper(self::BRAZIL_COUNTRY_CODE)
        );

        self::assertInstanceOf(BrazilTaxIdValidator::class, $mutator->getValidator());
        self::assertInstanceOf(BrazilTaxIdFormatter::class, $mutator->getFormatter());
    }

    /**
     * Test that passing in "br" (lowercase) as the country code will return the
     * Brazil formatter and validator
     *
     * @return void
     */
    public function testPassingBrazilCountryCodeAsLowercaseWillReturnBrazilMutatorClasses(): void
    {
        $mutator = $this->taxIdMutatorFactory->create(
            strtolower(self::BRAZIL_COUNTRY_CODE)
        );

        self::assertInstanceOf(BrazilTaxIdValidator::class, $mutator->getValidator());
        self::assertInstanceOf(BrazilTaxIdFormatter::class, $mutator->getFormatter());
    }

    /**
     * Test that passing in an undefined country code will return the default
     * formatter and validator
     *
     * @return void
     */
    public function testPassingRandomCountryCodeWillReturnDefaultMutatorClasses(): void
    {
        $mutator = $this->taxIdMutatorFactory->create(strtolower(
            $this->helper->fakerCountryCodeOtherThan(self::BRAZIL_COUNTRY_CODE)
        ));

        self::assertInstanceOf(self::DEFAULT_VALIDATOR, $mutator->getValidator());
        self::assertInstanceOf(self::DEFAULT_FORMATTER, $mutator->getFormatter());
    }
}
