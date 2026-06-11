<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Placard Lapak Tenant</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm;
            /* Margin sedikit dirapatkan agar proporsional */
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Helvetica", "Arial", sans-serif;
            color: #1e293b;
            background-color: #ffffff;
        }

        /* PERUBAHAN UTAMA DI SINI */
        .placard-container {
            /* Kunci posisi absolut agar tidak menembus halaman kedua */
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;

            border: 6px double #1e3a8a;
            border-radius: 20px;
            padding: 30px;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .institution {
            font-size: 14pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .title {
            font-size: 22pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            margin-top: 10px;
            letter-spacing: 1px;
        }

        .main-layout {
            width: 100%;
            border-collapse: collapse;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
        }

        .content-table td {
            padding: 12px 10px;
            vertical-align: middle;
        }

        .label {
            font-size: 14pt;
            color: #64748b;
            font-weight: bold;
            width: 35%;
        }

        .value {
            font-size: 14pt;
            color: #0f172a;
            font-weight: bold;
            width: 65%;
        }

        .qr-section {
            text-align: center;
        }

        .qr-box {
            display: inline-block;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background-color: #ffffff;
        }

        .qr-instruction {
            font-size: 11pt;
            color: #64748b;
            margin-top: 15px;
            font-weight: bold;
        }

        .footer {
            position: absolute;
            bottom: 25px;
            left: 30px;
            right: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }

        .tagline {
            font-size: 13pt;
            font-style: italic;
            color: #1e3a8a;
            font-weight: bold;
        }

        .validation-text {
            font-size: 9pt;
            color: #94a3b8;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="placard-container">
        <div class="header">
            <div class="institution">
                Badan Pengelola Usaha (BPU) Universitas Lampung
            </div>
            <div class="title">Tanda Izin Penempatan Lapak</div>
        </div>

        <table class="main-layout">
            <tr>
                <td style="width: 65%; vertical-align: top;">
                    <table class="content-table">
                        <tr>
                            <td class="label">Nama Booth / Usaha</td>
                            <td class="value">
                                : {{ strtoupper($rent->business_name) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Nama Penyewa</td>
                            <td class="value">: {{ $rent->user->name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Kode Penyewaan</td>
                            <td class="value">
                                : {{ $rent->id . '-' . $rent->slot->slot_number }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Masa Berlaku</td>
                            <td class="value">
                                : {{ \Carbon\Carbon::parse($rent->start_date)->format('d M Y') }} s.d. {{ \Carbon\Carbon::parse($rent->end_date)->format('d M Y') }}
                            </td>
                        </tr>
                    </table>
                </td>

                <td style="width: 35%; vertical-align: top; text-align: right;">
                    <div class="qr-section">
                        <div class="qr-box">
                            <img
                                src="data:image/svg+xml;base64, {!! $qrCode !!}"
                                style="width: 160px; height: 160px" />
                            <div class="qr-instruction">
                                Pindai QR Code untuk<br>Verifikasi Keaslian Lapak
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            <div class="tagline">Wajah Digital untuk Bisnis Profesional</div>
            <div class="validation-text">
                Diterbitkan secara sah oleh Sistem Informasi Manajemen Lapak BPU Unila.
            </div>
        </div>
    </div>
</body>

</html>