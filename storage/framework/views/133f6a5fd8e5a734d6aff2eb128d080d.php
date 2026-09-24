<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'status',
    'context' => 'booking', // booking|kamar|kos|kontrak|penghuni|checkout|tagihan|verification
    'label' => null,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'status',
    'context' => 'booking', // booking|kamar|kos|kontrak|penghuni|checkout|tagihan|verification
    'label' => null,
]); ?>
<?php foreach (array_filter(([
    'status',
    'context' => 'booking', // booking|kamar|kos|kontrak|penghuni|checkout|tagihan|verification
    'label' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $map = [
        'booking' => ['\\StatusLabels::bookingLabel', '\\StatusLabels::bookingBadge'],
        'kamar' => ['\\StatusLabels::kamarLabel', '\\StatusLabels::kamarBadge'],
        'kos' => ['\\StatusLabels::kosLabel', '\\StatusLabels::kosBadge'],
        'kontrak' => ['\\StatusLabels::kontrakLabel', '\\StatusLabels::kontrakBadge'],
        'penghuni' => ['\\StatusLabels::penghuniLabel', '\\StatusLabels::penghuniBadge'],
        'checkout' => ['\\StatusLabels::checkoutLabel', '\\StatusLabels::checkoutBadge'],
        'tagihan' => ['\\PaymentLabels::tagihanLabel', '\\PaymentLabels::tagihanBadge'],
        'verification' => ['\\PaymentLabels::verificationLabel', '\\PaymentLabels::verificationBadge'],
    ];

    [$labelFn, $badgeFn] = $map[$context] ?? $map['booking'];
?>

<span <?php echo e($attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium whitespace-nowrap ' . $badgeFn($status)])); ?>>
    <?php echo e($label ?? $labelFn($status)); ?>

</span>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/components/status-badge.blade.php ENDPATH**/ ?>