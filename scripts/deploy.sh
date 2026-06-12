#!/bin/bash
set -e

echo "🚀 Starting Delni deployment process..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to print colored messages
success() {
    echo -e "${GREEN}✓ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

error() {
    echo -e "${RED}✗ $1${NC}"
    exit 1
}

# Step 1: Verify PHP and Composer
echo -e "\n${YELLOW}Step 1: Checking PHP and Composer...${NC}"
if ! command -v php &> /dev/null; then
    error "PHP is not installed or not in PATH"
fi
success "PHP found: $(php -v | head -n 1)"

if ! command -v composer &> /dev/null; then
    error "Composer is not installed or not in PATH"
fi
success "Composer found: $(composer --version)"

# Step 2: Install/Update Composer Dependencies
echo -e "\n${YELLOW}Step 2: Installing composer dependencies...${NC}"
if [ "$ENVIRONMENT" = "production" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction --no-progress
    success "Dependencies installed (production mode)"
else
    composer install --no-interaction --no-progress
    success "Dependencies installed (development mode)"
fi

# Step 3: Set Permissions
echo -e "\n${YELLOW}Step 3: Setting directory permissions...${NC}"
if [ -d "storage" ]; then
    chmod -R 775 storage
    success "Storage directory permissions set"
fi

if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache
    success "Bootstrap cache permissions set"
fi

# Step 4: Generate Application Key (if not exists)
echo -e "\n${YELLOW}Step 4: Checking application key...${NC}"
if [ -z "$APP_KEY" ]; then
    warning "APP_KEY not set, generating one..."
    php artisan key:generate --force
    success "Application key generated"
else
    success "Application key already set"
fi

# Step 5: Clear Caches
echo -e "\n${YELLOW}Step 5: Clearing caches...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
success "Caches cleared"

# Step 6: Run Database Migrations
echo -e "\n${YELLOW}Step 6: Running database migrations...${NC}"
if [ "$SKIP_MIGRATIONS" != "true" ]; then
    php artisan migrate --force --no-interaction
    success "Database migrations completed"
else
    warning "Skipping migrations (SKIP_MIGRATIONS=true)"
fi

# Step 7: Seed Database (optional, only if SEED_DATABASE=true)
echo -e "\n${YELLOW}Step 7: Checking if database seeding is needed...${NC}"
if [ "$SEED_DATABASE" = "true" ]; then
    php artisan db:seed --force --no-interaction
    success "Database seeded"
else
    success "Skipping database seeding"
fi

# Step 8: Optimize for Production
echo -e "\n${YELLOW}Step 8: Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
success "Application optimized"

# Step 9: Check Dependencies
echo -e "\n${YELLOW}Step 9: Verifying critical services...${NC}"

# Check if database is accessible
if php artisan tinker --execute "DB::connection()->getPdo();" 2>/dev/null; then
    success "Database connection verified"
else
    warning "Could not verify database connection (may be expected if DB is not yet provisioned)"
fi

# Step 10: Compile Assets (if needed)
echo -e "\n${YELLOW}Step 10: Checking for npm dependencies...${NC}"
if [ -f "package.json" ]; then
    if command -v npm &> /dev/null; then
        warning "package.json found, installing npm dependencies..."
        npm ci --production 2>/dev/null || npm install --production
        success "npm dependencies installed"

        if grep -q "\"build\"" package.json; then
            warning "Building assets..."
            npm run build
            success "Assets built"
        fi
    else
        warning "npm not found, skipping frontend build"
    fi
else
    success "No package.json found, skipping npm"
fi

echo -e "\n${GREEN}✅ Deployment completed successfully!${NC}"
echo -e "${YELLOW}Application is ready to run.${NC}"
echo ""
echo "Environment Summary:"
echo "  - PHP: $(php -v | head -n 1)"
echo "  - Composer: $(composer --version)"
echo "  - APP_NAME: ${APP_NAME}"
echo "  - APP_ENV: ${APP_ENV}"
echo ""
