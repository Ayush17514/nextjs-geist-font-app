# College Admin Student Monitoring System

A PHP-based web application that enables college administrators to monitor students' personal learning activities across GitHub, LeetCode, and LinkedIn without requiring manual student input.

## Features

- **Admin Authentication**: Secure login system for college administrators
- **Student Management**: View and manage student profiles with their platform usernames
- **Automated Data Collection**: Fetch activity data from GitHub, LeetCode, and LinkedIn
- **Activity Monitoring**: Track student progress and engagement across platforms
- **Modern UI**: Clean, responsive interface built with Tailwind CSS
- **Real-time Statistics**: Dashboard with student count and recent activity metrics

## Technology Stack

- **Backend**: PHP (no frameworks)
- **Database**: MySQL
- **Frontend**: HTML, Tailwind CSS, JavaScript
- **Fonts**: Google Fonts (Inter)

## Prerequisites

- XAMPP or similar local development environment
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

## Installation & Setup

### 1. Database Setup

1. Start XAMPP and ensure MySQL is running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create a new database named `student_monitor`
4. Import the database schema:
   - Go to the `student_monitor` database
   - Click on "Import" tab
   - Select the `schema.sql` file from this project
   - Click "Go" to execute

### 2. Configuration

1. Copy the `php_app` folder to your XAMPP `htdocs` directory
2. Open `config.php` and update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'student_monitor');
   ```

### 3. API Configuration (Optional)

To improve data collection, you can add API tokens:

1. **GitHub API Token**:
   - Go to GitHub Settings > Developer settings > Personal access tokens
   - Generate a new token with `public_repo` scope
   - Add it to `config.php`:
     ```php
     define('GITHUB_API_TOKEN', 'your_github_token_here');
     ```

2. **LinkedIn API** (Advanced):
   - LinkedIn API requires approval and is more complex to implement
   - Current implementation uses basic web scraping

### 4. Access the Application

1. Open your web browser
2. Navigate to `http://localhost/php_app`
3. You'll be redirected to the login page

## Default Login Credentials

- **Username**: `admin`
- **Password**: `admin123`

## Usage

### 1. Admin Login
- Use the default credentials to log in
- You'll be redirected to the dashboard

### 2. View Students
- The dashboard shows all registered students
- Click "View Details" to see individual student activity

### 3. Fetch Data
- Click "Fetch Data" in the navigation to manually collect student activity data
- This process will gather information from GitHub, LeetCode, and LinkedIn
- You can also set up automated data collection using cron jobs

### 4. Monitor Activities
- View detailed student profiles with their latest activities
- Track GitHub repositories, commits, and profile information
- Monitor LeetCode problem-solving progress
- Check LinkedIn profile updates

## Sample Data

The system comes with sample student data:
- john.doe@college.edu (GitHub: johndoe)
- jane.smith@college.edu (GitHub: janesmith)
- mike.wilson@college.edu (GitHub: mikewilson)

## File Structure

```
php_app/
├── assets/css/          # CSS assets (if needed)
├── views/               # View templates
│   ├── header.php       # Common header
│   └── footer.php       # Common footer
├── config.php           # Configuration file
├── functions.php        # Helper functions
├── index.php           # Entry point
├── login.php           # Admin login
├── logout.php          # Logout script
├── dashboard.php       # Main dashboard
├── student_details.php # Student detail view
├── fetch_data.php      # Data collection script
├── schema.sql          # Database schema
└── README.md           # This file
```

## Data Collection

### GitHub
- Uses GitHub API to fetch user profile and repository information
- Collects: public repositories, followers, following, recent activity
- Rate limited: 60 requests/hour without token, 5000 with token

### LeetCode
- Uses web scraping (no official API available)
- Collects: profile information, problems solved
- May require adjustments if LeetCode changes their HTML structure

### LinkedIn
- Basic web scraping implementation
- Limited data due to LinkedIn's anti-scraping measures
- For production use, consider LinkedIn's official API

## Automation

To automate data collection, set up a cron job:

```bash
# Run every hour
0 * * * * /usr/bin/php /path/to/php_app/fetch_data.php

# Run daily at 2 AM
0 2 * * * /usr/bin/php /path/to/php_app/fetch_data.php
```

## Security Considerations

1. **Password Security**: Change default admin password
2. **Database Security**: Use strong database credentials
3. **API Keys**: Keep API tokens secure and never commit them to version control
4. **Input Validation**: All user inputs are sanitized
5. **SQL Injection**: Uses prepared statements throughout

## Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Ensure MySQL is running in XAMPP
   - Check database credentials in `config.php`
   - Verify database `student_monitor` exists

2. **GitHub API Rate Limiting**:
   - Add a GitHub API token to increase rate limits
   - Consider implementing caching for frequently accessed data

3. **LeetCode/LinkedIn Scraping Issues**:
   - These platforms may block scraping attempts
   - Consider using proxies or implementing delays between requests
   - Update scraping logic if HTML structure changes

4. **Permission Issues**:
   - Ensure PHP has write permissions for session files
   - Check file permissions in the web directory

## Future Enhancements

- Add more platforms (CodeChef, HackerRank, etc.)
- Implement data visualization with charts
- Add email notifications for activity updates
- Create student self-registration system
- Add bulk student import functionality
- Implement API rate limiting and caching

## Support

For issues and questions:
1. Check the troubleshooting section
2. Review PHP error logs
3. Ensure all prerequisites are met
4. Verify database schema is properly imported

## License

This project is for educational purposes. Please respect the terms of service of GitHub, LeetCode, and LinkedIn when collecting data.
