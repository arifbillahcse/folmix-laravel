<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.shipping-zones.edit.title')
    </x-slot>

    @include('admin::settings.shipping-zones.form', ['zone' => $zone])
</x-admin::layouts>
