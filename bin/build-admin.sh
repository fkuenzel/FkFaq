#!/bin/bash
set -e

# Build Administration UI
# This script is run in the context of a Shopware 6 installation
# It dumps the bundles and rebuilds the administration

cd "$(dirname "$0")/.."

if [ ! -f "../../bin/console" ]; then
    echo "Error: Shopware console not found at ../../bin/console"
    echo "Make sure this plugin is installed in a Shopware 6 installation"
    exit 1
fi

echo "Building FkFaq Administration UI..."
php ../../bin/console bundle:dump
php ../../bin/console administration:build

echo "Build completed successfully!"
