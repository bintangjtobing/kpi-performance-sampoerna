<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Performance Sampoerna KPI</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#2563eb">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://upload-widget.cloudinary.com/global/all.js" type="text/javascript" onerror="console.error('Failed to load Cloudinary script')"></script>
    <style>
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background-color: #e5e7eb;
            border: 1px solid #e5e7eb;
        }

        .calendar-day {
            background-color: white;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            min-height: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .calendar-day:hover {
            background-color: #f3f4f6;
        }

        .calendar-day.has-progress {
            background-color: #dbeafe;
            border-left: 4px solid #2563eb;
        }

        .calendar-day.has-progress:hover {
            background-color: #bfdbfe;
        }

        .calendar-day.submittable {
            background-color: #f0fff4;
            border-left: 4px solid #16a34a;
        }

        .calendar-day.submittable:hover {
            background-color: #dcfce7;
        }

        .calendar-day.future {
            background-color: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
        }

        .calendar-day.selected {
            background-color: #2563eb;
            color: white;
        }

        .calendar-day.selected.has-progress {
            background-color: #1d4ed8;
            border-left: 4px solid #ffffff;
        }

        [x-cloak] {
            display: none !important;
        }


        /* Logo Styles */
        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border-radius: 12px;
            font-weight: 800;
            font-size: 18px;
            color: white;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .logo:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
        }

        .logo-text {
            margin-left: 16px;
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            white-space: nowrap;
        }

        /* Mobile hamburger menu */
        .mobile-menu {
            position: fixed;
            top: 64px;
            left: 0;
            right: 0;
            z-index: 50;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-in-out;
        }

        .mobile-menu.open {
            max-height: 400px;
        }

        .hamburger {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            width: 24px;
            height: 18px;
            cursor: pointer;
        }

        .hamburger span {
            display: block;
            height: 2px;
            width: 100%;
            background-color: #374151;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }

        /* Mobile touch improvements */
        @media (max-width: 768px) {
            .logo {
                width: 40px;
                height: 40px;
                font-size: 16px;
                border-radius: 10px;
            }

            .logo-text {
                font-size: 16px;
                margin-left: 12px;
            }

            .desktop-nav {
                display: none;
            }

            input,
            button,
            select,
            textarea {
                font-size: 16px !important;
                -webkit-appearance: none;
                -webkit-tap-highlight-color: transparent;
            }

            .calendar-day {
                min-height: 50px;
                padding: 4px;
            }

            .touch-target {
                min-height: 44px;
                min-width: 44px;
            }

            /* Fix mobile viewport and touch */
            body {
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
            }

            /* Ensure clickable elements are properly sized */
            button,
            input[type="submit"],
            input[type="button"] {
                min-height: 44px;
                padding: 12px 16px;
                cursor: pointer;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div id="app" x-data="kpiApp()" x-init="init()">

        <!-- Main Content -->
        <div>
            <!-- Header -->
            <header class="bg-white shadow-lg" x-show="currentUser || true" x-cloak>
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center">
                            <div class="logo">PSK</div>
                            <span class="logo-text hidden sm:block">Performance Sampoerna KPI</span>
                        </div>
                        
                        <!-- Desktop Navigation -->
                        <div class="hidden md:flex items-center space-x-6">
                            <div class="flex items-center space-x-4">
                                <button @click="currentPage = 'dashboard'"
                                    :class="currentPage === 'dashboard' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800'"
                                    class="px-3 py-2 text-sm font-medium transition-colors">
                                    Dashboard
                                </button>
                                <button @click="currentPage = 'history'; loadHistory()"
                                    :class="currentPage === 'history' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800'"
                                    class="px-3 py-2 text-sm font-medium transition-colors">
                                    History
                                </button>
                                <button @click="currentPage = 'reports'; loadReports()"
                                    :class="currentPage === 'reports' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800'"
                                    class="px-3 py-2 text-sm font-medium transition-colors">
                                    Reports
                                </button>
                                <button @click="currentPage = 'admin'; loadAdmin()" x-show="currentUser?.is_admin"
                                    :class="currentPage === 'admin' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800'"
                                    class="px-3 py-2 text-sm font-medium transition-colors">
                                    Admin
                                </button>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-600">Welcome, <span x-text="currentUser?.name"></span></span>
                                <button @click="logout()" class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Mobile Navigation -->
                        <div class="md:hidden flex items-center space-x-2">
                            <button @click="logout()" class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                            <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2">
                                <div class="hamburger" :class="{'active': mobileMenuOpen}">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Menu Dropdown -->
                <div class="md:hidden mobile-menu bg-white border-t border-gray-200 shadow-lg" :class="{'open': mobileMenuOpen}">
                    <div class="px-4 py-2 space-y-1">
                        <button @click="currentPage = 'dashboard'; mobileMenuOpen = false"
                            :class="currentPage === 'dashboard' ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                        </button>
                        <button @click="currentPage = 'history'; loadHistory(); mobileMenuOpen = false"
                            :class="currentPage === 'history' ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-history mr-3"></i>History
                        </button>
                        <button @click="currentPage = 'reports'; loadReports(); mobileMenuOpen = false"
                            :class="currentPage === 'reports' ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-chart-bar mr-3"></i>Reports
                        </button>
                        <button @click="currentPage = 'admin'; loadAdmin(); mobileMenuOpen = false" x-show="currentUser?.is_admin"
                            :class="currentPage === 'admin' ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-users-cog mr-3"></i>Admin Panel
                        </button>
                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <div class="px-4 py-2 text-sm text-gray-600">
                                Welcome, <span x-text="currentUser?.name"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6" :class="currentUser ? 'pt-6' : ''">
                <!-- User Authentication Step -->
                <div x-show="currentStep === 'name'" class="flex items-center justify-center min-h-screen px-4 -mt-24">
                    <div class="w-full max-w-md">
                        <div
                            class="bg-white rounded-xl shadow-lg p-8 transform transition-all duration-300 hover:scale-105">
                            <div class="text-center mb-8">
                                <i class="fas fa-user-circle text-blue-600 text-6xl mb-4"></i>
                                <h2 class="text-2xl font-bold text-gray-900">Selamat Datang!</h2>
                                <p class="text-gray-600 mt-2">Masukkan nama Anda untuk memulai</p>
                            </div>
                            <form @submit.prevent="checkUser()">
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                    <input type="text" x-model="userName" x-ref="nameInput"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                        placeholder="Masukkan nama lengkap Anda" autocomplete="name" required>
                                </div>
                                <button type="submit" :disabled="loading"
                                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transform transition-all duration-200 hover:scale-105 disabled:opacity-50">
                                    <span x-show="!loading">Lanjutkan</span>
                                    <span x-show="loading">
                                        <i class="fas fa-spinner fa-spin"></i> Memproses...
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Password Step -->
                <div x-show="currentStep === 'password'"
                    class="flex items-center justify-center min-h-screen px-4 -mt-24">
                    <div class="w-full max-w-md">
                        <div
                            class="bg-white rounded-xl shadow-lg p-8 transform transition-all duration-300 hover:scale-105">
                            <div class="text-center mb-8">
                                <i class="fas fa-lock text-blue-600 text-6xl mb-4"></i>
                                <h2 class="text-2xl font-bold text-gray-900">Masukkan Password</h2>
                                <p class="text-gray-600 mt-2">Hai <span x-text="userName"></span>, silakan masukkan
                                    password Anda</p>
                            </div>
                            <form @submit.prevent="login()">
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                    <input type="password" x-model="userPassword" x-ref="passwordInput"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Masukkan password Anda" required>
                                </div>
                                <button type="submit" :disabled="loading"
                                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transform transition-all duration-200 hover:scale-105 disabled:opacity-50">
                                    <span x-show="!loading">Login</span>
                                    <span x-show="loading">
                                        <i class="fas fa-spinner fa-spin"></i> Login...
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Registration Step -->
                <div x-show="currentStep === 'register'"
                    class="flex items-center justify-center min-h-screen px-4 -mt-24">
                    <div class="w-full max-w-md">
                        <div
                            class="bg-white rounded-xl shadow-lg p-8 transform transition-all duration-300 hover:scale-105">
                            <div class="text-center mb-8">
                                <i class="fas fa-user-plus text-green-600 text-6xl mb-4"></i>
                                <h2 class="text-2xl font-bold text-gray-900">Registrasi</h2>
                                <p class="text-gray-600 mt-2">Lengkapi data Anda untuk melanjutkan</p>
                            </div>
                            <form @submit.prevent="register()">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                    <input type="text" x-model="userName"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        readonly>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" x-model="userEmail"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="nama@email.com" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp</label>
                                    <input type="text" x-model="userWhatsapp"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="628xxxxxxxxx" pattern="628[0-9]{8,11}" required>
                                    <p class="text-xs text-gray-500 mt-1">Format: 628xxxxxxxxx</p>
                                </div>
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                    <input type="password" x-model="userPassword"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Masukkan password" required>
                                    <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                                </div>
                                
                                <!-- Warning dan Agreement -->
                                <div class="mb-6">
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                        <div class="flex items-start">
                                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-2"></i>
                                            <div class="text-sm text-yellow-800">
                                                <p class="font-medium mb-2">Perhatian Penting!</p>
                                                <ul class="space-y-1 text-xs">
                                                    <li>• <strong>Email:</strong> Pastikan email yang Anda masukkan <strong>benar dan aktif</strong>, karena akan digunakan untuk verifikasi akun dan menerima laporan harian.</li>
                                                    <li>• <strong>WhatsApp:</strong> Pastikan nomor WhatsApp yang Anda masukkan <strong>benar dan aktif</strong>, karena akan digunakan untuk informasi penting ke nomor tersebut.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <input type="checkbox" id="dataAccuracy" x-model="agreedToDataAccuracy"
                                            class="mt-1 mr-3 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                        <label for="dataAccuracy" class="text-sm text-gray-700 cursor-pointer">
                                            Saya telah <strong>memeriksa dan memastikan</strong> bahwa email dan nomor WhatsApp yang saya masukkan adalah <strong>benar dan aktif</strong>. Saya memahami bahwa data ini akan digunakan untuk komunikasi penting.
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" :disabled="loading || !agreedToDataAccuracy"
                                    class="w-full bg-green-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-green-700 transform transition-all duration-200 hover:scale-105 disabled:opacity-50">
                                    <span x-show="!loading">Daftar</span>
                                    <span x-show="loading">
                                        <i class="fas fa-spinner fa-spin"></i> Mendaftar...
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Email Verification Step -->
                <div x-show="currentStep === 'email_verification'"
                    class="flex items-center justify-center min-h-screen px-4 -mt-24">
                    <div class="w-full max-w-md">
                        <div
                            class="bg-white rounded-xl shadow-lg p-8 transform transition-all duration-300 hover:scale-105">
                            <div class="text-center mb-8">
                                <i class="fas fa-envelope-open text-blue-600 text-6xl mb-4"></i>
                                <h2 class="text-2xl font-bold text-gray-900">Verifikasi Email</h2>
                                <p class="text-gray-600 mt-2">Masukkan kode verifikasi yang dikirim ke email dan WhatsApp Anda</p>
                                <p class="text-sm text-blue-600 mt-2 font-medium" x-text="userEmail"></p>
                            </div>
                            
                            <form @submit.prevent="verifyEmail()">
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-4 text-center">Kode Verifikasi</label>
                                    <div class="flex justify-center space-x-2">
                                        <input type="text" 
                                               x-model="verificationCode[0]"
                                               @input="handleCodeInput($event, 0)"
                                               @keydown="handleKeyDown($event, 0)"
                                               @paste="handlePaste($event)"
                                               class="w-12 h-12 text-center text-lg font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                               maxlength="1" 
                                               id="code-0">
                                        <input type="text" 
                                               x-model="verificationCode[1]"
                                               @input="handleCodeInput($event, 1)"
                                               @keydown="handleKeyDown($event, 1)"
                                               class="w-12 h-12 text-center text-lg font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                               maxlength="1" 
                                               id="code-1">
                                        <input type="text" 
                                               x-model="verificationCode[2]"
                                               @input="handleCodeInput($event, 2)"
                                               @keydown="handleKeyDown($event, 2)"
                                               class="w-12 h-12 text-center text-lg font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                               maxlength="1" 
                                               id="code-2">
                                        <input type="text" 
                                               x-model="verificationCode[3]"
                                               @input="handleCodeInput($event, 3)"
                                               @keydown="handleKeyDown($event, 3)"
                                               class="w-12 h-12 text-center text-lg font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                               maxlength="1" 
                                               id="code-3">
                                        <input type="text" 
                                               x-model="verificationCode[4]"
                                               @input="handleCodeInput($event, 4)"
                                               @keydown="handleKeyDown($event, 4)"
                                               class="w-12 h-12 text-center text-lg font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                               maxlength="1" 
                                               id="code-4">
                                        <input type="text" 
                                               x-model="verificationCode[5]"
                                               @input="handleCodeInput($event, 5)"
                                               @keydown="handleKeyDown($event, 5)"
                                               class="w-12 h-12 text-center text-lg font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                               maxlength="1" 
                                               id="code-5">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-3 text-center">Masukkan 6 digit kode verifikasi yang dikirim ke email dan WhatsApp Anda</p>
                                </div>

                                <button type="submit" :disabled="loading || !isCodeComplete"
                                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transform transition-all duration-200 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-show="!loading">Verifikasi Email</span>
                                    <span x-show="loading">
                                        <i class="fas fa-spinner fa-spin"></i> Memverifikasi...
                                    </span>
                                </button>
                            </form>

                            <div class="mt-6 text-center">
                                <p class="text-sm text-gray-500 mb-3">Tidak menerima kode di email atau WhatsApp?</p>
                                <button @click="resendVerificationCode()" :disabled="loading || resendCooldown > 0"
                                    class="text-blue-600 hover:text-blue-800 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-show="resendCooldown === 0">Kirim Ulang Kode</span>
                                    <span x-show="resendCooldown > 0">Kirim Ulang (<span x-text="resendCooldown"></span>s)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Page -->
                <div x-show="currentStep === 'progress' && currentPage === 'dashboard'" class="space-y-6">
                    <!-- Progress Check -->
                    <div x-show="todayProgress && submissionMode !== 'backdate'" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-yellow-600 text-xl mr-3"></i>
                            <div>
                                <h3 class="text-lg font-medium text-yellow-800">Progress Hari Ini Sudah Disubmit</h3>
                                <p class="text-yellow-700">Anda sudah menginput progress untuk hari ini. Silakan kembali
                                    besok.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Form -->
                    <div x-show="!todayProgress || submissionMode === 'backdate'">
                        <div class="bg-white rounded-xl shadow-lg p-8">
                            <div class="text-center mb-8">
                                <i class="fas fa-chart-bar text-blue-600 text-4xl mb-4"></i>
                                <h2 class="text-2xl font-bold text-gray-900">Input Progress Harian</h2>
                                <p class="text-gray-600 mt-2">Masukkan progress Anda untuk hari ini</p>

                                <!-- Auto-save indicator -->
                                <div class="mt-4 flex items-center justify-center space-x-2">
                                    <div x-show="autoSaveStatus === 'saving'" class="flex items-center text-orange-600">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>
                                        <span class="text-sm">Menyimpan...</span>
                                    </div>
                                    <div x-show="autoSaveStatus === 'saved' && autoSaveMessage"
                                        class="flex items-center text-green-600">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <span class="text-sm" x-text="autoSaveMessage"></span>
                                    </div>
                                    <div x-show="autoSaveStatus === 'failed'" class="flex items-center text-red-600">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        <span class="text-sm" x-text="autoSaveMessage"></span>
                                    </div>
                                </div>

                                <div class="mt-2 text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Setiap yang Anda tulis akan tersimpan secara otomatis
                                </div>
                            </div>

                            <!-- Backdate Submission Indicator -->
                            <div x-show="submissionMode === 'backdate'" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                                        <div>
                                            <p class="text-sm font-medium text-blue-900">Mode Input Tanggal Mundur</p>
                                            <p class="text-xs text-blue-700">
                                                Laporan akan diinput untuk tanggal: 
                                                <span class="font-semibold" x-text="selectedDateForSubmission ? new Date(selectedDateForSubmission).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : ''"></span>
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="cancelBackdateSubmission()" 
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        <i class="fas fa-times mr-1"></i>
                                        Batal
                                    </button>
                                </div>
                            </div>

                            <form @submit.prevent="submitProgress()">
                                <div class="space-y-8">
                                    <!-- Progress Items -->
                                    <template x-for="(items, category) in progressItems" :key="category">
                                        <div class="bg-gray-50 rounded-lg p-6">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                                <i class="fas fa-folder text-blue-600 mr-2"></i>
                                                <span x-text="category"></span>
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <template x-for="(shouldShow, itemName) in items" :key="itemName">
                                                    <div x-show="shouldShow">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2"
                                                            x-text="itemName"></label>
                                                        <input type="number" x-model="progressData[itemName]"
                                                            @input="autoSaveData()"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                            placeholder="0" min="0">
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Photo Upload -->
                                    <div class="bg-gray-50 rounded-lg p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                            <i class="fas fa-camera text-blue-600 mr-2"></i>
                                            Foto Dokumentasi
                                        </h3>
                                        <div class="mb-4">
                                            <button type="button" @click="openCloudinaryWidget()" :disabled="loading"
                                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center space-x-2 disabled:opacity-50">
                                                <i class="fas fa-cloud-upload-alt" x-show="!loading"></i>
                                                <i class="fas fa-spinner fa-spin" x-show="loading"></i>
                                                <span x-show="!loading">Upload Foto dengan Cloudinary</span>
                                                <span x-show="loading">Memuat Widget...</span>
                                            </button>
                                            <p class="text-xs text-gray-500 mt-1">Upload langsung ke cloud - tanpa
                                                batasan ukuran</p>
                                        </div>
                                        <div x-show="photos.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            <template x-for="(photo, index) in photos" :key="index">
                                                <div class="relative">
                                                    <img :src="photo.secure_url"
                                                        class="w-full h-24 object-cover rounded-lg shadow-md">
                                                    <button type="button" @click="removePhoto(index)"
                                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 shadow-md">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <div
                                                        class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 rounded-b-lg">
                                                        <i class="fas fa-cloud mr-1"></i>Cloudinary
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <button type="submit" :disabled="loading"
                                        class="w-full bg-blue-600 text-white py-4 px-6 rounded-lg font-medium hover:bg-blue-700 transform transition-all duration-200 hover:scale-105 disabled:opacity-50">
                                        <span x-show="!loading">Submit Progress</span>
                                        <span x-show="loading">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            <span
                                                x-text="photos.length > 0 ? 'Mengupload foto...' : 'Menyimpan...'"></span>
                                        </span>
                                    </button>
                                    <div x-show="photos.length > 0" class="mt-2 text-sm text-gray-600 text-center">
                                        <span x-text="photos.length"></span> foto dipilih
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- History Page -->
                <div x-show="currentStep === 'progress' && currentPage === 'history'" class="space-y-6">
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">History Progress</h2>
                            <div class="flex items-center space-x-4">
                                <select x-model="selectedMonth" @change="loadMonthlyData()"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih Bulan</option>
                                    <template x-for="month in availableMonths" :key="month.year + '-' + month.month">
                                        <option :value="month.year + '-' + month.month" x-text="month.display"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Calendar View -->
                        <div x-show="selectedMonth">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="monthName"></h3>

                            <!-- Calendar Header -->
                            <div class="grid grid-cols-7 gap-1 mb-2">
                                <div class="text-center font-medium text-gray-700 py-2">Sun</div>
                                <div class="text-center font-medium text-gray-700 py-2">Mon</div>
                                <div class="text-center font-medium text-gray-700 py-2">Tue</div>
                                <div class="text-center font-medium text-gray-700 py-2">Wed</div>
                                <div class="text-center font-medium text-gray-700 py-2">Thu</div>
                                <div class="text-center font-medium text-gray-700 py-2">Fri</div>
                                <div class="text-center font-medium text-gray-700 py-2">Sat</div>
                            </div>

                            <!-- Calendar Grid -->
                            <div class="calendar-grid rounded-lg overflow-hidden">
                                <template x-for="(day, date) in calendarData" :key="date">
                                    <div class="calendar-day"
                                        :class="{
                                            'has-progress': day.has_progress, 
                                            'selected': selectedDate === date,
                                            'submittable': !day.has_progress && new Date(date) <= new Date(),
                                            'future': new Date(date) > new Date()
                                        }"
                                        @click="selectDate(date, day)">
                                        <div class="text-sm font-medium" x-text="day.date"></div>
                                        <div x-show="day.has_progress" class="text-xs text-blue-200 mt-1"
                                            x-text="Math.round(day.percentage) + '%'"></div>
                                        
                                        <!-- Submit Button for dates without progress -->
                                        <div x-show="!day.has_progress && new Date(date) <= new Date()" class="mt-1">
                                            <button @click.stop="selectDateForSubmission(date)" 
                                                    class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700 transition-colors">
                                                Input
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Detail -->
                    <div x-show="selectedDateProgress" class="bg-white rounded-xl shadow-lg p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Progress Detail</h3>
                            <div class="flex space-x-2">
                                <button @click="previewDailyReport()"
                                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                                    <i class="fas fa-eye mr-2"></i>Preview
                                </button>
                                <button @click="downloadDailyReport()"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-download mr-2"></i>Download PDF
                                </button>
                            </div>
                        </div>

                        <div class="mb-6">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <p class="text-sm text-gray-600">Tanggal: <span x-text="selectedDate"></span></p>
                                <p class="text-2xl font-bold text-blue-600"
                                    x-text="(selectedDateProgress?.progress?.overall_percentage ? Math.round(selectedDateProgress.progress.overall_percentage) : 0) + '%'">
                                </p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <template x-for="(items, category) in selectedDateProgress?.grouped_items" :key="category">
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4" x-text="category"></h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <template x-for="item in items" :key="item.id">
                                            <div class="bg-white rounded-md p-4">
                                                <div class="flex justify-between items-center">
                                                    <p class="text-sm font-medium text-gray-700"
                                                        x-text="item.item_name"></p>
                                                    <span class="text-sm font-bold"
                                                        :class="item.percentage >= 70 ? 'text-green-600' : item.percentage >= 50 ? 'text-yellow-600' : 'text-red-600'"
                                                        x-text="Math.round(item.percentage) + '%'"></span>
                                                </div>
                                                <div class="mt-2 text-xs text-gray-500">
                                                    <span x-text="item.actual_value"></span> / <span
                                                        x-text="item.target_value"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Photos -->
                        <div x-show="selectedDateProgress?.progress" class="mt-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">
                                Foto Dokumentasi
                                <span class="text-sm text-gray-500"
                                    x-text="selectedDateProgress?.progress.photos ? '(' + selectedDateProgress.progress.photos.length + ' photos)' : '(0 photos)'"></span>
                            </h4>

                            <!-- Debug info -->
                            <div x-show="!selectedDateProgress?.progress.photos || selectedDateProgress.progress.photos.length === 0"
                                class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                <p class="text-sm text-yellow-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Tidak ada foto dokumentasi untuk tanggal ini
                                </p>
                                <p class="text-xs text-yellow-600 mt-1">
                                    Photos data: <span
                                        x-text="JSON.stringify(selectedDateProgress?.progress.photos)"></span>
                                </p>
                            </div>

                            <div x-show="selectedDateProgress?.progress.photos && selectedDateProgress.progress.photos.length > 0"
                                class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <template x-for="(photo, index) in selectedDateProgress?.progress.photos" :key="index">
                                    <div class="relative">
                                        <img :src="photo" class="w-full h-24 object-cover rounded-lg shadow-md"
                                            :alt="'Photo ' + (index + 1)"
                                            x-on:error="console.error('Failed to load image:', photo)"
                                            x-on:load="console.log('Image loaded successfully:', photo)">
                                        <div
                                            class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 rounded-b-lg">
                                            <i class="fas fa-camera mr-1"></i>Photo <span x-text="index + 1"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reports Page -->
                <div x-show="currentStep === 'progress' && currentPage === 'reports'" class="space-y-6">
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Laporan Bulanan</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <template x-for="report in availableReports" :key="report.year + '-' + report.month">
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900" x-text="report.display"></h3>
                                        <span class="text-sm text-gray-500"
                                            x-text="report.progress_count + ' hari'"></span>
                                    </div>
                                    <div class="space-y-2">
                                        <button @click="previewMonthlyReport(report)"
                                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 text-sm">
                                            <i class="fas fa-eye mr-2"></i>Preview
                                        </button>
                                        <button @click="downloadMonthlyReport(report)" :disabled="!report.is_complete"
                                            class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 text-sm disabled:opacity-50"
                                            :class="{'opacity-50 cursor-not-allowed': !report.is_complete}">
                                            <i class="fas fa-download mr-2"></i>Download PDF
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Monthly Report Preview -->
                    <div x-show="monthlyReportPreview" class="bg-white rounded-xl shadow-lg p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Preview Laporan</h3>
                            <button @click="monthlyReportPreview = null" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="bg-blue-50 rounded-lg p-4 text-center">
                                    <p class="text-sm text-gray-600">Completion Rate</p>
                                    <p class="text-2xl font-bold text-blue-600"
                                        x-text="monthlyReportPreview?.summary.completion_rate + '%'"></p>
                                </div>
                                <div class="bg-green-50 rounded-lg p-4 text-center">
                                    <p class="text-sm text-gray-600">Average Performance</p>
                                    <p class="text-2xl font-bold text-green-600"
                                        x-text="monthlyReportPreview?.summary.average_performance + '%'"></p>
                                </div>
                                <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                    <p class="text-sm text-gray-600">Progress Days</p>
                                    <p class="text-2xl font-bold text-yellow-600"
                                        x-text="monthlyReportPreview?.summary.progress_days + '/' + monthlyReportPreview?.summary.total_days">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Panel -->
                <div x-show="currentStep === 'progress' && currentPage === 'admin' && currentUser?.is_admin"
                    class="space-y-6">
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Admin Panel</h2>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                            <div class="bg-blue-50 rounded-lg p-6 text-center">
                                <i class="fas fa-users text-blue-600 text-2xl mb-2"></i>
                                <p class="text-sm text-gray-600">Total Users</p>
                                <p class="text-2xl font-bold text-blue-600" x-text="adminStats?.total_users"></p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-6 text-center">
                                <i class="fas fa-user-check text-green-600 text-2xl mb-2"></i>
                                <p class="text-sm text-gray-600">Active This Month</p>
                                <p class="text-2xl font-bold text-green-600"
                                    x-text="adminStats?.active_users_this_month"></p>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-6 text-center">
                                <i class="fas fa-chart-line text-yellow-600 text-2xl mb-2"></i>
                                <p class="text-sm text-gray-600">Progress Today</p>
                                <p class="text-2xl font-bold text-yellow-600" x-text="adminStats?.total_progress_today">
                                </p>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-6 text-center">
                                <i class="fas fa-trophy text-purple-600 text-2xl mb-2"></i>
                                <p class="text-sm text-gray-600">Avg Performance</p>
                                <p class="text-2xl font-bold text-purple-600"
                                    x-text="Math.round(adminStats?.avg_performance_this_month) + '%'"></p>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Last Progress</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            This Month</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Role</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="user in adminUsers" :key="user.id">
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900" x-text="user.name"></p>
                                                    <p class="text-xs text-gray-500" x-text="user.email"></p>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div x-show="user.last_progress">
                                                    <p class="text-sm text-gray-900" x-text="user.last_progress?.date">
                                                    </p>
                                                    <p class="text-xs text-gray-500"
                                                        x-text="Math.round(user.last_progress?.percentage) + '%'"></p>
                                                </div>
                                                <span x-show="!user.last_progress" class="text-xs text-gray-400">No
                                                    progress</span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="text-sm text-gray-900"
                                                    x-text="user.this_month_count + ' days'"></span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="user.is_admin ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                                    x-text="user.is_admin ? 'Admin' : 'User'">
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                                <button @click="viewUserHistory(user)"
                                                    class="text-blue-600 hover:text-blue-900 mr-2">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button @click="toggleUserAdmin(user)"
                                                    class="text-yellow-600 hover:text-yellow-900 mr-2">
                                                    <i class="fas fa-user-cog"></i>
                                                </button>
                                                <button @click="deleteUser(user)"
                                                    class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Result -->
                <div x-show="currentStep === 'result'" class="max-w-2xl mx-auto">
                    <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                        <div class="mb-6">
                            <i class="fas fa-trophy text-yellow-500 text-6xl mb-4"></i>
                            <h2 class="text-2xl font-bold text-gray-900">Progress Berhasil Disimpan!</h2>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Performance Anda</h3>
                            <div class="text-3xl font-bold text-blue-600 mb-2"
                                x-text="Math.round(overallPercentage) + '%'"></div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="bg-blue-600 h-4 rounded-full transition-all duration-1000"
                                    :style="'width: ' + Math.min(overallPercentage, 100) + '%'"></div>
                            </div>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 mb-6">
                            <p class="text-gray-700" x-text="resultMessage"></p>
                        </div>
                        <button @click="currentPage = 'dashboard'; currentStep = 'progress'; submissionMode = 'today'; selectedDateForSubmission = null; checkTodayProgress()"
                            class="bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transform transition-all duration-200 hover:scale-105">
                            Kembali ke Dashboard
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Global error handling
        window.addEventListener('error', function(event) {
            console.error('Global error:', event.error);
            // Suppress errors from external scripts like Rollbar
            if (event.filename && event.filename.includes('rollbar')) {
                event.preventDefault();
                return false;
            }
        });

        // Handle unhandled promise rejections
        window.addEventListener('unhandledrejection', function(event) {
            console.error('Unhandled promise rejection:', event.reason);
            // Suppress Rollbar and other external script errors
            if (event.reason && event.reason.stack && event.reason.stack.includes('rollbar')) {
                event.preventDefault();
                return false;
            }
        });

        // Cloudinary configuration
        const CLOUDINARY_CONFIG = {
            cloudName: '{{ env("CLOUDINARY_CLOUD_NAME") }}',
            uploadPreset: '{{ env("CLOUDINARY_UPLOAD_PRESET") }}',
            folder: '{{ env("CLOUDINARY_FOLDER", "kpi-performance-reports") }}'
        };

        function kpiApp() {
            return {
                currentStep: 'name',
                currentPage: 'dashboard',
                loading: false,
                mobileMenuOpen: false,
                userName: '',
                userEmail: '',
                userWhatsapp: '',
                userPassword: '',
                currentUser: null,
                progressItems: {},
                progressData: {},
                photos: [],
                todayProgress: false,
                overallPercentage: 0,
                resultMessage: '',
                targetValue: 42,
                autoSaveStatus: 'saved', // 'saving', 'saved', 'failed'
                autoSaveMessage: '',
                autoSaveTimeout: null,
                
                // Email verification
                verificationCode: ['', '', '', '', '', ''],
                resendCooldown: 0,
                resendInterval: null,
                
                // Registration validation
                agreedToDataAccuracy: false,

                // History
                availableMonths: [],
                selectedMonth: '',
                monthName: '',
                calendarData: {},
                selectedDate: '',
                selectedDateProgress: null,
                selectedDateForSubmission: null, // For backdate submission
                submissionMode: 'today', // 'today' or 'backdate'

                // Reports
                availableReports: [],
                monthlyReportPreview: null,

                // Admin
                adminStats: null,
                adminUsers: [],

                init() {
                    this.loadProgressItems();
                    this.checkTodayProgress();
                    this.checkSession();
                    this.setAutoFocus();
                    this.loadAutoSavedData();
                },

                setAutoFocus() {
                    this.$nextTick(() => {
                        if (this.currentStep === 'name') {
                            this.$refs.nameInput?.focus();
                        } else if (this.currentStep === 'password') {
                            this.$refs.passwordInput?.focus();
                        }
                    });
                },

                checkSession() {
                    const savedUser = localStorage.getItem('currentUser');
                    const savedStep = localStorage.getItem('currentStep');

                    if (savedUser && savedStep) {
                        this.currentUser = JSON.parse(savedUser);
                        this.currentStep = savedStep;
                        this.checkTodayProgress();
                    }
                },

                saveSession() {
                    if (this.currentUser) {
                        localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
                        localStorage.setItem('currentStep', this.currentStep);
                    }
                },

                clearSession() {
                    localStorage.removeItem('currentUser');
                    localStorage.removeItem('currentStep');
                    localStorage.removeItem('autoSavedProgress');
                    localStorage.removeItem('autoSavedPhotos');
                },

                // Auto-save functions
                loadAutoSavedData() {
                    if (!this.currentUser) return;

                    const savedData = localStorage.getItem(`autoSavedProgress_${this.currentUser.id}`);
                    if (savedData) {
                        try {
                            this.progressData = JSON.parse(savedData);
                            this.autoSaveMessage = 'Data sebelumnya berhasil dipulihkan';
                        } catch (error) {
                            console.error('Error loading auto-saved data:', error);
                        }
                    }
                },

                autoSaveData() {
                    if (!this.currentUser) return;

                    // Debounce auto-save
                    clearTimeout(this.autoSaveTimeout);
                    this.autoSaveTimeout = setTimeout(() => {
                        this.autoSaveStatus = 'saving';
                        this.autoSaveMessage = 'Menyimpan...';

                        try {
                            localStorage.setItem(`autoSavedProgress_${this.currentUser.id}`, JSON.stringify(this.progressData));
                            this.autoSaveStatus = 'saved';
                            this.autoSaveMessage = 'Tersimpan otomatis';

                            setTimeout(() => {
                                this.autoSaveMessage = '';
                            }, 2000);
                        } catch (error) {
                            console.error('Error auto-saving data:', error);
                            this.autoSaveStatus = 'failed';
                            this.autoSaveMessage = 'Gagal menyimpan';
                        }
                    }, 500); // Wait 500ms before saving
                },

                clearAutoSavedData() {
                    if (!this.currentUser) return;
                    localStorage.removeItem(`autoSavedProgress_${this.currentUser.id}`);
                    this.autoSaveMessage = '';
                },

                async checkUser() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/check-user', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ name: this.userName })
                        });

                        const data = await response.json();

                        if (data.exists) {
                            this.currentStep = 'password';
                        } else {
                            this.currentStep = 'register';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    } finally {
                        this.loading = false;
                    }
                },

                async register() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/register', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                name: this.userName,
                                email: this.userEmail,
                                whatsapp: this.userWhatsapp,
                                password: this.userPassword
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            if (data.step === 'email_verification') {
                                this.currentStep = 'email_verification';
                                this.userEmail = data.email;
                                this.showMessage(data.message, 'success');
                                
                                // Auto-focus first input after a brief delay
                                setTimeout(() => {
                                    const firstInput = document.getElementById('code-0');
                                    if (firstInput) {
                                        firstInput.focus();
                                    }
                                }, 100);
                            } else {
                                this.currentUser = data.user;
                                this.currentStep = 'progress';
                                this.saveSession();
                                this.checkTodayProgress();
                                this.loadAutoSavedData();
                            }
                        } else {
                            alert('Registrasi gagal. Silakan coba lagi.');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    } finally {
                        this.loading = false;
                    }
                },

                async login() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                name: this.userName,
                                password: this.userPassword
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.currentUser = data.user;
                            this.currentStep = 'progress';
                            this.saveSession();
                            this.checkTodayProgress();
                            this.loadAutoSavedData();
                        } else {
                            alert(data.message || 'Login gagal. Silakan coba lagi.');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    } finally {
                        this.loading = false;
                    }
                },

                async loadProgressItems() {
                    try {
                        const response = await fetch('/api/progress-items');
                        const data = await response.json();
                        this.progressItems = data.items;
                        this.targetValue = data.target_value;
                    } catch (error) {
                        console.error('Error loading progress items:', error);
                    }
                },

                async checkTodayProgress() {
                    if (!this.currentUser) return;

                    try {
                        const response = await fetch(`/api/today-progress?user_id=${this.currentUser.id}`);
                        const data = await response.json();
                        this.todayProgress = data.has_progress;
                    } catch (error) {
                        console.error('Error checking today progress:', error);
                    }
                },

                async loadCloudinaryScript() {
                    if (typeof cloudinary !== 'undefined') {
                        return Promise.resolve();
                    }

                    return new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        script.src = 'https://upload-widget.cloudinary.com/global/all.js';
                        
                        // Add timeout
                        const timeout = setTimeout(() => {
                            console.error('Cloudinary script loading timeout');
                            reject(new Error('Cloudinary script loading timeout'));
                        }, 10000); // 10 second timeout
                        
                        script.onload = () => {
                            clearTimeout(timeout);
                            console.log('Cloudinary script loaded successfully');
                            // Wait a bit for cloudinary to initialize
                            setTimeout(() => {
                                resolve();
                            }, 100);
                        };
                        script.onerror = () => {
                            clearTimeout(timeout);
                            console.error('Failed to load Cloudinary script');
                            reject(new Error('Failed to load Cloudinary script'));
                        };
                        document.head.appendChild(script);
                    });
                },

                async openCloudinaryWidget() {
                    // Set loading state
                    this.loading = true;
                    
                    try {
                        // Try to load Cloudinary script if not available
                        await this.loadCloudinaryScript();
                        
                        // Check if cloudinary is available
                        if (typeof cloudinary === 'undefined') {
                            console.error('Cloudinary script not loaded');
                            alert('Cloudinary widget tidak dapat dimuat. Silakan refresh halaman.');
                            this.loading = false;
                            return;
                        }
                    } catch (error) {
                        console.error('Error loading Cloudinary script:', error);
                        alert('Tidak dapat memuat Cloudinary widget. Silakan coba lagi atau refresh halaman.');
                        this.loading = false;
                        return;
                    }

                    try {
                        // Debug configuration
                        console.log('Cloudinary config:', CLOUDINARY_CONFIG);
                        
                        // Validate configuration
                        if (!CLOUDINARY_CONFIG.cloudName || !CLOUDINARY_CONFIG.uploadPreset) {
                            console.error('Missing Cloudinary configuration');
                            alert('Konfigurasi Cloudinary tidak lengkap. Silakan hubungi administrator.');
                            return;
                        }
                        
                        const widget = cloudinary.createUploadWidget({
                            cloudName: CLOUDINARY_CONFIG.cloudName,
                            uploadPreset: CLOUDINARY_CONFIG.uploadPreset,
                            folder: CLOUDINARY_CONFIG.folder,
                        resourceType: 'image',
                        multiple: true,
                        clientAllowedFormats: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                        maxFiles: 20,
                        cropping: false,
                        showAdvancedOptions: false,
                        showUploadMoreButton: true,
                        theme: 'default',
                        styles: {
                            palette: {
                                window: '#F5F5F5',
                                sourceBg: '#FFFFFF',
                                windowBorder: '#90a0b3',
                                tabIcon: '#0078FF',
                                inactiveTabIcon: '#69778A',
                                menuIcons: '#0078FF',
                                link: '#0078FF',
                                action: '#0078FF',
                                inProgress: '#0078FF',
                                complete: '#20B832',
                                error: '#EA2727',
                                textDark: '#000000',
                                textLight: '#FFFFFF'
                            }
                        }
                    }, (error, result) => {
                        if (!error && result && result.event === 'success') {
                            console.log('Upload successful:', result.info);
                            this.photos.push({
                                public_id: result.info.public_id,
                                secure_url: result.info.secure_url,
                                original_filename: result.info.original_filename,
                                bytes: result.info.bytes,
                                width: result.info.width,
                                height: result.info.height,
                                format: result.info.format
                            });
                        } else if (error) {
                            console.error('Upload error:', error);
                            alert('Terjadi kesalahan saat mengupload foto. Silakan coba lagi.');
                        }
                    });

                    widget.open();
                    
                    // Clear loading state after widget opens
                    this.loading = false;
                    
                    } catch (error) {
                        console.error('Error opening Cloudinary widget:', error);
                        alert('Terjadi kesalahan saat membuka widget upload. Silakan coba lagi.');
                        this.loading = false;
                    }
                },

                removePhoto(index) {
                    this.photos.splice(index, 1);
                },

                async submitProgress() {
                    this.loading = true;
                    try {
                        // Prepare photo URLs from Cloudinary
                        const photoUrls = this.photos.map(photo => photo.secure_url);

                        const requestData = {
                            user_id: this.currentUser.id,
                            items: JSON.stringify(this.progressData),
                            photo_urls: photoUrls
                        };

                        // Add progress_date if in backdate mode
                        if (this.submissionMode === 'backdate' && this.selectedDateForSubmission) {
                            requestData.progress_date = this.selectedDateForSubmission;
                        }

                        const response = await fetch('/api/submit-progress', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(requestData)
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();

                        if (data.success) {
                            this.overallPercentage = data.overall_percentage;
                            this.resultMessage = data.message;
                            this.currentStep = 'result';
                            this.saveSession();
                            this.clearAutoSavedData();
                            
                            // Reset backdate mode after successful submission
                            if (this.submissionMode === 'backdate') {
                                this.selectedDateForSubmission = null;
                                this.submissionMode = 'today';
                            }
                        } else {
                            alert(data.message || 'Terjadi kesalahan saat menyimpan progress');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        if (error.message.includes('413')) {
                            alert('File terlalu besar. Silakan kurangi ukuran atau jumlah foto.');
                        } else {
                            alert('Terjadi kesalahan. Silakan coba lagi.');
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                // History functions
                async loadHistory() {
                    try {
                        const response = await fetch(`/api/history/months?user_id=${this.currentUser.id}`);
                        const data = await response.json();
                        this.availableMonths = data.months;
                        if (this.availableMonths.length > 0) {
                            this.selectedMonth = this.availableMonths[0].year + '-' + this.availableMonths[0].month;
                            this.loadMonthlyData();
                        }
                    } catch (error) {
                        console.error('Error loading history:', error);
                    }
                },

                async loadMonthlyData() {
                    if (!this.selectedMonth) return;

                    const [year, month] = this.selectedMonth.split('-');
                    try {
                        const response = await fetch(`/api/history/monthly?user_id=${this.currentUser.id}&year=${year}&month=${month}`);
                        const data = await response.json();
                        this.calendarData = data.calendar;
                        this.monthName = data.month_name;
                    } catch (error) {
                        console.error('Error loading monthly data:', error);
                    }
                },

                async selectDate(date, day) {
                    this.selectedDate = date;
                    
                    if (day.has_progress) {
                        // Load existing progress detail
                        try {
                            const response = await fetch(`/api/history/detail?user_id=${this.currentUser.id}&date=${date}`);
                            const data = await response.json();
                            console.log('Progress detail response:', data);
                            console.log('Photos data:', data.progress?.photos);
                            this.selectedDateProgress = data;
                        } catch (error) {
                            console.error('Error loading progress detail:', error);
                        }
                    } else {
                        // Clear progress detail for empty days
                        this.selectedDateProgress = null;
                    }
                },

                async selectDateForSubmission(date) {
                    // Check if date is in the future
                    const selectedDate = new Date(date);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    selectedDate.setHours(0, 0, 0, 0);
                    
                    if (selectedDate > today) {
                        alert('Tidak dapat memilih tanggal yang akan datang');
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/api/progress-for-date?user_id=${this.currentUser.id}&date=${date}`);
                        const data = await response.json();
                        
                        if (data.has_progress) {
                            const dateFormatted = new Date(date).toLocaleDateString('id-ID', {
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric'
                            });
                            alert(`Progress sudah disubmit untuk tanggal ${dateFormatted}`);
                            return;
                        }
                        
                        this.selectedDateForSubmission = date;
                        this.submissionMode = 'backdate';
                        this.currentPage = 'dashboard';
                        
                        // Clear form data
                        this.resetProgressForm();
                        
                        // Show success message
                        const dateFormatted = new Date(date).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        });
                        
                        // Use timeout to ensure UI updates
                        setTimeout(() => {
                            alert(`Terpilih tanggal ${dateFormatted} untuk input laporan. Form siap untuk diisi.`);
                        }, 100);
                        
                    } catch (error) {
                        console.error('Error checking date:', error);
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    }
                },

                resetProgressForm() {
                    this.progressData = {};
                    this.photos = [];
                    this.overallPercentage = 0;
                    this.resultMessage = '';
                },

                cancelBackdateSubmission() {
                    this.selectedDateForSubmission = null;
                    this.submissionMode = 'today';
                    this.resetProgressForm();
                    
                    // Check today's progress again to update UI
                    this.checkTodayProgress();
                },

                async previewDailyReport() {
                    if (!this.selectedDate) return;

                    const url = `/api/reports/preview-daily?user_id=${this.currentUser.id}&date=${this.selectedDate}`;
                    window.open(url, '_blank');
                },
                async downloadDailyReport() {
                    if (!this.selectedDate) return;

                    const url = `/api/reports/download-daily?user_id=${this.currentUser.id}&date=${this.selectedDate}`;
                    window.open(url, '_blank');
                },

                // Reports functions
                async loadReports() {
                    try {
                        const response = await fetch(`/api/reports/available?user_id=${this.currentUser.id}`);
                        const data = await response.json();
                        this.availableReports = data.reports;
                    } catch (error) {
                        console.error('Error loading reports:', error);
                    }
                },

                async previewMonthlyReport(report) {
                    const url = `/api/reports/preview-monthly?user_id=${this.currentUser.id}&year=${report.year}&month=${report.month}`;
                    window.open(url, '_blank');
                },

                async downloadMonthlyReport(report) {
                    const url = `/api/reports/download-monthly?user_id=${this.currentUser.id}&year=${report.year}&month=${report.month}`;
                    window.open(url, '_blank');
                },

                // Admin functions
                async loadAdmin() {
                    if (!this.currentUser?.is_admin) return;

                    try {
                        const [statsResponse, usersResponse] = await Promise.all([
                            fetch('/api/admin/stats'),
                            fetch('/api/admin/users')
                        ]);

                        const statsData = await statsResponse.json();
                        const usersData = await usersResponse.json();

                        this.adminStats = statsData;
                        this.adminUsers = usersData.users;
                    } catch (error) {
                        console.error('Error loading admin data:', error);
                    }
                },

                async toggleUserAdmin(user) {
                    try {
                        const response = await fetch('/api/admin/update-user', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                user_id: user.id,
                                is_admin: !user.is_admin
                            })
                        });

                        const data = await response.json();
                        if (data.success) {
                            user.is_admin = !user.is_admin;
                        }
                    } catch (error) {
                        console.error('Error updating user:', error);
                    }
                },

                async deleteUser(user) {
                    if (!confirm('Are you sure you want to delete this user?')) return;

                    try {
                        const response = await fetch('/api/admin/delete-user', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                user_id: user.id
                            })
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.adminUsers = this.adminUsers.filter(u => u.id !== user.id);
                        }
                    } catch (error) {
                        console.error('Error deleting user:', error);
                    }
                },

                viewUserHistory(user) {
                    this.currentUser = user;
                    this.currentPage = 'history';
                    this.loadHistory();
                },

                resetApp() {
                    this.currentStep = 'name';
                    this.currentPage = 'dashboard';
                    this.userName = '';
                    this.userEmail = '';
                    this.userWhatsapp = '';
                    this.userPassword = '';
                    this.currentUser = null;
                    this.progressData = {};
                    this.photos = [];
                    this.todayProgress = false;
                    this.overallPercentage = 0;
                    this.resultMessage = '';
                    this.clearSession();
                },

                logout() {
                    this.resetApp();
                },

                // Email verification computed properties
                get isCodeComplete() {
                    return this.verificationCode.every(digit => digit.trim() !== '');
                },

                // Email verification methods
                handleCodeInput(event, index) {
                    const value = event.target.value;
                    
                    // Only allow numbers
                    if (!/^\d*$/.test(value)) {
                        event.target.value = '';
                        this.verificationCode[index] = '';
                        return;
                    }
                    
                    this.verificationCode[index] = value;
                    
                    // Auto-focus next input
                    if (value && index < 5) {
                        const nextInput = document.getElementById(`code-${index + 1}`);
                        if (nextInput) {
                            nextInput.focus();
                        }
                    }
                    
                    // Auto-submit when complete
                    if (this.isCodeComplete) {
                        setTimeout(() => {
                            this.verifyEmail();
                        }, 100);
                    }
                },

                handleKeyDown(event, index) {
                    // Handle backspace
                    if (event.key === 'Backspace' && !this.verificationCode[index] && index > 0) {
                        const prevInput = document.getElementById(`code-${index - 1}`);
                        if (prevInput) {
                            prevInput.focus();
                        }
                    }
                    
                    // Handle arrow keys
                    if (event.key === 'ArrowLeft' && index > 0) {
                        const prevInput = document.getElementById(`code-${index - 1}`);
                        if (prevInput) {
                            prevInput.focus();
                        }
                    }
                    
                    if (event.key === 'ArrowRight' && index < 5) {
                        const nextInput = document.getElementById(`code-${index + 1}`);
                        if (nextInput) {
                            nextInput.focus();
                        }
                    }
                },

                handlePaste(event) {
                    event.preventDefault();
                    const pastedData = event.clipboardData.getData('text');
                    
                    // Only process if pasted data is exactly 6 digits
                    if (/^\d{6}$/.test(pastedData)) {
                        const digits = pastedData.split('');
                        this.verificationCode = digits;
                        
                        // Focus the last input
                        const lastInput = document.getElementById('code-5');
                        if (lastInput) {
                            lastInput.focus();
                        }
                        
                        // Auto-submit
                        setTimeout(() => {
                            this.verifyEmail();
                        }, 100);
                    }
                },

                async verifyEmail() {
                    if (!this.isCodeComplete) return;
                    
                    this.loading = true;
                    try {
                        const code = this.verificationCode.join('');
                        
                        const response = await fetch('/api/verify-email', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                email: this.userEmail,
                                code: code
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.currentUser = data.user;
                            this.currentStep = 'progress';
                            this.saveSession();
                            this.checkTodayProgress();
                            this.loadAutoSavedData();
                            this.showMessage(data.message, 'success');
                        } else {
                            this.showMessage(data.message, 'error');
                            // Clear code inputs on error
                            this.verificationCode = ['', '', '', '', '', ''];
                            document.getElementById('code-0').focus();
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showMessage('Terjadi kesalahan. Silakan coba lagi.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async resendVerificationCode() {
                    if (this.resendCooldown > 0) return;
                    
                    this.loading = true;
                    try {
                        const response = await fetch('/api/send-verification-code', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                email: this.userEmail
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.showMessage(data.message, 'success');
                            this.startResendCooldown();
                        } else {
                            this.showMessage(data.message, 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showMessage('Terjadi kesalahan. Silakan coba lagi.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                startResendCooldown() {
                    this.resendCooldown = 60; // 60 seconds cooldown
                    this.resendInterval = setInterval(() => {
                        this.resendCooldown--;
                        if (this.resendCooldown <= 0) {
                            clearInterval(this.resendInterval);
                            this.resendInterval = null;
                        }
                    }, 1000);
                },

                showMessage(message, type) {
                    // You can implement a toast notification system here
                    if (type === 'success') {
                        // Show success message
                        console.log('Success:', message);
                    } else {
                        // Show error message
                        console.error('Error:', message);
                        alert(message);
                    }
                }
            }
        }
    </script>
</body>

</html>
