@props([
    'request',
    'primaryClass' => 'text-sm font-medium text-gray-900',
    'detailClass' => 'text-xs text-gray-500 mt-0.5 leading-snug',
])

<div {{ $attributes->merge(['class' => 'min-w-0']) }}>
    <p class="{{ $primaryClass }}">{{ $request->type_label }}</p>
    @foreach($request->type_detail_lines as $detailLine)
        <p class="{{ $detailClass }}">{{ $detailLine }}</p>
    @endforeach
</div>
