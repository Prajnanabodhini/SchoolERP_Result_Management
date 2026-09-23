<x-app-layout>
    <div class="max-w-7xl mx-auto py-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-blue-700 uppercase">
                    STANDARD SUBJECT ALLOCATION
                </h2>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded mb-6 text-base">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Filter Form -->
            <form method="GET" action="{{ route('standard-subject-allocation.index') }}">
                <div class="flex flex-wrap items-end gap-4 mb-6">
                    <div class="w-64">
                        <label class="block text-base font-medium text-gray-700 mb-1">
                            Select Standard <span class="text-red-500">*</span>
                        </label>
                        <select name="standard_id" class="w-full border border-gray-300 rounded px-3 h-10 text-base focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Standard</option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->id }}" {{ $selectedStandard == $standard->id ? 'selected' : '' }}>
                                    {{ $standard->standard_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 h-10 rounded text-base transition duration-150">
                            Load Subjects
                        </button>
                    </div>
                </div>
            </form>

            @if($selectedStandard && ($academicSubjects->count() || $skillSubjects->count()))
                <form method="POST" action="{{ route('standard-subject-allocation.save') }}">
                    @csrf
                    <input type="hidden" name="standard_id" value="{{ $selectedStandard }}">

                    @php
                        $maxRows = max($academicSubjects->count(), $skillSubjects->count());
                    @endphp

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="w-full text-base">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 border-b w-1/2">Academic Subjects</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 border-b w-1/2">Skill Subjects</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @for($i = 0; $i < $maxRows; $i++)
                                    <tr class="hover:bg-gray-50">
                                        <!-- Academic Column -->
                                        <td class="px-4 py-2 align-top">
                                            @if(isset($academicSubjects[$i]))
                                                @php $subject = $academicSubjects[$i]; @endphp
                                                <div class="flex items-center mb-1">
                                                    <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" class="mr-2"
                                                        {{ in_array($subject->id, $allocatedSubjects) ? 'checked' : '' }}>
                                                    <label>{{ $subject->subject_name }}</label>
                                                </div>
                                            @endif
                                        </td>
                                        <!-- Skill Column (Merged) -->
                                        <td class="px-4 py-2 align-top">
                                            @if(isset($skillSubjects[$i]))
                                                @php $subject = $skillSubjects[$i]; @endphp
                                                <div class="flex items-center mb-1">
                                                    <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" class="mr-2"
                                                        {{ in_array($subject->id, $allocatedSubjects) ? 'checked' : '' }}>
                                                    <label>{{ $subject->subject_name }}</label>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 h-10 rounded text-base transition duration-150">
                            Save Allocation
                        </button>
                    </div>
                </form>
            @elseif($selectedStandard)
                <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p class="text-base text-gray-500">No subjects found for this standard.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>