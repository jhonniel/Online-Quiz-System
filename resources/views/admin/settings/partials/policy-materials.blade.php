@include('admin.settings.partials.document-materials', [
    'materialType' => 'policy',
    'fieldPrefix' => 'policy_materials',
    'materialItems' => old('policy_materials', $settings['policy_materials'] ?? \App\Support\EmployeePolicyMaterial::all()),
    'sectionTitle' => 'Policy material PDFs',
    'sectionDescription' => 'Upload one or more policy PDFs. Each file appears under <strong>Policy</strong> in the employee Documents menu using the display name you set here.',
    'namePlaceholder' => 'e.g. Data Privacy Policy, OSH Policy',
])
