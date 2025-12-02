<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Riwayat Donasi - {{ $donor->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #0F766E;
        }
        
        .header h1 {
            color: #0F766E;
            font-size: 18pt;
            margin-bottom: 5px;
        }
        
        .header h2 {
            color: #64748B;
            font-size: 14pt;
            font-weight: normal;
            margin-bottom: 3px;
        }
        
        .header p {
            color: #9CA3AF;
            font-size: 9pt;
        }
        
        .donor-info {
            background-color: #F9FAFB;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #0F766E;
        }
        
        .donor-info p {
            margin: 5px 0;
            font-size: 10pt;
        }
        
        .donor-info strong {
            color: #0F766E;
            display: inline-block;
            width: 80px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        thead {
            background-color: #0F766E;
            color: white;
        }
        
        thead th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #0F766E;
        }
        
        tbody td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 9pt;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .type-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            text-align: center;
        }
        
        .type-nasi {
            background-color: #E6FFEA;
            color: #065F46;
        }
        
        .type-snack {
            background-color: #E0F2FF;
            color: #075985;
        }
        
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #F9FAFB;
            border-radius: 5px;
            border-left: 4px solid #0F766E;
        }
        
        .summary p {
            margin: 5px 0;
            font-size: 10pt;
        }
        
        .summary strong {
            color: #0F766E;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8pt;
            color: #9CA3AF;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        /* Page break handling */
        table {
            page-break-inside: auto;
        }
        
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        thead {
            display: table-header-group;
        }
        
        tfoot {
            display: table-footer-group;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Riwayat Donasi</h1>
        <h2>{{ $donor->name }}</h2>
        <p>Masjid An-Nur - Takjil Manager</p>
    </div>
    
    <div class="donor-info">
        <p><strong>WhatsApp:</strong> {{ $donor->whatsapp }}</p>
        <p><strong>Alamat:</strong> {{ $donor->address ?: '-' }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 40%;">Tanggal</th>
                <th style="width: 15%;" class="text-center">Tipe</th>
                <th style="width: 15%;" class="text-center">Jumlah</th>
                <th style="width: 22%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($donations as $index => $donation)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    @if($donation->date)
                        @php
                            $dateObj = \Carbon\Carbon::parse($donation->date);
                            $dayName = $dateObj->locale('id')->isoFormat('dddd');
                            $dateFormatted = $dateObj->locale('id')->isoFormat('D MMM YYYY');
                        @endphp
                        {{ $dayName }}, {{ $dateFormatted }}
                    @else
                        <em style="color: #9CA3AF;">Belum dijadwalkan</em>
                    @endif
                </td>
                <td class="text-center">
                    <span class="type-badge type-{{ $donation->type }}">
                        {{ strtoupper($donation->type) }}
                    </span>
                </td>
                <td class="text-center font-bold">{{ $donation->quantity }} Box</td>
                <td style="font-size: 8pt;">{{ $donation->description ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada data donasi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="summary">
        <p><strong>Total Donasi:</strong> {{ $donations->count() }} kali</p>
        <p><strong>Total Nasi:</strong> {{ $totalNasi }} Box</p>
        <p><strong>Total Snack:</strong> {{ $totalSnack }} Box</p>
    </div>
    
    <div class="footer">
        <p>Dicetak pada: {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') }} WIB</p>
    </div>
</body>
</html>
