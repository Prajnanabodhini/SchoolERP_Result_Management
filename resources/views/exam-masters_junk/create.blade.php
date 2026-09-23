<x-app-layout>


@include('exam-masters.form', [
    'mode' => 'create',
    'examMaster' => null,
    'academicYears' => $academicYears,
    'standards' => $standards,
    'nextDisplayOrder' => $nextDisplayOrder,
    'hasUsedData' => false,
    'examType' => '',
    'initialStructure' => $initialStructure,
    'snapshot' => [],
    'subjects' => collect(),
])


</x-app-layout>
