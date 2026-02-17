@extends('layouts.landing')

@section('title', 'Report a Problem')
@section('description', 'Submit a problem report')

@section('content')
<section class="gradient-bg text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Report a Problem</h1>
            <p class="text-lg text-gray-100">Describe the issue and we’ll look into it.</p>
        </div>
    </div>
</section>

<section class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-red-800 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow rounded-lg border border-gray-200 overflow-hidden">
            <form action="{{ url('/report-problem') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type of problem <span class="text-red-500">*</span></label>
                    <select name="type" id="type" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select type...</option>
                        @foreach($problemTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Explain the problem <span class="text-red-500">*</span></label>
                    <textarea name="description" id="description" rows="5" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Describe what happened and what you expected.">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700">Upload photo (optional)</label>
                    <input type="file" name="photo" id="photo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">Max 5MB. JPEG, PNG, GIF, or WebP.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <label for="full_name" class="block text-sm font-medium text-gray-700">Full name <span class="text-red-500">*</span></label>
                        <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact number</label>
                        <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number') }}" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="office" class="block text-sm font-medium text-gray-700">Office</label>
                        <input type="text" name="office" id="office" value="{{ old('office') }}" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" id="address" rows="2" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('address') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
