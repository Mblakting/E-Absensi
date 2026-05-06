#!/bin/bash

# Deployment Monitoring Script
# This script monitors deployment process health and provides alerts

set -e

echo "================================================"
echo "   E-Presensi - Deployment Monitoring"
echo "   Real-time deployment health monitoring"
echo "================================================"

# Configuration
MONITOR_INTERVAL=30        # Check every 30 seconds
BUILD_TIMEOUT=1200        # 20 minutes maximum build time
HEALTH_CHECK_TIMEOUT=10   # 10 seconds for health checks
LOG_FILE="deployment-monitor-$(date +%Y%m%d-%H%M%S).log"
ALERT_THRESHOLD=300       # Alert if build takes more than 5 minutes

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Logging functions
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

alert() {
    echo -e "${PURPLE}🚨 ALERT: $1${NC}" | tee -a "$LOG_FILE"
}

# Railway CLI check
check_railway_cli() {
    if command -v railway &> /dev/null; then
        success "Railway CLI is available"
        railway --version | tee -a "$LOG_FILE"
        return 0
    else
        error "Railway CLI is not installed or not in PATH"
        log "Install Railway CLI: npm install -g @railway/cli"
        return 1
    fi
}

# Get deployment status
get_deployment_status() {
    local project_id=$1
    
    if [ -z "$project_id" ]; then
        # Try to get project info from current directory
        railway status 2>/dev/null || {
            warning "No Railway project linked. Use 'railway login' and 'railway link' first."
            return 1
        }
    else
        railway status --project "$project_id" 2>/dev/null || {
            error "Failed to get status for project: $project_id"
            return 1
        }
    fi
}

# Monitor build logs
monitor_build_logs() {
    local start_time=$(date +%s)
    local last_log_time=$start_time
    local build_started=false
    local build_completed=false
    local hang_detected=false
    
    log "Starting build log monitoring..."
    log "Build timeout: ${BUILD_TIMEOUT}s ($(($BUILD_TIMEOUT / 60)) minutes)"
    log "Alert threshold: ${ALERT_THRESHOLD}s ($(($ALERT_THRESHOLD / 60)) minutes)"
    
    while true; do
        local current_time=$(date +%s)
        local elapsed=$((current_time - start_time))
        
        # Check for build timeout
        if [ $elapsed -gt $BUILD_TIMEOUT ]; then
            alert "Build timeout exceeded: ${elapsed}s > ${BUILD_TIMEOUT}s"
            alert "Build may be hanging - manual intervention required"
            return 1
        fi
        
        # Check for alert threshold
        if [ $elapsed -gt $ALERT_THRESHOLD ] && [ "$build_started" = true ] && [ "$build_completed" = false ]; then
            if [ "$hang_detected" = false ]; then
                alert "Build duration exceeded alert threshold: ${elapsed}s"
                alert "Monitoring for potential hang conditions..."
                hang_detected=true
            fi
        fi
        
        # Get recent logs
        log "Checking deployment logs... (elapsed: ${elapsed}s)"
        
        # Try to get logs from Railway
        local logs_output
        if logs_output=$(railway logs --tail 10 2>/dev/null); then
            # Check for build progress indicators
            if echo "$logs_output" | grep -q "BUILD\|DEPLOY\|Starting\|Installing\|Compiling"; then
                if [ "$build_started" = false ]; then
                    success "Build process detected in logs"
                    build_started=true
                fi
                last_log_time=$current_time
                log "Build activity detected - updating last activity time"
            fi
            
            # Check for completion indicators
            if echo "$logs_output" | grep -q "deployed\|ready\|listening\|server.*started"; then
                if [ "$build_completed" = false ]; then
                    success "Deployment completion detected in logs"
                    build_completed=true
                    break
                fi
            fi
            
            # Check for error indicators
            if echo "$logs_output" | grep -qi "error\|failed\|timeout\|hang"; then
                warning "Error indicators found in logs:"
                echo "$logs_output" | grep -i "error\|failed\|timeout\|hang" | tee -a "$LOG_FILE"
            fi
            
            # Check for hang indicators (no activity for extended period)
            local time_since_activity=$((current_time - last_log_time))
            if [ $time_since_activity -gt 300 ] && [ "$build_started" = true ]; then
                alert "Potential hang detected: No build activity for ${time_since_activity}s"
                alert "Last activity: $(date -d "@$last_log_time" '+%Y-%m-%d %H:%M:%S')"
            fi
            
        else
            warning "Failed to retrieve deployment logs"
        fi
        
        # Sleep before next check
        sleep $MONITOR_INTERVAL
    done
    
    local total_time=$(($(date +%s) - start_time))
    success "Build monitoring completed in ${total_time}s ($(($total_time / 60)) minutes)"
}

