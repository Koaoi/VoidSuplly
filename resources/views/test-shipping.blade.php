<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cek Ongkir - RajaOngkir Komerce</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        /* Style untuk select dan input - DIPERKUAT */
        select, input {
            width: 100% !important;
            padding: 12px 15px !important;
            border: 2px solid #ddd !important;
            border-radius: 10px !important;
            font-size: 14px !important;
            background-color: #ffffff !important;
            color: #333333 !important;
            cursor: pointer !important;
            display: block !important;
        }

        /* Style khusus untuk dropdown options */
        select option {
            background-color: #ffffff !important;
            color: #333333 !important;
            padding: 12px !important;
            font-size: 14px !important;
            border: none !important;
        }

        /* Hover effect untuk option */
        select option:hover {
            background-color: #667eea !important;
            color: #ffffff !important;
        }

        /* Style untuk select yang disabled */
        select:disabled {
            background-color: #f0f0f0 !important;
            color: #999999 !important;
            cursor: not-allowed !important;
        }

        input:disabled {
            background-color: #f0f0f0 !important;
            color: #999999 !important;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
        }

        .result {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            display: none;
        }

        .result.show {
            display: block;
        }

        .result h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .cost-item {
            padding: 12px;
            margin-bottom: 10px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .cost-service {
            font-weight: bold;
            color: #667eea;
            font-size: 16px;
        }

        .cost-detail {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            color: #666;
        }

        .loading {
            text-align: center;
            padding: 20px;
            display: none;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .error.show {
            display: block;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>📦 Cek Ongkos Kirim</h1>
        
        <div id="error" class="error"></div>
        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p style="margin-top: 10px;">Memuat data...</p>
        </div>

        <form id="ongkirForm">
            <div class="form-group">
                <label>Provinsi Asal (Jakarta Pusat)</label>
                <input type="text" value="Jakarta Pusat" disabled>
                <input type="hidden" name="origin" id="origin" value="501">
            </div>

            <div class="form-group">
                <label>Provinsi Tujuan *</label>
                <select name="province" id="province" required>
                    <option value="">-- Pilih Provinsi --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Kota/Kabupaten Tujuan *</label>
                <select name="city" id="city" required disabled>
                    <option value="">-- Pilih Provinsi Terlebih Dahulu --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Berat (gram) *</label>
                <input type="number" name="weight" id="weight" placeholder="Contoh: 1000" required min="1" value="1000">
            </div>

            <div class="form-group">
                <label>Kurir *</label>
                <select name="courier" id="courier" required>
                    <option value="">-- Pilih Kurir --</option>
                    <option value="jne">JNE</option>
                    <option value="tiki">TIKI</option>
                    <option value="pos">POS Indonesia</option>
                    <option value="jnt">J&T Express</option>
                    <option value="sicepat">SiCepat</option>
                    <option value="anteraja">AnterAja</option>
                </select>
            </div>

            <button type="submit">Cek Ongkir →</button>
        </form>

        <div id="result" class="result"></div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const BASE_URL = '/api/shipping';

        function showLoading(show) {
            const loadingDiv = document.getElementById('loading');
            if (show) {
                loadingDiv.classList.add('show');
            } else {
                loadingDiv.classList.remove('show');
            }
        }

        function showError(message) {
            const errorDiv = document.getElementById('error');
            errorDiv.textContent = message;
            errorDiv.classList.add('show');
            setTimeout(() => {
                errorDiv.classList.remove('show');
            }, 5000);
        }

        function hideError() {
            const errorDiv = document.getElementById('error');
            errorDiv.classList.remove('show');
        }

        // Load provinces
        document.addEventListener('DOMContentLoaded', function () {
            showLoading(true);
            
            fetch(`${BASE_URL}/provinces`)
                .then(res => res.json())
                .then(data => {
                    console.log('Provinces response:', data);
                    
                    if (data.success && data.data) {
                        const select = document.getElementById('province');
                        select.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
                        
                        data.data.forEach(province => {
                            const opt = document.createElement('option');
                            opt.value = province.id;
                            opt.textContent = province.name;
                            select.appendChild(opt);
                        });
                        
                        console.log(`✅ ${data.data.length} provinsi dimuat`);
                    } else {
                        showError('Gagal memuat data provinsi');
                    }
                })
                .catch(err => {
                    console.error('Error provinces:', err);
                    showError('Gagal memuat data provinsi: ' + err.message);
                })
                .finally(() => showLoading(false));
        });

        // Load cities on province change
        document.getElementById('province').addEventListener('change', function () {
            const provinceId = this.value;
            const provinceName = this.options[this.selectedIndex]?.text;
            const citySelect = document.getElementById('city');
            
            if (!provinceId) {
                citySelect.innerHTML = '<option value="">-- Pilih Provinsi Terlebih Dahulu --</option>';
                citySelect.disabled = true;
                return;
            }

            showLoading(true);
            citySelect.disabled = true;
            citySelect.innerHTML = '<option value="">⏳ Memuat kota...</option>';
            hideError();

            fetch(`${BASE_URL}/destinations?search=${encodeURIComponent(provinceName)}`)
                .then(res => res.json())
                .then(data => {
                    console.log('Cities response:', data);
                    
                    if (data.success && data.data && data.data.length > 0) {
                        citySelect.innerHTML = '<option value="">-- Pilih Kota --</option>';
                        
                        data.data.forEach(city => {
                            const opt = document.createElement('option');
                            opt.value = city.id;
                            opt.textContent = city.name;
                            citySelect.appendChild(opt);
                        });
                        citySelect.disabled = false;
                        console.log(`✅ ${data.data.length} kota dimuat`);
                    } else {
                        citySelect.innerHTML = '<option value="">❌ Tidak ada kota</option>';
                        showError('Tidak ada kota untuk provinsi ini');
                    }
                })
                .catch(err => {
                    console.error('Error cities:', err);
                    showError('Gagal memuat data kota: ' + err.message);
                    citySelect.innerHTML = '<option value="">❌ Gagal memuat kota</option>';
                })
                .finally(() => showLoading(false));
        });

        // Submit cek ongkir
        document.getElementById('ongkirForm').addEventListener('submit', function (event) {
            event.preventDefault();
            
            const cityId = document.getElementById('city').value;
            const weight = document.getElementById('weight').value;
            const courier = document.getElementById('courier').value;
            
            if (!cityId) {
                showError('Silakan pilih kota tujuan terlebih dahulu');
                return;
            }
            
            if (!weight || weight < 1) {
                showError('Berat harus diisi minimal 1 gram');
                return;
            }
            
            if (!courier) {
                showError('Silakan pilih kurir');
                return;
            }

            showLoading(true);
            hideError();

            fetch(`${BASE_URL}/calculate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    origin: parseInt(document.getElementById('origin').value),
                    destination: parseInt(cityId),
                    weight: parseInt(weight),
                    courier: courier,
                }),
            })
                .then(res => res.json())
                .then(data => {
                    console.log('Cost data:', data);
                    
                    if (data.success && data.data) {
                        const resultDiv = document.getElementById('result');
                        resultDiv.innerHTML = '<h3>📋 Hasil Ongkos Kirim</h3>';
                        
                        const costs = data.data;
                        let hasResults = false;
                        
                        for (const [courierName, services] of Object.entries(costs)) {
                            if (services.costs && services.costs.length > 0) {
                                hasResults = true;
                                services.costs.forEach(cost => {
                                    const costDiv = document.createElement('div');
                                    costDiv.className = 'cost-item';
                                    costDiv.innerHTML = `
                                        <div class="cost-service">${courierName.toUpperCase()} - ${cost.service}</div>
                                        <div class="cost-detail">
                                            <span>💰 Biaya: <strong>Rp ${formatNumber(cost.cost)}</strong></span>
                                            <span>📅 Estimasi: ${cost.etd} hari</span>
                                        </div>
                                        <div style="font-size: 12px; color: #888; margin-top: 5px;">${cost.description || ''}</div>
                                    `;
                                    resultDiv.appendChild(costDiv);
                                });
                            }
                        }
                        
                        if (!hasResults) {
                            resultDiv.innerHTML += '<p style="color: #c33;">⚠️ Tidak ada layanan untuk kurir ini.</p>';
                        }
                        
                        resultDiv.classList.add('show');
                    } else {
                        showError(data.message || 'Gagal menghitung ongkos kirim');
                    }
                })
                .catch(err => {
                    console.error('Error cost:', err);
                    showError('Gagal menghitung ongkos kirim: ' + err.message);
                })
                .finally(() => showLoading(false));
        });

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }
    </script>
</body>
</html>