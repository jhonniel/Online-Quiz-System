@php
    $editorTitle = old('title', $titleValue ?? '');
    $editorContent = old('content', $contentValue ?? '');
@endphp
<div
    x-data="{
        title: @js($editorTitle),
        content: @js($editorContent),
        formatPreview(text) {
            const lines = (text || '').split(/\r?\n/);
            let html = '';
            let inUl = false;
            let inOl = false;
            const close = () => {
                if (inUl) { html += '</ul>'; inUl = false; }
                if (inOl) { html += '</ol>'; inOl = false; }
            };
            const escape = (s) => {
                const d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            };
            for (const raw of lines) {
                const line = raw.trim();
                if (!line) { close(); continue; }
                if (/^[-•*]\s+/.test(line)) {
                    if (inOl) { html += '</ol>'; inOl = false; }
                    if (!inUl) { html += '<ul class=\'announcement-list\'>'; inUl = true; }
                    html += '<li>' + escape(line.replace(/^[-•*]\s+/, '')) + '</li>';
                    continue;
                }
                if (/^\d+\.\s+/.test(line)) {
                    if (inUl) { html += '</ul>'; inUl = false; }
                    if (!inOl) { html += '<ol class=\'announcement-list announcement-list-ordered\'>'; inOl = true; }
                    html += '<li>' + escape(line.replace(/^\d+\.\s+/, '')) + '</li>';
                    continue;
                }
                close();
                html += '<p>' + escape(line) + '</p>';
            }
            close();
            return html || '<p class=\'text-gray-400 italic\'>Your message preview will appear here.</p>';
        }
    }"
>
    {{ $slot }}
</div>
