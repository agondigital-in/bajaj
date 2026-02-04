# EMI Card Application System with AI

AI-powered PHP application for EMI card applications with screenshot verification, fraud detection, and admin panel.

## Features

### User Side
- Image gallery with AI-optimized loading
- Dynamic click ID generation
- Auto-redirect to Bajaj Finserv
- Screenshot upload with AI OCR verification
- Smart form auto-fill
- Real-time validation

### AI Features
- OCR screenshot verification
- Automatic data extraction
- Fraud detection
- Risk scoring
- Duplicate detection
- Image optimization

### Admin Panel
- Real-time dashboard
- Application management
- Screenshot preview
- Risk score analysis
- Status management
- Activity logs

## Installation

1. Import database:
```bash
mysql -u root -p < database.sql
```

2. Configure database in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'emi_card_system');
```

3. Create upload directories:
```bash
mkdir -p uploads/screenshots
chmod 777 uploads/screenshots
```

4. Install Tesseract OCR (optional):
```bash
# Ubuntu/Debian
sudo apt-get install tesseract-ocr

# Windows
# Download from: https://github.com/UB-Mannheim/tesseract/wiki
```

5. Configure AI settings in `config/ai_config.php`

## Usage

### User Flow
1. Visit `index.php`
2. Select card design
3. Click "Apply Now"
4. Upload approval screenshot
5. AI verifies screenshot
6. Submit name + UPI
7. Done!

### Admin Access
- URL: `admin/login.php`
- Username: `admin`
- Password: `admin123`

## Project Structure
```
├── api/
│   ├── apply.php
│   ├── validate_screenshot.php
│   ├── submit_application.php
│   └── track_selection.php
├── admin/
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   └── api/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
│   ├── database.php
│   └── ai_config.php
├── includes/
│   └── functions.php
├── uploads/
│   └── screenshots/
├── index.php
├── contact.php
└── database.sql
```

## AI Configuration

Edit `config/ai_config.php` to customize:
- OCR API settings
- Validation keywords
- Risk thresholds
- Image optimization

## Security Notes

1. Change default admin password
2. Use HTTPS in production
3. Enable CSRF protection
4. Sanitize all inputs
5. Use prepared statements
6. Implement rate limiting

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- Tesseract OCR (optional)
- GD Library

## License

MIT License
