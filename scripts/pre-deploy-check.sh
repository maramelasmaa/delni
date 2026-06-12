#!/bin/bash
set -e

echo "🔍 Running pre-deployment checks for Railway..."

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

success() { echo -e "${GREEN}✓ $1${NC}"; }
warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
error() { echo -e "${RED}✗ $1${NC}"; exit 1; }

PASS=0
FAIL=0

check() {
    if $1 2>/dev/null; then
        success "$2"
        ((PASS++))
    else
        error "$2"
        ((FAIL++))
    fi
}

echo -e "\n${YELLOW}=== PHP & Composer ===${NC}"
command -v php > /dev/null && success "PHP installed" || error "PHP not found"
command -v composer > /dev/null && success "Composer installed" || error "Composer not found"

echo -e "\n${YELLOW}=== Laravel Config ===${NC}"
[ -f ".env" ] && success ".env file exists" || warning ".env file missing (will use .env.railway)"
[ -f "app/Http/Kernel.php" ] && success "Laravel app configured" || error "Not a Laravel project"

echo -e "\n${YELLOW}=== Directories & Permissions ===${NC}"
[ -d "storage" ] && success "storage/ directory exists" || error "storage/ missing"
[ -d "bootstrap/cache" ] && success "bootstrap/cache/ exists" || error "bootstrap/cache/ missing"
[ -d "database" ] && success "database/ directory exists" || error "database/ missing"

echo -e "\n${YELLOW}=== Composer Dependencies ===${NC}"
if [ -f "composer.lock" ]; then
    success "composer.lock exists"
else
    warning "composer.lock missing - run 'composer install'"
fi

if [ -f "composer.json" ]; then
    success "composer.json exists"

    # Check critical packages
    if grep -q '"laravel/framework"' composer.json; then
        success "laravel/framework is installed"
    else
        error "laravel/framework not found in composer.json"
    fi
else
    error "composer.json not found"
fi

echo -e "\n${YELLOW}=== Database Config ===${NC}"
if grep -q "DB_CONNECTION" .env 2>/dev/null || grep -q "DB_CONNECTION" .env.railway; then
    success "Database configuration found"
else
    warning "Database configuration missing"
fi

echo -e "\n${YELLOW}=== Environment ===${NC}"
if [ -f ".env.railway" ]; then
    success ".env.railway template exists"
    if grep -q "APP_KEY=" .env.railway; then
        warning ".env.railway has empty APP_KEY - will generate during deploy"
    fi
else
    warning ".env.railway template missing"
fi

echo -e "\n${YELLOW}=== Deployment Files ===${NC}"
[ -f "Procfile" ] && success "Procfile exists" || error "Procfile missing - run 'composer install' to regenerate"
[ -f "scripts/deploy.sh" ] && success "deploy.sh exists" || warning "deploy.sh missing"

echo -e "\n${YELLOW}=== Git Status ===${NC}"
if [ -d ".git" ]; then
    success "Git repository initialized"

    if [ -z "$(git status --porcelain)" ]; then
        success "No uncommitted changes"
    else
        warning "Uncommitted changes found:"
        git status --short
    fi
else
    warning "Not a git repository - required for Railway"
fi

echo -e "\n${YELLOW}=== Code Quality ===${NC}"
if [ -f "vendor/bin/pint" ]; then
    if vendor/bin/pint --test --quiet 2>/dev/null; then
        success "Code formatting OK"
    else
        warning "Code formatting issues (run: vendor/bin/pint)"
    fi
else
    warning "Pint not installed - formatting check skipped"
fi

echo -e "\n${YELLOW}=== Security ===${NC}"
if grep -r "APP_KEY=" .env* 2>/dev/null | grep -q "base64:"; then
    success "APP_KEY is properly formatted"
else
    if grep -q "APP_DEBUG=false" .env.railway; then
        success "Debug mode disabled for production"
    else
        warning "Verify APP_DEBUG=false before production deploy"
    fi
fi

echo -e "\n${YELLOW}=== Asset Compilation ===${NC}"
if [ -f "package.json" ]; then
    if [ -d "node_modules" ]; then
        success "node_modules directory exists"
    else
        warning "node_modules missing - run 'npm install'"
    fi

    if [ -d "public/build" ]; then
        success "Assets compiled (public/build exists)"
    else
        warning "Assets not compiled - run 'npm run build'"
    fi
else
    success "No package.json - frontend build not needed"
fi

echo -e "\n${YELLOW}=== Database Files ===${NC}"
[ -d "database/migrations" ] && success "Migrations directory exists" || error "migrations/ missing"
[ -f "database/migrations" ] && warning "No migrations found" || success "Migrations present"

echo -e "\n${YELLOW}=== Critical Files ===${NC}"
[ -f "public/index.php" ] && success "public/index.php exists" || error "public/index.php missing"
[ -f "artisan" ] && success "artisan CLI exists" || error "artisan missing"

echo -e "\n${YELLOW}=== Configuration Caching ===${NC}"
warning "Config caching will happen during deployment"
echo "  → php artisan config:cache"
echo "  → php artisan route:cache"
echo "  → php artisan view:cache"

echo -e "\n${GREEN}═══════════════════════════════════════${NC}"
echo -e "${GREEN}Pre-deployment checks complete!${NC}"
echo -e "${GREEN}═══════════════════════════════════════${NC}"

echo -e "\n${YELLOW}Ready to Deploy? Run:${NC}"
echo "  1. git push origin main"
echo "  2. Railway will auto-deploy"
echo "  3. Check status: railway logs"

echo -e "\n${YELLOW}Or manually deploy with:${NC}"
echo "  railway up"

echo -e "\n${YELLOW}Need help?${NC}"
echo "  - Read: DEPLOY_QUICK_START.md"
echo "  - Advanced: RAILWAY_DEPLOYMENT.md"
echo ""
