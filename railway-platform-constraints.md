# Railway Platform Constraints Research

## Overview

This document outlines Railway platform-specific limitations that could cause PHP extension compilation to hang during deployment, based on research conducted for the deployment error fix bugfix spec.

## Railway Build System Architecture

### Build Process Phases

Railway deployments go through several distinct phases, each with specific constraints:

1. **Initialization** (Snapshot Code) - Seconds
2. **Build Phase** - 1-10+ minutes (most critical for PHP extension compilation)
3. **Pre-Deploy** - Seconds to minutes
4. **Deploy** (Creating containers) - 30 seconds to 2 minutes
5. **Network** (Healthchecks) - Up to 5 minutes (configurable)
6. **Post-Deploy** - Seconds

### Build System Options

Railway supports two primary build systems:
- **Docker**: Uses Dockerfile for custom build instructions
- **Nixpacks (Railpack)**: Railway's native zero-config build system

## Build Timeout Limits

### Default Timeouts
- **Healthcheck timeout**: 300 seconds (5 minutes) - configurable
- **HTTP request timeout**: 15 minutes maximum
- **Build phase**: No explicit timeout documented, but builds can hang indefinitely

### Timeout Behavior
- Build processes can hang without error messages
- No automatic termination for stuck compilation processes
- Users report builds hanging for 15+ minutes during PHP extension compilation

## Memory and CPU Constraints

### Resource Limits by Plan
| Plan | Per-Replica vCPU Limit | Per-Replica Memory Limit |
|------|------------------------|---------------------------|
| Hobby | 8 vCPU | 8 GB |
| Pro | 24 vCPU | 24 GB |
| Enterprise | Custom | Custom |

### Build-Time Resource Issues
- **Memory exhaustion**: PHP extension compilation can consume significant memory
- **CPU throttling**: Complex compilation processes may hit CPU limits
- **Resource allocation**: Build containers may have different limits than runtime containers
- **No swap configuration**: Limited memory can cause OOM kills during compilation

## Known Issues with PHP Extension Compilation

### Docker-based Compilation Problems

1. **docker-php-ext-install hangs**: 
   - Common with PDO and PDO MySQL extensions
   - No error messages or completion signals
   - Consistent failure pattern across deployments

2. **Resource-intensive compilation**:
   - Requires development headers and build tools
   - Complex dependency resolution for MySQL client libraries
   - Potential conflicts between system packages

3. **Network timeouts**:
   - Downloading extension source code may timeout
   - Package repository access issues during build

### Platform-Specific Limitations

1. **Build environment constraints**:
   - Limited control over build container configuration
   - No access to build container debugging tools
   - Restricted ability to monitor resource usage during build

2. **Compilation complexity**:
   - Manual compilation involves multiple system dependencies
   - Build tools and headers must be installed separately
   - Version compatibility issues between PHP and system libraries

## Railway's Nixpacks System Advantages

### Pre-compiled Extensions
- Uses Nix packages with pre-built PHP extensions
- Eliminates compilation time and hang risks
- Better resource management and timeout handling
- Native integration with Railway's infrastructure

### PHP Extension Installation via Nixpacks
```json
{
  "require": {
    "php": ">=8.2",
    "ext-ctype": "*",
    "ext-iconv": "*",
    "ext-pdo": "*",
    "ext-pdo_mysql": "*"
  }
}
```

### Supported PHP Versions in Nixpacks
- PHP 8.1
- PHP 8.2 (Default)
- PHP 8.3
- PHP 8.4

## Recommended Practices for PHP Builds on Railway

### 1. Use Nixpacks Instead of Docker
- Avoid manual PHP extension compilation
- Leverage pre-compiled packages from nixpkgs
- Reduce build time and eliminate hang risks

### 2. Optimize Build Configuration
```toml
# nixpacks.toml
[phases.setup]
nixPkgs = [
  "php82",
  "php82Extensions.pdo",
  "php82Extensions.pdo_mysql",
  "php82Extensions.mbstring",
  "php82Extensions.xml",
  "php82Extensions.curl",
  "php82Extensions.zip",
  "php82Extensions.gd"
]
```

### 3. Implement Build Monitoring
- Add progress indicators for long operations
- Use timeout mechanisms for potentially hanging steps
- Implement retry logic for transient failures

### 4. Resource Management
- Monitor memory usage during builds
- Use cache mounts to reduce rebuild times
- Optimize dependency installation order

## Platform Limitations Summary

### Build System Constraints
- **No explicit build timeout**: Builds can hang indefinitely
- **Limited debugging access**: Cannot inspect build containers during hangs
- **Resource allocation opacity**: Build-time limits may differ from documented runtime limits

### Docker-specific Issues
- **Extension compilation complexity**: Manual compilation prone to hangs
- **Dependency management**: Complex system package requirements
- **Error reporting**: Limited visibility into compilation failures

### Network and Infrastructure
- **Geographic latency**: Build performance varies by region
- **Package repository access**: Network issues can cause timeouts
- **Cache invalidation**: Fresh builds may take significantly longer

## Mitigation Strategies

### Immediate Solutions
1. **Switch to Nixpacks**: Use Railway's native build system
2. **Pre-compiled extensions**: Avoid manual compilation entirely
3. **Timeout protection**: Add explicit timeouts to build steps

### Long-term Optimizations
1. **Build caching**: Implement effective layer caching
2. **Multi-stage builds**: Isolate compilation from runtime
3. **Resource monitoring**: Track build performance metrics

## Conclusion

Railway platform constraints that contribute to PHP extension compilation hangs include:

1. **Lack of build timeouts** allowing indefinite hangs
2. **Resource limitations** during compilation phases
3. **Docker compilation complexity** vs. Nixpacks simplicity
4. **Limited build debugging capabilities**

The recommended solution is to migrate from Docker-based compilation to Railway's Nixpacks system, which uses pre-compiled PHP extensions and eliminates the compilation hang risk entirely.