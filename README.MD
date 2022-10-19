# Fulfillment Micro Service
This is a microservice that act as a middleman to route orders, shipment, ack and stock information between warehouses and commerce platforms

# Get Started
## Technical terms
### Fulfillment Handlers
Handlers are used to handle order, ack, stock and shipment communication with warehouses
### Service Client
It's a client that handle communication call back to sales channels
#### Service Client Handlers
It's used to handle different events(order, ack, stock and shipment).

## Configuration
There are two non-standard configurations
### MOM
```
MOM_URL="${MOM_URL}"
MOM_OAUTH_URL="${MOM_OAUTH_URL}"
MOM_OAUTH_CLIENT_ID="${MOM_OAUTH_CLIENT_ID}"
MOM_OAUTH_CLIENT_SECRET="${MOM_OAUTH_CLIENT_SECRET}"
MOM_APPLICATION_ID="${MOM_APPLICATION_ID}"
MOM_SECRET="${MOM_SECRET}"
```
### MES Directories
```
#templar location to store order files, doesn't need to be in side MES directoies
MES_TMP_ORDER_DIR="${MES_TMP_ORDER_DIR}" 
MES_LIVE_ORDER_DIR="${MES_LIVE_ORDER_DIR}"
MES_HISTORY_SHIPMENT_DIR="${MES_HISTORY_SHIPMENT_DIR}"
MES_LIVE_SHIPMENT_DIR="${MES_LIVE_SHIPMENT_DIR}"
MES_HISTORY_ACK_DIR="${MES_HISTORY_ACK_DIR}"
MES_LIVE_ACK_DIR="${MES_LIVE_ACK_DIR}"
```

## Installation
After having the .env file configured. 
You can just run the following command to have it up and running
```bash
composer install
php artisan migrate
php artisan passport:install
```

# Commands
## External Services
Services are the platforms that communicate with warehouses via this micro service.
The service command is under namespace wmg:service

### Examples
```bash
# Add a service
php artisan wmg:service add -i mom -t mom -c mom -e item.shipped -e item.warehouse.received

# Remove a service
php artisan wmg:service remove -i mom

# Show all services
php artisan wmg:service ls

# List all events
php artisan wmg:service events
```

## Worker
```bash
php artisan queue:work
```

## Crons
### Export orders
```php artisan wmg:fulfillment order```
### Import shipments
```php artisan wmg:fulfillment shipment```
### Import ack
```php artisan wmg:fulfillment ack```
### Import stock
```php artisan wmg:fulfillment stock```

## Fakers
### Fake shipment file
```php artisan wmg:mes:faker shipment -o {order id}```
### Fake ack file
```php artisan wmg:mes:faker ack -o {order id}```

