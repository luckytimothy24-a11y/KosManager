<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($title); ?></title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header p { margin: 5px 0 0; font-size: 11px; color: #666; }
        .meta { margin-bottom: 15px; font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background-color: #1e40af; color: white; padding: 8px 6px; text-align: left; font-size: 11px; }
        td { padding: 6px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .summary { margin-top: 15px; border-top: 2px solid #e5e7eb; padding-top: 10px; }
        .summary h3 { font-size: 13px; margin-bottom: 8px; }
        .summary-item { display: flex; justify-content: space-between; padding: 3px 0; font-size: 11px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo e($title); ?></h1>
        <p>KosManager - Sistem Manajemen Kos</p>
    </div>

    <?php if($startDate || $endDate): ?>
        <div class="meta">
            Periode: <?php echo e($startDate ?? '-'); ?> s/d <?php echo e($endDate ?? '-'); ?>

        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <?php if($type ?? null): ?>
                    <?php if($type === 'pendapatan'): ?>
                        <th>No</th><th>Tanggal</th><th>Penghuni</th><th>Kamar</th><th>Kos</th><th>Nominal</th><th>Metode</th><th>Status</th>
                    <?php elseif($type === 'penghuni'): ?>
                        <th>No</th><th>Nama</th><th>Email</th><th>Kamar</th><th>Kos</th><th>Telepon</th><th>Masuk</th><th>Status</th>
                    <?php elseif($type === 'booking'): ?>
                        <th>No</th><th>Kode</th><th>User</th><th>Kamar</th><th>Kos</th><th>Periode</th><th>Harga</th><th>Status</th>
                    <?php elseif($type === 'kamar'): ?>
                        <th>No</th><th>Nomor</th><th>Nama</th><th>Kos</th><th>Tipe</th><th>Harga/Bulan</th><th>Status</th>
                    <?php elseif($type === 'tagihan'): ?>
                        <th>No</th><th>Nomor</th><th>Penghuni</th><th>Kamar</th><th>Jenis</th><th>Jatuh Tempo</th><th>Total</th><th>Status</th>
                    <?php endif; ?>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <?php if(($loop->count ?? 0) > 0): ?>
                        <td><?php echo e($loop->iteration); ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" style="text-align:center">Tidak ada data</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($summary): ?>
        <div class="summary">
            <h3>Ringkasan</h3>
            <?php $__currentLoopData = $summary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="summary-item">
                    <span><?php echo e($key); ?></span>
                    <span><?php echo e($value); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\exports\pdf\layout.blade.php ENDPATH**/ ?>