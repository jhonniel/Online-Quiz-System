# 🎓 Online Quiz Management System

A comprehensive Laravel-based online quiz platform with advanced features for educational institutions, training centers, and organizations.

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

# 5. Build and run
npm run build
php artisan serve --host=0.0.0.0 --port=8000
```

**Access your system:**
- Local: http://localhost:8000
- Network: http://YOUR_IP:8000
- Admin: admin@quiz.com / password

## 📋 Complete Installation Commands

### One-Line Installation Script

```bash
# Complete setup in one command (for Linux/macOS)
git clone https://github.com/yourusername/online-quiz-system.git && cd online-quiz-system && composer install && npm install && cp .env.example .env && php artisan key:generate && touch database/database.sqlite && php artisan migrate --seed && npm run build && echo "✅ Installation complete! Run: php artisan serve --host=0.0.0.0 --port=8000"
```

### Step-by-Step Commands

#### 1. **System Requirements Check**
```bash
# Check PHP version (requires 8.1+)
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

# Clear and cache configurations
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
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

# Clear all caches
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear

# Rebuild frontend assets
npm run build

# Check application status
php artisan about
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
- **PHP 8.1+** with extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML, GD
- **Composer** (PHP dependency manager)
- **Node.js 16+** and NPM
- **MySQL 5.7+** or MariaDB 10.3+ (or SQLite for development)
- **Git** (for cloning the repository)

**System Requirements:**
- **RAM**: 2GB minimum, 4GB recommended
- **Storage**: 1GB free space
- **OS**: Windows 10+, macOS 10.15+, or Linux (Ubuntu 18.04+)

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
brew install php@8.1 composer node mysql
brew services start mysql
```

#### Linux (Ubuntu/Debian)
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install php8.1 php8.1-cli php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-gd php8.1-zip php8.1-bcmath

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

### Step 6: Build Frontend Assets

```bash
# For production
npm run build

# For development (with hot reload)
npm run dev
```

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
- PHP 8.1+ with required extensions
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
   sudo apt install apache2 mysql-server php8.1 php8.1-cli php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-gd php8.1-zip php8.1-bcmath php8.1-intl php8.1-xmlrpc php8.1-soap
   
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
   FROM php:8.1-apache
   
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

## 📱 Usage

### For Administrators

1. **Login** to the admin panel
2. **Manage Users** - Create, edit, and approve user accounts
3. **Create Quizzes** - Set up quizzes with questions and time limits
4. **Assign Quizzes** - Assign quizzes to specific users or groups
5. **Monitor Performance** - View analytics and reports
6. **Manage System** - Configure settings and handle support

### For Users

1. **Register** for an account (requires admin approval)
2. **Login** to access the dashboard
3. **Take Quizzes** - Enter quiz codes or access assigned quizzes
4. **View Results** - Check quiz scores and feedback
5. **Manage Profile** - Update personal information and photos
6. **Connect with Friends** - Add friends and send messages
7. **Participate in Forums** - Join discussions and share content

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
