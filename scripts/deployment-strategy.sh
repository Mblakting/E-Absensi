#!/bin/bash

# Railway Deployment Strategy Selection Script
# This script manages the deployment strategy selection between nixpacks and Docker

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check current deployment strategy
check_current_strategy() {
    print_status "Checking current deployment strategy..."
    
    cd "$PROJECT_ROOT"
    
    if [ -f "nixpacks.toml" ] && [ ! -f "Dockerfile" ]; then
        print_success "Current strategy: NIXPACKS (Primary)"
        echo "  ✅ nixpacks.toml present"
        echo "  ✅ Dockerfile excluded (proper configuration)"
        if [ -f "Dockerfile.backup" ]; then
            echo "  ✅ Docker fallback available"
        else
            print_warning "  ⚠️  No Docker fallback found"
        fi
        return 0
    elif [ -f "Dockerfile" ] && [ ! -f "nixpacks.toml" ]; then
        print_warning "Current strategy: DOCKER (Fallback)"
        echo "  ⚠️  Using Docker deployment"
        echo "  ⚠️  nixpacks.toml not found"
        return 1
    elif [ -f "nixpacks.toml" ] && [ -f "Dockerfile" ]; then
        print_error "Strategy conflict detected!"
        echo "  ❌ Both nixpacks.toml and Dockerfile present"
        echo "  ❌ Railway may use Docker instead of nixpacks"
        echo "  💡 Run: $0 fix-priority"
        return 2
    else
        print_error "No deployment strategy configured!"
        echo "  ❌ Neither nixpacks.toml nor Dockerfile found"
        return 3
    fi
}

# Function to activate nixpacks strategy (primary)
activate_nixpacks() {
    print_status "Activating nixpacks deployment strategy..."
    
    cd "$PROJECT_ROOT"
    
    # Ensure nixpacks.toml exists
    if [ ! -f "nixpacks.toml" ]; then
        if [ -f "nixpacks.toml.disabled" ]; then
            mv "nixpacks.toml.disabled" "nixpacks.toml"
            print_success "Restored nixpacks.toml from disabled state"
        else
            print_error "nixpacks.toml not found and no backup available"
            return 1
        fi
    fi
    
    # Move Dockerfile to backup if it exists
    if [ -f "Dockerfile" ]; then
        if [ -f "Dockerfile.backup" ]; then
            print_warning "Dockerfile.backup already exists, removing current Dockerfile"
            rm "Dockerfile"
        else
            mv "Dockerfile" "Dockerfile.backup"
            print_success "Moved Dockerfile to Dockerfile.backup"
        fi
    fi
    
    # Update .railwayignore to exclude Docker files
    if [ -f ".railwayignore" ]; then
        if ! grep -q "^Dockerfile$" ".railwayignore"; then
            echo "Dockerfile" >> ".railwayignore"
            echo "Dockerfile.backup" >> ".railwayignore"
            echo "Dockerfile.*" >> ".railwayignore"
            print_success "Updated .railwayignore to exclude Docker files"
        fi
    else
        cat > ".railwayignore" << EOF
# Force Railway to use nixpacks.toml
Dockerfile
Dockerfile.backup
Dockerfile.*
EOF
        print_success "Created .railwayignore to exclude Docker files"
    fi
    
    print_success "Nixpacks strategy activated successfully!"
    echo "  ✅ nixpacks.toml active"
    echo "  ✅ Dockerfile excluded"
    echo "  ✅ Docker fallback preserved"
}

# Function to activate Docker strategy (fallback)
activate_docker() {
    print_status "Activating Docker deployment strategy (fallback)..."
    
    cd "$PROJECT_ROOT"
    
    # Disable nixpacks temporarily
    if [ -f "nixpacks.toml" ]; then
        mv "nixpacks.toml" "nixpacks.toml.disabled"
        print_success "Disabled nixpacks.toml (moved to .disabled)"
    fi
    
    # Restore Dockerfile from backup
    if [ -f "Dockerfile.backup" ]; then
        cp "Dockerfile.backup" "Dockerfile"
        print_success "Restored Dockerfile from backup"
    else
        print_error "Dockerfile.backup not found - cannot activate Docker strategy"
        return 1
    fi
    
    # Update .railwayignore to allow Dockerfile
    if [ -f ".railwayignore" ]; then
        sed -i 's/^Dockerfile$/#Dockerfile/' ".railwayignore" 2>/dev/null || true
        sed -i 's/^Dockerfile\.backup$/#Dockerfile.backup/' ".railwayignore" 2>/dev/null || true
        sed -i 's/^Dockerfile\.\*$/#Dockerfile.*/' ".railwayignore" 2>/dev/null || true
        print_success "Updated .railwayignore to allow Dockerfile"
    fi
    
    print_warning "Docker strategy activated (fallback mode)!"
    echo "  ⚠️  Using Docker deployment"
    echo "  ⚠️  nixpacks.toml disabled"
    echo "  💡 To restore nixpacks: $0 activate-nixpacks"
}