# Health check deployment
health_check_deployment() {
    local deployment_url=$1
    
    if [ -z "$deployment_url" ]; then
        warning "No deployment URL provided for health check"
        return 1
    fi
    
    log "Performing health check on: $deployment_url"
    
    # Basic HTTP health check
    local http_status
    if http_status=$(curl -s -o /dev/null -w "%{http_code}" --max-time $HEALTH_CHECK_TIMEOUT "$deployment_url" 2>/dev/null); then
        if [ "$http_status" = "200" ]; then
            success "Health check passed: HTTP $http_status"
            return 0
        else
            warning "Health check warning: HTTP $http_status"
            return 1
        fi
    else
        error "Health check failed: Unable to connect to $deployment_url"
        return 1
    fi
}

# Monitor deployment performance
monitor_performance() {
    local deployment_url=$1
    local check_count=5
    local total_time=0
    local successful_checks=0
    
    if [ -z "$deployment_url" ]; then
        warning "No deployment URL provided for performance monitoring"
        return 1
    fi
    
    log "Monitoring deployment performance: $deployment_url"
    log "Performing $check_count performance checks..."
    
    for i in $(seq 1 $check_count); do
        log "Performance check $i/$check_count"
        
        local start_time=$(date +%s%3N)  # Milliseconds
        local http_status
        
        if http_status=$(curl -s -o /dev/null -w "%{http_code}" --max-time $HEALTH_CHECK_TIMEOUT "$deployment_url" 2>/dev/null); then
            local end_time=$(date +%s%3N)
            local response_time=$((end_time - start_time))
            
            if [ "$http_status" = "200" ]; then
                success "Check $i: HTTP $http_status, Response time: ${response_time}ms"
                total_time=$((total_time + response_time))
                successful_checks=$((successful_checks + 1))
            else
                warning "Check $i: HTTP $http_status, Response time: ${response_time}ms"
            fi
        else
            error "Check $i: Connection failed"
        fi
        
        # Wait between checks
        if [ $i -lt $check_count ]; then
            sleep 2
        fi
    done
    
    if [ $successful_checks -gt 0 ]; then
        local avg_response_time=$((total_time / successful_checks))
        success "Performance monitoring completed"
        log "Successful checks: $successful_checks/$check_count"
        log "Average response time: ${avg_response_time}ms"
        
        # Performance alerts
        if [ $avg_response_time -gt 5000 ]; then
            alert "High response time detected: ${avg_response_time}ms > 5000ms"
        elif [ $avg_response_time -gt 2000 ]; then
            warning "Elevated response time: ${avg_response_time}ms > 2000ms"
        fi
        
        if [ $successful_checks -lt $((check_count * 80 / 100)) ]; then
            alert "Low success rate: $successful_checks/$check_count (< 80%)"
        fi
    else
        error "All performance checks failed"
        return 1
    fi
}

