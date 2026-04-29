@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto p-4 sm:p-6">
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
        <h1 class="text-xl font-semibold text-gray-900 mb-4">Create Evaluation Form</h1>
        <form method="POST" action="{{ url('/admin/evaluations') }}">
            @include('admin.evaluations._form')
        </form>
    </div>
</div>
@endsection
