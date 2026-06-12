@props(['status'])

@php
    $classes = match (strtolower($status)) {
        'pending'                   => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
        'approved'                  => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
        'active'                    => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
        'expired'                   => 'bg-gray-100 text-gray-600 ring-1 ring-gray-400/30',
        'revoked'                   => 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20',
        'rejected', 'denied'        => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
        'completed'                 => 'bg-sky-50 text-sky-700 ring-1 ring-sky-600/20',
        'success'                   => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
        'failed'                    => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
        'blocked'                   => 'bg-orange-50 text-orange-700 ring-1 ring-orange-600/20',
        // Health check variants
        'health-ok'                 => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
        'health-tcp'                => 'bg-sky-50 text-sky-700 ring-1 ring-sky-600/20',
        'health-fail'               => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
        'health-unknown'            => 'bg-gray-100 text-gray-500 ring-1 ring-gray-400/30',
        default                     => 'bg-gray-50 text-gray-600 ring-1 ring-gray-400/30',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize $classes"]) }}>
    {{ $status }}
</span>