# Generate monitoring report
generate_monitoring_report() {
    local report_file="deployment-monitoring-report-$(date +%Y%m%d-%H%M%S).md"
    
    log "Generating monitoring report: $report_file"
    
    cat > "$report_file" << EOF
# Deployment Monitoring Report

**Generated**: $(date '+%Y-%m-%d %H:%M:%S')
**Log File**: $LOG_FILE

## Monitoring Summary

This report contains the results of deployment monitoring and health checks.

## Monitoring Configuration

- **Monitor Interval**: ${MONITOR_INTERVAL}s
- **Build Timeout**: ${BUILD_TIMEOUT}s ($(($BUILD_TIMEOUT / 60)) minutes)
- **Health Check Timeout**: ${HEALTH_CHECK_TIMEOUT}s
- **Alert Threshold**: ${ALERT_THRESHOLD}s ($(($ALERT_THRESHOLD / 60)) minutes)

## Monitoring Results

### Build Process Monitoring
- Monitored build logs for progress indicators
- Detected build start and completion events
- Checked for error and hang conditions
- Provided real-time alerts for issues

### Health Check Results
- Performed HTTP health checks on deployment
- Validated application responsiveness
- Checked deployment accessibility

### Performance Monitoring
- Measured response times across multiple checks
- Calculated average performance metrics
- Provided performance alerts and warnings

## Detailed Logs

See $LOG_FILE for detailed monitoring logs and timestamps.

## Alerts and Recommendations

Based on monitoring results:
1. Monitor build times and optimize if consistently high
2. Set up automated alerts for deployment failures
3. Implement health check endpoints for better monitoring
4. Consider performance optimization if response times are high

## Next Steps

1. Review any alerts or warnings from monitoring
2. Optimize deployment process based on performance data
3. Set up continuous monitoring for production deployments
4. Document any issues and resolutions for future reference

EOF

    success "Monitoring report generated: $report_file"
}

# Main monitoring function
main() {
    local deployment_url=""
    local project_id=""
    local monitor_build=true
    local monitor_health=true
    local monitor_perf=true
    
    # Parse command line arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --url)
                deployment_url="$2"
                shift 2
                ;;
            --project)
                project_id="$2"
                shift 2
                ;;
            --no-build)
                monitor_build=false
                shift
                ;;
            --no-health)
                monitor_health=false
                shift
                ;;
            --no-perf)
                monitor_perf=false
                shift
                ;;
            --help)
                echo "Usage: $0 [OPTIONS]"
                echo "Options:"
                echo "  --url URL        Deployment URL for health checks"
                echo "  --project ID     Railway project ID"
                echo "  --no-build       Skip build monitoring"
                echo "  --no-health      Skip health checks"
                echo "  --no-perf        Skip performance monitoring"
                echo "  --help           Show this help message"
                exit 0
                ;;
            *)
                warning "Unknown option: $1"
                shift
                ;;
        esac
    done
    
    log "Starting deployment monitoring..."
    log "Log file: $LOG_FILE"
    
    # Check prerequisites
    if ! check_railway_cli; then
        error "Railway CLI is required for deployment monitoring"
        exit 1
    fi
    
    local overall_result=0
    
    # Monitor build process
    if [ "$monitor_build" = true ]; then
        log "=== Build Process Monitoring ==="
        monitor_build_logs || overall_result=1
    fi
    
    # Health check deployment
    if [ "$monitor_health" = true ] && [ -n "$deployment_url" ]; then
        log "=== Health Check Monitoring ==="
        health_check_deployment "$deployment_url" || overall_result=1
    fi
    
    # Performance monitoring
    if [ "$monitor_perf" = true ] && [ -n "$deployment_url" ]; then
        log "=== Performance Monitoring ==="
        monitor_performance "$deployment_url" || overall_result=1
    fi
    
    # Generate report
    generate_monitoring_report
    
    if [ $overall_result -eq 0 ]; then
        success "Deployment monitoring completed successfully!"
        log "All monitoring checks passed"
    else
        error "Some monitoring checks failed - review logs for details"
        log "Check alerts and warnings for issues requiring attention"
    fi
    
    log "Monitoring completed. Check $LOG_FILE for detailed logs."
    return $overall_result
}

# Show usage if no arguments provided
if [ $# -eq 0 ]; then
    echo "Deployment Monitoring Script"
    echo "Usage: $0 [OPTIONS]"
    echo "Use --help for detailed options"
    echo ""
    echo "Example: $0 --url https://your-app.railway.app --project your-project-id"
fi

# Run main function
main "$@"