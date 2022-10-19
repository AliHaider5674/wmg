#!/bin/bash
#
# post-deploy.sh cloud hook
#
# This script will run after a code deploy on Fulfillment,
# use it to run commands needed after a new code is deployed.
#
# Example commands:
#
#     php artisan migrate

echo "Fulfillment Post Deploy Cloud Hook - Start"

echo "Run artisan migrate after code deploy - Post Deploy Cloud Hook"
yes | SHELL_INTERACTIVE=1 php artisan migrate

echo "Fulfillment Post Deploy Cloud Hook - Start"
