# 📷 Driveway Timeline Application

A PHP application that displays images stored in a folder with timestamps embedded in the filename, creating a beautiful vertical timeline with captions stored in a MySQL database.

## Features

- 🖼️ **Automatic Image Discovery**: Scans `images/driveway/` folder for images with timestamp format
- ⏰ **Timeline Display**: Beautiful vertical timeline with alternating left/right image layout
- 💾 **Database Integration**: MySQL database for storing and retrieving image captions
- 🔍 **Search Functionality**: Search captions with relevance scoring and highlighting
- 🔌 **REST API**: Clean API endpoints for caption management and search
- 🐳 **Docker Ready**: Containerized with php-alpine for easy deployment
- 📱 **Responsive Design**: Works on desktop, tablet, and mobile devices
- ⚡ **Performance**: Lazy loading images and optimized database queries

## Expected Image Format

Images should be named with the format: `driveway_YYYYMMDD_HHMMSS.ext`

Examples:
- `driveway_20250714_193205.jpg`
- `driveway_20250714_120000.png`
- `driveway_20250713_080000.jpeg`

## Project Structure

```
timeline/
├── api/
│   └── captions.php          # REST API endpoints
├── classes/
│   ├── Database.php          # Database connection class
│   └── TimelineManager.php   # Timeline logic and image scanning
├── config/
│   └── database.php          # Database configuration
├── css/
│   └── timeline.css          # Timeline styling
├── images/
│   └── driveway/            # Image storage folder
├── js/
│   └── timeline.js          # Frontend JavaScript
├── sql/
│   └── setup.sql            # Database setup script
├── index.php                # Main landing page
├── get_timeline.php         # Timeline data endpoint
├── Dockerfile               # Docker configuration
├── docker-compose.yml       # Docker Compose setup
└── README.md               # This file
```

## Installation & Setup

### Option 1: Docker Deployment (Recommended)

1. **Clone or copy the application files**
2. **Create environment file**:
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

3. **Build and run with Docker Compose**:
   ```bash
   docker-compose up -d
   ```

4. **Access the application**:
   - Timeline: http://localhost:8080
   - MySQL: localhost:3306

### Option 2: Existing MySQL Database

1. **Configure database connection**:
   - Edit `config/database.php` with your MySQL credentials
   - Or set environment variables: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`

2. **Run database setup**:
   ```sql
   mysql -u your_user -p your_database < sql/setup.sql
   ```

3. **Deploy to your php-alpine container**:
   ```bash
   docker build -t timeline-app .
   docker run -p 8080:80 \
     -e DB_HOST=your_mysql_host \
     -e DB_NAME=your_database \
     -e DB_USER=your_username \
     -e DB_PASS=your_password \
     -v /path/to/images:/var/www/html/images \
     timeline-app
   ```

## Database Schema

The application creates a `captions` table:

```sql
CREATE TABLE captions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp VARCHAR(14) NOT NULL,
    text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_timestamp (timestamp)
);
```

## API Endpoints

### Add/Update Caption
```http
POST /api/captions.php
Content-Type: application/json

{
    "timestamp": "20250714_193205",
    "text": "Your caption here"
}
```

### Get Specific Caption
```http
GET /api/captions.php?timestamp=20250714_193205
```

### Get All Captions
```http
GET /api/captions.php
```

### Delete Caption
```http
DELETE /api/captions.php?timestamp=20250714_193205
```

### Get Application Statistics

### Search Captions
```http
GET /api/search.php?q=search_term&exact=false&case=false&limit=50
```

### Get Search Suggestions
```http
GET /api/search.php?suggestions=1&q=partial_term&limit=10
```

### Get Popular Search Terms
```http
GET /api/search.php?popular=1&limit=20
```

### Advanced Search
```http
POST /api/search.php
Content-Type: application/json

{
    "advanced_search": true,
    "criteria": {
        "text": "search_term",
        "date_from": "20250701_000000",
        "date_to": "20250731_235959",
        "min_length": 10,
        "limit": 20
    }
}
```
```http
GET /api/captions.php?stats=1
```

## Usage Examples

### Adding Images
1. Copy your images to the `images/driveway/` folder
2. Ensure filenames follow the format: `driveway_YYYYMMDD_HHMMSS.ext`
3. Refresh the timeline to see new images

### Adding Captions via API
```bash
# Add a caption
curl -X POST http://localhost:8080/api/captions.php \
  -H "Content-Type: application/json" \
  -d '{"timestamp": "20250714_193205", "text": "Evening driveway check"}'

# Get all captions
curl http://localhost:8080/api/captions.php
```

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | `localhost` | MySQL host |
| `DB_NAME` | `timeline_db` | Database name |
| `DB_USER` | `root` | Database username |
| `DB_PASS` | `password` | Database password |

## Development

### Local Development Setup
1. Ensure PHP 8.0+ with PDO MySQL extension
2. Set up MySQL database
3. Run SQL setup script
4. Configure database connection
5. Start PHP development server:
   ```bash
   php -S localhost:8080
   ```

### File Permissions
Ensure the web server can read/write to:
- `images/driveway/` folder
- Configuration files

## Features & Functionality

### Timeline Display
- **Vertical Layout**: Images displayed in chronological order
- **Alternating Sides**: Images alternate between left and right sides
- **Responsive Design**: Adapts to different screen sizes
- **Lazy Loading**: Images load as needed for better performance

### Database Integration
- **Automatic Schema**: Database and table created automatically
- **Caption Management**: Store, retrieve, update, and delete captions
- **Timestamp Indexing**: Optimized queries with proper indexing

### API Features
- **RESTful Design**: Standard HTTP methods and status codes
- **JSON Responses**: Consistent JSON API responses
- **Error Handling**: Proper error messages and status codes
- **CORS Support**: Cross-origin requests supported

## Troubleshooting

### Common Issues

1. **Images not displaying**:
   - Check file permissions on `images/driveway/` folder
   - Verify image filenames match the expected format
   - Ensure images are valid image files

2. **Database connection errors**:
   - Verify database credentials in `config/database.php`
   - Check if MySQL server is running
   - Ensure database exists and user has proper permissions

3. **API not working**:
   - Check web server configuration
   - Verify `.htaccess` settings for URL rewriting
   - Check PHP error logs

### Docker Issues

1. **Container won't start**:
   ```bash
   docker-compose logs timeline-app
   ```

2. **Database connection issues**:
   ```bash
   docker-compose logs mysql
   ```

3. **Reset everything**:
   ```bash
   docker-compose down -v
   docker-compose up -d
   ```

## Performance Optimization

- Images are lazy-loaded for faster initial page load
- Database queries use proper indexing
- CSS animations are GPU-accelerated
- Responsive images adapt to viewport size

## Security Considerations

- SQL injection protection with prepared statements
- Input validation for API endpoints
- File access restricted to images folder
- Environment variables for sensitive configuration

## License

This project is open source and available under the MIT License.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Support

For issues and questions:
1. Check the troubleshooting section
2. Review server logs
3. Verify configuration settings
4. Test API endpoints independently