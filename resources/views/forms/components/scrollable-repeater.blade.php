<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="w-full overflow-x-auto">
        <div class="min-w-[1500px]">
            {{ $getChildComponentContainer() }}
        </div>
    </div>
</x-dynamic-component>