# Function to fix strategy priority (ensure nixpacks is prioritized)
fix_priority() {
    print_status "Fixing deployment strategy priority..."
    activate_nixpacks
}

# Function to validate deployment configuration
validate_config() {
    print_status "Validating deployment configuration..."
    
    cd "$PROJECT_ROOT"
    
    local errors=0
    
    # Check nixpacks.toml syntax
    if [ -f "nixpacks.toml" ]; then
        print_status "Validating nixpacks.toml syntax..."
        # Basic TOML syntax check
        if command -v toml-test >/dev/null 2>&1; then
            if toml-test nixpacks.toml >/dev/null 2>&1; then
                print_success "nixpacks.toml syntax is valid"
            else
                print_error "nixpacks.toml has syntax errors"
                errors=$((errors + 1))
            fi
        else
            print_warning "toml-test not available, skipping syntax validation"
        fi
    fi
    
    # Check Dockerfile.backup syntax
    if [ -f "Dockerfile.backup" ]; then
        print_status "Validating Dockerfile.backup syntax..."
        if docker --version >/dev/null 2>&1; then
            if docker build -f Dockerfile.backup --dry-run . >/dev/null 2>&1; then
                print_success "Dockerfile.backup syntax is valid"
            else
                print_warning "Dockerfile.backup may have syntax issues"
            fi
        else
            print_warning "Docker not available, skipping Dockerfile validation"
        fi
    fi
    
    # Check .railwayignore configuration
    if [ -f ".railwayignore" ]; then
        if grep -q "^Dockerfile$" ".railwayignore"; then
            print_success ".railwayignore properly excludes Dockerfile"
        else
            print_warning ".railwayignore does not exclude Dockerfile"
            errors=$((errors + 1))
        fi
    else
        print_warning ".railwayignore not found"
        errors=$((errors + 1))
    fi
    
    if [ $errors -eq 0 ]; then
        print_success "Deployment configuration validation passed!"
    else
        print_error "Deployment configuration has $errors issue(s)"
        return 1
    fi
}

# Function to show help
show_help() {
    echo "Railway Deployment Strategy Selection Script"
    echo ""
    echo "Usage: $0 [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  check              Check current deployment strategy"
    echo "  activate-nixpacks  Activate nixpacks strategy (primary)"
    echo "  activate-docker    Activate Docker strategy (fallback)"
    echo "  fix-priority       Fix strategy priority (ensure nixpacks is primary)"
    echo "  validate           Validate deployment configuration"
    echo "  help               Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 check                    # Check current strategy"
    echo "  $0 activate-nixpacks        # Switch to nixpacks (recommended)"
    echo "  $0 activate-docker          # Switch to Docker (emergency fallback)"
    echo "  $0 fix-priority             # Fix conflicts and prioritize nixpacks"
    echo "  $0 validate                 # Validate configuration files"
    echo ""
    echo "Strategy Priority:"
    echo "  1. nixpacks.toml (Primary - recommended)"
    echo "  2. Dockerfile.backup (Fallback - emergency use)"
    echo ""
}

# Main script logic
case "${1:-check}" in
    "check")
        check_current_strategy
        ;;
    "activate-nixpacks")
        activate_nixpacks
        ;;
    "activate-docker")
        activate_docker
        ;;
    "fix-priority")
        fix_priority
        ;;
    "validate")
        validate_config
        ;;
    "help"|"-h"|"--help")
        show_help
        ;;
    *)
        print_error "Unknown command: $1"
        echo ""
        show_help
        exit 1
        ;;
esac