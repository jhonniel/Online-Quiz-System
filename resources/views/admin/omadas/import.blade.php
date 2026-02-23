@extends('layouts.admin')

@section('title', 'Import Omadas')
@section('page-title', 'Import Omadas')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 py-4 max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ url('/admin/omadas') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Omadas
        </a>
    </div>

    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800 flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-5 border-b border-gray-200 bg-gray-50/50">
            <h2 class="text-lg font-semibold text-gray-900">Import Omada devices from CSV</h2>
            <p class="text-sm text-gray-500 mt-0.5">Upload a CSV file with the same columns as the sample template. <code class="text-xs bg-gray-100 px-1 rounded">license_expiration</code> must be in <code class="text-xs bg-gray-100 px-1 rounded">Y-m-d</code> format (e.g. 2025-12-31).</p>
        </div>
        <div class="p-4 sm:p-6 space-y-6">
            <div class="rounded-lg border border-indigo-200 bg-indigo-50/50 p-4">
                <p class="text-sm font-medium text-indigo-900 mb-2">Download sample template</p>
                <p class="text-sm text-indigo-800 mb-3">Use this file to see the required column headers and an example row. Fill in your data and save as CSV.</p>
                <a href="{{ url('/admin/omadas/import/template') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Download omadas_import_template.csv
                </a>
            </div>

            <form action="{{ url('/admin/omadas/import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-1">CSV file</label>
                    <input type="file" name="file" id="file" accept=".csv,.txt" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ url('/admin/omadas') }}" class="inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">Cancel</a>
                    <button type="submit" class="inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Import CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
