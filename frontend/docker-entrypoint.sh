#!/bin/sh
set -e

echo "=== Vue.js Frontend Initialization ==="

# Install npm dependencies if node_modules doesn't exist
if [ ! -d node_modules ] || [ ! -f node_modules/.package-lock.json ]; then
    echo "Installing npm dependencies..."
    npm install
    echo "✓ npm dependencies installed"
else
    echo "✓ npm dependencies already installed"
fi

echo ""
echo "=== Starting Vue.js Development Server ==="
echo ""

exec "$@"
