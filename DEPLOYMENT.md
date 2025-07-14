# 🚀 Deployment Guide - Driveway Timeline Application

This guide provides step-by-step instructions for deploying the PHP Timeline application using MySQLi in a php-alpine Docker container.

## 📋 Prerequisites

- Docker and Docker Compose installed
- MySQL database (existing or new)
- Images stored in the correct folder structure

## 🔧 Quick Setup

### 1. Configure Database Connection

Edit [`config/database.php`](config/database.php) with your MySQL credentials:

```php
return [
    'host' => 'your_mysql_host',
    'database' => 'your_database_name',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8mb4',
    'port' => 3306
];
```

Or use environment variables:
```bash
export DB_HOST=your_mysql_host
export DB_NAME=your_database_name
export DB_USER=your_username
export DB_PASS=your_password
```

### 2. Setup Database Schema

Run the SQL setup script on your MySQL database:

```bash
mysql -h your_host -u your_user -p your_database < sql/setup.sql
```

### 3. Deploy with Docker

#### Option A: Standalone Container

```bash
# Build the image
docker build -t timeline-app .

# Run the container
docker run -d \
  --name timeline-app \
  -p 8080:80 \
  -e DB_HOST=your_mysql_host \
  -e DB_NAME=your_database \
  -e DB_USER=your_username \
  -e DB_PASS=your_password \
  -v $(pwd)/images:/var/www/html/images \
  timeline-app
```

#### Option B: Docker Compose (with MySQL)

```bash
# Copy environment file
cp .env.example .env

# Edit .env with your settings
nano .env

# Start services
docker-compose up -d
```

### 4. Add Images

Place your images in the `images/driveway/` folder with the format:
```
driveway_YYYYMMDD_HHMMSS.jpg
```

Examples:
- `driveway_20250714_193205.jpg`
- `driveway_20250714_120000.png`

### 5. Access Application

- **Timeline**: http://localhost:8080
- **API Tester**: http://localhost:8080/test_api.php

## 🧪 Testing

### Test Database Connection

1. Visit: http://localhost:8080/test_api.php
2. Click "Test Connection"
3. Verify successful connection

### Test API Endpoints

Use the built-in API tester or curl commands:

```bash
# Add a caption
curl -X POST http://localhost:8080/api/captions.php \
  -H "Content-Type: application/json" \
  -d '{"timestamp": "20250714_193205", "text": "Sample caption"}'

# Get all captions
curl http://localhost:8080/api/captions.php

# Get specific caption
curl http://localhost:8080/api/captions.php?timestamp=20250714_193205

# Get statistics
curl http://localhost:8080/api/captions.php?stats=1
```

## 📁 File Structure

```
timeline/
├── api/
│   └── captions.php          # REST API endpoints
├── classes/
│   ├── Database.php          # MySQLi database connection
│   └── TimelineManager.php   # Timeline processing
├── config/
│   └── database.php          # Database configuration
├── css/
│   └── timeline.css          # Timeline styling
├── images/
│   └── driveway/            # Image storage (create this folder)
├── js/
│   └── timeline.js          # Frontend JavaScript
├── sql/
│   └── setup.sql            # Database setup script
├── index.php                # Main timeline page
├── get_timeline.php         # Timeline data API
├── test_api.php             # API testing interface
├── Dockerfile               # Docker configuration
├── docker-compose.yml       # Docker Compose setup
├── .env.example             # Environment variables template
├── README.md               # Main documentation
└── DEPLOYMENT.md           # This deployment guide
```

## 🔍 Troubleshooting

### Common Issues

#### 1. Database Connection Failed
```
Error: Database connection failed: Access denied
```
**Solution**: Check database credentials in `config/database.php` or environment variables.

#### 2. Images Not Displaying
```
Timeline shows "No images found"
```
**Solution**: 
- Ensure `images/driveway/` folder exists
- Check image filename format: `driveway_YYYYMMDD_HHMMSS.ext`
- Verify file permissions

#### 3. API Errors
```
500 Internal Server Error
```
**Solution**:
- Check PHP error logs: `docker logs timeline-app`
- Verify database connection
- Test with `test_api.php`

#### 4. MySQLi Extension Not Available
```
Class 'mysqli' not found
```
**Solution**: The Dockerfile includes MySQLi installation. Rebuild the image:
```bash
docker build --no-cache -t timeline-app .
```

### Debug Steps

1. **Check container logs**:
   ```bash
   docker logs timeline-app
   ```

2. **Test database connection**:
   ```bash
   docker exec -it timeline-app php -r "
   \$mysqli = new mysqli('host', 'user', 'pass', 'db');
   echo \$mysqli->connect_error ? 'Failed' : 'Success';
   "
   ```

3. **Verify file permissions**:
   ```bash
   docker exec -it timeline-app ls -la /var/www/html/images/
   ```

4. **Test API manually**:
   Visit: http://localhost:8080/test_api.php

## 🔧 Configuration Options

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | `localhost` | MySQL host |
| `DB_NAME` | `timeline_db` | Database name |
| `DB_USER` | `root` | Database username |
| `DB_PASS` | `password` | Database password |
| `DB_PORT` | `3306` | Database port |

### Docker Compose Options

Edit `docker-compose.yml` to customize:

```yaml
services:
  timeline-app:
    ports:
      - "8080:80"  # Change external port
    volumes:
      - ./images:/var/www/html/images  # Mount images folder
    environment:
      - DB_HOST=mysql  # Use internal MySQL service
```

## 📈 Performance Tips

1. **Image Optimization**: Compress images before adding to timeline
2. **Database Indexing**: The setup script includes proper indexes
3. **Caching**: Consider adding Redis for large datasets
4. **CDN**: Use CDN for static assets in production

## 🔒 Security Considerations

1. **Database Access**: Use restricted database user with minimal permissions
2. **File Uploads**: The app only reads images, doesn't accept uploads
3. **API Security**: Consider adding authentication for production use
4. **SSL/TLS**: Use HTTPS in production environments

## 🚀 Production Deployment

### Additional Steps for Production

1. **SSL Certificate**: Configure HTTPS
2. **Domain Configuration**: Set up proper domain and DNS
3. **Backup Strategy**: Implement database and image backups
4. **Monitoring**: Add application and server monitoring
5. **Load Balancing**: Scale horizontally if needed

### Production Docker Command

```bash
docker run -d \
  --name timeline-app-prod \
  --restart unless-stopped \
  -p 443:80 \
  -e DB_HOST=prod-mysql-host \
  -e DB_NAME=timeline_production \
  -e DB_USER=timeline_user \
  -e DB_PASS=secure_password \
  -v /opt/timeline/images:/var/www/html/images:ro \
  -v /opt/timeline/config:/var/www/html/config:ro \
  timeline-app
```

## 📞 Support

If you encounter issues:

1. Check this troubleshooting guide
2. Review application logs
3. Test individual components with `test_api.php`
4. Verify database connectivity and schema