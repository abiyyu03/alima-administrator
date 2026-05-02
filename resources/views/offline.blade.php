<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alima — Offline</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .card { background: white; border-radius: 1rem; padding: 2.5rem; text-align: center; max-width: 360px; width: 100%; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .icon { font-size: 3rem; margin-bottom: 1rem; }
        h1 { font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: .5rem; }
        p { font-size: .875rem; color: #6b7280; line-height: 1.6; }
        button { margin-top: 1.5rem; padding: .625rem 1.5rem; background: #15803d; color: white; border: none; border-radius: .5rem; font-size: .875rem; font-weight: 600; cursor: pointer; }
        button:hover { background: #166534; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">📡</div>
        <h1>Tidak ada koneksi</h1>
        <p>Periksa koneksi internet kamu dan coba lagi.</p>
        <button onclick="location.reload()">Coba Lagi</button>
    </div>
</body>
</html>
