<x-app-layout>

<style>
.es-page, .es-page * { box-sizing: border-box; font-family: Arial, Helvetica, sans-serif !important; font-size: 13px; }
.es-page { background: #f0f0f0; padding: 20px; }
.es-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
.es-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
.es-header h2 { margin: 0; font-size: 21px; font-weight: 700; color: #1D4ED8; }

/* Filter bar */
.es-filter { display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
.es-filter-group { display: flex; flex-direction: column; gap: 5px; flex: 0 0 auto; }
.es-filter-label { font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .3px; white-space: nowrap; }
.es-filter-select {
    height: 34px; padding: 4px 28px 4px 10px;
    font-size: 13px; font-weight: 600;
    border: 1px solid #9CA3AF; border-radius: 6px;
    background: #fff; color: #111827;
    cursor: pointer; appearance: none; outline: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3e%3cpath fill='%236b7280' d='M0 0l5 6 5-6z'/%3e%3c/svg%3e");
    background-repeat: no-repeat; background-position: right 10px center;
}
.es-filter-select:focus { border-color: #2563EB; box-shadow: 0 0 0 2px rgba(37,99,235,.15); }
.es-select-year      { width: 170px; }
.es-select-standard  { width: 190px; }
.es-select-examtype  { width: 190px; }

.es-btn-filter, .es-btn-clear {
    height: 34px; min-width: 120px; padding: 0 18px;
    font-size: 13px; font-weight: 700; border-radius: 6px;
    display: inline-flex; align-items: center; justify-content: center;
    text-decoration: none; white-space: nowrap; cursor: pointer; border: 1px solid transparent;
}
.es-btn-filter { background: #16A34A; color: #fff !important; border-color: #15803D; }
.es-btn-filter:hover { background: #15803D; }
.es-btn-clear { background: #6B7280; color: #fff !important; border-color: #4B5563; }
.es-btn-clear:hover { background: #4B5563; }

.es-alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 14px; font-size: 12.5px; font-weight: 600; }
.es-alert.ok  { background: #dcfce7; border: 1px solid #86efac; color: #166534; }
.es-alert.err { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }

.es-info-bar {
    background: #eff6ff; border: 1px solid #93c5fd; color: #1e40af;
    padding: 10px 14px; border-radius: 8px; margin-bottom: 14px;
    font-weight: 700; font-size: 12.5px;
    display: flex; flex-wrap: wrap; gap: 16px;
}
.es-info-bar span strong { color: #1e3a8a; }

.es-block { background: #fafbfc; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 16px; overflow: hidden; }
.es-block-head {
    background: #1e3a8a; color: #fff; padding: 10px 14px;
    font-weight: 700; font-size: 12.5px; letter-spacing: .4px;
    display: flex; justify-content: space-between; align-items: center;
}
.es-block-head a {
    background: #2563EB; color: #fff !important; border: 1px solid #1D4ED8;
    padding: 5px 14px; font-size: 11.5px; font-weight: 700; border-radius: 5px;
    text-decoration: none; white-space: nowrap;
}
.es-block-head a:hover { background: #1D4ED8; }
.es-block-body { overflow-x: auto; background: #fff; }
.es-block table { width: 100%; border-collapse: collapse; font-size: 12px; min-width: 850px; }
.es-block thead th {
    background: #eff6ff; color: #1e3a8a; font-size: 10.5px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .3px; padding: 8px;
    border: 1px solid #d1d5db; text-align: center;
}
.es-block thead th:first-child { text-align: left; }

/* Group Headers */
.es-block thead th.group-summative { background: #e0e7ff; color: #3730a3; font-size: 11.5px; }
.es-block thead th.group-formative { background: #dcfce7; color: #166534; font-size: 11.5px; }

.es-block td { padding: 6px 8px; border: 1px solid #e5e7eb; text-align: center; color: #111827; font-size: 12px; }
.es-block td.subject-cell { text-align: left; font-weight: 700; }
.es-block td.total-cell { font-weight: 800; color: #92400E; background: #FEF3C7; white-space: nowrap; }

.es-empty {
    padding: 40px 20px; text-align: center; color: #6b7280;
    background: #fafbfc; border: 1px dashed #d1d5db;
    border-radius: 8px; font-weight: 600; font-size: 13px;
}
</style>

<div class="es-page">
    <div class="es-card">

        <div class="es-header">
            <h2>Exam Structure</h2>
        </div>

        @if(session('success'))
            <div class="es-alert ok">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="es-alert err">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('exam-structures.index') }}" id="esFilterForm">
            <div class="es-filter">
                <div class="es-filter-group">
                    <label class="es-filter-label">Academic Year</label>
                    <select name="academic_year_id" id="es_year" class="es-filter-select es-select-year">
                        <option value="">All Academic Years</option>
                        @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" {{ (string)$academicYearId === (string)$y->id ? 'selected' : '' }}>
                                {{ $y->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="es-filter-group">
                    <label class="es-filter-label">Standard</label>
                    <select name="standard_id" id="es_standard" class="es-filter-select es-select-standard">
                        <option value="">All Standards</option>
                        @foreach($standards as $s)
                            <option value="{{ $s->id }}" {{ (string)$standardId === (string)$s->id ? 'selected' : '' }}>
                                {{ $s->standard_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="es-filter-group">
                    <label class="es-filter-label">Exam Type</label>
                    <select name="exam_type" id="es_exam_type" class="es-filter-select es-select-examtype">
                        <option value="">All Exam Types</option>
                        @foreach($examTypes as $t)
                            <option value="{{ $t }}" {{ $selectedExamType === $t ? 'selected' : '' }}>
                                {{ $t }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="es-filter-group">
                    <button type="submit" class="es-btn-filter">Filter</button>
                </div>
                <div class="es-filter-group">
                    <a href="{{ route('exam-structures.index') }}" class="es-btn-clear">Clear</a>
                </div>
            </div>
        </form>

        @if($selectedStandard)

            <div class="es-info-bar">
                <span><strong>Standard:</strong> {{ $selectedStandard->standard_name }}</span>
                @if($selectedExamType)
                    <span><strong>Exam Type:</strong> {{ $selectedExamType }}</span>
                @endif
                <span><strong>Subjects:</strong> {{ $subjects->count() }}</span>
            </div>

            @forelse($grouped as $examType => $subjectRows)
                @php
                    $layout = $tableLayouts[$examType] ?? null;
                @endphp
                <div class="es-block">
                    <div class="es-block-head">
                        <span>{{ $examType }}</span>
                        <a href="{{ route('exam-structures.edit', [
                                'standardKey' => $selectedStandardKey,
                                'exam_type'   => $examType,
                            ]) }}">Edit</a>
                    </div>
                    <div class="es-block-body">
                        <table>
                            <thead>
                                @if($layout)
                                    <!-- Grouped Header Row -->
                                    <tr>
                                        <th style="width:200px;text-align:left;" rowspan="2">Subject</th>
                                        
                                        @php
                                            $s_theory = in_array('theory', $layout['sum_cols']);
                                            $s_oral = in_array('oral', $layout['sum_cols']);
                                            $s_prac = in_array('practical', $layout['sum_cols']);
                                            $sum_colspan = ($s_theory ? 2 : 0) + ($s_oral ? 2 : 0) + ($s_prac ? 2 : 0);

                                            $f_theory = in_array('theory', $layout['form_cols']);
                                            $f_oral = in_array('oral', $layout['form_cols']);
                                            $f_prac = in_array('practical', $layout['form_cols']);
                                            $form_colspan = ($f_theory ? 2 : 0) + ($f_oral ? 2 : 0) + ($f_prac ? 2 : 0);
                                        @endphp

                                        @if($sum_colspan > 0)
                                            <th colspan="{{ $sum_colspan }}" class="group-summative">{{ $layout['sum_lbl'] }}</th>
                                        @endif
                                        
                                        @if($form_colspan > 0)
                                            <th colspan="{{ $form_colspan }}" class="group-formative">{{ $layout['form_lbl'] }}</th>
                                        @endif
                                        
                                        <th rowspan="2" style="background: #fef08a; color: #854d0e; font-weight: 800;">Total Max / Pass</th>
                                    </tr>
                                    <!-- Sub Header Row -->
                                    <tr>
                                        @if($s_theory || $f_theory) <th>Theory Max</th><th>Theory Pass</th> @endif
                                        @if($s_oral || $f_oral) <th>Oral Max</th><th>Oral Pass</th> @endif
                                        @if($s_prac || $f_prac) <th>Prac Max</th><th>Prac Pass</th> @endif
                                    </tr>
                                @else
                                    <!-- Flat Header Row -->
                                    <tr>
                                        <th style="width:200px;text-align:left;">Subject</th>
                                        <th>Theory Max</th>
                                        <th>Theory Pass</th>
                                        <th>Oral Max</th>
                                        <th>Oral Pass</th>
                                        <th>Prac Max</th>
                                        <th>Prac Pass</th>
                                        <th>Total Max / Pass</th>
                                    </tr>
                                @endif
                            </thead>
                            <tbody>
                                @foreach($subjectRows as $row)
                                    <tr>
                                        <td class="subject-cell">{{ $row->subject_name }}</td>
                                        <td>{{ $row->theory_max_marks }}</td>
                                        <td>{{ $row->theory_passing_marks }}</td>
                                        <td>{{ $row->oral_max_marks }}</td>
                                        <td>{{ $row->oral_passing_marks }}</td>
                                        <td>{{ $row->practical_max_marks }}</td>
                                        <td>{{ $row->practical_passing_marks }}</td>
                                        <td class="total-cell">
                                            {{ $row->total_max_marks }} / {{ $row->total_passing_marks }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="es-empty">
                    No academic subjects found for <strong>{{ $selectedStandard->standard_name }}</strong>.
                    Map subjects in <strong>Subject Master</strong> first.
                </div>
            @endforelse

        @else
            <div class="es-empty">
                Select a <strong>Standard</strong> (and optionally an <strong>Exam Type</strong>), then click <strong>Filter</strong>.
            </div>
        @endif

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    ['es_year', 'es_standard'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', function () {
            const ex = document.getElementById('es_exam_type');
            if (ex) ex.value = '';
            document.getElementById('esFilterForm').submit();
        });
    });
});
</script>

</x-app-layout>