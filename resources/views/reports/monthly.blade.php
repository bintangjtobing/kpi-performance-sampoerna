<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Progress Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2563eb;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .user-info {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .user-info h2 {
            margin: 0 0 10px 0;
            color: #374151;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-card {
            background-color: #dbeafe;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-card h3 {
            margin: 0 0 10px 0;
            color: #1e40af;
            font-size: 14px;
        }
        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .summary-card .unit {
            font-size: 12px;
            color: #6b7280;
        }
        .category-summary {
            margin-bottom: 20px;
        }
        .category-summary h3 {
            background-color: #374151;
            color: white;
            padding: 10px;
            margin: 0 0 10px 0;
            border-radius: 4px;
        }
        .category-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .category-table th,
        .category-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }
        .category-table th {
            background-color: #f9fafb;
            font-weight: bold;
        }
        .category-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .percentage {
            text-align: right;
            font-weight: bold;
        }
        .percentage.good {
            color: #16a34a;
        }
        .percentage.average {
            color: #d97706;
        }
        .percentage.poor {
            color: #dc2626;
        }
        .daily-progress {
            margin-top: 20px;
        }
        .daily-progress h3 {
            color: #374151;
            margin-bottom: 10px;
        }
        .daily-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .daily-table th,
        .daily-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: center;
        }
        .daily-table th {
            background-color: #f9fafb;
            font-weight: bold;
        }
        .daily-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #d1d5db;
            color: #6b7280;
            font-size: 10px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Monthly Progress Report</h1>
        <p>Performance Sampoerna KPI</p>
        <p>Period: {{ $period }}</p>
        <p>Generated on {{ date('d F Y H:i:s') }}</p>
    </div>

    <div class="user-info">
        <h2>User Information</h2>
        <p><strong>Name:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Username:</strong> {{ $user->username }}</p>
        <p><strong>Period:</strong> {{ $period }}</p>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <h3>Completion Rate</h3>
            <div class="value">{{ $summary['completion_rate'] }}</div>
            <div class="unit">%</div>
        </div>
        <div class="summary-card">
            <h3>Average Performance</h3>
            <div class="value">{{ $summary['average_performance'] }}</div>
            <div class="unit">%</div>
        </div>
        <div class="summary-card">
            <h3>Progress Days</h3>
            <div class="value">{{ $summary['progress_days'] }}</div>
            <div class="unit">/ {{ $summary['total_days'] }} days</div>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <h3>Best Performance</h3>
            <div class="value">{{ $summary['best_performance'] }}</div>
            <div class="unit">%</div>
        </div>
        <div class="summary-card">
            <h3>Worst Performance</h3>
            <div class="value">{{ $summary['worst_performance'] }}</div>
            <div class="unit">%</div>
        </div>
        <div class="summary-card">
            <h3>Status</h3>
            <div class="value" style="font-size: 14px;">
                @if($summary['average_performance'] >= 70)
                    <span style="color: #16a34a;">EXCELLENT</span>
                @elseif($summary['average_performance'] >= 50)
                    <span style="color: #d97706;">GOOD</span>
                @else
                    <span style="color: #dc2626;">NEEDS IMPROVEMENT</span>
                @endif
            </div>
        </div>
    </div>

    <div class="category-summary">
        <h3>Category Performance Summary</h3>
        <table class="category-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Total Target</th>
                    <th>Total Actual</th>
                    <th>Achievement Rate</th>
                    <th>Average Performance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($category_summary as $category => $data)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ $data['total_target'] }}</td>
                    <td>{{ $data['total_actual'] }}</td>
                    <td class="percentage {{ $data['achievement_rate'] >= 70 ? 'good' : ($data['achievement_rate'] >= 50 ? 'average' : 'poor') }}">
                        {{ $data['achievement_rate'] }}%
                    </td>
                    <td class="percentage {{ $data['average_percentage'] >= 70 ? 'good' : ($data['average_percentage'] >= 50 ? 'average' : 'poor') }}">
                        {{ $data['average_percentage'] }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    <div class="daily-progress">
        <h3>Daily Progress Detail</h3>
        <table class="daily-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Overall %</th>
                    <th>Items Count</th>
                    <th>Photos</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daily_progress as $progress)
                <tr>
                    <td>{{ $progress->progress_date->format('d/m/Y') }}</td>
                    <td>{{ $progress->progress_date->format('l') }}</td>
                    <td class="percentage {{ $progress->overall_percentage >= 70 ? 'good' : ($progress->overall_percentage >= 50 ? 'average' : 'poor') }}">
                        {{ number_format($progress->overall_percentage, 2) }}%
                    </td>
                    <td>{{ $progress->progressItems->count() }}</td>
                    <td>{{ $progress->photos ? count($progress->photos) : 0 }}</td>
                    <td>
                        @if($progress->overall_percentage >= 70)
                            <span style="color: #16a34a;">GOOD</span>
                        @elseif($progress->overall_percentage >= 50)
                            <span style="color: #d97706;">AVERAGE</span>
                        @else
                            <span style="color: #dc2626;">POOR</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This report was generated automatically by Performance Sampoerna KPI System</p>
        <p>Report covers {{ $summary['progress_days'] }} days of progress data for {{ $period }}</p>
        <p>Generated on {{ date('d F Y H:i:s') }}</p>
    </div>
</body>
</html>