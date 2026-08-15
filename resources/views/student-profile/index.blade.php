<x-app-layout>

<div class="max-w-7xl mx-auto py-4 px-4">

    <div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

        <h2 class="text-2xl font-bold text-blue-600 mb-6">
            Student Complete Profile
        </h2>

        <form method="GET"
              action="{{ route('student-profile.index') }}">

            <div class="flex items-center gap-3 mb-6">

                <label class="font-semibold whitespace-nowrap">
                    Search Student
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Student Name / Student ID / Father Mobile / Saral ID"
                       class="border rounded px-3 py-2 w-96">

                <button type="submit"
                        class="erp-btn erp-btn-save">
                    Search
                </button>

            </div>

        </form>

        @if($student)

@php
    $fields = $student->getAttributes();

    $chunks = array_chunk(
        array_keys($fields),
        2
    );
@endphp

<div class="overflow-x-auto">

    <table class="w-full border border-gray-300">

        <tbody>

        @foreach($chunks as $pair)

            <tr>

                {{-- First Field --}}
                <td class="border p-2 bg-gray-100 font-semibold w-48">
                    {{ $pair[0] }}
                </td>

                <td class="border p-2">
                    {{ $fields[$pair[0]] }}
                </td>

                {{-- Second Field --}}
                @if(isset($pair[1]))

                    <td class="border p-2 bg-gray-100 font-semibold w-48">
                        {{ $pair[1] }}
                    </td>

                    <td class="border p-2">
                        {{ $fields[$pair[1]] }}
                    </td>

                @else

                    <td class="border p-2"></td>
                    <td class="border p-2"></td>

                @endif

            </tr>

        @endforeach

        </tbody>

    </table>

</div>

      @elseif(request('search'))

            <div class="bg-red-100 text-red-700 p-3 rounded">
                Student Not Found
            </div>

        @else

            <div class="bg-blue-100 text-blue-700 p-3 rounded">
                Enter Student Name, Student ID, Mobile Number or Saral ID.
            </div>

        @endif

    </div>

</div>

</x-app-layout>