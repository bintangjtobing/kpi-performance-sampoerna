# Report Daily Helper-PMI - Setup Guide

## Overview
Aplikasi web tracking performance KPI harian untuk Sampoerna. Sistem ini memungkinkan user untuk:
- Input nama untuk authentication atau registrasi
- Memasukkan data progress harian dengan berbagai kategori metrics
- Upload foto dokumentasi (maksimal 20 foto)
- Mendapatkan feedback performance dengan percentage calculation
- Target default 42 yang dapat diubah admin

## Features
✅ **Multi-step Authentication Flow**
- Check user existence by name
- Auto-registration for new users
- Username auto-generated from email

✅ **Progress Tracking System**
- Comprehensive metrics across 8 categories
- Real-time percentage calculation
- Target-based performance evaluation
- Daily progress validation (one submission per day)

✅ **Photo Documentation**
- Cloudinary integration for image upload
- Support for up to 20 photos per submission
- Image preview and removal functionality

✅ **Responsive Design**
- Mobile-friendly interface
- Elegant step-card design
- Smooth animations and transitions
- TailwindCSS styling

✅ **Dynamic Performance Feedback**
- Personalized messages based on performance
- Target achievement notifications
- Motivational feedback system

## Installation

### 1. Clone & Setup Laravel
```bash
git clone <repository-url>
cd performanceSampoerna-kpi
composer install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup
Update `.env` file with your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kpisampoerna
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Cloudinary Setup
Add your Cloudinary credentials to `.env`:
```
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
CLOUDINARY_UPLOAD_PRESET=your_upload_preset
```

### 5. Run Migrations & Seeders
```bash
php artisan migrate
php artisan db:seed
```

### 6. Start Development Server
```bash
php artisan serve
```

Visit: `http://localhost:8000`

## Database Structure

### Users Table
- `id`, `name`, `email`, `username`, `whatsapp`, `password` (nullable), `timestamps`

### Daily Progress Table
- `id`, `user_id`, `progress_date`, `overall_percentage`, `photos` (JSON), `timestamps`
- Unique constraint on (`user_id`, `progress_date`)

### Progress Items Table
- `id`, `daily_progress_id`, `item_name`, `target_value`, `actual_value`, `percentage`, `timestamps`

### Targets Table
- `id`, `name`, `target_value` (default: 42), `is_active`, `timestamps`

## API Endpoints

### Authentication
- `POST /api/check-user` - Check if user exists
- `POST /api/register` - Register new user
- `POST /api/login` - Login user (if password needed)

### Progress Management
- `GET /api/progress-items` - Get all progress items structure
- `POST /api/submit-progress` - Submit daily progress
- `GET /api/today-progress` - Check today's progress status

### Target Management
- `GET /api/target` - Get current target value
- `POST /api/update-target` - Update target value (admin)

## Progress Items Structure

The system tracks performance across 8 main categories:

1. **Visit** - Plan Visit, Actual Visit, OOR Outlet, Eff Outlet
2. **Ecosystem** - Various submit/login requirements
3. **Volume** - DTC12, NAT20, TWP16, VEEV, KBL12
4. **Eff Call** - Effectiveness metrics for each product
5. **Av Out** - Average outlet metrics
6. **Stick Selling** - Stick selling metrics
7. **Private Label & Cricket** - Cricket and ADK metrics
8. **Others** - Bookmarking, PVP, referrals, compliance

## Performance Calculation
- Each item percentage = (actual_value / target_value) * 100
- Overall percentage = average of all item percentages
- Target threshold: 70% for success feedback

## Security Features
- CSRF protection on all forms
- File upload validation (images only, max 2MB)
- Input sanitization and validation
- SQL injection protection via Eloquent ORM

## Mobile Responsiveness
- Responsive grid layouts
- Touch-friendly interface
- Optimized for mobile screens
- Progressive enhancement

## Photo Upload
- Cloudinary integration for reliable image storage
- Client-side image preview
- Drag & drop support
- Multiple file selection
- Image compression and optimization

## Performance Feedback Messages
Dynamic messages based on performance:
- **≥70%**: Success/celebration messages
- **≥50%**: Encouraging improvement messages  
- **<50%**: Motivational push messages

## Customization
- Target values can be adjusted via admin interface
- Progress items can be modified in `ProgressController`
- Feedback messages can be customized in `generateFeedbackMessage()`
- UI styling can be modified in the Blade template

## Troubleshooting

### Common Issues
1. **Migration errors**: Check database connection and credentials
2. **Cloudinary upload fails**: Verify API credentials and upload preset
3. **CSRF token mismatch**: Ensure meta tag is present in HTML head
4. **Photos not uploading**: Check file size and format restrictions

### Debug Mode
Enable debug mode in `.env` for detailed error messages:
```
APP_DEBUG=true
```

## New Features Added (v2.0)

### ✅ **Complete Feature Set:**

1. **Sidebar Navigation**
   - Responsive hamburger menu for mobile
   - Desktop persistent sidebar
   - Navigation between Dashboard, History, Reports, and Admin Panel

2. **History Page**
   - Calendar view with monthly navigation
   - Visual indicators for days with progress
   - Detailed progress view for selected dates
   - PDF export for daily reports

3. **Reports Page**
   - Monthly report generation
   - Preview before download
   - PDF export with comprehensive analytics
   - Automatic report availability after month completion

4. **Admin Panel**
   - View all users and their statistics
   - User management (promote/demote admin rights)
   - System-wide analytics
   - User deletion capabilities

5. **Role-Based Access Control**
   - `is_admin` field in database
   - Admin-only features protection
   - Proper authorization checks

6. **PDF Export System**
   - Daily progress reports
   - Monthly comprehensive reports
   - Professional PDF styling
   - Auto-generated filenames

### 🔧 **Technical Improvements:**
- Enhanced database structure with proper relationships
- Comprehensive API endpoints for all features
- Better error handling and validation
- Responsive design improvements
- Advanced JavaScript functionality with Alpine.js

### 📊 **Database Schema:**
- **users**: Added `is_admin` field
- **daily_progress**: Main progress tracking
- **progress_items**: Detailed item tracking
- **targets**: Configurable targets system

### 🚀 **Ready for Production:**
All features are fully implemented and tested. The system now provides:
- Complete user management
- Historical data tracking
- Professional reporting
- Admin oversight capabilities
- Mobile-responsive interface

## Next Steps / Enhancement Ideas
- Add email notifications for performance alerts
- Implement data visualization charts
- Add bulk data import/export
- Include performance analytics dashboard
- Add notification system for missed submissions
