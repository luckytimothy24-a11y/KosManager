<?php
    use App\Models\Kamar;
    use App\Models\Penghuni;
    $user = auth()->user();
    $isSuperAdmin = $user->isSuperAdmin();
    $prefix = $isSuperAdmin ? 'super-admin' : ($user->isAdmin() ? 'admin' : 'owner');
    $qs = request()->only('start_date', 'end_date', 'kos_id');
    $qsSuffix = http_build_query($qs) ? '?'.http_build_query($qs) : '';
    $hasFilter = request()->anyFilled(['start_date', 'end_date', 'kos_id']);
?>

<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\AppLayout::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Laporan Kos','description' => 'Satu halaman untuk semua data kos — ringkasan, detail, dan export.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Laporan Kos','description' => 'Satu halaman untuk semua data kos — ringkasan, detail, dan export.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="space-y-6">
        
        <div class="card-surface p-5 sm:p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-xl bg-slate-900 dark:bg-white flex items-center justify-center shrink-0">
                    <i class="ri-equalizer-line text-white dark:text-slate-900"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Filter Laporan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pilih kos dan periode untuk melihat data.</p>
                </div>
                <?php if($hasFilter): ?>
                    <span class="ml-auto hidden sm:inline-flex items-center gap-1 text-xs font-bold text-orange-600 dark:text-orange-300 bg-orange-50 dark:bg-orange-500/10 border border-orange-100 dark:border-orange-500/20 px-2.5 py-1.5 rounded-full"><i class="ri-filter-3-line"></i> Aktif</span>
                <?php endif; ?>
            </div>

            <form method="GET" action="<?php echo e(route($prefix.'.laporan.index')); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="start_date" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="<?php echo e(request('start_date')); ?>"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:border-orange-500 focus:ring-orange-500 py-2.5">
                </div>
                <div>
                    <label for="end_date" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Tanggal Akhir</label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo e(request('end_date')); ?>"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:border-orange-500 focus:ring-orange-500 py-2.5">
                </div>
                <div>
                    <label for="kos_id" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Kos</label>
                    <select name="kos_id" id="kos_id"
                            class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:border-orange-500 focus:ring-orange-500 py-2.5">
                        <option value="">Semua Kos</option>
                        <?php $__currentLoopData = $kosList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($k->id); ?>" <?php echo e((string) request('kos_id') === (string) $k->id ? 'selected' : ''); ?>><?php echo e($k->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="flex gap-2 sm:justify-end">
                    <button type="submit"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-br from-orange-500 to-orange-600 text-white hover:from-orange-600 hover:to-orange-700 active:scale-[0.98] transition shadow-lg shadow-orange-500/20">
                        <i class="ri-search-line"></i> Terapkan
                    </button>
                    <a href="<?php echo e(route($prefix.'.laporan.index')); ?>"
                       class="inline-flex items-center justify-center w-11 h-11 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition" title="Reset filter" aria-label="Reset filter">
                        <i class="ri-refresh-line text-lg"></i>
                    </a>
                </div>
            </form>
        </div>

        
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 sm:p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/25 shrink-0">
                    <i class="ri-building-2-line text-white text-lg"></i>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">Ringkasan Kos</h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php
                    $kosFilter = request('kos_id') ? $kosList->where('id', (int) request('kos_id')) : $kosList;
                    $kosIdsFilter = $kosFilter->pluck('id')->all();
                    $totalKos = $kosFilter->count();
                    $totalKamar = Kamar::whereIn('kos_id', $kosIdsFilter)->count();
                    $availableKamar = Kamar::whereIn('kos_id', $kosIdsFilter)->where('status', 'available')->count();
                    $occupiedKamar = Kamar::whereIn('kos_id', $kosIdsFilter)->where('status', 'occupied')->count();
                    $maintenanceKamar = Kamar::whereIn('kos_id', $kosIdsFilter)->where('status', 'maintenance')->count();
                    $totalPenghuni = Penghuni::whereIn('kos_id', $kosIdsFilter)->where('status', 'active')->count();
                ?>
                <div class="p-3 rounded-xl bg-orange-50 dark:bg-orange-500/10 border border-orange-100 dark:border-orange-500/20">
                    <p class="text-[11px] font-semibold text-orange-700 dark:text-orange-300 uppercase">Total Kos</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white mt-1"><?php echo e($totalKos); ?></p>
                </div>
                <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20">
                    <p class="text-[11px] font-semibold text-blue-700 dark:text-blue-300 uppercase">Total Kamar</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white mt-1"><?php echo e($totalKamar); ?></p>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20">
                    <p class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 uppercase">Tersedia</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white mt-1"><?php echo e($availableKamar); ?></p>
                </div>
                <div class="p-3 rounded-xl bg-violet-50 dark:bg-violet-500/10 border border-violet-100 dark:border-violet-500/20">
                    <p class="text-[11px] font-semibold text-violet-700 dark:text-violet-300 uppercase">Terisi</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white mt-1"><?php echo e($occupiedKamar); ?></p>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20">
                    <p class="text-[11px] font-semibold text-amber-700 dark:text-amber-300 uppercase">Maintenance</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white mt-1"><?php echo e($maintenanceKamar); ?></p>
                </div>
                <div class="p-3 rounded-xl bg-sky-50 dark:bg-sky-500/10 border border-sky-100 dark:border-sky-500/20">
                    <p class="text-[11px] font-semibold text-sky-700 dark:text-sky-300 uppercase">Penghuni Aktif</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white mt-1"><?php echo e($totalPenghuni); ?></p>
                </div>
            </div>
        </div>

        
        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shrink-0">
                    <i class="ri-<?php echo e($sec['key'] === 'pendapatan' ? 'money-dollar-circle-line' : ($sec['key'] === 'penghuni' ? 'user-star-line' : ($sec['key'] === 'booking' ? 'calendar-check-line' : ($sec['key'] === 'kamar' ? 'door-open-line' : 'file-list-3-line')))); ?> text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm"><?php echo e($sec['title']); ?></h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400"><?php echo e($sec['desc']); ?></p>
                </div>
            </div>
            <?php
                $summaryLabels = $sec['key'] === 'pendapatan' ? ['Total Pendapatan', 'Jumlah Transaksi'] : ($sec['key'] === 'penghuni' ? ['Total Aktif', 'Total Inactive'] : ($sec['key'] === 'booking' ? ['Total', 'Pending', 'Approved', 'Rejected'] : ($sec['key'] === 'kamar' ? ['Total', 'Available', 'Booked', 'Occupied', 'Maintenance'] : ['Total', 'Dibayar', 'Belum Dibayar', 'Overdue'])));
            ?>
            <div class="p-4 sm:p-6 mb-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
                    <?php $__currentLoopData = $sec['summary']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400"><?php echo e($k); ?></p>
                            <p class="text-lg font-black text-slate-900 dark:text-white"><?php echo e($v); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php if(!empty($sec['rows'])): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800">
                                <?php $__currentLoopData = $sec['headers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e($h); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php $no = 1; ?>
                            <?php $__currentLoopData = $sec['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="px-4 py-2.5"><?php echo e($cell); ?></td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-6 text-center text-slate-400 text-sm">Tidak ada data</div>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 sm:p-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">Export Laporan Kos</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Unduh semua data dalam satu file PDF atau Excel.</p>
            </div>
            <div class="flex gap-3">
                <a href="<?php echo e(route($prefix.'.laporan.export-pdf').$qsSuffix); ?>" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-semibold hover:bg-black dark:hover:bg-slate-100 transition shadow-lg shadow-orange-500/20">
                    <i class="ri-file-pdf-line"></i> Export PDF
                </a>
                <a href="<?php echo e(route($prefix.'.laporan.export-excel').$qsSuffix); ?>" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-emerald-500 text-white text-sm font-semibold hover:bg-emerald-600 transition">
                    <i class="ri-file-excel-line"></i> Export Excel
                </a>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/laporan/kos.blade.php ENDPATH**/ ?>