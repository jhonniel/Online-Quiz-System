@extends('layouts.admin')

@section('content')
<x-responsive-admin-table
    title="Stacks"
    description="Manage technology stacks displayed on the landing page"
    icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
    search-placeholder="Search stacks..."
    add-button-text="Add Stack"
    :add-button-route="route('admin.stacks.create')"
>
    <x-responsive-table
        :headers="[
            ['text' => 'ID', 'responsive' => 'hidden sm:inline'],
            ['text' => 'Name'],
            ['text' => 'Icon', 'responsive' => 'hidden md:inline'],
            ['text' => 'Order', 'responsive' => 'hidden lg:inline'],
            ['text' => 'Status', 'responsive' => 'hidden md:inline'],
            ['text' => 'Actions']
        ]"
        :data="$stacks"
        empty-message="No stacks found. Add your first stack to get started."
        empty-icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
    >
        @foreach($stacks as $stack)
            <tr class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900 hidden sm:table-cell">
                    #{{ str_pad($stack->id, 4, '0', STR_PAD_LEFT) }}
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap">
                    <div class="flex items-center">
                        @if($stack->image)
                            <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10 mr-2 sm:mr-3">
                                <img src="{{ $stack->image_url }}" alt="{{ $stack->name }}" class="h-8 w-8 sm:h-10 sm:w-10 object-contain rounded">
                            </div>
                        @elseif($stack->icon)
                            <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10 mr-2 sm:mr-3">
                                @if(filter_var($stack->icon, FILTER_VALIDATE_URL))
                                    <img src="{{ $stack->icon }}" alt="{{ $stack->name }}" class="h-8 w-8 sm:h-10 sm:w-10 object-contain rounded">
                                @else
                                    <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                        <i class="{{ $stack->icon }} text-primary text-lg"></i>
                                    </div>
                                @endif
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900">{{ $stack->name }}</div>
                            @if($stack->color)
                                <div class="text-xs text-gray-500 hidden md:block">Color: {{ $stack->color }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap hidden md:table-cell">
                    @if($stack->image)
                        <span class="text-xs text-gray-600">Image uploaded</span>
                    @elseif($stack->icon)
                        <span class="text-xs text-gray-600 font-mono">{{ Str::limit($stack->icon, 30) }}</span>
                    @else
                        <span class="text-gray-400 italic text-xs">No icon/image</span>
                    @endif
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap text-sm text-gray-900 hidden lg:table-cell">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ $stack->order }}
                    </span>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap hidden md:table-cell">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $stack->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $stack->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.stacks.edit', $stack) }}" class="text-indigo-600 hover:text-indigo-900">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        <form action="{{ route('admin.stacks.destroy', $stack) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this stack?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-responsive-table>
</x-responsive-admin-table>
@endsection
