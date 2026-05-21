{{-- Hidden skeleton HTML for AppSkeleton.js (loaded on admin, user, and app layouts) --}}
<div id="app-skeleton-templates" class="hidden" aria-hidden="true">
    @foreach(['sidebar', 'list', 'table', 'quiz-grid', 'grading', 'stats', 'modal', 'panel', 'text', 'badge'] as $skeletonVariant)
        <template data-skeleton="{{ $skeletonVariant }}">
            @include('components.skeleton-patterns', ['variant' => $skeletonVariant])
        </template>
    @endforeach
</div>
