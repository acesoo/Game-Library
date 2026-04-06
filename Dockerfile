FROM php:8.2-cli

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /app

# Copy project files
COPY . /app/

# Create uploads directory
RUN mkdir -p /app/uploads && chmod 777 /app/uploads

EXPOSE 8080

# Use PHP built-in server instead of Apache
CMD php -S 0.0.0.0:${PORT:-8080} -t /app
