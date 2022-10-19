<?php declare(strict_types=1);

namespace Tests\Unit\Printful\Services;

use App\Core\Models\RawData\Order;
use App\Printful\Service\PrintfulExternalIdParser;
use Tests\TestCase;

/**
 * Class ToShipmentLineChangeParameterTest
 * @package Tests\Unit\Printful
 */
class PrintfulExternalIdParserTest extends TestCase
{
    /**
     * @var PrintfulExternalIdParser
     */
    private $printfulExternalIdParser;

    /**
     * Set up tests
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->printfulExternalIdParser = new PrintfulExternalIdParser();
    }

    /**
     * Test getLocalOrderId method
     *
     * @group Printful
     * @group Unit
     * @group Parser
     * @dataProvider parseExternalIdDataProvider
     * @param string $externalId
     * @param int    $orderId
     */
    public function testGetLocalOrderId(string $externalId, int $orderId): void
    {
        self::assertEquals(
            $orderId,
            $this->printfulExternalIdParser->getLocalOrderId($externalId)
        );
    }

    /**
     * Test createPrintfulExternalIdFromOrder method
     *
     * @group Printful
     * @group Unit
     * @group Parser
     * @param int    $orderId
     * @dataProvider generateExternalIdDataProvider
     * @param string $orderNumber
     * @param string $externalId
     */
    public function testCreatePrintfulExternalIdFromOrder(
        int $orderId,
        string $orderNumber,
        string $externalId
    ): void {
        $order = new Order();
        $order->id = $orderId;
        $order->orderId = $orderNumber;

        self::assertEquals(
            $externalId,
            $this->printfulExternalIdParser->createPrintfulExternalIdFromOrder(
                $order
            )
        );
    }

    /**
     * Test createPrintfulExternalId method
     *
     * @group Printful
     * @group Unit
     * @group Parser
     * @dataProvider generateExternalIdDataProvider
     * @param int    $orderId
     * @param string $orderNumber
     * @param string $externalId
     */
    public function testCreatePrintfulExternalId(
        int $orderId,
        string $orderNumber,
        string $externalId
    ): void {
        self::assertEquals(
            $externalId,
            $this->printfulExternalIdParser->createPrintfulExternalId(
                $orderId,
                $orderNumber
            )
        );
    }

    /**
     * @return array[]
     */
    public function parseExternalIdDataProvider(): array
    {
        return [
            [
                '86400000052898-33194',
                33194,
            ],
            [
                '34300001474612-294731',
                294731,
            ],
            [
                '0000888220363482-193',
                193,
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function generateExternalIdDataProvider(): array
    {
        return [
            [
                33194,
                '86400000052898',
                '86400000052898-33194',
            ],
            [
                294731,
                '34300001474612',
                '34300001474612-294731',
            ],
            [
                193,
                '0000888220363482',
                '0000888220363482-193',
            ]
        ];
    }
}
