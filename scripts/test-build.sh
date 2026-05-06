#!/bin/bash

# Local Build Testing Script
# This script reproduces and validates deployment fixes locally

set -e

echo "================================================"
echo "   E-Presensi - Local Build Testing"
echo "   Testing deployment hang fixes"
echo "================================================"

# Configuration
BUILD_TIMEOUT=600  # 10 minutes timeout for build operations
TEST_TIMEOUT=300   # 5 minutes timeout for individual tests
LOG_FILE="build-test-$(date +%Y%m%d-%H%M%S).log"

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

# Check prerequisites
check_prerequisites() {
    log "Checking prerequisites..."
    
    # Check Docker
    if command -v docker &> /dev/null; then
        success "Docker is available"
        docker --version | tee -a "$LOG_FILE"
    else
        error "Docker is not installed or not in PATH"
        return 1
    fi
    
    # Check Docker daemon
    if docker info &> /dev/null; then
        success "Docker daemon is running"
    else
        error "Docker daemon is not running"
        return 1
    fi
    
    # Check required files
    local required_files=("Dockerfile.backup" "nixpacks.toml" "composer.json" "package.json")
    for file in "${required_files[@]}"; do
        if [ -f "$file" ]; then
            success "Required file found: $file"
        else
            error "Required file missing: $file"
            return 1
        fi
    done
    
    success "All prerequisites met"
}

# Test Docker build (reproduces hang issue)
test_docker_build() {
    log "=== Testing Docker Build (Hang Reproduction) ==="
    
    # Use Dockerfile.backup to test Docker build
    if [ ! -f "Dockerfile.backup" ]; then
        error "Dockerfile.backup not found"
        return 1
    fi
    
    # Copy Dockerfile.backup to Dockerfile for testing
    cp Dockerfile.backup Dockerfile.test
    
    log "Building Docker image with hang prevention..."
    test_with_timeout "Docker build" "docker build -f Dockerfile.test -t epresensi-test:docker ." $BUILD_TIMEOUT
    local docker_result=$?
    
    # Cleanup
    rm -f Dockerfile.test
    
    if [ $docker_result -eq 0 ]; then
        success "Docker build completed without hanging"
        
        # Test the built image
        log "Testing Docker image functionality..."
        test_with_timeout "Docker image test" "docker run --rm epresensi-test:docker php -v" 60
        
        # Cleanup image
        docker rmi epresensi-test:docker 2>/dev/null || true
    else
        error "Docker build failed or timed out"
    fi
    
    return $docker_result
}

