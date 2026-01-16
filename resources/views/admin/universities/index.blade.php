@extends('layouts.admin')

@section('content')
<x-responsive-admin-table
    title="Universities"
    description="Manage educational institutions and their settings"
    icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
    search-placeholder="Search universities..."
    add-button-text="Add University"
    :add-button-route="route('admin.universities.create')"
>
    <x-responsive-table
        :headers="[
            ['text' => 'University ID', 'responsive' => 'hidden sm:inline'],
            ['text' => 'Name'],
            ['text' => 'Code', 'responsive' => 'hidden md:inline'],
            ['text' => 'Location', 'responsive' => 'hidden lg:inline'],
            ['text' => 'Students', 'responsive' => 'hidden md:inline'],
            ['text' => 'Status', 'responsive' => 'hidden md:inline'],
            ['text' => 'Created', 'responsive' => 'hidden lg:inline']
        ]"
        :data="$universities"
        empty-message="No universities found"
        empty-icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
    >

        @foreach($universities as $university)
            <tr class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                    #{{ str_pad($university->id, 4, '0', STR_PAD_LEFT) }}
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900 truncate">{{ $university->name }}</div>
                            @if($university->description)
                                <div class="text-xs sm:text-sm text-gray-500 truncate max-w-xs hidden md:block">{{ $university->description }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap hidden md:table-cell">
                    @if($university->code)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 font-mono">
                            {{ $university->code }}
                        </span>
                    @else
                        <span class="text-gray-400 italic text-xs">Not specified</span>
                    @endif
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap text-sm text-gray-900 hidden lg:table-cell">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="truncate max-w-xs">{{ $university->location ?? 'Not specified' }}</span>
                    </div>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap text-sm text-gray-900 hidden md:table-cell">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ $university->users_count }} student{{ $university->users_count != 1 ? 's' : '' }}
                        </span>
                    </div>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap hidden md:table-cell">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $university->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $university->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                    {{ $university->created_at->format('M d, Y') }}
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap text-right text-sm font-medium">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 p-1">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                            </svg>
                        </button>

                        <div x-show="open"
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">

                            <a href="{{ route('admin.universities.show', $university) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View
                            </a>

                            <a href="{{ route('admin.universities.edit', $university) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </a>

                            <form method="POST" action="{{ route('admin.universities.toggle-status', $university) }}" class="block">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm {{ $university->is_active ? 'text-red-700 hover:bg-red-50' : 'text-green-700 hover:bg-green-50' }} flex items-center">
                                    @if($university->is_active)
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                        </svg>
                                        Deactivate
                                    @else
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Activate
                                    @endif
                                </button>
                            </form>

                            @if($university->users_count == 0)
                                <div class="border-t border-gray-100"></div>

                                <form method="POST" action="{{ route('admin.universities.destroy', $university) }}" class="block" onsubmit="return confirmUniversityAction('delete', this)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-responsive-table>
</x-responsive-admin-table>

<script>
    function confirmUniversityAction(action, button) {
        event.preventDefault();

        if (confirm('Are you sure you want to delete this university? This action cannot be undone.')) {
            button.closest('form').submit();
        }

        return false;
    }
</script>
@endsection
