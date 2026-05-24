<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Produk</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 20px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .title { 
            font-size: 18px; 
            font-weight: bold; 
            margin-bottom: 5px;
        }
        .subtitle { 
            font-size: 14px; 
            color: #666; 
            margin-top: 5px; 
        }
        .summary { 
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .summary-box {
            border: 1px solid #ddd;
            padding: 10px;
            width: 23%;
            text-align: center;
            border-radius: 8px;
        }
        .summary-box .label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        .summary-box .value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f5f5f5; 
            font-weight: bold; 
        }
        .text-center { 
            text-align: center; 
        }
        .text-right { 
            text-align: right; 
        }
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 10px; 
            color: #999; 
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 10px;
        }
        .badge-green { background: #e8f5e9; color: #2e7d32; }
        .badge-yellow { background: #fff3e0; color: #e65100; }
        .badge-red { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN PRODUK</div>
        <div class="subtitle">Per {{ date('d/m/Y') }}</div>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total Produk</div>
            <div class="value">{{ number_format($totalProducts) }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Total Stok</div>
            <div class="value">{{ number_format($totalStock) }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Nilai Inventaris</div>
            <div class="value">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Stok Menipis</div>
            <div class="value">{{ number_format($lowStockProducts ?? 0) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Kategori</th>
                <th class="text-right">Harga</th>
                <th class="text-center">Stok</th>
                <th class="text-center">Terjual</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($product->stock) }}</td>
                <td class="text-center">{{ number_format($product->total_sold ?? 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>