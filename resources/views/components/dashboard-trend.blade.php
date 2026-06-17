@props([
    'change' => 0,
    'suffix' => '%',
    'goodWhenNegative' => false,
])

@php
    $change = (int) $change;
    $isPositive = $change >= 0;
    $isGood = $goodWhenNegative ? $change <= 0 : $isPositive;
@endphp

<div class="trend {{ $isGood ? 'up' : 'down' }}">
    <i class="bi bi-arrow-{{ $isPositive ? 'up' : 'down' }}"></i>
    <strong>{{ abs($change) }}{{ $suffix }}</strong> from last week
</div>
