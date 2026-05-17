<!DOCTYPE html>
<html>

<head>
    <title>Cek Ongkir</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <form id="ongkirForm">
        <select name="province" id="province">
            <option value="">Pilih Provinsi</option>
        </select>

        <select name="city" id="city">
            <option value="">Pilih Kota</option>
        </select>

        <input type="number" name="weight" id="weight" placeholder="Berat (gram)">

        <select name="courier" id="courier">
            <option value="">Pilih Kurir</option>
            <option value="jne">JNE</option>
            <option value="tiki">TIKI</option>
            <option value="pos">POS Indonesia</option>
        </select>

        <button type="submit">Cek Ongkir</button>
    </form>

    <div id="result"></div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function isOk(data) {
            return data?.meta?.code === 200;
        }

        // Load provinces
        document.addEventListener('DOMContentLoaded', function () {
            fetch('/provinces')
                .then(res => res.json())
                .then(data => {
                    if (!isOk(data)) return console.error('Gagal ambil provinsi:', data);

                    const select = document.getElementById('province');
                    data.data.forEach(province => {
                        const opt = new Option(province.name, province.id);
                        select.appendChild(opt);
                    });
                })
                .catch(err => console.error('Error provinces:', err));
        });

        // Load cities on province change
        document.getElementById('province').addEventListener('change', function () {
            fetch(`/cities?province_id=${this.value}`)
                .then(res => res.json())
                .then(data => {
                    if (!isOk(data)) return console.error('Gagal ambil kota:', JSON.stringify(data));
                    const select = document.getElementById('city');
                    select.innerHTML = '<option value="">Pilih Kota</option>';
                    data.data.forEach(city => {
                        const opt = new Option(city.name, city.id);
                        select.appendChild(opt);
                    });
                })
                .catch(err => console.error('Error cities:', err));
        });

        // Submit cek ongkir
        document.getElementById('ongkirForm').addEventListener('submit', function (event) {
            event.preventDefault();

            fetch('/cost', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    origin: 501,
                    destination: document.getElementById('city').value,
                    weight: document.getElementById('weight').value,
                    courier: document.getElementById('courier').value,
                }),
            })
                .then(res => res.json())
                .then(data => {
                    console.log('Cost data:', data); // sementara, buat cek struktur cost
                    if (!isOk(data)) return console.error('Gagal ambil ongkir:', data);

                    const resultDiv = document.getElementById('result');
                    resultDiv.innerHTML = '';
                    data.data.forEach(cost => {
                        const div = document.createElement('div');
                        div.textContent = `${cost.service} : Rp ${cost.cost} (${cost.etd} hari)`;
                        resultDiv.appendChild(div);
                    });
                })
                .catch(err => console.error('Error cost:', err));
        });
    </script>
</body>

</html>