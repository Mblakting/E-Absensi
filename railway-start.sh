#!/bin/bash
set -e

# Build Timeout Prevention Configuration
OPERATION_TIMEOUT=120  # 2 minutes per operation
HEALTH_CHECK_INTERVAL=30  # 30 seconds between health checks
MAX_RETRIES=3  # Maximum retry attempts for failed operations

echo "================================================"
echo "   E-Presensi - Railway Startup Script"
echo "   Build Timeout Prevention: ENABLED"
echo "   Operation Timeout: ${OPERATION_TIMEOUT}s"
echo "   Health Check Interval: ${HEALTH_CHECK_INTERVAL}s"
echo "================================================"

PORT=${PORT:-8080}

# Health check function
health_check() {
    local operation=$1
    local start_time=$(date +%s)
    echo "⏱️  Health check: $operation started at $(date '+%Y-%m-%d %H:%M:%S')"
    
    # Send heartbeat to prevent timeout
    while true; do
        sleep $HEALTH_CHECK_INTERVAL
        local current_time=$(date +%s)
        local elapsed=$((current_time - start_time))
        echo "💓 Heartbeat: $operation running for ${elapsed}s (timeout: ${OPERATION_TIMEOUT}s)"
        
        if [ $elapsed -gt $OPERATION_TIMEOUT ]; then
            echo "⚠️  Operation timeout warning: $operation exceeded ${OPERATION_TIMEOUT}s"
            break
        fi
    done &
    
    # Store background process PID for cleanup
    HEALTH_CHECK_PID=$!
}

# Stop health check function
stop_health_check() {
    if [ ! -z "$HEALTH_CHECK_PID" ]; then
        kill $HEALTH_CHECK_PID 2>/dev/null || true
        unset HEALTH_CHECK_PID
    fi
}

# Retry mechanism for failed operations
retry_operation() {
    local operation_name=$1
    local operation_command=$2
    local attempt=1
    
    while [ $attempt -le $MAX_RETRIES ]; do
        echo "🔄 Attempt $attempt/$MAX_RETRIES: $operation_name"
        
        if timeout $OPERATION_TIMEOUT bash -c "$operation_command"; then
            echo "✅ $operation_name completed successfully"
            return 0
        else
            echo "❌ $operation_name failed (attempt $attempt/$MAX_RETRIES)"
            if [ $attempt -eq $MAX_RETRIES ]; then
                echo "🚨 $operation_name failed after $MAX_RETRIES attempts"
                return 1
            fi
            attempt=$((attempt + 1))
            echo "⏳ Waiting 10 seconds before retry..."
            sleep 10
        fi
    done
}

# 1. Regenerasi autoloader Composer dengan timeout prevention
echo ""
echo "[1/6] Regenerasi autoloader Composer..."
health_check "Composer autoloader regeneration"
retry_operation "Composer autoloader regeneration" "composer dump-autoload --optimize"
stop_health_check
echo "      ✅ Autoloader selesai!"

# 2. Hapus SEMUA cache lama dengan timeout prevention
echo ""
echo "[2/6] Membersihkan cache lama..."
health_check "Cache clearing"
retry_operation "Cache clearing" "
    php artisan config:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
    php artisan cache:clear 2>/dev/null || true
    php artisan clear-compiled 2>/dev/null || true
"
stop_health_check
echo "      ✅ Cache bersih!"

# 3. Jalankan migrasi database dengan timeout prevention
echo ""
echo "[3/6] Menjalankan migrasi database..."
health_check "Database migration"
retry_operation "Database migration" "php artisan migrate --force"
stop_health_check
echo "      ✅ Migrasi selesai!"

# 4. Seed data awal dengan timeout prevention
echo ""
echo "[4/6] Mengisi data awal..."
health_check "Database seeding"
retry_operation "Database seeding" "php artisan db:seed --force"
stop_health_check
echo "      ✅ Seeding selesai!"

# 5. Buat symlink storage dengan timeout prevention
echo ""
echo "[5/6] Membuat storage symlink..."
health_check "Storage symlink creation"
retry_operation "Storage symlink creation" "php artisan storage:link --force 2>/dev/null || true"
stop_health_check
echo "      ✅ Storage link selesai!"

# 6. Set permission dengan timeout prevention
echo ""
echo "[6/6] Set permission folder..."
health_check "Permission setting"
retry_operation "Permission setting" "chmod -R 775 storage bootstrap/cache 2>/dev/null || true"
stop_health_check
echo "      ✅ Permission selesai!"

echo ""
echo "================================================"
echo "   ✅ Semua setup selesai! Menjalankan server..."
echo "   Port: $PORT"
echo "   Build Timeout Prevention: ACTIVE"
echo "   Health Monitoring: ENABLED"
echo "================================================"

# Final health check before starting server
echo "🔍 Final system health check..."
echo "PHP Version: $(php -v | head -1)"
echo "Available Memory: $(free -h | grep '^Mem:' | awk '{print $7}' 2>/dev/null || echo 'N/A')"
echo "Disk Space: $(df -h . | tail -1 | awk '{print $4}' 2>/dev/null || echo 'N/A')"
echo "Database Connection: $(php artisan tinker --execute='DB::connection()->getPdo(); echo "OK";' 2>/dev/null || echo 'Failed')"

# Server startup with automatic restart capability
echo ""
echo "🚀 Starting Laravel server with health monitoring..."
echo "⏱️  Server startup timeout: ${OPERATION_TIMEOUT}s"

# Start server with timeout and restart capability
while true; do
    echo "💫 Starting server at $(date '+%Y-%m-%d %H:%M:%S')"
    
    # Start server with timeout
    timeout $OPERATION_TIMEOUT php artisan serve --host=0.0.0.0 --port=$PORT || {
        echo "⚠️  Server stopped or timed out, checking for restart..."
        
        # Check if it's a normal shutdown or error
        if [ $? -eq 124 ]; then
            echo "🔄 Server timeout detected, restarting..."
        else
            echo "❌ Server error detected, attempting restart in 5 seconds..."
            sleep 5
        fi
        
        # Health check before restart
        echo "🔍 Pre-restart health check..."
        php -v > /dev/null 2>&1 || {
            echo "🚨 PHP not responding, critical error detected"
            exit 1
        }
        
        echo "✅ PHP responsive, restarting server..."
        continue
    }
    
    # If we reach here, server exited normally
    echo "ℹ️  Server exited normally"
    break
done
