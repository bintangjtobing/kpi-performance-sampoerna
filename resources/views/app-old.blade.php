<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Sampoerna KPI</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div id="app" x-data="kpiApp()" x-init="init()">
        <!-- Header -->
        <header class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-blue-600 text-2xl mr-3"></i>
                        <h1 class="text-xl font-bold text-gray-900">Performance Sampoerna KPI</h1>
                    </div>
                    <div x-show="currentUser" class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Welcome, <span x-text="currentUser?.name"></span></span>
                        <button @click="logout()" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- User Authentication Step -->
            <div x-show="currentStep === 'name'" class="max-w-md mx-auto">
                <div class="bg-white rounded-xl shadow-lg p-8 transform transition-all duration-300 hover:scale-105">
                    <div class="text-center mb-8">
                        <i class="fas fa-user-circle text-blue-600 text-6xl mb-4"></i>
                        <h2 class="text-2xl font-bold text-gray-900">Selamat Datang!</h2>
                        <p class="text-gray-600 mt-2">Masukkan nama Anda untuk memulai</p>
                    </div>
                    <form @submit.prevent="checkUser()">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" 
                                   x-model="userName" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                   placeholder="Masukkan nama lengkap Anda"
                                   required>
                        </div>
                        <button type="submit" 
                                :disabled="loading"
                                class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transform transition-all duration-200 hover:scale-105 disabled:opacity-50">
                            <span x-show="!loading">Lanjutkan</span>
                            <span x-show="loading">
                                <i class="fas fa-spinner fa-spin"></i> Memproses...
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Registration Step -->
            <div x-show="currentStep === 'register'" class="max-w-md mx-auto">
                <div class="bg-white rounded-xl shadow-lg p-8 transform transition-all duration-300 hover:scale-105">
                    <div class="text-center mb-8">
                        <i class="fas fa-user-plus text-green-600 text-6xl mb-4"></i>
                        <h2 class="text-2xl font-bold text-gray-900">Registrasi</h2>
                        <p class="text-gray-600 mt-2">Lengkapi data Anda untuk melanjutkan</p>
                    </div>
                    <form @submit.prevent="register()">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" 
                                   x-model="userName" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   readonly>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" 
                                   x-model="userEmail" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="nama@email.com"
                                   required>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp</label>
                            <input type="text" 
                                   x-model="userWhatsapp" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="628xxxxxxxxx"
                                   pattern="628[0-9]{8,11}"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Format: 628xxxxxxxxx</p>
                        </div>
                        <button type="submit" 
                                :disabled="loading"
                                class="w-full bg-green-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-green-700 transform transition-all duration-200 hover:scale-105 disabled:opacity-50">
                            <span x-show="!loading">Daftar</span>
                            <span x-show="loading">
                                <i class="fas fa-spinner fa-spin"></i> Mendaftar...
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Progress Input Form -->
            <div x-show="currentStep === 'progress'" class="space-y-6">
                <!-- Progress Check -->
                <div x-show="todayProgress" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-yellow-600 text-xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-medium text-yellow-800">Progress Hari Ini Sudah Disubmit</h3>
                            <p class="text-yellow-700">Anda sudah menginput progress untuk hari ini. Silakan kembali besok.</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Form -->
                <div x-show="!todayProgress">
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <div class="text-center mb-8">
                            <i class="fas fa-chart-bar text-blue-600 text-4xl mb-4"></i>
                            <h2 class="text-2xl font-bold text-gray-900">Input Progress Harian</h2>
                            <p class="text-gray-600 mt-2">Masukkan progress Anda untuk hari ini</p>
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
                                                    <label class="block text-sm font-medium text-gray-700 mb-2" x-text="itemName"></label>
                                                    <input type="number" 
                                                           x-model="progressData[itemName]" 
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                           placeholder="0"
                                                           min="0">
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
                                        <input type="file" 
                                               @change="handleFileUpload($event)"
                                               multiple 
                                               accept="image/*"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <p class="text-xs text-gray-500 mt-1">Maksimal 20 foto (JPG, PNG, GIF)</p>
                                    </div>
                                    <div x-show="photos.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <template x-for="(photo, index) in photos" :key="index">
                                            <div class="relative">
                                                <img :src="photo.preview" class="w-full h-24 object-cover rounded-lg">
                                                <button type="button" 
                                                        @click="removePhoto(index)"
                                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" 
                                        :disabled="loading"
                                        class="w-full bg-blue-600 text-white py-4 px-6 rounded-lg font-medium hover:bg-blue-700 transform transition-all duration-200 hover:scale-105 disabled:opacity-50">
                                    <span x-show="!loading">Submit Progress</span>
                                    <span x-show="loading">
                                        <i class="fas fa-spinner fa-spin"></i> Menyimpan...
                                    </span>
                                </button>
                            </div>
                        </form>
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
                        <div class="text-3xl font-bold text-blue-600 mb-2" x-text="Math.round(overallPercentage) + '%'"></div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div class="bg-blue-600 h-4 rounded-full transition-all duration-1000" 
                                 :style="'width: ' + Math.min(overallPercentage, 100) + '%'"></div>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 mb-6">
                        <p class="text-gray-700" x-text="resultMessage"></p>
                    </div>
                    <button @click="resetApp()" 
                            class="bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transform transition-all duration-200 hover:scale-105">
                        Input Progress Lagi Besok
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        function kpiApp() {
            return {
                currentStep: 'name',
                loading: false,
                userName: '',
                userEmail: '',
                userWhatsapp: '',
                currentUser: null,
                progressItems: {},
                progressData: {},
                photos: [],
                todayProgress: false,
                overallPercentage: 0,
                resultMessage: '',
                targetValue: 42,

                init() {
                    this.loadProgressItems();
                    this.checkTodayProgress();
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
                            this.currentUser = data.user;
                            this.currentStep = 'progress';
                            this.checkTodayProgress();
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
                                whatsapp: this.userWhatsapp
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.currentUser = data.user;
                            this.currentStep = 'progress';
                            this.checkTodayProgress();
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

                handleFileUpload(event) {
                    const files = Array.from(event.target.files);
                    
                    if (this.photos.length + files.length > 20) {
                        alert('Maksimal 20 foto');
                        return;
                    }

                    files.forEach(file => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.photos.push({
                                    file: file,
                                    preview: e.target.result
                                });
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                },

                removePhoto(index) {
                    this.photos.splice(index, 1);
                },

                async submitProgress() {
                    this.loading = true;
                    try {
                        const formData = new FormData();
                        formData.append('user_id', this.currentUser.id);
                        formData.append('items', JSON.stringify(this.progressData));
                        
                        this.photos.forEach((photo, index) => {
                            formData.append(`photos[${index}]`, photo.file);
                        });

                        const response = await fetch('/api/submit-progress', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.overallPercentage = data.overall_percentage;
                            this.resultMessage = data.message;
                            this.currentStep = 'result';
                        } else {
                            alert(data.message || 'Terjadi kesalahan saat menyimpan progress');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    } finally {
                        this.loading = false;
                    }
                },

                resetApp() {
                    this.currentStep = 'name';
                    this.userName = '';
                    this.userEmail = '';
                    this.userWhatsapp = '';
                    this.currentUser = null;
                    this.progressData = {};
                    this.photos = [];
                    this.todayProgress = false;
                    this.overallPercentage = 0;
                    this.resultMessage = '';
                },

                logout() {
                    this.resetApp();
                }
            }
        }
    </script>
</body>
</html>