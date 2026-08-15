<x-app-layout>

<style>

.exam-master-page,
.exam-master-page * {
    font-family: Arial, sans-serif !important;
    font-size: 13px !important;
}

.exam-master-page h2 {
    font-size: 22px !important;
    font-weight: 700 !important;
    color: #1D4ED8 !important;
    margin: 0;
}

.exam-master-page table {
    width: 100%;
    font-size: 13px !important;
    border-collapse: collapse;
}

.exam-master-page table th {
    font-size: 13px !important;
    font-weight: 700 !important;
    text-align: center;
    padding: 8px !important;
}

.exam-master-page table td {
    font-size: 13px !important;
    padding: 8px !important;
}

.exam-master-page .erp-btn {
    font-size: 13px !important;
    padding: 5px 10px !important;
}

.exam-master-page .text-green-700,
.exam-master-page .text-red-700 {
    font-size: 13px !important;
    font-weight: 600 !important;
}

</style>


<div class="max-w-7xl mx-auto py-4 px-4 exam-master-page">

    <div class="
        bg-gradient-to-br
        from-red-100
        via-yellow-100
        to-orange-100
        rounded-xl
        shadow-xl
        border-4
        border-amber-400
        p-5
    ">

        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <div class="flex justify-between items-center mb-4">

            <h2>
                Exam Master
            </h2>

            <a href="{{ route('exam-masters.create') }}"
               class="erp-btn erp-btn-add">

                + Add Exam

            </a>

        </div>


        {{-- =====================================================
             SUCCESS MESSAGE
        ====================================================== --}}

        @if(session('success'))

            <div class="
                bg-green-100
                border
                border-green-300
                text-green-700
                p-3
                rounded
                mb-4
            ">

                {{ session('success') }}

            </div>

        @endif


        {{-- =====================================================
             ERROR MESSAGE
        ====================================================== --}}

        @if(session('error'))

            <div class="
                bg-red-100
                border
                border-red-300
                text-red-700
                p-3
                rounded
                mb-4
            ">

                {{ session('error') }}

            </div>

        @endif


        {{-- =====================================================
             EXAM MASTER TABLE
        ====================================================== --}}

        <div class="overflow-x-auto">

            <table class="
                border
                border-gray-400
                bg-white
            ">

                <thead class="bg-blue-200">

                    <tr>

                        <th class="border">
                            Exam ID
                        </th>

                        <th class="border">
                            Exam Name
                        </th>

                        <th class="border">
                            Standard ID
                        </th>

                        <th class="border">
                            Standard Name
                        </th>

                        <th class="border">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($examMasters as $exam)

                        <tr class="hover:bg-yellow-50">

                            {{-- EXAM ID --}}

                            <td class="border text-center">
                                {{ $exam->id }}
                            </td>


                            {{-- EXAM NAME --}}

                            <td class="border">
                                {{ $exam->exam_name }}
                            </td>


                            {{-- STANDARD ID --}}

                            <td class="border text-center">
                                {{ $exam->standard_id }}
                            </td>


                            {{-- STANDARD NAME --}}

                            <td class="border">

                                {{ $exam->standard->standard_name ?? '-' }}

                            </td>


                            {{-- ACTION --}}

                            <td class="
                                border
                                text-center
                                whitespace-nowrap
                            ">

                                {{-- EDIT --}}

                                <a href="{{ route(
                                    'exam-masters.edit',
                                    $exam->id
                                ) }}"
                                   class="erp-btn erp-btn-edit">

                                    Edit

                                </a>


                                {{-- DELETE --}}

                                <form
                                    action="{{ route(
                                        'exam-masters.destroy',
                                        $exam->id
                                    ) }}"
                                    method="POST"
                                    style="display:inline-block;"
                                >

                                    @csrf

                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="erp-btn erp-btn-delete"
                                        onclick="return confirmExamDelete();"
                                    >

                                        Force Delete

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="
                                    border
                                    p-4
                                    text-center
                                    text-gray-500
                                "
                            >

                                No Records Found

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- =========================================================
     DELETE CONFIRMATION
========================================================= --}}

<script>

function confirmExamDelete()
{
    return confirm(
        'FINAL WARNING!\n\n' +

        'You are about to permanently delete this Exam.\n\n' +

        'Depending on the controller/database relationships, ' +
        'the following related records may also be deleted:\n\n' +

        '• Exam Subject Configuration\n' +
        '• Teacher Marks Status\n' +
        '• Marks Entries\n' +
        '• Student Marks\n' +
        '• Student Results\n' +
        '• Result Details\n\n' +

        'THIS ACTION CANNOT BE UNDONE.\n\n' +

        'Click OK only if you are sure you want to delete this Exam.'
    );
}

</script>

</x-app-layout>