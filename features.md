# Web Application Features Documentation

## Core Features

### Authentication System
- User login/authentication (login.php)
- User logout functionality (logout.php)
- Session management
- Secure authentication implementation with password hashing and CSRF protection

### Database Integration
- Database configuration (config/database.php)
- Database migration tools (migrate_database.php, fix_database.php)
- SQL setup and schema management (setup.sql)

### Content Management
- Folder organization and management (FolderManager.php)
- Timeline creation and management (TimelineManager.php)
- Media handling with image storage (images/ directory structure) including upload functionality

### API Endpoints
- RESTful API implementation (/api/)ollama ps
  - GET /api/timeline: Retrieves timeline data for a given user
  - POST /api/captions: Adds new captions to the database
  - PUT /api/search: Handles search queries across timelines and folders
- Search functionality (search.php)
- Timeline data retrieval (timeline.php) with pagination support
- Captions management (captions.php) including CRUD operations

## Technical Details

### Database Schema
- Tables for users, folders, timelines, and media
- Relationships between entities:
  - Users have multiple folders
  - Folders contain multiple timelines
  - Timelines consist of events and associated media
- Constraints and normalization rules ensuring data integrity

### Security Measures
- Input validation using PHP's filter_var() function
- SQL injection prevention via prepared statements and parameterized queries
- Session security practices including session expiration and secure cookies
- CSRF protection implementation in forms

### Performance Optimizations
- Query optimization with indexing on frequently searched fields (e.g., timeline IDs)
- Caching mechanisms:
  - APCu for PHP code and function results
  - Redis for caching API responses and search results
- Efficient data retrieval patterns such as lazy loading of media attachments

## Known Limitations
1. Limited error handling in some API endpoints, particularly around input validation
2. No rate limiting implemented to prevent abuse
3. Basic testing coverage with unit tests only covering core functionality (test_api.php, test_dates.php)
4. Inadequate logging for security events and performance metrics

## Future Improvements
1. Implement proper request validation using JSON Schema for API endpoints
2. Add comprehensive documentation including:
   - API documentation with Swagger
   - Usage examples in multiple formats (curl, Postman)
3. Improve error handling and logging by integrating Monolog or PSR-3 compliant logger
4. Add unit tests for all core functionality using PHPUnit
5. Implement rate limiting using Redis-based solution
6. Add caching layer for static resources to reduce server load

## Example Use Cases

### Timeline API Usage
```php
// Retrieve timeline data for user with ID 1
$timeline = new TimelineManager();
$data = $timeline->getTimeline(1);
// Returns array of timelines with dates, events, and media attachments
```

### Media Management
```php
// Upload a new image to the 'front' folder
$imageUploader = new ImageUploader('images/front/');
$imageUploader->upload($_FILES['image']);
```

### Search Functionality
```php
// Search for 'meeting' across all timelines
$search = new SearchManager();
$results = $search->query('meeting');
// Returns array of matching events with timeline context
```

## Deployment and Configuration

### Environment Setup
1. Clone the repository to your server
2. Install dependencies using Composer:
   ```bash
   composer install
   ```
3. Configure database settings in `config/database.php`
4. Run database migrations:
   ```bash
   php migrate_database.php
   ```

### API Configuration
- Enable CORS for API endpoints by modifying `cors` configuration in your web server
- Set appropriate cache expiration headers in `api/timeline.php`

## Contributing and Development

### Contribution Guidelines
1. Fork the repository
2. Create a feature branch
3. Commit changes with clear commit messages
4. Push to the branch and create a Pull Request

### Code Style
- Follow PSR-12 coding standards
- Use consistent indentation (4 spaces)
- Include docblocks for all public methods

## License
[Insert your license information here]
