<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Target - Ringkasan Operasional</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-target: #4cc9f0; 
            --bg-light: #f8fafc;
            --glass: rgba(255, 255, 255, 0.9);
            --slate-600: #475569;
        }

        body { 
            background-color: var(--bg-light); 
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #334155;
        }

        /* Tombol Kembali Styling */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--slate-600);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 20px;
            transition: 0.3s;
            padding: 8px 12px;
            border-radius: 10px;
        }
        .btn-back:hover {
            background: rgba(0,0,0,0.05);
            color: #000;
        }

        /* Header Styling */
        .header-box {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 35px;
        }
        .icon-box {
            background: white;
            padding: 15px;
            border-radius: 18px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
            color: var(--primary-target);
        }

        /* Card Stats */
        .stat-card {
            border: none;
            border-radius: 20px;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
            border-left: 5px solid var(--primary-target);
        }

        /* Progress Bar Modern */
        .progress {
            height: 10px;
            border-radius: 50px;
            background-color: #f1f5f9;
            overflow: visible;
        }
        .progress-bar {
            border-radius: 50px;
            background: linear-gradient(90deg, #4cc9f0, #4361ee);
            position: relative;
        }
        .progress-bar::after {
            content: '';
            position: absolute;
            right: 0;
            top: -4px;
            width: 18px;
            height: 18px;
            background: white;
            border: 4px solid #4cc9f0;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Table Styling */
        .table-container {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.03);
        }
        .table thead th {
            background: transparent;
            color: #94a3b8;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        .table tbody td {
            padding: 20px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .btn-add {
            background: #334155;
            color: white;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }
        .btn-add:hover { background: #000; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="container py-4">
    <a href="javascript:history.back()" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
    </a>

    <div class="header-box">
        <div class="icon-box">
            <i class="fas fa-bullseye fa-2x"></i>
        </div>
        <div>
            <h2 class="fw-bold mb-0">Buku Target</h2>
            <p class="text-muted mb-0">Pantau progres pencapaian tagihan nasabah</p>
        </div>
        <button class="btn btn-add ms-auto">
            <i class="fas fa-plus me-2"></i> Set Target Baru
        </button>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stat-card">
                <small class="text-muted d-block mb-1">Total Target Bulan Ini</small>
                <h3 class="fw-bold mb-0">Rp 45.000.000</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-left-color: #4ade80;">
                <small class="text-muted d-block mb-1">Total Tercapai</small>
                <h3 class="fw-bold mb-0 text-success">Rp 28.500.000</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-left-color: #f87171;">
                <small class="text-muted d-block mb-1">Sisa Target</small>
                <h3 class="fw-bold mb-0 text-danger">Rp 16.500.000</h3>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Nasabah</th>
                    <th>Nominal Target</th>
                    <th>Progres Pencapaian</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="far fa-user text-secondary"></i>
                            </div>
                            <div>
                                <span class="d-block fw-bold">Budi Santoso</span>
                                <small class="text-muted">AG001</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="fw-bold">Rp 5.000.000</span>
                    </td>
                    <td style="width: 40%;">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="fw-bold text-primary">Rp 3.500.000</small>
                            <small class="text-muted">70%</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: 70%"></div>
                        </div>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light border-0" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-light border-0 text-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>