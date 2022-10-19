<?php

namespace Tests\Unit\Core\Services\Mutators\TaxId\Brazil;

use App\Core\Services\Mutators\TaxId\Brazil\BrazilTaxIdFormatter;
use Tests\TestCase;

/**
 * Class BrazilTaxIdFormatterTest
 * @package App\Core
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class BrazilTaxIdFormatterTest extends TestCase
{
    /**
     * @var BrazilTaxIdFormatter
     */
    private $formatter;

    /**
     * Set up tests
     */
    public function setUp(): void
    {
        $this->formatter = new BrazilTaxIdFormatter();

        parent::setUp();
    }

    /**
     * Test that formatting Tax IDs results in the correct format
     *
     * @test
     * @group mutator
     * @group taxid
     * @param string $before
     * @param string $after
     * @dataProvider taxIdProvider
     */
    public function formatTaxIdsFormatsCorrectly(string $before, string $after): void
    {
        self::assertSame(
            $after,
            $this->formatter->format($before)
        );
    }

    /**
     * Tax IDs before and after formatting
     *
     * @return string[][]
     */
    public function taxIdProvider(): array
    {
        return [
            [
                'before' => '92039459030',
                'after' => 'CPF/CNPJ:920394590-30',
            ],
            [
                'before' => '920.394.590-30',
                'after' => 'CPF/CNPJ:920394590-30',
            ],
            [
                'before' => '920394590-30',
                'after' => 'CPF/CNPJ:920394590-30',
            ],
            [
                'before' => '28.345.677/0001-80',
                'after' => 'CPF/CNPJ:283456770001-80',
            ],
            [
                'before' => '28345677/0001-80',
                'after' => 'CPF/CNPJ:283456770001-80',
            ],
            [
                'before' => '283456770001-80',
                'after' => 'CPF/CNPJ:283456770001-80',
            ],
            [
                'before' => '28345677000180',
                'after' => 'CPF/CNPJ:283456770001-80',
            ],
        ];
    }
}