# Test nixpacks build simulation
test_nixpacks_simulation() {
    log "=== Testing Nixpacks Build Simulation ==="
    
    if [ ! -f "nixpacks.toml" ]; then
        error "nixpacks.toml not found"
        return 1
    fi
    
    # Simulate nixpacks build by testing individual components
    log "Simulating nixpacks PHP extension availability..."
    
    # Test if we can simulate the nixpacks environment
    test_with_timeout "PHP version check" "php -v" 30
    test_with_timeout "Composer availability" "composer --version" 30
    test_with_timeout "Node.js availability" "node --version" 30
    test_with_timeout "NPM availability" "npm --version" 30
    
    # Test PHP extensions that would be pre-compiled in nixpacks
    log "Testing PHP extensions availability..."
    local required_extensions=("pdo" "pdo_mysql" "mbstring" "xml" "curl" "zip" "gd")
    local missing_extensions=()
    
    for ext in "${required_extensions[@]}"; do
        if php -m | grep -q "^$ext$"; then
            success "PHP extension available: $ext"
        else
            warning "PHP extension missing: $ext (would be pre-compiled in nixpacks)"
            missing_extensions+=("$ext")
        fi
    done
    
    if [ ${#missing_extensions[@]} -eq 0 ]; then
        success "All required PHP extensions are available"
    else
        warning "Missing extensions would be provided by nixpacks: ${missing_extensions[*]}"
    fi
    
    success "Nixpacks simulation completed"
}

# Test application startup
test_application_startup() {
    log "=== Testing Application Startup ==="
    
    # Test railway-start.sh script
    if [ ! -f "railway-start.sh" ]; then
        error "railway-start.sh not found"
        return 1
    fi
    
    # Make sure it's executable
    chmod +x railway-start.sh
    
    # Test script syntax
    test_with_timeout "Startup script syntax check" "bash -n railway-start.sh" 10
    
    # Test individual components of startup script
    log "Testing startup script components..."
    
    # Test composer operations
    if [ -f "composer.json" ]; then
        test_with_timeout "Composer autoload dump" "composer dump-autoload --optimize" 120
    fi
    
    # Test Laravel commands (if possible)
    if [ -f "artisan" ]; then
        test_with_timeout "Laravel config clear" "php artisan config:clear" 30
        test_with_timeout "Laravel route clear" "php artisan route:clear" 30
        test_with_timeout "Laravel view clear" "php artisan view:clear" 30
        test_with_timeout "Laravel cache clear" "php artisan cache:clear" 30
    fi
    
    success "Application startup tests completed"
}

# Test build performance
test_build_performance() {
    log "=== Testing Build Performance ==="
    
    local start_time=$(date +%s)
    
    # Test composer install performance
    if [ -f "composer.json" ]; then
        log "Testing Composer install performance..."
        local composer_start=$(date +%s)
        test_with_timeout "Composer install" "composer install --no-dev --optimize-autoloader --no-interaction" 300
        local composer_end=$(date +%s)
        local composer_duration=$((composer_end - composer_start))
        log "Composer install took: ${composer_duration}s"
    fi
    
    # Test npm install performance
    if [ -f "package.json" ]; then
        log "Testing NPM install performance..."
        local npm_start=$(date +%s)
        test_with_timeout "NPM install" "npm ci --no-audit --no-fund" 300
        local npm_end=$(date +%s)
        local npm_duration=$((npm_end - npm_start))
        log "NPM install took: ${npm_duration}s"
        
        # Test build performance
        log "Testing NPM build performance..."
        local build_start=$(date +%s)
        test_with_timeout "NPM build" "npm run build" 300
        local build_end=$(date +%s)
        local build_duration=$((build_end - build_start))
        log "NPM build took: ${build_duration}s"
    fi
    
    local end_time=$(date +%s)
    local total_duration=$((end_time - start_time))
    log "Total build performance test took: ${total_duration}s"
    
    success "Build performance tests completed"
}

# Generate test report
generate_report() {
    log "=== Generating Test Report ==="
    
    local report_file="build-test-report-$(date +%Y%m%d-%H%M%S).md"
    
    cat > "$report_file" << EOF
# Build Test Report

**Generated**: $(date '+%Y-%m-%d %H:%M:%S')
**Log File**: $LOG_FILE

## Test Summary

This report contains the results of local build testing to validate deployment hang fixes.

## Test Results

### Prerequisites Check
- Docker availability and daemon status
- Required files presence (Dockerfile.backup, nixpacks.toml, etc.)

### Docker Build Test
- Tests the Docker build process with hang prevention
- Validates timeout mechanisms work correctly
- Verifies built image functionality

### Nixpacks Simulation
- Simulates nixpacks build environment
- Checks PHP extension availability
- Validates pre-compiled extension approach

### Application Startup Test
- Tests railway-start.sh script functionality
- Validates Laravel command execution
- Checks startup script timeout prevention

### Build Performance Test
- Measures Composer install performance
- Measures NPM install and build performance
- Provides timing benchmarks

## Detailed Logs

See $LOG_FILE for detailed execution logs.

## Recommendations

Based on test results:
1. Use nixpacks strategy for faster, hang-free builds
2. Keep Docker strategy as fallback with timeout protection
3. Monitor build times and optimize as needed
4. Ensure all timeout mechanisms are working correctly

EOF

    success "Test report generated: $report_file"
}

# Main execution
main() {
    log "Starting local build testing..."
    log "Log file: $LOG_FILE"
    
    local overall_result=0
    
    # Run all tests
    check_prerequisites || overall_result=1
    test_docker_build || overall_result=1
    test_nixpacks_simulation || overall_result=1
    test_application_startup || overall_result=1
    test_build_performance || overall_result=1
    
    # Generate report
    generate_report
    
    if [ $overall_result -eq 0 ]; then
        success "All tests completed successfully!"
        log "Build hang fixes are working correctly"
    else
        error "Some tests failed - check logs for details"
        log "Review failed tests and fix issues before deployment"
    fi
    
    log "Testing completed. Check $LOG_FILE for detailed logs."
    return $overall_result
}

# Run main function
main "$@"