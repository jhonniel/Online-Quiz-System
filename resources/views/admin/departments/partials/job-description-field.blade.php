@php
    $jobDescriptionValue = old('job_description', $jobDescriptionValue ?? '');
@endphp

<div class="md:col-span-2">
    <label for="job_description" class="block text-sm font-medium text-gray-700 mb-2">Job Description</label>
    <textarea name="job_description" id="job_description" rows="8"
              placeholder="Enter one responsibility per line, for example:&#10;Develop and maintain web applications&#10;Collaborate with the team on project requirements&#10;Document technical processes"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm">{{ $jobDescriptionValue }}</textarea>
    <p class="mt-1 text-sm text-gray-500">Enter one item per line. Each line will be saved and displayed as a bullet point. You may start lines with <code class="text-xs bg-gray-100 px-1 rounded">-</code> or <code class="text-xs bg-gray-100 px-1 rounded">•</code> if you prefer.</p>
    @error('job_description')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
        <p class="text-sm font-medium text-gray-900 mb-2">Preview</p>
        <ul id="job-description-preview" class="list-disc list-inside space-y-1 text-sm text-gray-700">
            <li class="text-gray-400 italic">Bullet preview will appear here</li>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const textarea = document.getElementById('job_description');
    const preview = document.getElementById('job-description-preview');
    if (!textarea || !preview) {
        return;
    }

    function parseBullets(value) {
        return value
            .split(/\r?\n/)
            .map(function (line) {
                return line.trim().replace(/^[\-*•]\s*/, '');
            })
            .filter(function (line) {
                return line !== '';
            });
    }

    function renderPreview() {
        const bullets = parseBullets(textarea.value);
        if (bullets.length === 0) {
            preview.innerHTML = '<li class="text-gray-400 italic">Bullet preview will appear here</li>';
            return;
        }

        preview.innerHTML = bullets
            .map(function (item) {
                return '<li>' + item.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</li>';
            })
            .join('');
    }

    textarea.addEventListener('input', renderPreview);
    renderPreview();
});
</script>
