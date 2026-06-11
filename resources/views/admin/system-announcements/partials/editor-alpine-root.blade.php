@php
    $editorTitle = old('title', $titleValue ?? '');
    $editorContent = old('content', $contentValue ?? '');
    $editorFeatureLinks = old('feature_links', $featureLinksValue ?? []);
    if (! is_array($editorFeatureLinks)) {
        $editorFeatureLinks = [];
    }
@endphp
<div
    x-data="{
        title: @js($editorTitle),
        content: @js($editorContent),
        featureLinks: @js(array_values($editorFeatureLinks)),
        addFeatureLink() {
            this.featureLinks.push({ url: '', label: '' });
        },
        removeFeatureLink(index) {
            this.featureLinks.splice(index, 1);
        },
        featureLinkPreviews() {
            return this.featureLinks
                .map((link) => ({
                    href: (link.url || '').trim(),
                    label: (link.label || '').trim() || 'Open feature',
                }))
                .filter((link) => link.href !== '');
        },
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
