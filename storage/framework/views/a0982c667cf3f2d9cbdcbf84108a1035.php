<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Gabungan</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #111827; padding-bottom: 10px; }
        .header h1 { font-size: 20px; margin: 0; }
        .header p { margin: 4px 0 0; font-size: 11px; color: #6b7280; }
        .meta { margin-bottom: 14px; font-size: 11px; color: #4b5563; text-align: center; }
        .section { margin-bottom: 22px; }
        .section-title { font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 6px; border-left: 4px solid #1e40af; padding-left: 8px; }
        .section-desc { font-size: 10px; color: #6b7280; margin: 0 0 8px 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th { background-color: #1e40af; color: white; padding: 6px 6px; text-align: left; font-size: 10px; }
        td { padding: 5px 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .summary { margin-top: 6px; border-top: 1px solid #e5e7eb; padding-top: 6px; }
        .summary h4 { font-size: 11px; margin: 0 0 6px; }
        .summary-item { display: flex; justify-content: space-between; padding: 2px 0; font-size: 10px; }
        .text-right { text-align: right; }
        .page-break { page-break-after: always; }
        .badge { display: inline-block; font-size: 9px; padding: 2px 6px; border-radius: 9999px; background: #eef2ff; color: #3730a3; }
        .empty { text-align: center; color: #9ca3af; padding: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Gabungan</h1>
        <p>KosManager — Rekap operasional &amp; keuangan</p>
        <p style="font-size:10px; color:#6b7280;">Pendapatan · Penghuni · Booking · Kamar · Tagihan — dalam 1 file</p>
    </div>

    <div class="meta">
        <?php if($startDate || $endDate): ?>
            Periode: <?php echo e($startDate ?? '-'); ?> s/d <?php echo e($endDate ?? '-'); ?> &nbsp;|&nbsp;
        <?php endif; ?>
        <?php if($kosName): ?>
            Kos: <?php echo e($kosName); ?>

        <?php else: ?>
            Kos: Semua Kos
        <?php endif; ?>
        &nbsp;|&nbsp; Dicetak: <?php echo e(now()->format('d/m/Y H:i')); ?>

    </div>

    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="section <?php echo e($idx < count($sections)-1 ? 'page-break' : ''); ?>">
            <div class="section-title"><?php echo e($loop->iteration); ?>. <?php echo e($sec['title']); ?></div>
            <p class="section-desc"><?php echo e($sec['desc']); ?></p>

            <table>
                <thead>
                    <tr>
                        <?php $__currentLoopData = $sec['headers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th><?php echo e($h); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $sec['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td <?php if(is_numeric(str_replace(['Rp ','. ',','], '', $cell)) && str_contains($cell, 'Rp')): ?> class="text-right" <?php endif; ?>><?php echo e($cell); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="<?php echo e(count($sec['headers'])); ?>" class="empty">Tidak ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if(!empty($sec['summary'])): ?>
                <div class="summary">
                    <h4>Ringkasan <?php echo e($sec['title']); ?></h4>
                    <?php $__currentLoopData = $sec['summary']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="summary-item"><span><?php echo e($k); ?></span><span><?php echo e($v); ?></span></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>
</html>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/exports/pdf/gabungan.blade.php ENDPATH**/ ?>