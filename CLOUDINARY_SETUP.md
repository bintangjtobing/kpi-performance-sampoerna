# Cloudinary Setup untuk Upload Foto Dokumentasi

## Cara Setup Cloudinary

### 1. Daftar ke Cloudinary
- Buka https://cloudinary.com/
- Sign up atau login
- Catat Cloud Name, API Key, dan API Secret dari dashboard

### 2. Buat Upload Preset
- Masuk ke Settings > Upload
- Klik "Add upload preset"
- Atur:
  - Upload preset name: `kpi-performance-sampoerna`
  - Signing Mode: `Unsigned`
  - Folder: `kpi-performance-sampoerna`
  - Allowed formats: `jpg,jpeg,png,gif,webp`
  - Max file size: tidak perlu dibatasi (sudah handled oleh Cloudinary)
  - Transformation: optional, bisa dikosongkan
- Save preset

### 3. Update Environment Variables
Tambahkan ke file `.env`:
```
CLOUDINARY_CLOUD_NAME=your_cloud_name_here
CLOUDINARY_API_KEY=your_api_key_here
CLOUDINARY_API_SECRET=your_api_secret_here
CLOUDINARY_UPLOAD_PRESET=kpi-performance-sampoerna
CLOUDINARY_FOLDER=kpi-performance-sampoerna
CLOUDINARY_SECURE=true
```

### 4. Fitur yang Sudah Diimplementasikan
- ✅ Upload langsung ke Cloudinary tanpa melalui server
- ✅ Tidak ada batasan ukuran file
- ✅ Multiple file upload (maksimal 20 foto)
- ✅ Preview foto sebelum submit
- ✅ UI yang user-friendly dengan Cloudinary Upload Widget
- ✅ Auto-transformation dan optimization dari Cloudinary
- ✅ Secure HTTPS URLs

### 5. Keuntungan Menggunakan Cloudinary
- 🚀 Upload langsung ke cloud, tidak membebani server
- 📱 Responsive dan mobile-friendly
- 🔒 Secure URLs dengan HTTPS
- 📊 Auto-optimization untuk performa
- 🌐 CDN global untuk akses cepat
- 💾 Tidak ada batasan storage di server

### 6. Testing
1. Pastikan environment variables sudah diset
2. Reload aplikasi
3. Coba upload foto melalui form progress
4. Periksa di Cloudinary dashboard apakah foto sudah terupload

### 7. Troubleshooting
- Jika upload gagal: Cek upload preset sudah dibuat dan mode `Unsigned`
- Jika tidak muncul widget: Cek Cloud Name dan Upload Preset di environment
- Jika preview tidak muncul: Cek secure_url di response Cloudinary