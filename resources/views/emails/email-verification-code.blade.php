<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Email</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .lock-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .content {
            padding: 40px 30px;
            text-align: center;
        }

        .greeting {
            font-size: 18px;
            color: #4a5568;
            margin-bottom: 25px;
        }

        .verification-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            position: relative;
        }

        .verification-section::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            z-index: -1;
        }

        .verification-title {
            font-size: 20px;
            color: #2d3748;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .verification-code {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 8px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .code-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            border: 2px dashed #667eea;
            display: inline-block;
            min-width: 300px;
        }

        .copy-hint {
            font-size: 14px;
            color: #718096;
            margin-top: 15px;
            font-style: italic;
        }

        .instructions {
            background-color: #e6f3ff;
            border-left: 4px solid #3182ce;
            padding: 20px;
            margin: 30px 0;
            border-radius: 5px;
            text-align: left;
        }

        .instructions h3 {
            color: #2a69ac;
            margin: 0 0 15px 0;
            font-size: 18px;
        }

        .instructions ul {
            margin: 0;
            padding-left: 20px;
        }

        .instructions li {
            color: #2a69ac;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .expiry-warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 25px 0;
            border-radius: 5px;
        }

        .expiry-warning h3 {
            color: #856404;
            margin: 0 0 10px 0;
            font-size: 16px;
        }

        .expiry-warning p {
            color: #856404;
            margin: 0;
            font-size: 14px;
        }

        .security-note {
            background-color: #f0fff4;
            border-left: 4px solid #48bb78;
            padding: 15px;
            margin: 25px 0;
            border-radius: 5px;
        }

        .security-note h3 {
            color: #2d7d32;
            margin: 0 0 10px 0;
            font-size: 16px;
        }

        .security-note p {
            color: #2d7d32;
            margin: 0;
            font-size: 14px;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 25px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }

        .footer p {
            margin: 0;
            line-height: 1.6;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
        }

        .highlight {
            background: linear-gradient(120deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: bold;
        }

        @media (max-width: 600px) {
            .content {
                padding: 20px;
            }

            .verification-code {
                font-size: 36px;
                letter-spacing: 4px;
            }

            .code-container {
                min-width: 250px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="lock-icon">🔐</div>
            <h1>Verifikasi Email</h1>
            <p>Report Daily Helper - Philip Morris International</p>
        </div>

        <div class="content">
            <div class="greeting">
                @if($userName)
                Halo <strong>{{ $userName }}</strong>! 👋
                @else
                Halo! 👋
                @endif
            </div>

            <p style="font-size: 16px; color: #4a5568; margin-bottom: 30px;">
                Terima kasih telah mendaftar di sistem Report Daily Helper - Philip Morris International. Untuk
                melanjutkan proses registrasi, silakan
                verifikasi email Anda dengan kode di bawah ini.
            </p>

            <div class="verification-section">
                <div class="verification-title">
                    🎯 Kode Verifikasi Anda
                </div>

                <div class="code-container">
                    <div class="verification-code">{{ $verificationCode }}</div>
                    <div class="copy-hint">
                        💡 Klik untuk menyalin kode ini
                    </div>
                </div>
            </div>

            <div class="instructions">
                <h3>📋 Cara Menggunakan Kode Verifikasi</h3>
                <ul>
                    <li>Salin kode <span class="highlight">{{ $verificationCode }}</span> di atas</li>
                    <li>Kembali ke halaman registrasi</li>
                    <li>Masukkan kode pada kolom verifikasi yang tersedia</li>
                    <li>Klik tombol "Verifikasi Email" untuk menyelesaikan proses</li>
                </ul>
            </div>

            <div class="expiry-warning">
                <h3>⏰ Penting!</h3>
                <p>Kode verifikasi ini akan <strong>kadaluarsa dalam {{ $expiryMinutes }} menit</strong>. Pastikan Anda
                    segera melakukan verifikasi sebelum waktu habis.</p>
            </div>

            <div class="security-note">
                <h3>🔒 Keamanan</h3>
                <p>Jangan bagikan kode verifikasi ini kepada siapa pun. Kode ini bersifat rahasia dan hanya untuk Anda
                    gunakan dalam proses verifikasi email.</p>
            </div>
        </div>

        <div class="footer">
            <p>
                <strong>Jika Anda tidak melakukan registrasi ini, abaikan email ini.</strong><br>
                Email otomatis dari sistem Report Daily Helper - Philip Morris International<br>
                Terkirim pada {{ now()->format('d F Y, H:i') }} WIB
            </p>
            <p style="margin-top: 15px;">
                Butuh bantuan? Hubungi tim support di
                <a href="mailto:support@sampoerna.com">support@sampoerna.com</a>
            </p>
        </div>
    </div>

    <script>
        // Auto-copy code when clicked
        document.addEventListener('DOMContentLoaded', function() {
            const codeContainer = document.querySelector('.code-container');
            const verificationCode = '{{ $verificationCode }}';

            if (codeContainer) {
                codeContainer.style.cursor = 'pointer';
                codeContainer.addEventListener('click', function() {
                    navigator.clipboard.writeText(verificationCode).then(function() {
                        const copyHint = document.querySelector('.copy-hint');
                        const originalText = copyHint.textContent;
                        copyHint.textContent = '✅ Kode berhasil disalin!';
                        copyHint.style.color = '#2d7d32';

                        setTimeout(function() {
                            copyHint.textContent = originalText;
                            copyHint.style.color = '#718096';
                        }, 2000);
                    });
                });
            }
        });
    </script>
</body>

</html>
