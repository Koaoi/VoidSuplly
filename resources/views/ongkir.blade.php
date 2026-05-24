<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cek Ongkir - RajaOngkir Komerce</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        select, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover { background: #0056b3; }
        .result { margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px; display: none; }
        .result.show { display: block; }
        .cost-item {
            padding: 10px;
            margin: 10px 0;
            background: white;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .loading { text-align: center; padding: 20px; display: none; }
        .loading.show { display: block; }
        .error {
            padding: 10px;
            background: #fee;
            color: #c00;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none;
        }
        .error.show { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Cek Ongkos Kirim</h1>
        
        <div id="error" class="error"></div>
        <div id="loading" class="loading">⏳ Memuat data...</div>

        <div class="form-group">
            <label>Provinsi Asal (Jakarta Pusat)</label>
            <input type="text" value="Jakarta Pusat" disabled style="background: #f0f0f0;">
            <input type="hidden" id="origin" value="501">
        </div>

        <div class="form-group">
            <label>Provinsi Tujuan</label>
            <select id="province">
                <option value="">-- Pilih Provinsi --</option>
            </select>
        </div>

        <div class="form-group">
            <label>Kota/Kabupaten Tujuan</label>
            <select id="city" disabled>
                <option value="">-- Pilih Provinsi Dulu --</option>
            </select>
        </div>

        <div class="form-group">
            <label>Berat (gram)</label>
            <input type="number" id="weight" value="1000" min="1">
        </div>

        <div class="form-group">
            <label>Kurir</label>
            <select id="courier">
                <option value="jne">JNE</option>
                <option value="tiki">TIKI</option>
                <option value="pos">POS Indonesia</option>
                <option value="jnt">J&T Express</option>
                <option value="sicepat">SiCepat</option>
                <option value="anteraja">AnterAja</option>
            </select>
        </div>

        <button id="cekBtn">🔍 Cek Ongkir</button>

        <div id="result" class="result">
            <h3>📋 Hasil Ongkos Kirim</h3>
            <div id="resultContent"></div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function showLoading(show) {
            const loading = document.getElementById('loading');
            if (show) loading.classList.add('show');
            else loading.classList.remove('show');
        }

        function showError(msg) {
            const errorDiv = document.getElementById('error');
            errorDiv.textContent = msg;
            errorDiv.classList.add('show');
            setTimeout(() => errorDiv.classList.remove('show'), 5000);
        }

        // Load Provinces
        async function loadProvinces() {
            showLoading(true);
            try {
                const res = await fetch('/api/shipping/provinces');
                const data = await res.json();
                console.log('Provinces:', data);
                
                if (data.data && data.data.length) {
                    const select = document.getElementById('province');
                    data.data.forEach(prov => {
                        const option = document.createElement('option');
                        option.value = prov.id;
                        option.textContent = prov.name;
                        select.appendChild(option);
                    });
                }
            } catch (err) {
                console.error(err);
                showError('Gagal memuat provinsi');
            } finally {
                showLoading(false);
            }
        }

        // Load Cities when province changes
        document.getElementById('province').addEventListener('change', async function() {
            const provinceId = this.value;
            const provinceName = this.options[this.selectedIndex]?.text;
            const citySelect = document.getElementById('city');
            
            if (!provinceId) {
                citySelect.innerHTML = '<option value="">-- Pilih Provinsi Dulu --</option>';
                citySelect.disabled = true;
                return;
            }

            showLoading(true);
            citySelect.disabled = true;
            citySelect.innerHTML = '<option value="">⏳ Memuat kota...</option>';

            try {
                const res = await fetch(`/api/shipping/search?q=${encodeURIComponent(provinceName)}`);
                const data = await res.json();
                console.log('Cities:', data);
                
                if (data.data && data.data.length) {
                    const uniqueCities = new Map();
                    citySelect.innerHTML = '<option value="">-- Pilih Kota --</option>';
                    
                    data.data.forEach(city => {
                        const cityName = city.city_name;
                        if (!uniqueCities.has(cityName)) {
                            uniqueCities.set(cityName, city.id);
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = cityName;
                            citySelect.appendChild(option);
                        }
                    });
                    citySelect.disabled = false;
                } else {
                    citySelect.innerHTML = '<option value="">-- Tidak ada kota --</option>';
                }
            } catch (err) {
                console.error(err);
                citySelect.innerHTML = '<option value="">-- Gagal memuat kota --</option>';
                showError('Gagal memuat kota');
            } finally {
                showLoading(false);
            }
        });

        // Calculate Shipping Cost
        document.getElementById('cekBtn').addEventListener('click', async function() {
            const cityId = document.getElementById('city').value;
            const weight = document.getElementById('weight').value;
            const courier = document.getElementById('courier').value;
            const origin = document.getElementById('origin').value;

            if (!cityId) {
                showError('Pilih kota tujuan terlebih dahulu');
                return;
            }

            showLoading(true);
            const resultDiv = document.getElementById('result');
            const resultContent = document.getElementById('resultContent');
            resultDiv.classList.remove('show');

            try {
                const res = await fetch('/api/shipping/cost', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        origin: parseInt(origin),
                        destination: parseInt(cityId),
                        weight: parseInt(weight),
                        courier: courier
                    })
                });
                
                const data = await res.json();
                console.log('Cost:', data);
                
                resultContent.innerHTML = '';
                
                if (data.data && Object.keys(data.data).length > 0) {
                    let hasResults = false;
                    
                    for (const [courierName, services] of Object.entries(data.data)) {
                        if (services.costs && services.costs.length > 0) {
                            hasResults = true;
                            services.costs.forEach(cost => {
                                resultContent.innerHTML += `
                                    <div class="cost-item">
                                        <strong>${courierName.toUpperCase()} - ${cost.service}</strong><br>
                                        💰 Biaya: <strong>Rp ${formatNumber(cost.cost)}</strong><br>
                                        📅 Estimasi: ${cost.etd} hari<br>
                                        <small>${cost.description || ''}</small>
                                    </div>
                                `;
                            });
                        }
                    }
                    
                    if (!hasResults) {
                        resultContent.innerHTML = '<p style="color: #c33;">⚠️ Tidak ada layanan untuk kurir ini.</p>';
                    }
                    resultDiv.classList.add('show');
                } else {
                    resultContent.innerHTML = '<p style="color: #c33;">⚠️ Tidak ada layanan untuk kurir ini.</p>';
                    resultDiv.classList.add('show');
                }
            } catch (err) {
                console.error(err);
                showError('Gagal menghitung ongkos kirim');
            } finally {
                showLoading(false);
            }
        });

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Initialize
        loadProvinces();
    </script>
</body>
</html>