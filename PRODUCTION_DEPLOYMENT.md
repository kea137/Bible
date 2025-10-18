# Production Deployment Guide

This guide provides instructions for deploying the Bible Application to a production environment.

## Pre-Deployment Checklist

### 1. Security Configuration

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Generate a strong `APP_KEY`: `php artisan key:generate`
- [ ] Use HTTPS (SSL/TLS certificate installed)
- [ ] Set `SESSION_SECURE_COOKIE=true` in `.env`
- [ ] Configure secure database credentials
- [ ] Review and update CORS settings if needed
- [ ] Ensure all sensitive files are in `.gitignore`

### 2. Database Optimization

- [ ] Use MySQL or PostgreSQL instead of SQLite for production
- [ ] Run all migrations: `php artisan migrate --force`
- [ ] Run the performance indexes migration
- [ ] Configure database connection pooling
- [ ] Set up database backups
- [ ] Enable query caching

Example production database configuration:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bible_app
DB_USERNAME=bible_user
DB_PASSWORD=strong_password_here
```

### 3. Caching and Performance

- [ ] Install and configure Redis: `sudo apt-get install redis-server`
- [ ] Update cache driver: `CACHE_STORE=redis`
- [ ] Update session driver: `SESSION_DRIVER=redis`
- [ ] Update queue driver: `QUEUE_CONNECTION=redis`
- [ ] Run optimization commands:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan event:cache
  composer install --optimize-autoloader --no-dev
  ```

### 4. Asset Compilation

- [ ] Build production assets: `npm run build`
- [ ] Enable asset versioning (already configured in Vite)
- [ ] Configure CDN for static assets (optional)

### 5. Server Configuration

#### Apache
- Ensure `mod_rewrite` is enabled
- Ensure `mod_headers` is enabled for security headers
- Point document root to `/public` directory
- Configure `.htaccess` (already included with security headers)

#### Nginx
Example configuration:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/bible-app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 6. Queue Workers

- [ ] Set up queue workers with supervisor or systemd
- [ ] Configure queue worker monitoring
- [ ] Set up failed job handling

Example supervisor configuration:
```ini
[program:bible-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/bible-app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/bible-app/storage/logs/worker.log
stopwaitsecs=3600
```

### 7. Monitoring and Logging

- [ ] Set `LOG_LEVEL=error` in production
- [ ] Configure log rotation
- [ ] Set up application monitoring (e.g., Laravel Telescope for staging)
- [ ] Configure error tracking (e.g., Sentry, Bugsnag)
- [ ] Set up uptime monitoring
- [ ] Configure performance monitoring

### 8. Backup Strategy

- [ ] Set up automated database backups
- [ ] Configure file storage backups
- [ ] Test backup restoration process
- [ ] Document backup retention policy

### 9. SEO Optimization

- [ ] Verify robots.txt is properly configured
- [ ] Create and submit sitemap.xml to search engines
- [ ] Verify meta tags are rendering correctly
- [ ] Test Open Graph and Twitter Card tags
- [ ] Set up Google Analytics or similar (if needed)
- [ ] Configure Google Search Console

### 10. Testing

- [ ] Run all tests: `php artisan test`
- [ ] Test authentication flows
- [ ] Test critical user paths
- [ ] Load test the application
- [ ] Verify SSL certificate
- [ ] Test security headers
- [ ] Verify CSRF protection is working
- [ ] Test two-factor authentication

## Deployment Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/kea137/Bible.git
   cd Bible
   ```

2. **Install dependencies**
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with production values
   php artisan key:generate
   ```

4. **Set up database**
   ```bash
   php artisan migrate --force
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Optimize application**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```

7. **Set permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

8. **Start queue workers**
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start bible-worker:*
   ```

9. **Verify deployment**
   - Check application is accessible
   - Test critical functionality
   - Review logs for errors
   - Monitor performance

## Post-Deployment

### Regular Maintenance

- Monitor error logs regularly
- Update dependencies monthly for security patches
- Review and optimize database queries
- Monitor disk space and performance metrics
- Keep SSL certificates updated
- Review security advisories

### Updates

When deploying updates:
```bash
git pull origin main
composer install --optimize-autoloader --no-dev
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

## Security Checklist

- ✓ Security headers configured in `.htaccess`
- ✓ CSRF protection enabled
- ✓ XSS protection headers set
- ✓ SQL injection prevention via Eloquent ORM
- ✓ Two-factor authentication available
- ✓ Password hashing with bcrypt
- ✓ Session security configured
- ✓ File upload validation in place

## Performance Optimization

- ✓ Database indexes added for common queries
- ✓ Eager loading configured to prevent N+1 queries
- ✓ Asset compilation and minification
- ✓ Redis caching configured
- ✓ Query result caching available
- ✓ HTTP/2 support (via server configuration)
- ✓ Gzip compression (via server configuration)

## Troubleshooting

### Common Issues

**500 Internal Server Error**
- Check file permissions on `storage` and `bootstrap/cache`
- Verify `.env` configuration
- Check error logs in `storage/logs`

**Assets not loading**
- Verify assets are built: `npm run build`
- Check public directory permissions
- Verify Vite manifest exists

**Database connection errors**
- Verify database credentials in `.env`
- Ensure database server is running
- Check firewall rules

**Queue jobs not processing**
- Verify queue workers are running
- Check supervisor/systemd status
- Review queue worker logs

## Support

For issues and questions:
- GitHub Issues: https://github.com/kea137/Bible/issues
- Review application logs in `storage/logs`
- Check Laravel documentation: https://laravel.com/docs

## License and Liability

This application is provided under the MIT License without warranty. Users are responsible for their own deployment and use of the application. See README.md for full disclaimer.
