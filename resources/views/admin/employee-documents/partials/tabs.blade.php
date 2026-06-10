<div class="flex flex-wrap gap-2">
    @foreach(['nda' => 'NDA', 'contract' => 'Agreement', 'policy' => 'Policy', 'handbook' => 'Hand Book'] as $docType => $docLabel)
        @if(auth()->user()->canAccessEmployeeFeature('employee_'.$docType))
            <a href="{{ route('admin.employee-documents.'.$docType) }}"
               class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium {{ ($active ?? '') === $docType ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                {{ $docLabel }}
            </a>
        @endif
    @endforeach
    @if(auth()->user()->canAccessAnyEmployeeDocumentFeature())
        <a href="{{ route('admin.employee-documents.signatures') }}"
           class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium {{ ($active ?? '') === 'signatures' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
            Signatures
        </a>
    @endif
</div>
