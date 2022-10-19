# Shopify Module
This module handle the communication with Salesforce and warehouses

## API Documentation


## Get Started
There are couple steps need to take in order to setup the communication.
1. Register Salesforce in Fulfillment microservice
Make post request to api/1.0/service
```json
{
  "app_id":"salesforce",
  "name": "salesforce",
  "client": "salesforce.restful",
  "url": "",
  "events": ["*"],
  "event_rules": {
    "sales_channel": "^salesforce.*"
  },
  "addition": {
      "username" : "xxxxx",
      "password" : "xxxxx",
      "organization_id" : "xxxxx",
      "grant_type" : "client_credentials",
      "auth_url" : "https://xxxxx",
      "auth_scope" : "xxxxx",
      "base_url" : "https://xxxx"
  }
}
```


## Commands that might need during development
