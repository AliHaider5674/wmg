# Shopify Module
This module handle the communication with Shopify and warehouses

## API Documentation
https://wmg-athena.postman.co/workspace/WMG-Athena~ae7a0ab0-7784-436b-857d-764b2781e4d7/documentation/5424212-c129ff8b-f69c-4854-b042-1f47aa19bbda?entity=request-d6b18607-092e-4784-8753-6b58fae8f130
This require access to postman.

## Get Started
There are couple steps need to take in order to setup the communication.
1. Configure Shopify Secret
   Path: shopify.routes.signature-header

2. Register shopify store in Fulfillment microservice
Make post request to api/1.0/service
```json
{
  "app_id":"shopify-test", //Change this to Shopify store name with hyphen
  "name": "Shopify Test", //Change this to Shopify store name
  "client": "shopify.restful",
  "url": "wmg-sandbox.myshopify.com", //Change this to Shopify store url
  "events": ["item.shipped", "item.warehouse.received"],
  "event_rules": {
    "sales_channel": "^shopify-test$" //Change this to Shopify store name with hyphen
  },
  "addition": {
    "api_key" : "xxxxx", //Shopify API Key
    "password" : "xxxxx" //Shopify API Password
  }
}
```
Or command
```bash
php artisan wmg:service add -i shopify1 -t shopify1 -u wmg-sandbox.myshopify.com -r '{"sales_channel":"^shopify1$"}' -c shopify.restful -e item.shipped -e item.warehouse.received -a '{"api_key":"123","password":"124"}'
```

3. Register Fulfillment microservice as a fulfillment service in Shopify
The command below will register all shopify services that registered in Fulfillment microservice.
```command
php artisan shopify:fulfillment:register
```



## Commands that might need during development
shopify:order:fetch - fetch orders that assigned to this fulfillment service
wmg:fulfillment mes.ack - process MES ack files
wmg:fulfillment mes.shipment - process MES shipment files
wmg:mes:fake - to generate a fake MES file
