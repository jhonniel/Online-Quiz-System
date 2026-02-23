@extends('layouts.admin')

@section('title', 'Add Omada')
@section('page-title', 'Add Omada')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ url('/admin/omadas') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Omada</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
            <h2 class="text-lg font-semibold text-gray-900">Omada device details</h2>
            <p class="text-sm text-gray-500 mt-0.5">Enter the device and account information. Status will be Active or Expired based on license expiration.</p>
        </div>
        <form action="{{ url('/admin/omadas') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @include('admin.omadas._form', ['omada' => null])
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ url('/admin/omadas') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Add Omada</button>
            </div>
        </form>
    </div>
</div>
@endsection
