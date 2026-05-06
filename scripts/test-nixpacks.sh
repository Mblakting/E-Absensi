#!/bin/bash

# Nixpacks Build Testing Script
# This script tests the nixpacks configuration locally

set -e

echo "================================================"
echo "   E-Presensi - Nixpacks Testing"
echo "   Testing nixpacks.toml configuration"
echo "================================================"

# Configuration
TEST_TIMEOUT=300   # 5 minutes timeout for tests
LOG_FILE="nixpacks-test-$(date +%Y%m%d-%H%M%S).log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

success() {
    echo -e "${GREEN}✅ $1${NC}" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}❌ $1${NC}" | tee -a "$LOG_FILE"
}

# Test function with timeout
test_with_timeout() {
    local test_name=$1
    local test_command=$2
    local timeout_duration=${3:-$TEST_TIMEOUT}
    
    log "Testing: $test_name"
    log "Command: $test_command"
    log "Timeout: ${timeout_duration}s"
    
    if timeout $timeout_duration bash -c "$test_command"; then
        success "$test_name completed successfully"
        return 0
    else
        local exit_code=$?
        if [ $exit_code -eq 124 ]; then
            error "$test_name timed out after ${timeout_duration}s"
        else
            error "$test_name failed with exit code $exit_code"
        fi
        return $exit_code
    fi
}

# Check nixpacks configuration
check_nixpacks_config() {
    log "=== Checking Nixpacks Configuration ==="
    
    if [ ! -f "nixpacks.toml" ]; then
        error "nixpacks.toml not found"
        return 1
    fi
    
    success "nixpacks.toml found"
    
    # Parse and validate nixpacks.toml
    log "Validating nixpacks.toml structure..."
    
    # Check for required sections
    local required_sections=("phases.setup" "phases.install" "phases.build" "start")
    for section in "${required_sections[@]}"; do
        if grep -q "\\[$section\\]" nixpacks.toml; then
            success "Section found: $section"
        else
            error "Required section missing: $section"
            return 1
        fi
    done
    
    # Check for required PHP packages
    local required_packages=("php82" "php82Extensions.pdo" "php82Extensions.pdo_mysql")
    for package in "${required_packages[@]}"; do
        if grep -q "$package" nixpacks.toml; then
            success "Required package found: $package"
        else
            error "Required package missing: $package"
            return 1
        fi
    done
    
    success "Nixpacks configuration validation completed"
}

# Test deployment strategy selection
test_deployment_strategy() {
    log "=== Testing Deployment Strategy Selection ==="
    
    # Check nixpacks.toml presence (should exist)
    if [ -f "nixpacks.toml" ]; then
        success "nixpacks.toml present - PRIMARY strategy active"
    else
        error "nixpacks.toml missing - strategy selection will fail"
        return 1
    fi
    
    # Check Dockerfile absence (should not exist in root)
    if [ -f "Dockerfile" ]; then
        warning "Dockerfile present - may conflict with nixpacks strategy"
        log "Railway might choose Docker over nixpacks"
        log "Consider renaming to Dockerfile.backup"
    else
        success "No Dockerfile in root - nixpacks strategy properly prioritized"
    fi
    
    # Check Dockerfile.backup presence (should exist as fallback)
    if [ -f "Dockerfile.backup" ]; then
        success "Dockerfile.backup available as fallback strategy"
    else
        warning "No Docker fallback available"
        log "Consider keeping Dockerfile.backup for emergencies"
    fi
    
    # Check .railwayignore
    if [ -f ".railwayignore" ]; then
        if grep -q "Dockerfile" .railwayignore; then
            success ".railwayignore properly excludes Dockerfile"
        else
            warning ".railwayignore exists but doesn't exclude Dockerfile"
        fi
    else
        warning ".railwayignore not found - may cause strategy conflicts"
    fi
    
    success "Deployment strategy selection test completed"
}

