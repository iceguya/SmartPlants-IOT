#!/bin/bash

# SmartPlants IoT - Quick Development Setup
# This script sets up the entire development environment

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║   SmartPlants IoT - Development Setup                         ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    echo "✅ .env created"
else
    echo "✅ .env already exists"
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate
    echo "✅ App key generated"
else
    echo "✅ App key already set"
fi

# Install PHP dependencies
echo ""
echo "📦 Installing Composer dependencies..."
composer install
echo "✅ Composer dependencies installed"

# Install Node dependencies
echo ""
echo "📦 Installing NPM dependencies..."
npm install
echo "✅ NPM dependencies installed"

# Run migrations
echo ""
echo "🗄️  Running database migrations..."
php artisan migrate
echo "✅ Migrations completed"

# Clear caches
echo ""
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo "✅ Caches cleared"

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║   SETUP COMPLETE! 🎉                                           ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
echo "Next steps:"
echo "1. Configure your database in .env"
echo "2. Run: php artisan migrate (if database config changed)"
echo "3. Run: php artisan serve"
echo "4. Visit: http://localhost:8000"
echo ""
echo "For production deployment, see: SETUP.md"
echo ""
