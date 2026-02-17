# 🎓 Online Quiz Management System

A comprehensive Laravel-based online quiz platform with advanced features for educational institutions, training centers, and organizations.

## 🆕 Latest Updates

- ✅ **PHP 8.3 Compatible** - Fully tested and optimized for PHP 8.3 and 8.4
- ✅ **Laravel 12.41.1** - Latest Laravel framework with all security updates
- ✅ **Modern Dependencies** - All packages updated to latest compatible versions
- ✅ **Optimized Setup** - Streamlined installation and configuration process

## 📋 Table of Contents

- [Features](#-features)
- [Quick Start](#-quick-start)
- [Local Installation](#-local-installation)
- [Network Setup](#-network-setup)
- [Server Deployment](#-server-deployment)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [Troubleshooting](#-troubleshooting)
- [API Documentation](#-api-documentation)
- [Contributing](#-contributing)
- [License](#-license)

## ✨ Features

### 🔐 Authentication & User Management
- **Role-based Access Control** (Admin/User)
- **User Registration** with admin approval system
- **Secure Login/Logout** with remember me functionality
- **Password Reset** via email
- **User Profile Management** with profile pictures and cover photos
- **University/Institution Management** with dropdown selection
- **User Approval System** - Admins can approve/disapprove new registrations

### 📊 Admin Dashboard & Management
- **Comprehensive Admin Dashboard** with real-time statistics
- **User Management** - Create, edit, delete, activate/deactivate users
- **Quiz Management** - Full CRUD operations for quizzes
- **Question Management** - Multiple choice questions with import/export
- **Quiz Assignment** - Assign quizzes to specific users or groups
- **Analytics & Reports** - Student performance tracking and rankings
- **University Management** - Manage educational institutions
- **System Settings** - Customizable system name, logo, and maintenance mode
- **Contact Message Management** - Handle inquiries from landing page
- **Feedback Management** - Review and respond to user feedback
- **Forum Management** - Create and manage discussion threads
- **Notification System** - Send notifications to users

### 🎯 Quiz System
- **Dynamic Quiz Creation** with multiple question types
- **Question Import/Export** via Excel/CSV files
- **Randomized Questions** and answer choices
- **Timer-based Quizzes** with persistent timer across page refreshes
- **Quiz Assignment** to specific users or groups
- **Automatic Quiz Submission** when time expires
- **Quiz Results & Scoring** with detailed feedback
- **Quiz Status Tracking** (assigned, in-progress, completed, cancelled)
- **Topic-based Analytics** - Track performance by subject areas

### 👥 User Features
- **User Dashboard** with available quizzes and statistics
- **Quiz Taking Interface** with one-question-at-a-time navigation
- **Quiz Code Entry** via modal for easy access
- **Profile Management** with bio, profile picture, and cover photo
- **Friends System** - Add friends and manage friend requests
- **Private Messaging** between users
- **Live Chat Support** with admin assistance
- **Feedback System** with image uploads (up to 5 images)
- **Forum Participation** - Like, comment, and share threads
- **Notification System** - Real-time notifications for various activities

### 💬 Communication Features
- **Live Chat Support** - Real-time chat between users and admins
- **Chat Ticket System** - Organized support with ticket management
- **User-to-User Messaging** - Private conversations between friends
- **Forum System** - Discussion threads with likes, comments, and shares
- **Comment System** - Nested comments with mentions and image uploads
- **Notification Bell** - Real-time notifications for all activities
- **Pre-loaded Messages** - Quick response templates for admins
- **Typing Indicators** - Real-time typing status in chats

### 🎨 UI/UX Features
- **Responsive Design** - Works on desktop, tablet, and mobile
- **Modern Interface** - Clean and professional design
- **Dark/Light Theme Support** (configurable)
- **Customizable Branding** - System name, logo, and icon
- **Toast Notifications** - User-friendly success/error messages
- **Modal Dialogs** - Modern popup interfaces
- **Loading States** - Visual feedback for all operations
- **Image Upload** - Profile pictures, cover photos, and feedback images
- **Drag & Drop** - Easy file uploads with preview

### 📈 Analytics & Reporting
- **Student Performance Tracking** - Individual and group analytics
- **Quiz Statistics** - Completion rates, average scores, etc.
- **University Rankings** - Performance by institution
- **Topic-based Analytics** - Subject-wise performance tracking
- **Real-time User Activity** - Online/offline status tracking
- **Export Capabilities** - Data export in various formats
- **Dashboard Widgets** - Key metrics at a glance

### 🔧 System Features
- **Maintenance Mode** - System-wide maintenance with admin bypass
- **Caching System** - Optimized performance with Redis/Memcached support
- **File Storage** - Secure file uploads with validation
- **Database Optimization** - Efficient queries and indexing
- **Security Features** - CSRF protection, input validation, SQL injection prevention
- **Error Handling** - Comprehensive error logging and user-friendly messages
- **Backup System** - Database and file backup capabilities
- **Multi-language Support** - Internationalization ready

### 🌐 Network & Deployment Features
- **Local Network Access** - Run on local network for multiple users simultaneously
- **Cross-Device Compatibility** - Works on desktop, tablet, and mobile devices
- **Real-time Synchronization** - Live updates across all connected devices
- **Multi-User Support** - Handle hundreds of concurrent users
- **Production Ready** - Optimized for server deployment
- **Load Balancing Support** - Scale across multiple servers
- **SSL/HTTPS Support** - Secure connections for production use

### 🎪 Special Features
- **Seasonal Effects** - Halloween and Christmas themes (admin configurable)
- **Carousel System** - Auto-scrolling quiz displays on landing page
- **Counter Animations** - Animated statistics on landing page
- **Real-time Updates** - Live data updates without page refresh
- **Memory Optimization** - Handles large datasets efficiently
- **Performance Monitoring** - Built-in performance tracking

## 🚀 Quick Start

Get up and running in 5 minutes:

```bash
# 1. Clone and setup
git clone https://github.com/yourusername/online-quiz-system.git
cd online-quiz-system

# 2. Install dependencies
composer install && npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Setup database (SQLite for quick start)
touch database/database.sqlite
php artisan migrate --seed

# 5. Complete system setup
php artisan optimize:clear
npm run build

# 6. Start the server
php artisan serve --host=0.0.0.0 --port=8000
```

**Access your system:**
- Local: http://localhost:8000
- Network: http://YOUR_IP:8000
- Admin: admin@quiz.com / password

**Note:** This system requires PHP 8.2+ (8.3 recommended) and is fully compatible with PHP 8.3 and 8.4.

## 📋 Complete Installation Commands

### 🔧 Run Everything Needed for the System

To set up and configure everything needed for the system, run these commands in order:

```bash
# 1. Install/Update PHP dependencies
composer install
composer update

# 2. Install Node.js dependencies
npm install

# 3. Clear all caches and optimize
php artisan optimize:clear

# 4. Run database migrations
php artisan migrate --force

# 5. Build frontend assets
npm run build

# 6. Verify system status
php artisan about
```

### One-Line Installation Script

```bash
# Complete setup in one command (for Linux/macOS)
git clone https://github.com/yourusername/online-quiz-system.git && cd online-quiz-system && composer install && npm install && cp .env.example .env && php artisan key:generate && touch database/database.sqlite && php artisan migrate --seed && npm run build && php artisan optimize:clear && echo "✅ Installation complete! Run: php artisan serve --host=0.0.0.0 --port=8000"
```

### Step-by-Step Commands

#### 1. **System Requirements Check**
```bash
# Check PHP version (requires 8.2+)
php -v

# Check Composer
composer --version

# Check Node.js (requires 16+)
node -v && npm -v

# Check MySQL (if using MySQL)
mysql --version
```

#### 2. **Project Setup**
```bash
# Clone repository
git clone https://github.com/yourusername/online-quiz-system.git
cd online-quiz-system

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

#### 3. **Environment Configuration**
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file (optional - for custom settings)
nano .env
```

#### 4. **Database Setup**

**Option A: SQLite (Quick Setup)**
```bash
# Create SQLite database
touch database/database.sqlite

# Run migrations and seeders
php artisan migrate --seed
```

**Option B: MySQL (Production Setup)**
```bash
# Create MySQL database
mysql -u root -p
CREATE DATABASE online_quiz_system;
CREATE USER 'quiz_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON online_quiz_system.* TO 'quiz_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Update .env file with MySQL credentials
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=online_quiz_system
# DB_USERNAME=quiz_user
# DB_PASSWORD=secure_password

# Run migrations and seeders
php artisan migrate --seed
```

#### 5. **Laravel Setup**
```bash
# Create storage symbolic link
php artisan storage:link

# Set proper permissions (Linux/macOS)
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache

# Clear all caches and optimize
php artisan optimize:clear
```

#### 6. **Frontend Build**
```bash
# Build for production
npm run build

# Or for development with hot reload
npm run dev
```

#### 7. **Start the Application**

**Local Development:**
```bash
# Start local server (localhost only)
php artisan serve
```

**Network Access:**
```bash
# Start with network access
php artisan serve --host=0.0.0.0 --port=8000

# Or use the provided script
chmod +x start-server.sh
./start-server.sh
```

**Production Optimized:**
```bash
# Start with production optimizations
php -d memory_limit=512M artisan serve --host=0.0.0.0 --port=8000
```

### 🔧 Essential Commands Reference

#### **Daily Development Commands**
```bash
# Start development server
php artisan serve --host=0.0.0.0 --port=8000

# Clear all caches (optimized command)
php artisan optimize:clear

# Rebuild frontend assets
npm run build

# Check application status
php artisan about
```

#### **Complete Setup Command (Run Everything)**
```bash
# Install/update all dependencies and setup system
composer install          # Install PHP dependencies
composer update           # Update to latest compatible versions
npm install              # Install Node.js dependencies
php artisan optimize:clear  # Clear all caches
php artisan migrate --force  # Run database migrations
npm run build            # Build production assets
php artisan about        # Verify system status
```

#### **Database Commands**
```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Reset database
php artisan migrate:reset

# Seed database
php artisan db:seed

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

#### **Maintenance Commands**
```bash
# Clear all caches
php artisan optimize:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate optimized autoloader
composer install --optimize-autoloader --no-dev
```

#### **Network Commands**
```bash
# Find your IP address
ifconfig | grep "inet " | grep -v 127.0.0.1

# Start with specific port
php artisan serve --host=0.0.0.0 --port=8080

# Start with increased memory
php -d memory_limit=1024M artisan serve --host=0.0.0.0 --port=8000
```

### 🚨 Troubleshooting Commands

#### **Permission Issues**
```bash
# Fix file permissions
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Fix Composer permissions
sudo chown -R $USER ~/.composer
```

#### **Cache Issues**
```bash
# Clear all Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Clear Composer cache
composer clear-cache

# Clear NPM cache
npm cache clean --force
```

#### **Database Issues**
```bash
# Reset database completely
php artisan migrate:fresh --seed

# Check database connection
php artisan tinker
# Then run: DB::connection()->getPdo();
```

### 📱 Network Access Commands

#### **Find Network IP**
```bash
# macOS/Linux
ifconfig | grep "inet " | grep -v 127.0.0.1

# Windows
ipconfig | findstr "IPv4"
```

#### **Start Network Server**
```bash
# Basic network access
php artisan serve --host=0.0.0.0 --port=8000

# With memory optimization
php -d memory_limit=512M artisan serve --host=0.0.0.0 --port=8000

# Using startup script
./start-server.sh
```

#### **Test Network Connectivity**
```bash
# Test if port is open (from another device)
telnet YOUR_IP 8000

# Check if server is running
ps aux | grep "php artisan serve"

# Kill existing server
pkill -f "php artisan serve"
```

## 🏠 Local Installation

### Prerequisites

**Required Software:**
- **PHP 8.2+** (8.3 recommended, fully tested with PHP 8.4) with extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML, GD
- **Composer** (PHP dependency manager) - Version 2.x recommended
- **Node.js 16+** and NPM (Node.js 18+ recommended)
- **MySQL 5.7+** or MariaDB 10.3+ (or SQLite for development)
- **Git** (for cloning the repository)

**PHP 8.3 Compatibility:**
- ✅ Fully compatible with PHP 8.3 and 8.4
- ✅ All dependencies updated for PHP 8.3 support
- ✅ No deprecated features used
- ✅ Optimized for PHP 8.3 performance improvements

**System Requirements:**

**Minimum Requirements:**
- **RAM**: 2GB (4GB recommended for production)
- **Storage**: 1GB free space (5GB+ recommended for production with file uploads)
- **CPU**: 1 core (2+ cores recommended)
- **OS**: Windows 10+, macOS 10.15+, or Linux (Ubuntu 18.04+)
- **Web Server**: Apache 2.4+ or Nginx 1.18+ (for production)
- **PHP Extensions**: All required extensions must be enabled

**Recommended for Production:**
- **RAM**: 4GB or more
- **Storage**: 10GB+ (for database, uploads, and logs)
- **CPU**: 2+ cores
- **Database**: MySQL 8.0+ or MariaDB 10.5+ (SQLite for development only)
- **SSL Certificate**: Required for HTTPS
- **Backup System**: Automated daily backups recommended

**PHP Extensions Required:**
- `bcmath` - For mathematical operations
- `ctype` - Character type checking
- `curl` - HTTP client functionality
- `dom` - XML/HTML parsing
- `fileinfo` - File type detection
- `json` - JSON encoding/decoding
- `mbstring` - Multibyte string handling
- `openssl` - Encryption and SSL support
- `pcre` - Regular expressions
- `pdo` - Database abstraction layer
- `pdo_mysql` or `pdo_sqlite` - Database drivers
- `tokenizer` - Code parsing
- `xml` - XML processing
- `gd` or `imagick` - Image processing (for profile pictures, uploads)

**Verify PHP Extensions:**
```bash
php -m | grep -E "(bcmath|ctype|curl|dom|fileinfo|json|mbstring|openssl|pcre|pdo|tokenizer|xml|gd)"
```

### Step 1: Environment Setup

#### Windows (Using XAMPP)
1. Download and install [XAMPP](https://www.apachefriends.org/download.html)
2. Install [Composer](https://getcomposer.org/download/)
3. Install [Node.js](https://nodejs.org/)
4. Start Apache and MySQL from XAMPP Control Panel

#### macOS (Using Homebrew)
```bash
# Install Homebrew if not already installed
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install required software
brew install php@8.3 composer node mysql
brew services start mysql
```

#### Linux (Ubuntu/Debian)
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install php8.3 php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-gd php8.3-zip php8.3-bcmath

# Install other requirements
sudo apt install composer nodejs npm mysql-server git
```

### Step 2: Clone and Setup Project

```bash
# Clone the repository
git clone https://github.com/yourusername/online-quiz-system.git
cd online-quiz-system

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Step 3: Database Configuration

#### Option A: MySQL/MariaDB (Recommended for Production)
```bash
# Create database
mysql -u root -p
CREATE DATABASE online_quiz_system;
CREATE USER 'quiz_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON online_quiz_system.* TO 'quiz_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Option B: SQLite (Quick Development Setup)
```bash
# Create SQLite database file
touch database/database.sqlite
```

### Step 4: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

**Update `.env` file with your database settings:**

For MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=online_quiz_system
DB_USERNAME=quiz_user
DB_PASSWORD=secure_password
```

For SQLite:
```env
DB_CONNECTION=sqlite
# DB_DATABASE=/full/path/to/database/database.sqlite
```

### Step 5: Database Migration and Seeding

```bash
# Run database migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed

# Create storage symbolic link
php artisan storage:link
```

### Step 6: Complete System Setup

Run all necessary commands to set up the system:

```bash
# Clear all caches
php artisan optimize:clear

# Run migrations (if not already done)
php artisan migrate --force

# Build frontend assets
npm run build

# Verify system status
php artisan about
```

**Note:** The `optimize:clear` command clears all caches (config, cache, routes, views, events, and compiled files) in one command, which is more efficient than clearing them individually.

### Step 7: Set Permissions (Linux/macOS)

```bash
# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

### Step 8: Start the Application

#### Local Development Server
```bash
# Start Laravel development server (localhost only)
php artisan serve
```

#### Network Access Server
```bash
# Start server with network access
php artisan serve --host=0.0.0.0 --port=8000
```

#### Using the Provided Script
```bash
# Make script executable and run
chmod +x start-server.sh
./start-server.sh
```

### Step 9: Verify Installation

1. **Open your browser** and navigate to:
   - Local: http://localhost:8000
   - Network: http://YOUR_IP:8000

2. **Login with default admin account:**
   - Email: `admin@quiz.com`
   - Password: `password`

3. **Change the default password** immediately for security

## 🌐 Network Setup

### Enable Network Access

The system is designed to run on your local network, allowing multiple devices to access it simultaneously.

#### Step 1: Find Your IP Address

**Windows:**
```cmd
ipconfig
```

**macOS/Linux:**
```bash
ifconfig | grep "inet " | grep -v 127.0.0.1
```

#### Step 2: Start Network Server

```bash
# Start with network access
php artisan serve --host=0.0.0.0 --port=8000

# Or use the provided script
./start-server.sh
```

#### Step 3: Access from Other Devices

**From any device on the same network:**
1. Connect to the same WiFi network
2. Open a web browser
3. Navigate to: `http://YOUR_IP:8000`
4. Example: `http://192.168.1.100:8000`

### Network Configuration

#### Firewall Settings

**Windows:**
1. Open Windows Defender Firewall
2. Click "Allow an app or feature through Windows Defender Firewall"
3. Add PHP or allow port 8000

**macOS:**
```bash
# Allow incoming connections on port 8000
sudo pfctl -f /etc/pf.conf
```

**Linux:**
```bash
# Allow port 8000 through firewall
sudo ufw allow 8000
```

#### Router Configuration

If devices can't connect:
1. Check router settings for device isolation
2. Ensure all devices are on the same subnet
3. Try using a different port if 8000 is blocked

### Performance Optimization for Network Use

```bash
# Increase PHP memory limit for better performance
php -d memory_limit=512M artisan serve --host=0.0.0.0 --port=8000

# Use production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🚀 Server Deployment

### Production Server Setup

Deploy your quiz system to a production server for public access.

#### Option 1: Shared Hosting (cPanel/WHM)

**Requirements:**
- PHP 8.2+ (8.3 recommended) with required extensions
- MySQL 5.7+ or MariaDB 10.3+
- SSL certificate (Let's Encrypt recommended)
- 2GB+ RAM, 10GB+ storage

**Deployment Steps:**

1. **Upload Files:**
   ```bash
   # Compress your project
   tar -czf quiz-system.tar.gz --exclude=node_modules --exclude=.git .
   
   # Upload via FTP/SFTP to public_html or subdomain folder
   ```

2. **Database Setup:**
   ```sql
   -- Create database in cPanel MySQL
   CREATE DATABASE your_quiz_db;
   CREATE USER 'quiz_user'@'localhost' IDENTIFIED BY 'secure_password';
   GRANT ALL PRIVILEGES ON your_quiz_db.* TO 'quiz_user'@'localhost';
   ```

3. **Environment Configuration:**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_quiz_db
   DB_USERNAME=quiz_user
   DB_PASSWORD=secure_password
   ```

4. **Install Dependencies:**
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install && npm run build
   ```

5. **Laravel Setup:**
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

#### Option 2: VPS/Dedicated Server (Ubuntu 20.04+)

**Server Requirements:**
- Ubuntu 20.04 LTS or newer
- 2GB+ RAM, 20GB+ SSD
- Root access or sudo privileges

**Installation Steps:**

1. **Update System:**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Install LAMP Stack:**
   ```bash
   # Install Apache, MySQL, PHP
   sudo apt install apache2 mysql-server php8.3 php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-gd php8.3-zip php8.3-bcmath php8.3-intl php8.3-xmlrpc php8.3-soap
   
   # Install Composer
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   
   # Install Node.js
   curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
   sudo apt install nodejs
   ```

3. **Configure MySQL:**
   ```bash
   sudo mysql_secure_installation
   
   # Create database
   sudo mysql -u root -p
   CREATE DATABASE online_quiz_system;
   CREATE USER 'quiz_user'@'localhost' IDENTIFIED BY 'secure_password';
   GRANT ALL PRIVILEGES ON online_quiz_system.* TO 'quiz_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

4. **Deploy Application:**
   ```bash
   # Clone repository
   cd /var/www
   sudo git clone https://github.com/yourusername/online-quiz-system.git
   sudo chown -R www-data:www-data online-quiz-system
   cd online-quiz-system
   
   # Install dependencies
   composer install --optimize-autoloader --no-dev
   npm install && npm run build
   
   # Configure environment
   sudo cp .env.example .env
   sudo nano .env
   ```

5. **Configure Apache:**
   ```bash
   # Create virtual host
   sudo nano /etc/apache2/sites-available/quiz-system.conf
   ```

   **Virtual Host Configuration:**
   ```apache
   <VirtualHost *:80>
       ServerName yourdomain.com
       DocumentRoot /var/www/online-quiz-system/public
       
       <Directory /var/www/online-quiz-system/public>
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog ${APACHE_LOG_DIR}/quiz_error.log
       CustomLog ${APACHE_LOG_DIR}/quiz_access.log combined
   </VirtualHost>
   ```

   ```bash
   # Enable site and modules
   sudo a2ensite quiz-system.conf
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

6. **SSL Certificate (Let's Encrypt):**
   ```bash
   sudo apt install certbot python3-certbot-apache
   sudo certbot --apache -d yourdomain.com
   ```

7. **Laravel Setup:**
   ```bash
   # Set permissions
   sudo chown -R www-data:www-data /var/www/online-quiz-system
   sudo chmod -R 775 /var/www/online-quiz-system/storage
   sudo chmod -R 775 /var/www/online-quiz-system/bootstrap/cache
   
   # Laravel commands
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

#### Option 3: Docker Deployment

**Docker Setup:**

1. **Create Dockerfile:**
   ```dockerfile
   FROM php:8.3-apache
   
   # Install system dependencies
   RUN apt-get update && apt-get install -y \
       git \
       curl \
       libpng-dev \
       libonig-dev \
       libxml2-dev \
       zip \
       unzip \
       nodejs \
       npm
   
   # Install PHP extensions
   RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
   
   # Install Composer
   COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
   
   # Set working directory
   WORKDIR /var/www/html
   
   # Copy application files
   COPY . .
   
   # Install dependencies
   RUN composer install --optimize-autoloader --no-dev
   RUN npm install && npm run build
   
   # Set permissions
   RUN chown -R www-data:www-data /var/www/html
   RUN chmod -R 775 storage bootstrap/cache
   
   # Configure Apache
   RUN a2enmod rewrite
   COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf
   
   EXPOSE 80
   ```

2. **Create docker-compose.yml:**
   ```yaml
   version: '3.8'
   
   services:
     app:
       build: .
       ports:
         - "80:80"
       environment:
         - APP_ENV=production
         - APP_DEBUG=false
         - DB_HOST=db
         - DB_DATABASE=online_quiz_system
         - DB_USERNAME=quiz_user
         - DB_PASSWORD=secure_password
       depends_on:
         - db
       volumes:
         - ./storage:/var/www/html/storage
   
     db:
       image: mysql:8.0
       environment:
         - MYSQL_DATABASE=online_quiz_system
         - MYSQL_USER=quiz_user
         - MYSQL_PASSWORD=secure_password
         - MYSQL_ROOT_PASSWORD=root_password
       volumes:
         - mysql_data:/var/lib/mysql
   
   volumes:
     mysql_data:
   ```

3. **Deploy with Docker:**
   ```bash
   # Build and start containers
   docker-compose up -d --build
   
   # Run Laravel setup
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate --force
   docker-compose exec app php artisan db:seed --force
   docker-compose exec app php artisan storage:link
   ```

### Performance Optimization

#### Production Optimizations

```bash
# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize database
php artisan optimize
```

#### Server Configuration

**PHP Configuration (php.ini):**
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 10M
```

**Apache Configuration:**
```apache
# Enable compression
LoadModule deflate_module modules/mod_deflate.so
<Location />
    SetOutputFilter DEFLATE
    SetEnvIfNoCase Request_URI \
        \.(?:gif|jpe?g|png)$ no-gzip dont-vary
    SetEnvIfNoCase Request_URI \
        \.(?:exe|t?gz|zip|bz2|sit|rar)$ no-gzip dont-vary
</Location>

# Enable caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
</IfModule>
```

### Monitoring and Maintenance

#### Log Monitoring
```bash
# Monitor Laravel logs
tail -f storage/logs/laravel.log

# Monitor Apache logs
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log
```

#### Backup Strategy
```bash
# Database backup
mysqldump -u quiz_user -p online_quiz_system > backup_$(date +%Y%m%d).sql

# Files backup
tar -czf files_backup_$(date +%Y%m%d).tar.gz storage/
```

#### Security Checklist
- [ ] Change default admin password
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall (only allow ports 80, 443, 22)
- [ ] Regular security updates
- [ ] Database user with limited privileges
- [ ] File permissions properly set
- [ ] Environment variables secured

## ⚙️ Configuration

### Admin Account Setup

After running the seeders, you can login with the default admin account:

- **Email**: `admin@quiz.com`
- **Password**: `password`

**Important**: Change the default password immediately after first login.

### System Settings

Access the admin panel and configure:

1. **System Information**:
   - System name
   - System logo
   - System icon
   - Contact information

2. **Maintenance Mode**:
   - Enable/disable maintenance mode
   - Custom maintenance message

3. **Email Configuration**:
   - SMTP settings for notifications
   - Email templates

### File Permissions

Ensure proper file permissions:

```bash
# Set ownership
sudo chown -R www-data:www-data storage bootstrap/cache

# Set permissions
sudo chmod -R 775 storage bootstrap/cache
```

### Memory Configuration

For better performance, increase PHP memory limit:

```bash
# In your php.ini file
memory_limit = 512M
max_execution_time = 300
```

## 📱 Usage Guide

### 🔐 Getting Started

#### First-Time Setup

1. **Access the System**
   - Navigate to `http://localhost:8000` (or your server URL)
   - You'll see the landing page with system information

2. **Default Admin Login**
   - **Email**: `admin@quiz.com`
   - **Password**: `password`
   - ⚠️ **Important**: Change this password immediately after first login!

3. **Initial Configuration**
   - Go to **Admin Panel → System → Settings**
   - Configure system name, logo, and contact information
   - Set up email configuration for notifications
   - Configure default leave balances (for employee management)

---

### 👨‍💼 For Administrators

#### Dashboard Overview

The admin dashboard provides:
- **Real-time Statistics**: Total users, quizzes, active sessions
- **Quick Actions**: Create quiz, add user, view reports
- **Recent Activity**: Latest user registrations, quiz completions
- **System Health**: Server status, PHP version, database connection

#### User Management

**1. Approve New Users**
   - Navigate to **User Management → Pending Approvals**
   - Review user registration details
   - Click **Approve** or **Reject** with optional notes
   - Approved users receive email notification

**2. Create New User**
   - Go to **User Management → Create User**
   - Fill in: Name, Email, Password, Role (Admin/Employee/Student)
   - Select University/Institution (if applicable)
   - Set Active status and Approval status
   - Click **Create User**

**3. Manage Existing Users**
   - **User Management → Users List**
   - Click on any user to view/edit profile
   - **Edit**: Update information, change password, upload profile picture
   - **Deactivate**: Temporarily disable user access
   - **Delete**: Permanently remove user (use with caution)
   - **View Profile**: See user's quiz history, leave requests, DTR records

**4. User Roles**
   - **Admin**: Full system access
   - **Employee**: Access to DTR, leave requests, quizzes
   - **Student**: Access to quizzes, student-specific features

#### Quiz Management

**1. Create a New Quiz**
   - Go to **Content Management → Quizzes → Create Quiz**
   - Enter quiz details:
     - **Title**: Quiz name
     - **Description**: Brief description
     - **Time Limit**: Duration in minutes
     - **Passing Score**: Minimum score to pass (percentage)
   - Click **Create Quiz**

**2. Add Questions**
   - Open the quiz you created
   - Click **Add Question**
   - Enter question text
   - Add multiple choice options (minimum 2)
   - Mark the correct answer
   - Set point value (default: 1)
   - Click **Save Question**
   - Repeat for all questions

**3. Import Questions (Bulk)**
   - Go to **Content Management → Import Questions**
   - Download the CSV template
   - Fill in questions following the format:
     - Question, Option A, Option B, Option C, Option D, Correct Answer, Points
   - Upload the CSV file
   - Select or create a quiz
   - Click **Import Questions**

**4. Assign Quizzes**
   - Go to **Content Management → Quiz Assignments**
   - Click **Assign Quiz**
   - Select quiz from dropdown
   - Choose assignment type:
     - **All Users**: Assign to everyone
     - **Specific Users**: Select individual users
     - **By University**: Assign to users from specific institutions
   - Set start and end dates
   - Click **Assign**

**5. View Quiz Results**
   - Go to **Analytics & Reports → Quiz Results**
   - Filter by quiz, user, or date range
   - View individual attempts, scores, and time taken
   - Export results to PDF or Excel

#### Employee Management (DTR & Leave)

**1. Daily Time Records (DTR)**
   - **Employee Management → DTR (Time Records)**
   - **Create DTR**: Add time records for employees
   - **View Records**: See grouped records by month/week/employee
   - **Export**: Download reports as PDF
   - **Mark as Travel**: Checkbox to mark travel days (auto-fills 8 hours)

**2. Leave Requests**
   - **Employee Management → Leave Requests**
   - View all pending, approved, and rejected requests
   - **Approve/Reject**: Review and take action on requests
   - **Add Notes**: Provide feedback to employees
   - Employees receive email notifications on status changes

**3. Leave Calendar**
   - **Employee Management → Leave Calendar**
   - Visual calendar view of all approved leaves
   - Filter by employee or date range
   - See overlapping leaves and availability

#### Student Management

**1. Student DTR**
   - **Student Management → DTR (Time Records)**
   - Manage time records specifically for students
   - Filter by school/university and student
   - Export PDF reports with filtering options

**2. Student Leave Requests**
   - **Student Management → Student Leave Requests**
   - Students can request: Additional Time, Absent, Other
   - Approve/reject student leave requests
   - View student leave calendar

#### System Settings

**1. General Settings**
   - **System → Settings → General**
   - Update system name, logo, and icon
   - Configure maintenance mode
   - Set default vacation and sick leave balances

**2. Email Configuration**
   - **System → Settings → Email**
   - Configure SMTP settings:
     - Mail driver (SMTP/Log)
     - Host, Port, Username, Password
     - Encryption (TLS/SSL)
   - Test email configuration
   - View current email setup

**3. Contact Information**
   - **System → Settings → Contact**
   - Set contact email, phone, live chat details
   - Configure FAQ link
   - Set support hours for different channels

#### Analytics & Reports

**1. Student Performance**
   - **Analytics & Reports → Student Performance**
   - View individual student scores
   - Track performance by topic/subject
   - See rankings and statistics

**2. Quiz Analytics**
   - **Analytics & Reports → Quiz Analytics**
   - Completion rates
   - Average scores
   - Most difficult questions
   - Time analysis

**3. University Rankings**
   - **Analytics & Reports → University Rankings**
   - Compare performance across institutions
   - View top-performing universities
   - Export ranking reports

#### Communication Features

**1. Live Chat Support**
   - **Communication → Live Chat**
   - View active chat tickets
   - Respond to user inquiries
   - Use pre-loaded messages for quick responses
   - Close tickets when resolved

**2. Contact Messages**
   - **Communication → Contact Messages**
   - View inquiries from landing page
   - Respond to contact form submissions
   - Mark messages as read/unread

**3. Notifications**
   - **Communication → Notifications**
   - Send system-wide notifications
   - Target specific user groups
   - Schedule notifications

---

### 👤 For Regular Users (Employees/Students)

#### Registration & Login

**1. Register for Account**
   - Click **Register** on the landing page
   - Fill in registration form:
     - Name, Email, Password
     - University/Institution (if applicable)
   - Submit registration
   - Wait for admin approval (you'll receive email notification)

**2. Login**
   - Go to login page
   - Enter email and password
   - Check **Remember Me** to stay logged in
   - Click **Login**

**3. Forgot Password**
   - Click **Forgot Password** on login page
   - Enter your email address
   - Check email for password reset link
   - Click link and set new password

#### Dashboard

Your dashboard shows:
- **Available Quizzes**: Quizzes assigned to you
- **Recent Activity**: Your quiz attempts and results
- **Statistics**: Your overall performance
- **Notifications**: Latest updates and messages

#### Taking Quizzes

**1. Access a Quiz**
   - **Method 1**: Click on quiz from **Available Quizzes** section
   - **Method 2**: Enter quiz code in the **Enter Quiz Code** modal
   - **Method 3**: Click on assigned quiz notification

**2. Taking the Quiz**
   - Read instructions and time limit
   - Click **Start Quiz**
   - Answer questions one at a time
   - Use **Previous** and **Next** buttons to navigate
   - Timer shows remaining time (auto-submits when time expires)
   - Review answers before submitting
   - Click **Submit Quiz** when done

**3. View Results**
   - After submission, see immediate results
   - View correct/incorrect answers
   - See your score and passing status
   - Check detailed feedback for each question

#### Profile Management

**1. Edit Profile**
   - Click on your name → **Profile**
   - Update personal information:
     - Name, Email, Bio
     - University/Institution
   - Upload profile picture (recommended: square image, max 2MB)
   - Upload cover photo (recommended: 1200x300px)
   - Click **Save Changes**

**2. Change Password**
   - Go to **Profile → Security**
   - Enter current password
   - Enter new password (minimum 8 characters)
   - Confirm new password
   - Click **Update Password**

#### Daily Time Records (Employees)

**1. View DTR Records**
   - **Dashboard → My DTR Records**
   - View your time records grouped by month/week
   - See total hours, overtime, and deficits
   - Check weekly summaries

**2. Request Leave**
   - **Dashboard → Leave Requests → New Request**
   - Select leave type:
     - **Vacation Leave**: Paid time off
     - **Sick Leave**: Medical leave
     - **Work From Home**: Remote work
     - **Absent**: Unpaid absence
     - **Overtime**: Request overtime credit
     - **Offset**: Use overtime balance for time off
   - Enter start and end dates
   - Add reason/notes
   - For Offset: Specify hours to deduct (defaults to 8 hours per day)
   - Submit request
   - Wait for admin approval

**3. View Leave Balance**
   - **Dashboard → Profile**
   - See available vacation and sick leave balances
   - View overtime balance (can be negative if deficits exist)
   - Check leave request history

#### Student Features

**1. Student Leave Requests**
   - **Dashboard → Leave Requests → New Request**
   - Available types:
     - **Additional Time**: Request extra time for activities
     - **Absent**: Report absence
     - **Other**: Other leave types
   - Submit with dates and reason

**2. View Student DTR**
   - **Dashboard → My DTR Records**
   - View time records specific to students
   - See weekly summaries and totals

#### Communication Features

**1. Live Chat Support**
   - **Dashboard → Live Chat**
   - Click **New Ticket** to start chat
   - Describe your issue or question
   - Chat with admin support in real-time
   - Receive notifications when admin responds
   - Close ticket when issue is resolved

**2. Friends & Messaging**
   - **Dashboard → Friends**
   - **Add Friend**: Search and send friend requests
   - **Accept Requests**: Approve incoming friend requests
   - **Send Messages**: Private chat with friends
   - **View Messages**: Check conversation history

**3. Forum Participation**
   - **Dashboard → Forum**
   - **Browse Threads**: View all discussion topics
   - **Create Thread**: Start new discussion
   - **Like & Comment**: Engage with posts
   - **Share**: Share interesting threads
   - **Upload Images**: Add images to posts/comments (up to 5 images)

**4. Notifications**
   - Click bell icon in top navigation
   - View all notifications:
     - Quiz assignments
     - Friend requests
     - Messages
     - Leave request updates
     - Forum mentions
   - Mark as read/unread
   - Clear notifications

#### Feedback & Support

**1. Submit Feedback**
   - **Dashboard → Feedback**
   - Click **Submit Feedback**
   - Select feedback type
   - Describe your feedback
   - Upload images (up to 5 images, max 2MB each)
   - Submit for admin review

**2. View Feedback Status**
   - Check your submitted feedback
   - See admin responses
   - Track feedback resolution status

---

### 🎯 Quick Reference

#### Common Admin Tasks

| Task | Location | Steps |
|------|----------|-------|
| Create Quiz | Content Management → Quizzes | Create → Add Questions → Assign |
| Approve User | User Management → Pending | Review → Approve/Reject |
| View Reports | Analytics & Reports | Select report type → Filter → Export |
| Configure Email | System → Settings → Email | Enter SMTP details → Save |
| Manage DTR | Employee Management → DTR | Create/View/Export records |

#### Common User Tasks

| Task | Location | Steps |
|------|----------|-------|
| Take Quiz | Dashboard → Available Quizzes | Click quiz → Start → Answer → Submit |
| Request Leave | Dashboard → Leave Requests | New Request → Fill form → Submit |
| Update Profile | Profile → Edit | Update info → Upload photos → Save |
| Chat Support | Dashboard → Live Chat | New Ticket → Chat → Close |
| Add Friend | Dashboard → Friends | Search → Send Request → Accept |

---

### 💡 Tips & Best Practices

**For Administrators:**
- Regularly backup the database
- Monitor system logs for errors
- Keep dependencies updated
- Review and approve users promptly
- Set clear quiz time limits
- Use bulk import for large question sets
- Configure email properly for notifications
- Regularly check system health

**For Users:**
- Complete quizzes before deadline
- Keep profile information updated
- Request leaves in advance
- Use live chat for quick support
- Participate in forums for community engagement
- Check notifications regularly
- Review quiz results to improve performance

## 🔌 API Documentation

The system includes RESTful APIs for:

- User authentication and management
- Quiz operations
- Chat and messaging
- File uploads
- Analytics and reporting

API endpoints are available at `/api/` with proper authentication.

## 🔧 Troubleshooting

### Common Issues and Solutions

#### Installation Issues

**Problem: Composer install fails**
```bash
# Solution: Update Composer and clear cache
composer self-update
composer clear-cache
composer install --no-cache
```

**Problem: Permission denied errors**
```bash
# Solution: Fix file permissions
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Problem: Database connection failed**
```bash
# Check database credentials in .env file
# Ensure MySQL service is running
sudo systemctl start mysql
# Test connection
mysql -u username -p -h localhost
```

#### Network Access Issues

**Problem: Can't access from other devices**
1. **Check firewall settings:**
   ```bash
   # Linux
   sudo ufw allow 8000
   
   # macOS
   sudo pfctl -f /etc/pf.conf
   ```

2. **Verify IP address:**
   ```bash
   # Get your IP address
   ifconfig | grep "inet " | grep -v 127.0.0.1
   ```

3. **Test connectivity:**
   ```bash
   # From another device, test if port is open
   telnet YOUR_IP 8000
   ```

**Problem: Server stops responding**
```bash
# Check if process is running
ps aux | grep "php artisan serve"

# Restart server with more memory
php -d memory_limit=512M artisan serve --host=0.0.0.0 --port=8000
```

#### Performance Issues

**Problem: Slow loading times**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Problem: Memory errors**
```bash
# Increase PHP memory limit
php -d memory_limit=1024M artisan serve --host=0.0.0.0 --port=8000

# Or update php.ini
memory_limit = 1024M
```

#### Database Issues

**Problem: Migration fails**
```bash
# Reset and re-run migrations
php artisan migrate:reset
php artisan migrate --force
```

**Problem: Seeder fails**
```bash
# Run specific seeder
php artisan db:seed --class=AdminUserSeeder
```

### Debug Mode

**Enable debug mode for development:**
```env
APP_DEBUG=true
APP_ENV=local
```

**View detailed error logs:**
```bash
tail -f storage/logs/laravel.log
```

### Getting Help

1. **Check the logs:**
   ```bash
   # Laravel logs
   tail -f storage/logs/laravel.log
   
   # Server logs (if using Apache/Nginx)
   tail -f /var/log/apache2/error.log
   ```

2. **Verify requirements:**
   ```bash
   # Check PHP version
   php -v
   
   # Check extensions
   php -m | grep -E "(pdo|mbstring|curl|gd|zip)"
   
   # Check Composer
   composer --version
   
   # Check Node.js
   node -v && npm -v
   ```

3. **Test database connection:**
   ```bash
   php artisan tinker
   # Then run: DB::connection()->getPdo();
   ```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:

- Create an issue in the GitHub repository
- Contact the development team
- Check the documentation wiki

## 🎯 Roadmap

- [ ] Mobile app development
- [ ] Advanced analytics dashboard
- [ ] Integration with LMS platforms
- [ ] Multi-language support
- [ ] Advanced question types
- [ ] Video question support
- [ ] Offline quiz capability
- [ ] Real-time collaboration features
- [ ] Advanced reporting and analytics
- [ ] API rate limiting and security enhancements

## 📋 Quick Reference

### Essential Commands

```bash
# Start development server (local only)
php artisan serve

# Start with network access
php artisan serve --host=0.0.0.0 --port=8000

# Use the provided startup script
./start-server.sh

# Install dependencies
composer install && npm install

# Setup database
php artisan migrate --seed

# Build assets
npm run build

# Clear caches
php artisan cache:clear && php artisan config:clear
```

### Default Admin Access
- **URL**: http://localhost:8000 (local) or http://YOUR_IP:8000 (network)
- **Email**: admin@quiz.com
- **Password**: password

### Network Access
- **Find your IP**: `ifconfig | grep "inet " | grep -v 127.0.0.1`
- **Access from other devices**: `http://YOUR_IP:8000`
- **Firewall**: Allow port 8000 through your firewall

### Production Deployment
1. **Shared Hosting**: Upload files, configure database, run Laravel setup
2. **VPS/Dedicated**: Install LAMP stack, configure Apache, setup SSL
3. **Docker**: Use provided docker-compose.yml for containerized deployment

---

**Built with ❤️ using Laravel, Vue.js, and modern web technologies.**

**Ready for local development, network access, and production deployment!**
