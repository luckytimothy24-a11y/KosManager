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
    <div class="space-y-6">
        <?php if (isset($component)) { $__componentOriginal5194778a3a7b899dcee5619d0610f5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $attributes = $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $component = $__componentOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>

        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('super-admin.advertising.campaigns.index')); ?>" class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-white/[0.08] text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i class="ri-arrow-left-line"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50"><?php echo e($campaign->campaign_number); ?></h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e($campaign->kos?->name ?? $campaign->advertiser_name); ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <?php if($campaign->kos_id === null && ! in_array($campaign->status, [
                    \App\Models\AdvertisingCampaign::STATUS_COMPLETED,
                    \App\Models\AdvertisingCampaign::STATUS_CANCELLED,
                ])): ?>
                    <a href="<?php echo e(route('super-admin.advertising.campaigns.edit', $campaign)); ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] px-4 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <i class="ri-edit-line"></i> Edit
                    </a>
                <?php endif; ?>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold <?php echo e(\App\Support\AdvertisingLabels::campaignBadge($campaign->status)); ?>">
                    <?php echo e(\App\Support\AdvertisingLabels::campaignLabel($campaign->status)); ?>

                </span>
                <?php if($campaign->status === \App\Models\AdvertisingCampaign::STATUS_PENDING_REVIEW): ?>
                    <form method="POST" action="<?php echo e(route('super-admin.advertising.campaigns.approve', $campaign)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-green-500 hover:bg-green-600 px-4 py-2.5 rounded-xl transition">
                            <i class="ri-check-line"></i> Setujui
                        </button>
                    </form>
                    <button type="button" onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                            class="inline-flex items-center gap-2 text-xs font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/40 px-4 py-2.5 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                        <i class="ri-close-circle-line"></i> Tolak
                    </button>
                <?php endif; ?>
                <?php if($campaign->isLive() || in_array($campaign->status, [
                    \App\Models\AdvertisingCampaign::STATUS_PENDING_REVIEW,
                    \App\Models\AdvertisingCampaign::STATUS_APPROVED,
                ])): ?>
                    <button type="button" onclick="document.getElementById('suspend-modal').classList.remove('hidden')"
                            class="inline-flex items-center gap-2 text-xs font-semibold text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/40 px-4 py-2.5 rounded-xl hover:bg-amber-50 dark:hover:bg-amber-500/10 transition">
                        <i class="ri-pause-circle-line"></i> Tangguhkan
                    </button>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-5">
                <h3 class="font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2"><i class="ri-information-line text-primary-500"></i> Detail Kampanye</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Owner','value' => $campaign->owner->name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Owner'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->owner->name)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                    <?php if($campaign->kos_id === null): ?>
                        <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Advertiser','value' => $campaign->advertiser_name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Advertiser'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->advertiser_name)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Partner','value' => $campaign->partner?->name ?? '-']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Partner'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->partner?->name ?? '-')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Headline','value' => $campaign->headline]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Headline'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->headline)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Placement','value' => \App\Support\AdvertisingLabels::placementLabel($campaign->placement)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Placement'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\App\Support\AdvertisingLabels::placementLabel($campaign->placement))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Destination','value' => $campaign->destination_url]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Destination'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->destination_url)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                        <?php if($campaign->campaign_code): ?>
                            <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Kode Kampanye','value' => $campaign->campaign_code]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Kode Kampanye'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->campaign_code)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                        <?php endif; ?>
                        <?php if($campaign->contract_reference): ?>
                            <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Referensi Kontrak','value' => $campaign->contract_reference]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Referensi Kontrak'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->contract_reference)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                        <?php endif; ?>
                        <?php if($campaign->monetization_type): ?>
                            <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Monetisasi','value' => \App\Support\AdvertisingLabels::monetizationLabel($campaign->monetization_type)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Monetisasi'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\App\Support\AdvertisingLabels::monetizationLabel($campaign->monetization_type))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                        <?php endif; ?>
                        <?php if($campaign->target_audience): ?>
                            <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Target Audiens','value' => $campaign->target_audience]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Target Audiens'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->target_audience)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Kos','value' => $campaign->kos?->name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Kos'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->kos?->name)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Perimeter','value' => $campaign->kos?->address]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Perimeter'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->kos?->address)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Budget','value' => 'Rp '.number_format($campaign->budget, 0, ',', '.').' ('.$campaign->package->name.')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Budget'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Rp '.number_format($campaign->budget, 0, ',', '.').' ('.$campaign->package->name.')')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Periode','value' => $campaign->starts_at?->format('d M Y').' - '.$campaign->ends_at?->format('d M Y')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Periode'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->starts_at?->format('d M Y').' - '.$campaign->ends_at?->format('d M Y'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal9c6c74375fcef32d32a52747013641cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c6c74375fcef32d32a52747013641cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advertising-detail-row','data' => ['label' => 'Disetujui oleh','value' => $campaign->approver?->name ?? '-']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('advertising-detail-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Disetujui oleh'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->approver?->name ?? '-')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $attributes = $__attributesOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__attributesOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c6c74375fcef32d32a52747013641cf)): ?>
<?php $component = $__componentOriginal9c6c74375fcef32d32a52747013641cf; ?>
<?php unset($__componentOriginal9c6c74375fcef32d32a52747013641cf); ?>
<?php endif; ?>
                </dl>
            </div>

            <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-5">
                <h3 class="font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2"><i class="ri-megaphone-line text-indigo-500"></i> Performa Iklan</h3>
                <div class="mt-4 grid grid-cols-2 gap-4 text-center">
                    <div class="bg-slate-50 dark:bg-surface-elevated/50 rounded-xl p-4">
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-50"><?php echo e(number_format($impressions)); ?></p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Impressions</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-surface-elevated/50 rounded-xl p-4">
                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400"><?php echo e($ctr); ?>%</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">CTR</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-surface-elevated/50 rounded-xl p-4">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo e(number_format($conversions)); ?></p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Booking</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-surface-elevated/50 rounded-xl p-4">
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400"><?php echo e($conversionRate); ?>%</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Konversi</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-5">
                <h3 class="font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2"><i class="ri-bank-card-line text-green-500"></i> Pembayaran</h3>
                <?php if($campaign->orders->isNotEmpty()): ?>
                    <dl class="mt-4 space-y-3 text-sm">
                        <?php $__currentLoopData = $campaign->orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border border-slate-100 dark:border-white/10 rounded-xl p-3">
                                <div class="flex justify-between">
                                    <dt class="text-slate-500 dark:text-slate-400"><?php echo e($order->order_number); ?></dt>
                                    <dd class="font-semibold text-slate-800 dark:text-slate-200">Rp <?php echo e(number_format($order->amount, 0, ',', '.')); ?></dd>
                                </div>
                                <div class="flex justify-between mt-1">
                                    <dt class="text-slate-500 dark:text-slate-400 text-[11px]">Status</dt>
                                    <dd>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold <?php echo e(\App\Support\AdvertisingLabels::orderBadge($order->status)); ?>">
                                            <?php echo e(\App\Support\AdvertisingLabels::orderLabel($order->status)); ?>

                                        </span>
                                    </dd>
                                </div>
                                <?php if($order->isRefunded()): ?>
                                    <div class="mt-2 border-t border-slate-100 dark:border-white/10 pt-2 text-[11px] text-slate-500 dark:text-slate-400">
                                        Pembayaran dibatalkan dan dicatat sebagai refunded <?php echo e($order->refunded_at?->format('d/m/Y H:i') ?? '-'); ?>

                                        <?php if($order->refund_reason): ?> · <?php echo e($order->refund_reason); ?> <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if($order->isPending() && $campaign->isThirdParty()): ?>
                                    <form method="POST" action="<?php echo e(route('super-admin.advertising.orders.mark-paid', $order)); ?>" class="mt-2">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="inline-flex items-center gap-1 text-[11px] font-bold text-white bg-green-500 hover:bg-green-600 px-3 py-1.5 rounded-lg transition">
                                            <i class="ri-check-double-line"></i> Tandai Terbayar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </dl>
                <?php else: ?>
                    <div class="mt-4 text-center py-6">
                        <i class="ri-bank-card-line text-3xl text-slate-300 dark:text-slate-600"></i>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Belum ada pembayaran.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div id="reject-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('reject-modal').classList.add('hidden')"></div>
            <form method="POST" action="<?php echo e(route('super-admin.advertising.campaigns.reject', $campaign)); ?>" class="relative bg-white dark:bg-surface-elevated rounded-2xl p-6 w-full max-w-md shadow-xl">
                <?php echo csrf_field(); ?>
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50">Tolak Kampanye</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Berikan alasan penolakan yang akan dilihat oleh owner.</p>
                <textarea name="reason" rows="3" required placeholder="Alasan penolakan..."
                          class="mt-4 w-full text-sm rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] px-4 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</button>
                    <button type="submit" class="text-sm font-bold text-white bg-red-500 hover:bg-red-600 px-5 py-2 rounded-xl transition">Tolak Kampanye</button>
                </div>
            </form>
        </div>

        <div id="suspend-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('suspend-modal').classList.add('hidden')"></div>
            <form method="POST" action="<?php echo e(route('super-admin.advertising.campaigns.suspend', $campaign)); ?>" class="relative bg-white dark:bg-surface-elevated rounded-2xl p-6 w-full max-w-md shadow-xl">
                <?php echo csrf_field(); ?>
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50">Tangguhkan Kampanye</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kampanye akan dihentikan sementara dari tayang.</p>
                <textarea name="reason" rows="3" required placeholder="Alasan penangguhan..."
                          class="mt-4 w-full text-sm rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-transparent"></textarea>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('suspend-modal').classList.add('hidden')" class="text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] px-4 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</button>
                    <button type="submit" class="text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 px-5 py-2 rounded-xl transition">Tangguhkan</button>
                </div>
            </form>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\super-admin\advertising\campaign\show.blade.php ENDPATH**/ ?>