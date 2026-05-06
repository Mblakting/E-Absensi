# Dockerfile Optimization for PHP Extension Installation

## Overview

This document describes the optimized Dockerfile implementation that prevents PHP extension compilation hangs during Railway deployment. The solution implements multiple reliability strategies to ensure successful deployment.

## Key Optimizations

### 1. Multi-Stage Build Approach

The Dockerfile uses a multi-stage build to isolate PHP extension compilation:

- **Stage 1 (php-extensions)**: Compiles all PHP extensions in isolation
- **Stage 2 (main)**: Copies pre-compiled extensions and builds the application

This approach prevents compilation issues from affecting the main application build.

### 2. Timeout Protection

All potentially long-running operations are wrapped with timeout commands:

- PHP extension compilation: 180 seconds (with 120s fallback)
- Composer installation: 300 seconds
- npm operations: 300 seconds
- Asset building: 300 seconds

### 3. Fallback Strategies

Multiple fallback mechanisms are implemented:

- **PHP Extensions**: If direct installation fails, try configure + install approach
- **Composer**: If curl installation fails, use pre-built composer image
- **npm**: If npm ci fails, fall back to npm install
- **Memory**: If composer fails, retry with increased memory limit

### 4. Pre-compiled Extension Strategy

Extensions are compiled once in the first stage and copied to the main stage, eliminating repeated compilation that could hang.

### 5. Resource Management

- Efficient layer caching for dependencies
- Cleanup of package caches to reduce image size
- Proper permission handling with error tolerance
- Health checks to ensure container functionality

## Extension Installation Strategy

### Current Approach (Optimized)

```dockerfile
# Compile extensions with timeout and fallback
(timeout 180 docker-php-ext-install pdo || \
    (echo "PDO install failed, trying alternative approach..." && \
     timeout 120 docker-php-ext-configure pdo && \
     timeout 120 docker-php-ext-install pdo))
```

### Previous Approach (Problematic)

```dockerfile
# Direct installation without timeout protection
docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip
```

## Build Process Flow

1. **Extension Compilation Stage**
   - Install build dependencies
   - Compile PHP extensions with timeout protection
   - Clean up build artifacts

2. **Application Stage**
   - Copy pre-compiled extensions
   - Install runtime dependencies
   - Install Composer dependencies
   - Install and build Node.js assets
   - Copy application code
   - Set permissions and configuration

3. **Verification**
   - Verify all PHP extensions are loaded
   - Run health checks
   - Prepare startup script

## Testing

Use the provided test scripts to validate the optimization:

```bash
# Test Docker build process
./scripts/test-build.sh

# Test nixpacks alternative (if available)
./scripts/test-nixpacks.sh
```

## Deployment Strategy

The optimized Dockerfile provides:

- **Primary Strategy**: Multi-stage Docker build with timeout protection
- **Fallback Strategy**: nixpacks.toml configuration (ignored via .railwayignore)
- **Recovery**: Dockerfile.backup contains original configuration

## Troubleshooting

If deployment still hangs:

1. Check Railway build logs for timeout messages
2. Verify all required system dependencies are available
3. Consider increasing timeout values for slower environments
4. Use nixpacks.toml as alternative deployment method

## Performance Benefits

- **Faster Builds**: Pre-compiled extensions reduce build time
- **Reliability**: Timeout protection prevents indefinite hangs
- **Caching**: Efficient layer caching reduces repeated work
- **Fallbacks**: Multiple strategies ensure deployment success