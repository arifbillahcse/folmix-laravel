<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.shipping-zones.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex flex-col gap-1">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.settings.shipping-zones.index.title')
            </p>

            <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                @lang('admin::app.settings.shipping-zones.index.info')
            </p>
        </div>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('settings.shipping_zones.create'))
                <a href="{{ route('admin.settings.shipping_zones.create') }}">
                    <div class="primary-button">
                        @lang('admin::app.settings.shipping-zones.index.create-btn')
                    </div>
                </a>
            @endif
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.settings.shipping_zones.index')" />
</x-admin::layouts>
