<?php declare(strict_types=1);

namespace Tests\Unit\Core\Handler;

use App\Core\Handlers\AckHandler;
use App\Core\Handlers\FulfillmentHandlerContainer;
use App\IM\Handler\OrderHandler;
use Tests\TestCase;

/**
 * Class FulfillmentHandlerContainer
 * @package Tests\Unit\Core\Handler
 */
class FulfillmentHandlerContainerTest extends TestCase
{
    /**
     * Valid handlers
     */
    private const VALID_HANDLERS = [
        'test.ack' => AckHandler::class,
        'test.order' => OrderHandler::class,
    ];

    /**
     * Invalid handlers
     */
    private const INVALID_HANDLERS = [
        'test.ack' => '\Test\Invalid\OrderHandlerClass',
        'text.order' => '\Test\Invalid\OrderHandlerClass',
    ];

    /**
     * @var FulfillmentHandlerContainerTest
     */
    protected $fulfillmentHandlerContainer;

    /**
     * Set up tests
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->fulfillmentHandlerContainer = new FulfillmentHandlerContainer(
            $this->app
        );
    }

    /**
     * Test register handlers works
     */
    public function testRegisterHandler(): void
    {
        foreach (self::VALID_HANDLERS as $type => $class) {
            $this->fulfillmentHandlerContainer->registerHandler($type, $class);
        }

        foreach (self::VALID_HANDLERS as $type => $class) {
            $handlers = $this->fulfillmentHandlerContainer->getHandlers($type);

            foreach ($handlers as $handler) {
                self::assertInstanceOf($class, $handler);
            }
        }

        $invalidExceptionArgumentThrown = false;

        try {
            foreach (self::INVALID_HANDLERS as $type => $class) {
                $this->fulfillmentHandlerContainer->registerHandler($type, $class);
            }
        } catch (\InvalidArgumentException $e) {
            $invalidExceptionArgumentThrown = true;
        }

        self::assertTrue($invalidExceptionArgumentThrown);
    }
}
