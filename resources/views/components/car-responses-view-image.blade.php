@if ($path)
    <div>
        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-gray-100">
            Picture After
        </label><br>
        <img src="{{ asset('storage/' . $path) }}" class="object-contain max-w-xs border rounded shadow md:max-w-sm"
            alt="After Image">
    </div>
@else
    <span class="text-gray-400 dark:text-gray-500">No image.</span>
@endif
