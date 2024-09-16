<div
    class="size-chart"
    role="button"
    data-bs-toggle="modal"
    data-bs-target="#size-chart-modal"
>View Size Chart</div>

<x-core::modal.action
    id="size-chart-modal"
    type="info"
    title="Size Chart"
    size="md"
>
    <div class="text-start">
        <img src="/storage/jewelry/ring-sizes-chart.jpg">
    </div>
</x-core::modal.action>
<style>
    .modal-backdrop{opacity:0.5 !important;display: none;}
    .text-start img {max-width: 100%;}
    .modal-footer, .mb-2{display: none;}
    .btn-close {float: right;}
</style>
