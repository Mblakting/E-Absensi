FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first (for layer caching)
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy package.json first for better layer caching
COPY package.json ./

# Copy package-lock.json if it exists (using wildcard to avoid failure if missing)
COPY package-lock.json* ./

# Install Node dependencies with fallback logic and error handling
RUN set -e; \
    if [ -f package-lock.json ] && [ -s package-lock.json ]; then \
        echo "Found package-lock.json, using npm ci for reproducible builds" && \
        npm ci --no-audit --no-fund --prefer-offline; \
    else \
        echo "No package-lock.json found, using npm install and generating lock file" && \
        npm install --no-audit --no-fund --prefer-offline && \
        echo "Generated package-lock.json for future builds"; \
    fi

# Build assets with error handling
RUN set -e; \
    echo "Building frontend assets..." && \
    npm run build && \
    echo "Frontend build completed successfully"

# Clean npm cache to reduce image size
RUN npm cache clean --force

# Copy the rest of the application
COPY . .

# Run composer scripts after full copy
RUN composer run-script post-autoload-dump 2>/dev/null || true

# Set permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Copy and set startup script
COPY railway-start.sh /railway-start.sh
RUN chmod +x /railway-start.sh

EXPOSE 8080

CMD ["/railway-start.sh"]
