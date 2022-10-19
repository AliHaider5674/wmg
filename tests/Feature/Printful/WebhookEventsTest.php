<?php declare(strict_types=1);

namespace Tests\Feature\Printful;

use Illuminate\Support\Str;
use WMGCore\Services\ConfigService;
use App\Printful\Constants\ConfigConstant;
use App\Printful\Models\PrintfulEvent;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

/**
 * Class WebhookRequestTest
 * @package Tests\Feature\Printful
 */
class WebhookEventsTest extends TestCase
{
    /**
     * Config service
     *
     * @var ConfigService
     */
    private $configService;

    /**
     * @var string
     */
    private $key;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->configService = app()->make(ConfigService::class);
        PrintfulEvent::query()->delete();
        $this->key = Str::random(32);
        $this->configService->update(ConfigConstant::WEBHOOK_KEY, $this->key);
    }

    /**
     * Test that the route to create a webhook route will successfully create a webhook route
     */
    public function testCreateWebhookRouteWithCorrectKeyCreatesWebhookEvent(): void
    {
        $webhookData = $this->getWebhookData();
        $webhookArray = json_decode($webhookData, true);
        $response = $this->post(
            '/api/printful/webhook/' . $this->key,
            $webhookArray
        );

        self::assertSame($response->getStatusCode(), 201);

        /** @var $event Model */
        $event = PrintfulEvent::first();

        self::assertNotNull($event);
        self::assertEqualsCanonicalizing(
            $webhookArray,
            json_decode($event->getRawOriginal('webhook_item'), true)
        );
    }

    /**
     * Test that the route to create a webhook route will successfully create a webhook route
     */
    public function testCreateWebhookRouteWithIncorrectKeyDoesNotCreateWebhookEvent(): void
    {
        $webhookData = $this->getWebhookData();
        $webhookArray = json_decode($webhookData, true);
        $response = $this->post(
            '/api/printful/webhook/' . $this->key . 'additional_text',
            $webhookArray
        );

        self::assertSame($response->getStatusCode(), 401);

        /** @var $event Model */
        $event = PrintfulEvent::first();

        self::assertNull($event);
    }

    /**
     * Get webhook JSON
     * @SuppressWarnings(PHPMD)
     */
    private function getWebhookData(): string
    {
        return <<<'WEBHOOK'
{
  "type": "order_put_hold",
  "created": 1619829616,
  "retries": 9,
  "store": 471,
  "data": {
    "reason": "Order put on hold",
    "order": {
      "id": 66,
      "external_id": "1661948",
      "store": 0,
      "status": "fulfilled",
      "error": null,
      "shipping": "USPS_PRIORITY",
      "created": 1619829616,
      "updated": 1619829616,
      "recipient": {
        "name": "Michaela Nader",
        "company": "Weber Ltd",
        "address1": "545 Lura Spur Apt. 579\nNew Earnestville, NC 93132",
        "address2": "670 Llewellyn Path\nRosemaryland, NY 58109-1515",
        "city": "Runteside",
        "state_code": "MA",
        "state_name": "Massachusetts",
        "country_code": "US",
        "country_name": "United States",
        "zip": "20054-8974",
        "phone": "259.371.4887 x67895",
        "email": "lemmerich@goldner.org"
      },
      "estimated_fulfillment": 1619829616,
      "notes": null,
      "items": [
        {
          "id": 5798,
          "external_id": "1567492",
          "variant_id": 4570237,
          "quantity": 2,
          "price": "35.00",
          "retail_price": "60.00",
          "name": "BAE Black and Educated - Sweatshirt - White / M",
          "product": {
            "variant_id": 520,
            "product_id": 57,
            "image": "https://www.printful.com/",
            "name": "Gildan 18000 Heavy Blend Crewneck Sweatshirt (White / M)"
          },
          "files": [
            {
              "id": 9690619,
              "type": "default",
              "hash": "9bd8d3e9c198a23ed8cd643ead4e901d",
              "url": null,
              "filename": "Untitled-2-Recovered.psd",
              "mime_type": "image/x-psd",
              "size": 2936091,
              "width": 5100,
              "height": 4500,
              "dpi": 300,
              "status": "ok",
              "created": 1619829616,
              "thumbnail_url": "https://www.printful.com/",
              "preview_url": "https://www.printful.com/",
              "visible": true
            },
            {
              "id": 14,
              "type": "preview",
              "hash": "df5d2845f6d1d71b2b945626b5c61f44",
              "url": null,
              "filename": "mockup-a3ca32cb.jpg",
              "mime_type": "image/jpeg",
              "size": 70512,
              "width": 1000,
              "height": 1000,
              "dpi": 72,
              "status": "ok",
              "created": 1619829616,
              "thumbnail_url": "https://www.printful.com/",
              "preview_url": "https://www.printful.com/",
              "visible": false
            }
          ],
          "options": [],
          "sku": null,
          "discontinued": false,
          "out_of_stock": false
        }
      ],
      "is_sample": false,
      "needs_approval": false,
      "not_synced": false,
      "has_discontinued_items": false,
      "can_change_hold": false,
      "costs": {
        "subtotal": "98.00",
        "discount": "49.00",
        "shipping": "82.00",
        "digitization": "61.00",
        "additional_fee": "33.00",
        "fulfillment_fee": "32.00",
        "tax": "93.00",
        "vat": "54.00",
        "total": "61.00"
      },
      "retail_costs": {
        "subtotal": "41.00",
        "discount": "86.00",
        "shipping": "38.00",
        "tax": "25.00",
        "vat": "31.00",
        "total": "17.00"
      },
      "shipments": [
        {
          "id": 9,
          "status": "onhold",
          "carrier": "USPS",
          "service": "USPS Priority Mail",
          "tracking_number": "9723494486921",
          "tracking_url": "https://www.printful.com/",
          "created": 1619829622,
          "ship_date": "2012-07-22",
          "shipped_at": 1619829622,
          "reshipment": false,
          "location": "USA",
          "estimated_delivery_dates": {
            "from": 1619829622,
            "to": 1619829622
          },
          "items": [
            {
              "item_id": 5798,
              "quantity": 1,
              "picked": 1,
              "printed": 1,
              "is_started": true
            }
          ],
          "packing_slip_url": "https://www.printful.com/"
        }
      ],
      "gift": null,
      "packing_slip": null,
      "dashboard_url": "https://www.printful.com/"
    }
  }
}
WEBHOOK;
    }
}
