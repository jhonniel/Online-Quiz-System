{{-- Responsive Table Component --}}
@props([
    'headers' => [],
    'data' => [],
    'emptyMessage' => 'No data found',
    'emptyIcon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
])

@if($data->count() > 0)
    <div class="mobile-table-scroll scrollbar-thin-x flex-1">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            @if(isset($header['responsive']))
                                <span class="{{ $header['responsive'] }}">{{ $header['text'] }}</span>
                            @else
                                {{ $header['text'] }}
                            @endif
                        </th>
                    @endforeach
                    <th scope="col" class="relative px-3 sm:px-4 lg:px-6 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(isset($data) && method_exists($data, 'links'))
        <div class="bg-white px-3 sm:px-6 lg:px-6 py-3 flex items-center justify-between border-t border-gray-200 flex-shrink-0">
            <div class="flex-1 flex justify-between sm:hidden">
                {{ $data->links() }}
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div class="flex items-center">
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ $data->firstItem() }}</span>
                        to
                        <span class="font-medium">{{ $data->lastItem() }}</span>
                        of
                        <span class="font-medium">{{ $data->total() }}</span>
                        results
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-700">Rows per page:</span>
                    <select class="js-per-page-select text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div>
                    {{ $data->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-per-page-select').forEach((select) => {
                select.addEventListener('change', function () {
                    const url = new URL(window.location.href);
                    const params = new URLSearchParams(url.search);
                    params.set('per_page', this.value);
                    params.delete('page');
                    url.search = params.toString();
                    window.location.href = url.toString();
                });
            });
        });
    </script>
@else
    <div class="text-center py-12 flex-1 flex items-center justify-center">
        <div>
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $emptyIcon }}"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $emptyMessage }}</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by adding new data.</p>
        </div>
    </div>
@endif
