#ImMuleSoft Module
The purpose of this module is to handle bidirectional communication with the Ingram Micro EU warehouse

## API Documentation

## Get Started

### Required Configuration values

#### Register new service
Make post request to api/1.0/service
```json
{
    "app_id":"immulesoft",
    "name": "ingram immulesoft",
    "client": "immulesoft.restful",
    "app_url":"",
    "events": ["immulesoft.response.message", "immulesoft.order.export"],
    "event_rules": {
        "sales_channel": "^ingram.*"
    },
    "addition": {
        "username" : "Ingram to provide username",
        "password" : "Ingram to provide password",
        "base_url" : "Ingram to provide base url note to include trailing slash"
    }
}
```

### Queue
Stock Level is added to Laravel default queue.

The default queue worker is required to process these jobs

$> php artisan queue:work


### Cron
The Ingram Micro order export command is periodically executed X number of times.

Default timing is every 5 minutes.

### Command

To initiate order export the following command will need to be executed:

$> php artisan wmg:fulfillment immulesoft.order
