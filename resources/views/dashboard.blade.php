<x-app-layout>

<div class="p-3">

    <div class="bg-white border rounded shadow p-3">

        <h2 class="text-xl font-bold text-amber-800">
            Pending Marks Entry
        </h2>

    </div>

    @if($pendingEntries->count())

    <div class="bg-white border rounded shadow p-3 mt-4">

        <table class="w-full border border-gray-300 text-sm">

            <thead class="bg-blue-100">

                <tr>

                    <th class="border p-2">
                        Exam
                    </th>

                    <th class="border p-2">
                        Subject
                    </th>

                    <th class="border p-2">
                        Standard
                    </th>

                    <th class="border p-2">
                        Division
                    </th>

                    <th class="border p-2">
                        Action
                    </th>

                </tr>

            </thead>

            <tbody>

            @foreach($pendingEntries as $entry)

                <tr>

                    <td class="border p-2">
                        {{ $entry->exam_name }}
                    </td>

                    <td class="border p-2">
                        {{ $entry->subject_name }}
                    </td>

                    <td class="border p-2">
                        {{ $entry->standard_name }}
                    </td>

                    <td class="border p-2">
                        {{ $entry->division_name }}
                    </td>

                    <td class="border p-2 text-center">

                        <a href="{{ route('marks-entry.index',[
                            'exam_master_id' =>
                                $entry->exam_id,

                            'teacher_subject_allocation_id' =>
                                $entry->teacher_subject_allocation_id
                        ]) }}"
                        class="erp-btn erp-btn-save">

                            Enter Marks

                        </a>

                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

    @else

    <div class="bg-green-100 border border-green-400 rounded p-4 mt-4">

        <strong>
            All marks entries completed.
        </strong>

    </div>

    @endif

</div>

</x-app-layout>