<script>
(function () {
    const PREVIEW_LIBS = {
        xlsx: 'https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js',
        mammoth: 'https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.8.0/mammoth.browser.min.js',
    };

    let xlsxPromise = null;
    let mammothPromise = null;

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Failed to load preview library.'));
            document.head.appendChild(script);
        });
    }

    function ensureXlsx() {
        if (window.XLSX) {
            return Promise.resolve();
        }

        if (!xlsxPromise) {
            xlsxPromise = loadScript(PREVIEW_LIBS.xlsx);
        }

        return xlsxPromise;
    }

    function ensureMammoth() {
        if (window.mammoth) {
            return Promise.resolve();
        }

        if (!mammothPromise) {
            mammothPromise = loadScript(PREVIEW_LIBS.mammoth);
        }

        return mammothPromise;
    }

    function extension(name) {
        const parts = String(name || '').split('.');
        return parts.length > 1 ? parts.pop().toLowerCase() : '';
    }

    function detectPreviewKind(name, mimeType) {
        const mime = String(mimeType || '').toLowerCase();
        const ext = extension(name);

        if (mime.startsWith('image/')) return 'image';
        if (mime === 'application/pdf') return 'pdf';
        if (mime.startsWith('video/')) return 'video';
        if (mime.startsWith('audio/')) return 'audio';

        if (mime.includes('spreadsheet') || mime.includes('excel') || ['xlsx', 'xls', 'xlsm', 'xlsb'].includes(ext)) {
            return 'spreadsheet';
        }

        if (mime === 'text/csv' || mime === 'application/csv' || mime === 'text/comma-separated-values' || ext === 'csv') {
            return 'csv';
        }

        if (mime.includes('wordprocessingml') || mime === 'application/msword' || ['docx', 'doc'].includes(ext)) {
            return mime === 'application/msword' || ext === 'doc' ? 'legacy-document' : 'document';
        }

        if (['txt', 'text/plain'].includes(ext) || mime.startsWith('text/')) {
            return 'text';
        }

        return 'unsupported';
    }

    function showLoading(container) {
        container.innerHTML = '<div class="text-center py-12"><p class="text-gray-500">Loading preview...</p></div>';
        container.className = 'bg-gray-50 rounded-lg p-4 min-h-[300px] flex items-center justify-center';
    }

    function showError(container, message) {
        container.innerHTML = '<div class="text-center py-12"><p class="text-gray-500">' + message + '</p><p class="text-sm text-gray-400 mt-2">Use the download button to open the file.</p></div>';
        container.className = 'bg-gray-50 rounded-lg p-4 min-h-[300px] flex items-center justify-center';
    }

    function styleSpreadsheetTable(table) {
        table.className = 'min-w-full text-xs sm:text-sm border-collapse bg-white';
        table.querySelectorAll('td, th').forEach((cell) => {
            cell.className = 'border border-gray-200 px-2 py-1 align-top whitespace-nowrap text-gray-800';
        });
        table.querySelectorAll('tr:first-child td, tr:first-child th').forEach((cell) => {
            cell.classList.add('bg-gray-100', 'font-semibold');
        });
    }

    function trimWorksheet(sheet, maxRows, maxCols) {
        if (!sheet || !sheet['!ref']) {
            return sheet;
        }

        const range = window.XLSX.utils.decode_range(sheet['!ref']);
        range.e.r = Math.min(range.e.r, range.s.r + maxRows - 1);
        range.e.c = Math.min(range.e.c, range.s.c + maxCols - 1);
        sheet['!ref'] = window.XLSX.utils.encode_range(range);

        return sheet;
    }

    async function fetchAuthenticated(url) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to load file for preview.');
        }

        return response;
    }

    async function renderSpreadsheetPreview(container, url, name) {
        await ensureXlsx();
        const response = await fetchAuthenticated(url);
        const ext = extension(name);
        let workbook;

        if (ext === 'csv') {
            const text = await response.text();
            workbook = window.XLSX.read(text, { type: 'string' });
        } else {
            const buffer = await response.arrayBuffer();
            workbook = window.XLSX.read(buffer, { type: 'array' });
        }

        if (!workbook.SheetNames.length) {
            showError(container, 'This spreadsheet is empty.');
            return;
        }

        const sheet = trimWorksheet({ ...workbook.Sheets[workbook.SheetNames[0]] }, 500, 50);
        const tableHtml = window.XLSX.utils.sheet_to_html(sheet, { editable: false });
        const wrapper = document.createElement('div');
        wrapper.className = 'overflow-auto max-h-[70vh] w-full rounded-lg border border-gray-200 bg-white p-2 text-left';
        wrapper.innerHTML = tableHtml;
        wrapper.querySelectorAll('table').forEach(styleSpreadsheetTable);

        container.innerHTML = '';
        container.className = 'bg-gray-50 rounded-lg p-4 min-h-[300px] w-full';
        container.appendChild(wrapper);
    }

    async function renderDocumentPreview(container, url) {
        await ensureMammoth();
        const response = await fetchAuthenticated(url);
        const buffer = await response.arrayBuffer();
        const result = await window.mammoth.convertToHtml({ arrayBuffer: buffer });
        const wrapper = document.createElement('div');
        wrapper.className = 'overflow-auto max-h-[70vh] w-full rounded-lg border border-gray-200 bg-white p-6 text-left prose prose-sm max-w-none text-gray-800';
        wrapper.innerHTML = result.value || '<p class="text-gray-500">Document is empty.</p>';
        container.innerHTML = '';
        container.className = 'bg-gray-50 rounded-lg p-4 min-h-[300px] w-full';
        container.appendChild(wrapper);
    }

    async function renderTextPreview(container, url) {
        const response = await fetchAuthenticated(url);
        const text = await response.text();
        const pre = document.createElement('pre');
        pre.className = 'overflow-auto max-h-[70vh] w-full rounded-lg border border-gray-200 bg-white p-4 text-left text-xs sm:text-sm text-gray-800 whitespace-pre-wrap break-words';
        pre.textContent = text.slice(0, 200000);
        container.innerHTML = '';
        container.className = 'bg-gray-50 rounded-lg p-4 min-h-[300px] w-full';
        container.appendChild(pre);
    }

    window.renderFileStoragePreview = async function (container, options) {
        const name = options.name || '';
        const mimeType = options.mimeType || '';
        const url = options.url || '';
        const kind = detectPreviewKind(name, mimeType);

        showLoading(container);

        try {
            if (kind === 'image') {
                container.innerHTML = '';
                container.className = 'bg-gray-50 rounded-lg p-4 min-h-[300px] flex items-center justify-center';
                const img = document.createElement('img');
                img.src = url;
                img.className = 'max-w-full max-h-[70vh] mx-auto rounded-lg';
                img.alt = name;
                container.appendChild(img);
                return;
            }

            if (kind === 'pdf') {
                container.innerHTML = '';
                container.className = 'bg-gray-50 rounded-lg p-4 min-h-[300px] w-full';
                const iframe = document.createElement('iframe');
                iframe.src = url;
                iframe.className = 'w-full h-[70vh] border-0 rounded-lg bg-white';
                container.appendChild(iframe);
                return;
            }

            if (kind === 'video') {
                container.innerHTML = '';
                container.className = 'bg-gray-50 rounded-lg p-4 min-h-[300px] flex items-center justify-center';
                const video = document.createElement('video');
                video.src = url;
                video.controls = true;
                video.controlsList = 'nodownload';
                video.preload = 'metadata';
                video.playsInline = true;
                video.className = 'max-w-full max-h-[70vh] mx-auto rounded-lg bg-black';
                container.appendChild(video);
                return;
            }

            if (kind === 'audio') {
                container.innerHTML = '';
                container.className = 'bg-gray-50 rounded-lg p-4 min-h-[300px] flex items-center justify-center';
                const audio = document.createElement('audio');
                audio.src = url;
                audio.controls = true;
                audio.className = 'w-full mx-auto';
                container.appendChild(audio);
                return;
            }

            if (kind === 'spreadsheet' || kind === 'csv') {
                await renderSpreadsheetPreview(container, url, name);
                return;
            }

            if (kind === 'document') {
                await renderDocumentPreview(container, url);
                return;
            }

            if (kind === 'legacy-document') {
                showError(container, 'Preview is not available for older .doc files.');
                return;
            }

            if (kind === 'text') {
                await renderTextPreview(container, url);
                return;
            }

            showError(container, 'Preview not available for this file type.');
        } catch (error) {
            showError(container, error?.message || 'Preview failed to load.');
        }
    };
})();
</script>
