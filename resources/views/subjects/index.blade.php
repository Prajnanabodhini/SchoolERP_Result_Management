<x-app-layout>

<div class="max-w-7xl mx-auto py-4 px-4">

    <div class="bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100
                rounded-xl shadow-xl border-4 border-amber-400 p-5">

        {{-- HEADER --}}
        <div class="flex justify-between items-center mb-4">

            <h2 class="text-2xl font-bold text-blue-700">
                Standard Wise Subject Mapping
            </h2>

            <a href="{{ route('subjects.create') }}"
               class="erp-btn erp-btn-add">
                + Add Subject
            </a>

        </div>


        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))

            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>

        @endif


        {{-- ERROR MESSAGE --}}
        @if(session('error'))

            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                {{ session('error') }}
            </div>

        @endif


        {{-- VALIDATION ERRORS --}}
        @if ($errors->any())

            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">

                <ul class="list-disc ml-5">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- FILTER --}}
        <form method="GET"
              action="{{ route('subjects.index') }}"
              class="mb-5">

            <div class="flex gap-3 items-center flex-wrap">

                <select name="standard_id"
                        class="border rounded px-3 py-2"
                        style="width:250px;height:40px;">

                    <option value="">
                        ALL STANDARDS
                    </option>

                    @foreach($standards as $standard)

                        <option value="{{ $standard->id }}"
                            {{ request('standard_id') == $standard->id ? 'selected' : '' }}>

                            {{ $standard->standard_name }}

                        </option>

                    @endforeach

                </select>


                <button type="submit"
                        class="erp-btn erp-btn-save">

                    Search

                </button>


                @if(request('standard_id'))

                    <a href="{{ route('subjects.index') }}"
                       class="erp-btn erp-btn-cancel">

                        Clear

                    </a>

                @endif

            </div>

        </form>


        {{-- SUBJECT TABLE --}}
        <div class="overflow-x-auto">

            <table class="w-full border border-gray-400 bg-white text-sm">

                <thead class="bg-blue-200">

                    <tr>

                        <th class="border border-gray-400 p-2">
                            #
                        </th>
<th class="border border-gray-400 p-2">
                            Subject_id
                        </th>
                        <th class="border border-gray-400 p-2">
                            Standard
                        </th>

                        <th class="border border-gray-400 p-2">
                            Subject Name
                        </th>

                        <th class="border border-gray-400 p-2">
                            Subject Code
                        </th>

                        <th class="border border-gray-400 p-2">
                            Short Name
                        </th>

                        {{-- <th class="border border-gray-400 p-2">
                            Subject_id
                        </th>

                        <th class="border border-gray-400 p-2">
                            Sort Order
                        </th> --}}

                        <th class="border border-gray-400 p-2">
                            Optional
                        </th>

                        <th class="border border-gray-400 p-2">
                            Status
                        </th>

                        <th class="border border-gray-400 p-2">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($subjects as $index => $mapping)

                        <tr class="hover:bg-yellow-50">

                            {{-- NUMBER --}}
                            <td class="border border-gray-400 p-2 text-center">
                                {{ $index + 1 }}
                            </td>

                            <td class="border border-gray-400 p-2 text-center">

                                {{ $mapping->subject->id ?? '-' }}

                            </td>

                            {{-- STANDARD --}}
                            <td class="border border-gray-400 p-2">

                                {{ $mapping->standard->standard_name
                                    ?? $mapping->standard_name
                                    ?? '-' }}

                            </td>


                            {{-- SUBJECT NAME --}}
                            <td class="border border-gray-400 p-2 font-semibold">

                                {{ $mapping->subject->subject_name
                                    ?? $mapping->subject_name
                                    ?? '-' }}

                            </td>


                            {{-- SUBJECT CODE --}}
                            <td class="border border-gray-400 p-2 text-center">

                                {{ $mapping->subject->subject_code ?? '-' }}

                            </td>


                            {{-- SHORT NAME --}}
                            <td class="border border-gray-400 p-2 text-center">

                                {{ $mapping->subject->short_name ?? '-' }}

                            </td>


                            {{-- SUBJECT TYPE --}}
                            <td class="border border-gray-400 p-2 text-center">

                                {{ $mapping->subject->subjectType->subject_type_name
                                    ?? $mapping->subject->subjectType->name
                                    ?? '-' }}

                            </td>



                            {{-- STATUS --}}
                            <td class="border border-gray-400 p-2 text-center">

                                @if($mapping->is_active)

                                    <span class="text-green-700 font-semibold">
                                        Active
                                    </span>

                                @else

                                    <span class="text-red-700 font-semibold">
                                        Inactive
                                    </span>

                                @endif

                            </td>


                            {{-- ACTION --}}
<td class="border border-gray-400 p-2 text-center">

    <a href="{{ route('subjects.edit', $mapping->id) }}"
       style="
           background:#f59e0b;
           color:#fff;
           padding:8px 14px;
           border-radius:6px;
           text-decoration:none;
           font-size:14px;
           font-weight:600;
           display:inline-block;
       ">
        Edit
    </a>

</td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="10"
                                class="border border-gray-400 p-5 text-center text-gray-600">

                                No Subject Mapping Found

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

</x-app-layout>