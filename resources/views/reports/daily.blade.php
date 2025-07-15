<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Progress Report</title>
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
        .performance-summary {
            background-color: #dbeafe;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .performance-summary h2 {
            margin: 0 0 10px 0;
            color: #1e40af;
        }
        .performance-percentage {
            font-size: 36px;
            font-weight: bold;
            color: #2563eb;
            margin: 10px 0;
        }
        .category {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .category h3 {
            background-color: #374151;
            color: white;
            padding: 10px;
            margin: 0 0 10px 0;
            border-radius: 4px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f9fafb;
            font-weight: bold;
        }
        .items-table tr:nth-child(even) {
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
        .photos-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .photos-section h3 {
            color: #374151;
            margin-bottom: 10px;
        }
        .photos-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        .photo-item {
            text-align: center;
            border: 1px solid #d1d5db;
            padding: 5px;
            border-radius: 4px;
        }
        .photo-item img {
            max-width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #d1d5db;
            color: #6b7280;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Progress Report</h1>
        <p>Performance Sampoerna KPI</p>
        <p>Generated on {{ date('d F Y H:i:s') }}</p>
    </div>

    <div class="user-info">
        <h2>User Information</h2>
        <p><strong>Name:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Username:</strong> {{ $user->username }}</p>
        <p><strong>Date:</strong> {{ $date }}</p>
    </div>

    <div class="performance-summary">
        <h2>Overall Performance</h2>
        <div class="performance-percentage">{{ number_format($progress->overall_percentage, 2) }}%</div>
        <p>Target: 70% | Status: 
            @if($progress->overall_percentage >= 70)
                <span style="color: #16a34a; font-weight: bold;">ACHIEVED</span>
            @elseif($progress->overall_percentage >= 50)
                <span style="color: #d97706; font-weight: bold;">GOOD</span>
            @else
                <span style="color: #dc2626; font-weight: bold;">NEEDS IMPROVEMENT</span>
            @endif
        </p>
    </div>

    @foreach($grouped_items as $category => $items)
    <div class="category">
        <h3>{{ $category }}</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Target</th>
                    <th>Actual</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->target_value }}</td>
                    <td>{{ $item->actual_value }}</td>
                    <td class="percentage {{ $item->percentage >= 70 ? 'good' : ($item->percentage >= 50 ? 'average' : 'poor') }}">
                        {{ number_format($item->percentage, 2) }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    @if((isset($photos) && count($photos) > 0) || ($progress->photos && count($progress->photos) > 0))
    <div class="photos-section">
        <h3>Documentation Photos ({{ isset($photos) ? count($photos) : count($progress->photos) }} photos)</h3>
        <div class="photos-grid">
            @if(isset($photos) && count($photos) > 0)
                @foreach($photos as $index => $photoPath)
                <div class="photo-item">
                    <img src="{{ $photoPath }}" alt="Photo {{ $index + 1 }}">
                    <p>Photo {{ $index + 1 }}</p>
                </div>
                @endforeach
            @else
                @foreach($progress->photos as $index => $photo)
                <div class="photo-item">
                    <img src="{{ $photo }}" alt="Photo {{ $index + 1 }}">
                    <p>Photo {{ $index + 1 }}</p>
                </div>
                @endforeach
            @endif
        </div>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by Performance Sampoerna KPI System</p>
        <p>Generated on {{ date('d F Y H:i:s') }}</p>
    </div>
</body>
</html>