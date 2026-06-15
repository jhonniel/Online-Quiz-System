@include('admin.settings.partials.document-materials', [
    'materialType' => 'handbook',
    'fieldPrefix' => 'handbook_materials',
    'materialItems' => old('handbook_materials', $settings['handbook_materials'] ?? \App\Support\EmployeeHandbookMaterial::all()),
    'sectionTitle' => 'Handbook material PDFs',
    'sectionDescription' => 'Upload one or more handbook PDFs. Each file appears under <strong>Handbook</strong> in the employee Documents menu using the display name you set here.',
    'namePlaceholder' => 'e.g. Company Handbook, Safety Manual',
])
