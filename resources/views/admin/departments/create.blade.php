@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <a href="{{ route('admin.departments.index') }}" class="mr-4 text-white hover:text-indigo-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Create Department</h1>
                <p class="text-indigo-100">Add a new department for employees</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
        <form action="{{ route('admin.departments.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Department Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" required
                           value="{{ old('name') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @include('admin.departments.partials.positions-field')

                <!-- Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Code</label>
                    <input type="text" name="code" id="code"
                           value="{{ old('code') }}"
                           placeholder="e.g., IT, HR, FIN"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-sm text-gray-500">Optional: Short code for the department (auto-generated if left empty)</p>
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="mt-1">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Only active departments can be assigned to employees</p>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Optional: Brief description of the department</p>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @include('admin.departments.partials.job-description-field', ['jobDescriptionValue' => old('job_description')])

                <!-- Supervisor Name -->
                <div class="md:col-span-2">
                    <label for="supervisor_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Immediate Supervisor Name
                    </label>
                    <input type="text" name="supervisor_name" id="supervisor_name"
                           value="{{ old('supervisor_name') }}"
                           placeholder="e.g., CHARMAINE JOY ROSATACE"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-sm text-gray-500">Name displayed as "IMMEDIATE SUPERVISOR" in leave request letters for this department</p>
                    @error('supervisor_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.departments.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Create Department
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

