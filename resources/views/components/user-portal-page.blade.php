@props([
    'title',
    'subtitle' => null,
    'fluid' => true,
])

<x-user-page-shell :fluid="$fluid">
    <x-user-page-header :title="$title" :subtitle="$subtitle">
        @isset($icon)
            <x-slot:icon>{{ $icon }}</x-slot:icon>
        @endisset
        @isset($subtitleSlot)
            <x-slot:subtitleSlot>{{ $subtitleSlot }}</x-slot:subtitleSlot>
        @endisset
        @isset($actions)
            <x-slot:actions>{{ $actions }}</x-slot:actions>
        @endisset
    </x-user-page-header>
    {{ $slot }}
</x-user-page-shell>
