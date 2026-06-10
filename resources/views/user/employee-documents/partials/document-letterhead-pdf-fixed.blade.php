@php
    $topOffset = $topOffset ?? '-33mm';
@endphp

<style>
    .document-fixed-letterhead {
        position: fixed;
        top: {{ $topOffset }};
        left: 0;
        right: 0;
        text-align: center;
        z-index: 1000;
    }

    .document-fixed-letterhead img {
        display: block;
        width: 100%;
        max-width: 400px;
        max-height: 30mm;
        height: auto;
        margin: 0 auto;
    }

    .document-page-letterhead {
        display: none;
    }
</style>

<div class="document-fixed-letterhead">
    <x-document-letterhead-pdf />
</div>
