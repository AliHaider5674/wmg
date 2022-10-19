<?php

namespace Tests\Unit;

use App\Core\Exceptions\Mutators\ValidationException;
use Tests\TestCase;
use App\Models\Order;
use App\Models\OrderAddress;

/**
 * Class OrderAddressesTest
 * @category WMG
 * @package  Tests\Unit
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class OrderAddressesTest extends TestCase
{
    /**
     * Brazilian country code
     */
    private const BRAZIL_COUNTRY_CODE = 'BR';

    /**
     * testGetBillingAddress
     * Test Order shipping address retrieval
     */
    public function testGetShippingAddress()
    {
        //create test orders
        $orders = Order::factory()->count(1)->create()->each(
            fn($order) => $order->addresses()->save(
                OrderAddress::factory()->make([
                    'customer_address_type' => OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING
                ])
            )
        );

        foreach ($orders as $order) {
            self::assertEquals(
                OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING,
                $order->getShippingAddress()->customer_address_type,
                'Order shipping address'
            );
        }
    }

    /**
     * testGetBillingAddress
     * Test Order billing address retrieval
     */
    public function testGetBillingAddress()
    {
        //create test orders
        $orders = Order::factory()->count(1)->create()->each(
            fn($order) => $order->addresses()->save(OrderAddress::factory()
                ->make(['customer_address_type' => OrderAddress::CUSTOMER_ADDRESS_TYPE_BILLING]))
        );

        foreach ($orders as $order) {
            self::assertEquals(
                OrderAddress::CUSTOMER_ADDRESS_TYPE_BILLING,
                $order->getBillingAddress()->customer_address_type,
                'Order billing address'
            );
        }
    }

    /**
     * Test that setting a valid Tax ID property on an OrderAddress model that
     * has a supported country code will format it correctly when saving
     *
     * @group mutator
     * @group taxid
     * @param string $taxIdBefore
     * @param string $taxIdAfter
     * @param string $countryCode
     * @dataProvider validTaxIdProvider
     */
    public function testSetTaxIdOnOrderAddressForBrazilWillSaveInCorrectFormat(
        string $taxIdBefore,
        string $taxIdAfter,
        string $countryCode
    ): void {
        $address = $this->helper->orderAddress([
            'country_code' => $countryCode
        ]);
        $address->tax_id = $taxIdBefore;
        $address->save();
        $address->refresh();
        self::assertSame($taxIdAfter, $address->tax_id);
    }

    /**
     * Test that setting a valid Tax ID property on an OrderAddress model that
     * has a supported country code will format it correctly when saving
     *
     * @group mutator
     * @group taxid
     * @param string $taxIdBefore
     * @param string $taxIdAfter
     * @param string $countryCode
     * @dataProvider validTaxIdProvider
     */
    public function testSetTaxIdThroughCustomAttributeOnOrderAddressForBrazilWillSaveInCorrectFormat(
        string $taxIdBefore,
        string $taxIdAfter,
        string $countryCode
    ): void {
        $address = $this->helper->orderAddress([
            'country_code' => $countryCode
        ]);

        $address->custom_attributes = [
            [
                'name' => 'tax_id',
                'value' => $taxIdBefore,
            ],
        ];

        $address->save();
        $address->refresh();
        self::assertSame($taxIdAfter, $address->tax_id);
    }

    /**
     * Test that setting a valid Tax ID property on an OrderAddress model that
     * has a supported country code in lowercase will format it correctly when
     * saving
     *
     * @group mutator
     * @group taxid
     * @param string $taxIdBefore
     * @param string $taxIdAfter
     * @param string $countryCode
     * @dataProvider validTaxIdProvider
     */
    public function testSetTaxIdOnOrderAddressWithLowercaseCountryCodeWillSaveInCorrectFormat(
        string $taxIdBefore,
        string $taxIdAfter,
        string $countryCode
    ): void {
        $address = $this->helper->orderAddress([
            'country_code' => strtolower($countryCode)
        ]);
        $address->tax_id = $taxIdBefore;
        $address->save();
        $address->refresh();
        self::assertSame($taxIdAfter, $address->tax_id);
    }

    /**
     * Test that setting a valid Tax ID property on an OrderAddress model that
     * has a supported country code will succeed when saving
     *
     * @group mutator
     * @group taxid
     */
    public function testSetRandomCustomAttributeOnOrderAddressWillSaveInCustomAttributes(): void
    {
        $address = $this->helper->orderAddress([
            'country_code' => $this->helper->fakerCountryCodeOtherThan(
                self::BRAZIL_COUNTRY_CODE
            ),
        ]);
        $attribute = $this->faker->word;
        $value = $this->faker->word;
        $address->custom_attributes = [$attribute => $value];
        $address->save();
        $address->refresh();
        self::assertSame($address->fresh()->custom_attributes[$attribute], $value);
    }

    /**
     * Test that when saving a Tax ID on an OrderAddress model that has an
     * unsupported country, a ValidatorNotDefined exception is thrown
     *
     * @group mutator
     * @group taxid
     * @param string $taxId
     * @param string $countryCode
     * @dataProvider unsupportedCountryTaxIdProvider
     */
    public function testSetTaxIdOnOrderAddressWithUnsupportedCountrySucceeds(
        string $taxId,
        string $countryCode
    ): void {
        $exceptionThrown = false;
        $address = $this->helper->orderAddress(['country_code' => $countryCode]);

        try {
            $address->tax_id = $taxId;
            $address->save();
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        self::assertFalse($exceptionThrown);
        self::assertSame($address->fresh()->tax_id, $taxId);
    }

    /**
     * Test that setting an invalid tax id property on an OrderAddress model
     * will throw an exception
     *
     * @group mutator
     * @group taxid
     * @param string $taxId
     * @param string $countryCode
     * @param int    $errorCount
     * @dataProvider invalidTaxIdProvider
     */
    public function testSetTaxIdOnOrderAddressWithInvalidTaxIdThrowsCorrectException(
        string $taxId,
        string $countryCode,
        int $errorCount
    ): void {
        $address = $this->helper->orderAddress(
            ['country_code' => $countryCode]
        );

        $address->tax_id = $taxId;
        $exceptionThrown = false;

        try {
            $address->save();
        } catch (ValidationException $e) {
            $exceptionThrown = true;
            self::assertCount($errorCount, $e->getErrors());
        }

        self::assertTrue($exceptionThrown);
    }

    /**
     * Test that setting an invalid tax id property through a custom attributes
     * array on an OrderAddress model will throw an exception
     *
     * @group mutator
     * @group taxid
     * @param string $taxId
     * @param string $countryCode
     * @param int    $errorCount
     * @dataProvider invalidTaxIdProvider
     */
    public function testSetTaxIdThroughCustomAttributesOnOrderAddressWithInvalidTaxIdThrowsCorrectException(
        string $taxId,
        string $countryCode,
        int $errorCount
    ): void {
        $address = $this->helper->orderAddress(
            ['country_code' => $countryCode]
        );

        $address->custom_attributes = [
            [
                'name' => 'tax_id',
                'value' => $taxId,
            ],
        ];

        $exceptionThrown = false;

        try {
            $address->save();
        } catch (ValidationException $e) {
            $exceptionThrown = true;
            self::assertCount($errorCount, $e->getErrors());
        }

        self::assertTrue($exceptionThrown);
    }

    /**
     * Tax IDs before and after transformation along with a valid country code
     *
     * @return string[][]
     */
    public function validTaxIdProvider(): array
    {
        return [
            [
                'taxIdBefore' => '92039459030',
                'taxIdAfter' => 'CPF/CNPJ:920394590-30',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
            ],
            [
                'taxIdBefore' => '920.394.590-30',
                'taxIdAfter' => 'CPF/CNPJ:920394590-30',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
            ],
            [
                'taxIdBefore' => '920394590-30',
                'taxIdAfter' => 'CPF/CNPJ:920394590-30',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
            ],
            [
                'taxIdBefore' => '28.345.677/0001-80',
                'taxIdAfter' => 'CPF/CNPJ:283456770001-80',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
            ],
            [
                'taxIdBefore' => '28345677/0001-80',
                'taxIdAfter' => 'CPF/CNPJ:283456770001-80',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
            ],
            [
                'taxIdBefore' => '283456770001-80',
                'taxIdAfter' => 'CPF/CNPJ:283456770001-80',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
            ],
            [
                'taxIdBefore' => '28345677000180',
                'taxIdAfter' => 'CPF/CNPJ:283456770001-80',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
            ],
        ];
    }

    /**
     * Country codes that don't have tax ID formatter along with their tax IDs
     *
     * @return string[][]
     */
    public function unsupportedCountryTaxIdProvider(): array
    {
        return [
            [
                'countryCode' => 'FR',
                'taxId' => '28434833242343495403',
            ],
            [
                'countryCode' => 'US',
                'taxId' => '2947723',
            ],
            [
                'countryCode' => 'IT',
                'taxId' => '282654043543',
            ],
        ];
    }

    /**
     * Invalid Tax IDs along with their countries which are supported, and the
     * amount of errors that the tax ID has.
     *
     * @return string[][]
     */
    public function invalidTaxIdProvider(): array
    {
        return [
            [
                'taxId' => '52834567700@180',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
                'errorCount' => 1,
            ],
            [
                'taxId' => 'ID: 92039459030',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
                'errorCount' => 1,
            ],
            [
                'taxId' => '9220.394.590-30362',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
                'errorCount' => 1,
            ],
            [
                'taxId' => '#920.394.590-30362',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
                'errorCount' => 1,
            ],
            [
                'taxId' => '920394f590303924543',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
                'errorCount' => 2,
            ],
            [
                'taxId' => '123456.382h2.ba038/7890',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
                'errorCount' => 2,
            ],
            [
                'taxId' => '283938/3-s0',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
                'errorCount' => 2,
            ],
            [
                'taxId' => '#920.394.590-3250362',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
                'errorCount' => 2,
            ],
            [
                'taxId' => '83456770s00180',
                'countryCode' => self::BRAZIL_COUNTRY_CODE,
                'errorCount' => 2,
            ],
        ];
    }
}
