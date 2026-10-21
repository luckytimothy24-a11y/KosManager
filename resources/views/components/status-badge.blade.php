@props([
    'status',
    'context' => 'booking', // booking|kamar|kos|kontrak|penghuni|checkout|tagihan|verification
    'label' => null,
])

@php
    $map = [
        'booking' => ['\\StatusLabels::bookingLabel', '\\StatusLabels::bookingBadge'],
        'kamar' => ['\\StatusLabels::kamarLabel', '\\StatusLabels::kamarBadge'],
        'kos' => ['\\StatusLabels::kosLabel', '\\StatusLabels::kosBadge'],
        'kontrak' => ['\\StatusLabels::kontrakLabel', '\\StatusLabels::kontrakBadge'],
        'penghuni' => ['\\StatusLabels::penghuniLabel', '\\StatusLabels::penghuniBadge'],
        'checkout' => ['\\StatusLabels::checkoutLabel', '\\StatusLabels::checkoutBadge'],
        'tagihan' => ['\\PaymentLabels::tagihanLabel', '\\PaymentLabels::tagihanBadge'],
        'verification' => ['\\PaymentLabels::verificationLabel', '\\PaymentLabels::verificationBadge'],
        'role' => ['\\StatusLabels::roleLabel', '\\StatusLabels::roleBadge'],
        'billing_mode' => ['\\StatusLabels::billingModeLabel', '\\StatusLabels::billingModeBadge'],
    ];

    [$labelFn, $badgeFn] = $map[$context] ?? $map['booking'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold whitespace-nowrap ' . $badgeFn($status)]) }}>
    {{ $label ?? $labelFn($status) }}
</span>
