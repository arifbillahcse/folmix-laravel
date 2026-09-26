<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.activity-log.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('admin::app.activity-log.index.title')
        </p>
    </div>

    {!! view_render_event('bagisto.admin.activity_log.list.before') !!}

    <x-admin::datagrid :src="route('admin.activity_log.index')" />

    {!! view_render_event('bagisto.admin.activity_log.list.after') !!}
</x-admin::layouts>