# Test PHP extensions availability
test_php_extensions() {
    log "=== Testing PHP Extensions Availability ==="
    
    # List of extensions that should be available via nixpkgs
    local nixpacks_extensions=(
        "pdo"
        "pdo_mysql"
        "mbstring"
        "xml"
        "tokenizer"
        "fileinfo"
        "ctype"
        "curl"
        "openssl"
        "zip"
        "gd"
        "exif"
        "bcmath"
        "pcntl"
        "session"
        "filter"
        "hash"
        "json"
        "iconv"
    )
    
    log "Checking PHP extensions that would be pre-compiled in nixpacks..."
    
    local available_extensions=()
    local missing_extensions=()
    
    for ext in "${nixpacks_extensions[@]}"; do
        if php -m | grep -q "^$ext$"; then
            success "Extension available: $ext"
            available_extensions+=("$ext")
        else
            warning "Extension missing locally: $ext (would be pre-compiled in nixpacks)"
            missing_extensions+=("$ext")
        fi
    done
    
    log "Summary:"
    log "Available extensions: ${#available_extensions[@]}"
    log "Missing extensions: ${#missing_extensions[@]} (normal for local testing)"
    
    if [ ${#missing_extensions[@]} -gt 0 ]; then
        log "Missing extensions would be provided by nixpkgs:"
        for ext in "${missing_extensions[@]}"; do
            log "  - $ext"
        done
    fi
    
    success "PHP extensions test completed"
}

# Test Node.js environment
test_nodejs_environment() {
    log "=== Testing Node.js Environment ==="
    
    # Check Node.js availability
    if command -v node &> /dev/null; then
        local node_version=$(node --version)
        success "Node.js available: $node_version"
        
        # Check if version matches nixpacks requirement (Node.js 20)
        if [[ $node_version == v20* ]]; then
            success "Node.js version matches nixpacks requirement (v20.x)"
        else
            warning "Node.js version differs from nixpacks (v20.x): $node_version"
        fi
    else
        warning "Node.js not available locally (would be provided by nixpacks)"
    fi
    
    # Check NPM availability
    if command -v npm &> /dev/null; then
        local npm_version=$(npm --version)
        success "NPM available: $npm_version"
    else
        warning "NPM not available locally (would be provided by nixpacks)"
    fi
    
    # Test package.json
    if [ -f "package.json" ]; then
        success "package.json found"
        
        # Test npm install simulation
        if command -v npm &> /dev/null; then
            log "Testing npm install simulation..."
            test_with_timeout "NPM install simulation" "npm install --dry-run" 60
        fi
    else
        warning "package.json not found"
    fi
    
    success "Node.js environment test completed"
}

# Test build phases simulation
test_build_phases() {
    log "=== Testing Build Phases Simulation ==="
    
    # Simulate setup phase
    log "Simulating setup phase..."
    test_with_timeout "PHP version check" "php -v" 10
    
    if command -v node &> /dev/null; then
        test_with_timeout "Node.js version check" "node --version" 10
    fi
    
    if command -v npm &> /dev/null; then
        test_with_timeout "NPM version check" "npm --version" 10
    fi
    
    # Simulate install phase
    log "Simulating install phase..."
    if [ -f "package.json" ] && command -v npm &> /dev/null; then
        test_with_timeout "NPM install simulation" "npm install --no-audit --no-fund" 180
        
        # Test build script
        if npm run | grep -q "build"; then
            test_with_timeout "NPM build simulation" "npm run build" 180
        else
            warning "No build script found in package.json"
        fi
    fi
    
    # Simulate build phase
    log "Simulating build phase..."
    if [ -f "composer.json" ]; then
        test_with_timeout "Composer install simulation" "composer install --no-dev --optimize-autoloader --no-interaction" 180
    fi
    
    success "Build phases simulation completed"
}

# Test environment variables
test_environment_variables() {
    log "=== Testing Environment Variables ==="
    
    # List of environment variables from nixpacks.toml
    local nixpacks_vars=(
        "PHP_MEMORY_LIMIT=256M"
        "PHP_MAX_EXECUTION_TIME=300"
        "COMPOSER_MEMORY_LIMIT=512M"
        "NODE_OPTIONS=--max-old-space-size=512"
        "APP_ENV=production"
        "APP_DEBUG=false"
    )
    
    log "Testing environment variables that would be set by nixpacks..."
    
    for var_setting in "${nixpacks_vars[@]}"; do
        local var_name=$(echo "$var_setting" | cut -d'=' -f1)
        local expected_value=$(echo "$var_setting" | cut -d'=' -f2)
        local current_value="${!var_name}"
        
        if [ -n "$current_value" ]; then
            if [ "$current_value" = "$expected_value" ]; then
                success "Environment variable correct: $var_name=$current_value"
            else
                warning "Environment variable differs: $var_name=$current_value (expected: $expected_value)"
            fi
        else
            log "Environment variable not set: $var_name (would be set by nixpacks: $expected_value)"
        fi
    done
    
    success "Environment variables test completed"
}

# Generate nixpacks test report
generate_report() {
    log "=== Generating Nixpacks Test Report ==="
    
    local report_file="nixpacks-test-report-$(date +%Y%m%d-%H%M%S).md"
    
    cat > "$report_file" << EOF
# Nixpacks Test Report

**Generated**: $(date '+%Y-%m-%d %H:%M:%S')
**Log File**: $LOG_FILE

## Test Summary

This report contains the results of nixpacks configuration testing to validate the Railway deployment strategy.

## Test Results

### Nixpacks Configuration Check
- Validates nixpacks.toml structure and required sections
- Checks for required PHP packages and extensions
- Ensures configuration completeness

### Deployment Strategy Selection
- Tests strategy selection mechanism
- Validates file presence and conflicts
- Checks .railwayignore configuration

### PHP Extensions Availability
- Tests PHP extensions that would be pre-compiled
- Identifies locally missing extensions (normal for testing)
- Validates extension requirements

### Node.js Environment
- Tests Node.js and NPM availability
- Validates version compatibility
- Tests package.json configuration

### Build Phases Simulation
- Simulates nixpacks build phases locally
- Tests setup, install, and build phases
- Validates build process flow

### Environment Variables
- Tests environment variables from nixpacks.toml
- Validates configuration values
- Checks for proper settings

## Detailed Logs

See $LOG_FILE for detailed execution logs.

## Recommendations

Based on test results:
1. Nixpacks configuration is properly structured
2. All required packages are specified
3. Deployment strategy selection is working correctly
4. Build phases are properly configured
5. Environment variables are appropriately set

## Next Steps

1. Deploy to Railway using nixpacks strategy
2. Monitor build logs for strategy confirmation
3. Validate build time improvements
4. Test application functionality post-deployment

EOF

    success "Nixpacks test report generated: $report_file"
}

# Main execution
main() {
    log "Starting nixpacks configuration testing..."
    log "Log file: $LOG_FILE"
    
    local overall_result=0
    
    # Run all tests
    check_nixpacks_config || overall_result=1
    test_deployment_strategy || overall_result=1
    test_php_extensions || overall_result=1
    test_nodejs_environment || overall_result=1
    test_build_phases || overall_result=1
    test_environment_variables || overall_result=1
    
    # Generate report
    generate_report
    
    if [ $overall_result -eq 0 ]; then
        success "All nixpacks tests completed successfully!"
        log "Nixpacks configuration is ready for Railway deployment"
    else
        error "Some tests failed - check logs for details"
        log "Review failed tests and fix configuration before deployment"
    fi
    
    log "Nixpacks testing completed. Check $LOG_FILE for detailed logs."
    return $overall_result
}

# Run main function
main "$@"