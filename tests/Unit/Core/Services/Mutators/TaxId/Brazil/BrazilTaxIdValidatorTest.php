<?php

namespace Tests\Unit\Core\Services\Mutators\TaxId\Brazil;

use App\Core\Services\Mutators\TaxId\Brazil\BrazilTaxIdValidator;
use Tests\TestCase;

/**
 * Class BrazilTaxIdValidatorTest
 * @package App\Core
 */
class BrazilTaxIdValidatorTest extends TestCase
{
    /**
     * @var BrazilTaxIdValidator
     */
    private $validator;

    /**
     * Set up tests
     */
    public function setUp(): void
    {
        $this->validator = new BrazilTaxIdValidator();

        parent::setUp();
    }

    /**
     * Test that validating valid Tax IDs returns true and has no errors
     *
     * @test
     * @group mutator
     * @group taxid
     * @param string $taxId
     * @dataProvider validTaxIdProvider
     */
    public function validateValidTaxIdReturnsTrue(string $taxId): void
    {
        self::assertTrue(
            $this->validator->validate($taxId)
        );

        self::assertFalse($this->validator->hasErrors());
        self::assertEmpty($this->validator->getErrors());
    }

    /**
     * Test that validating invalid Tax IDs will return false and has errors
     *
     * @test
     * @group mutator
     * @group taxid
     * @param string $taxId
     * @param int $errorsCount
     * @dataProvider invalidTaxIdProvider
     */
    public function validateInvalidTaxIdHasErrors(string $taxId, int $errorsCount): void
    {
        self::assertFalse(
            $this->validator->validate($taxId)
        );

        self::assertTrue($this->validator->hasErrors());
        self::assertNotEmpty($this->validator->getErrors());
        self::assertCount($errorsCount, $this->validator->getErrors());
    }

    /**
     * Valid Tax IDs
     *
     * @return string[][]
     */
    public function validTaxIdProvider(): array
    {
        return [
            [
                'taxId' => '92039459030',
            ],
            [
                'taxId' => '920.394.590-30',
            ],
            [
                'taxId' => '920394590-30',
            ],
            [
                'taxId' => '79.681.587/0001-00',
            ],
            [
                'taxId' => '23.555.418/0001-60',
            ],
            [
                'taxId' => '375.544.710-07',
            ],
            [
                'taxId' => '28.345.677/0001-80',
            ],
            [
                'taxId' => '28345677/0001-80',
            ],
            [
                'taxId' => '283456770001-80',
            ],
        ];
    }

    /**
     * Invalid Tax IDs
     *
     * @return string[][]
     */
    public function invalidTaxIdProvider(): array
    {
        return [
            [
                'taxId' => '52834567700@180',
                'errorCount' => 1,
            ],
            [
                'taxId' => 'ID: 92039459030',
                'errorCount' => 1,
            ],
            [
                'taxId' => '9220.394.590-30362',
                'errorCount' => 1,
            ],
            [
                'taxId' => '#920.394.590-30362',
                'errorCount' => 1,
            ],
            [
                'taxId' => '920394f590303924543',
                'errorCount' => 2,
            ],
            [
                'taxId' => '123456.382h2.ba038/7890',
                'errorCount' => 2,
            ],
            [
                'taxId' => '283938/3-s0',
                'errorCount' => 2,
            ],
            [
                'taxId' => '#920.394.590-3250362',
                'errorCount' => 2,
            ],
            [
                'taxId' => '83456770s00180',
                'errorCount' => 2,
            ],
        ];
    }
}
