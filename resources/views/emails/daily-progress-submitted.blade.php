<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian KPI</title>
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
            padding: 30px;
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

        .content {
            padding: 30px;
        }

        .greeting {
            font-size: 18px;
            color: #4a5568;
            margin-bottom: 25px;
        }

        .success-message {
            background-color: #f0fff4;
            border-left: 4px solid #48bb78;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 5px;
        }

        .success-message h3 {
            color: #2d7d32;
            margin: 0 0 10px 0;
            font-size: 16px;
        }

        .success-message p {
            color: #2d7d32;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e9ecef;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .progress-items {
            margin: 25px 0;
        }

        .progress-items h3 {
            color: #4a5568;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .progress-item {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }

        .progress-item h4 {
            margin: 0 0 8px 0;
            color: #2d3748;
            font-size: 16px;
        }

        .progress-item p {
            margin: 0;
            color: #718096;
            font-size: 14px;
        }

        .progress-bar {
            background-color: #e2e8f0;
            border-radius: 10px;
            height: 8px;
            margin-top: 10px;
            overflow: hidden;
        }

        .progress-bar-fill {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .feedback-message {
            background-color: #e6f3ff;
            border-left: 4px solid #3182ce;
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
        }

        .feedback-message h3 {
            color: #2a69ac;
            margin: 0 0 10px 0;
            font-size: 16px;
        }

        .feedback-message p {
            color: #2a69ac;
            margin: 0;
            line-height: 1.6;
        }

        .attachment-info {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 25px 0;
            border-radius: 5px;
        }

        .attachment-info h3 {
            color: #856404;
            margin: 0 0 10px 0;
            font-size: 16px;
        }

        .attachment-info p {
            color: #856404;
            margin: 0;
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
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
        }

        .date-badge {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 10px;
            display: inline-block;
        }

        @media (max-width: 600px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 Laporan Harian KPI</h1>
            <p>Report Daily Helper - Philip Morris International</p>
            <div class="date-badge">
                {{ $dailyProgress->created_at->format('d F Y') }}
            </div>
        </div>

        <div class="content">
            <div class="greeting">
                Halo <strong>{{ $user->name }}</strong>! 👋
            </div>

            <div class="success-message">
                <h3>✅ Laporan Berhasil Dikirim</h3>
                <p>Terima kasih telah mengirimkan laporan harian KPI Anda pada {{ $dailyProgress->created_at->format('d
                    F Y') }}. Laporan Anda telah berhasil diproses dan disimpan dalam sistem.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">{{ number_format($overallPercentage, 1) }}%</div>
                    <div class="stat-label">Performa Keseluruhan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $dailyProgress->progressItems->count() }}</div>
                    <div class="stat-label">Total KPI Item</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $dailyProgress->progressItems->where('percentage', '>=', 100)->count()
                        }}</div>
                    <div class="stat-label">Target Tercapai</div>
                </div>
            </div>

            <div class="progress-items">
                <h3>📈 Detail KPI Hari Ini</h3>
                @foreach($dailyProgress->progressItems as $item)
                <div class="progress-item">
                    <h4>{{ $item->kpi_item }}</h4>
                    <p><strong>Target:</strong> {{ number_format($item->target) }} {{ $item->unit }} |
                        <strong>Aktual:</strong> {{ number_format($item->actual) }} {{ $item->unit }}
                    </p>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: {{ min($item->percentage, 100) }}%"></div>
                    </div>
                    <p style="margin-top: 8px; font-size: 13px;">{{ number_format($item->percentage, 1) }}% dari target
                    </p>
                </div>
                @endforeach
            </div>

            <div class="feedback-message">
                <h3>💬 Pesan Feedback</h3>
                <p>{{ $message }}</p>
            </div>

            @if($pdfPath && file_exists($pdfPath))
            <div class="attachment-info">
                <h3>📎 Lampiran</h3>
                <p>Laporan harian Anda dalam format PDF telah dilampirkan pada email ini. Anda dapat mengunduh dan
                    menyimpannya sebagai dokumentasi.</p>
            </div>
            @endif
        </div>

        <div class="footer">
            <p>
                Email otomatis dari sistem Report Daily Helper - Philip Morris International<br>
                Terkirim pada {{ now()->format('d F Y, H:i') }} WIB
            </p>
            <p style="margin-top: 10px;">
                Jika ada pertanyaan, hubungi tim support di
                <a href="mailto:support@sampoerna.com">support@sampoerna.com</a>
            </p>
        </div>
    </div>
</body>

</html>
