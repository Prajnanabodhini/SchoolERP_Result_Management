<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

    <div class="flex justify-between items-center mb-4">

        <h2 class="text-xl font-bold text-blue-600">
            {{ $title }}
        </h2>

        @isset($action)
            {{ $action }}
        @endisset

    </div>

    {{ $slot }}

</div>