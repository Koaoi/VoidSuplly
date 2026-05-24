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

        select, input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            background: white;
            color: #333;
            cursor: pointer;
        }

        select:focus, input:focus {
            outline: none;
            border-color: #667eea;
        }

        select:disabled {
            background: #f5f5f5;
            color: #999;
            cursor: not-allowed;
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
                <label>Kecamatan Tujuan *</label>
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
                    if (data.success && data.data) {
                        const select = document.getElementById('province');
                        select.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
                        
                        data.data.forEach(province => {
                            const opt = document.createElement('option');
                            opt.value = province.id;
                            opt.textContent = province.name;
                            select.appendChild(opt);
                        });
                    } else {
                        showError('Gagal memuat data provinsi');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showError('Gagal memuat data provinsi: ' + err.message);
                })
                .finally(() => showLoading(false));
        });

        // Load cities/districts on province change
        document.getElementById('province').addEventListener('change', function () {
            const provinceId = this.value;
            const citySelect = document.getElementById('city');
            
            if (!provinceId) {
                citySelect.innerHTML = '<option value="">-- Pilih Provinsi Terlebih Dahulu --</option>';
                citySelect.disabled = true;
                return;
            }

            showLoading(true);
            citySelect.disabled = true;
            citySelect.innerHTML = '<option value="">⏳ Memuat kecamatan...</option>';
            hideError();

            // Diubah menggunakan parameter ID provinsi agar pencarian endpoint tujuan lebih akurat
            fetch(`${BASE_URL}/destinations?province_id=${provinceId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        citySelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                        
                        data.data.forEach(city => {
                            const opt = document.createElement('option');
                            opt.value = city.id;
                            
                            // fallback penamaan jika properti API berbeda
                            const district = city.subdistrict_name || city.district_name || city.city_name;
                            const type = city.type ? `(${city.type})` : '';
                            opt.textContent = `${district} ${type}`;
                            
                            citySelect.appendChild(opt);
                        });
                        citySelect.disabled = false;
                    } else {
                        citySelect.innerHTML = '<option value="">❌ Tidak ada kecamatan</option>';
                        showError('Tidak ada data kecamatan untuk provinsi ini');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showError('Gagal memuat data kecamatan: ' + err.message);
                    citySelect.innerHTML = '<option value="">❌ Gagal memuat kecamatan</option>';
                })
                .finally(() => showLoading(false));
        });

        // Submit cek ongkir
        document.getElementById('ongkirForm').addEventListener('submit', function (event) {
            event.preventDefault();
            
            const cityId = document.getElementById('city').value;
            const weight = document.getElementById('weight').value;
            const courier = document.getElementById('courier').value;
            const resultDiv = document.getElementById('result');
            
            if (!cityId) {
                showError('Silakan pilih kecamatan tujuan terlebih dahulu');
                return;
            }
            
            showLoading(true);
            hideError();
            resultDiv.classList.remove('show');

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
                    if (data.success && data.data) {
                        resultDiv.innerHTML = '<h3>📋 Hasil Ongkos Kirim</h3>';
                        const responseData = data.data;
                        let hasResults = false;

                        // Pengecekan flexibel untuk mendeteksi tipe Array (RajaOngkir standar) atau Object (Komerce Custom)
                        const costsArray = Array.isArray(responseData) ? responseData : Object.values(responseData);

                        costsArray.forEach(courierData => {
                            const code = (courierData.code || courier).toUpperCase();
                            const services = courierData.costs || [];

                            services.forEach(serviceItem => {
                                hasResults = true;
                                const detail = serviceItem.cost[0] || serviceItem; // mengantisipasi struktur nested cost ala rajaongkir
                                
                                const costDiv = document.createElement('div');
                                costDiv.className = 'cost-item';
                                costDiv.innerHTML = `
                                    <div class="cost-service">${code} - ${serviceItem.service || serviceItem.nama_layanan}</div>
                                    <div class="cost-detail">
                                        <span>💰 Biaya: <strong>Rp ${formatNumber(detail.value || detail.cost)}</strong></span>
                                        <span>📅 Estimasi: ${detail.etd || '-'} hari</span>
                                    </div>
                                    <div style="font-size: 12px; color: #888; margin-top: 5px;">${serviceItem.description || ''}</div>
                                `;
                                resultDiv.appendChild(costDiv);
                            });
                        });
                        
                        if (!hasResults) {
                            resultDiv.innerHTML += '<p style="color: #c33;">⚠️ Tidak ada layanan pengiriman yang tersedia untuk rute ini.</p>';
                        }
                        
                        resultDiv.classList.add('show');
                    } else {
                        showError(data.message || 'Gagal menghitung ongkos kirim');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
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