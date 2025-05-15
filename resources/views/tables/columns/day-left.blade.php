@php
    $remaining = $getRemainingDays();
    $color = $remaining > 0 ? 'gold' : ($remaining === 0 ? 'orange' : 'red');

    $text =
        $remaining > 0
            ? "{$remaining} day" . ($remaining > 1 ? 's' : '')
            : ($remaining === 0
                ? 'Due today'
                : 'Overdue by ' . abs($remaining) . ' day' . (abs($remaining) > 1 ? 's' : ''));
@endphp

<div style="color: {{ $color }}; font-size: 12px; font-weight: 500;">
    {{ $text }}
</div>
