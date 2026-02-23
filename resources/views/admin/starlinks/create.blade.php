@extends('layouts.admin')

@section('title', 'Add Starlink')
@section('page-title', 'Add Starlink')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 py-4 max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ url('/admin/starlinks') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Starlinks
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-5 border-b border-gray-200 bg-gray-50/50">
            <h2 class="text-lg font-semibold text-gray-900">Starlink device details</h2>
            <p class="text-sm text-gray-500 mt-0.5">Enter the device and account information.</p>
        </div>
        <form action="{{ url('/admin/starlinks') }}" method="POST" class="p-4 sm:p-6">
            @csrf
            @include('admin.starlinks._form', ['starlink' => null])
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-6 mt-6 border-t border-gray-200">
                <a href="{{ url('/admin/starlinks') }}" class="inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">Cancel</a>
                <button type="submit" class="inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Starlink
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
