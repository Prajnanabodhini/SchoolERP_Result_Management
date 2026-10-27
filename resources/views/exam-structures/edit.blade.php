<x-app-layout>

<style>
.es-page, .es-page * { box-sizing: border-box; font-family: Arial, Helvetica, sans-serif !important; font-size: 13px; }
.es-page { background: #f0f0f0; padding: 20px; }
.es-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
.es-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; flex-wrap: wrap; }
.es-header h2 { margin: 0; font-size: 20px; font-weight: 700; color: #1D4ED8; }
.es-btn-back { background: #6B7280; color: #fff !important; border: 1px solid #4B5563; height: 34px; padding: 0 18px; font-size: 13px; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; white-space: nowrap; }
.es-btn-back:hover { background: #4B5563; }
.es-btn-save { background: #16A34A; color: #fff !important; border: 1px solid #15803D; padding: 0 24px; height: 34px; font-size: 13px; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; cursor: pointer; }
.es-btn-save:hover { background: #15803D; }
.es-btn-save:disabled { background: #9CA3AF; cursor: not-allowed; }

.es-alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 14px; font-size: 12.5px; font-weight: 600; }
.es-alert.ok { background: #dcfce7; border: 1px solid #86efac; color: #166534; }
.es-alert.err { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }

.es-info-bar {
    background: #eff6ff; border: 1px solid #93c5fd; color: #1e40af;
    padding: 10px 14px; border-radius: 8px; margin-bottom: 14px;
    font-weight: 700; font-size: 12.5px; display: flex; gap: 16px; flex-wrap: wrap;
}
.es-info-bar span strong { color: #1e3a8a; }

.es-block { background: #fafbfc; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 16px; overflow: hidden; }
.es-block-head {
    background: #1e3a8a; color: #fff; padding: 10px 14px;
    font-weight: 700; font-size: 12.5px; letter-spacing: .4px;
}
.es-block-body { overflow-x: auto; background: #fff; }
.es-block table { width: 100%; border-collapse: collapse; font-size: 12px; min-width: 1100px; }
.es-block thead th {
    background: #eff6ff; color: #1e3a8a; font-size: 10.5px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .3px; padding: 8px;
    border: 1px solid #d1d5db; text-align: center; white-space: nowrap;
}
.es-block thead th:first-child { text-align: left; }
.es-block thead th.total-header { background: #fde68a; color: #7c2d12; }
.es-block td { padding: 6px; border: 1px solid #e5e7eb; text-align: center; color: #111827; }
.es-block td.subject-cell { text-align: left; font-weight: 700; padding: 8px; }
.es-block input[type="number"] {
    width: 80px; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px;
    text-align: center; font-size: 12px; font-weight: 600;
}
.es-block input[type="number"]:focus {
    border-color: #2563EB; box-shadow: 0 0 0 2px rgba(37,99,235,.15); outline: none;
}
.es-block input[type="checkbox"] { width: 16px; height: 16px; accent-color: #16A34A; cursor: pointer; }

/* Editable total cells — highlighted amber background */
.es-block td.total-cell {
    background: #FEF3C7;
    padding: 6px 4px;
}
.es-block td.total-cell input[type="number"] {
    background: #fffbeb;
    border-color: #fbbf24;
    color: #7c2d12;
    font-weight: 800;
    width: 70px;
}
.es-block td.total-cell input[type="number"]:focus {
    border-color: #d97706;
    box-shadow: 0 0 0 2px rgba(217,119,6,.2);
}

.es-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
</style>

<div class="es-page">
    <div class="es-card">

        <div class="es-header">
            <h2>Exam Structure — {{ $standard->standard_name }}</h2>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('exam-structures.index', ['standard_id' => $standard->id, 'exam_type' => $examTypeFilter]) }}"
                   class="es-btn-back">← Back</a>
            </div>
        </div>

        @if(session('success'))
            <div class="es-alert ok">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="es-alert err">{{ session('error') }}</div>
        @endif

        <div class="es-info-bar">
            <span><strong>Standard:</strong> {{ $standard->standard_name }}</span>
            @if($examTypeFilter)
                <span><strong>Exam Type:</strong> {{ $examTypeFilter }}</span>
            @else
                <span><strong>Showing:</strong> All Exam Types</span>
            @endif
        </div>

        <form method="POST" action="{{ route('exam-structures.update', $standardKey) }}" id="esForm">
            @csrf
            @method('PUT')

            @if($examTypeFilter)
                <input type="hidden" name="_exam_type" value="{{ $examTypeFilter }}">
            @endif

            @forelse($grouped as $examType => $subjectRows)
                <div class="es-block">
                    <div class="es-block-head">{{ $examType }}</div>

                    <div class="es-block-body">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:200px;text-align:left;">Subject</th>
                                    <th>Theory<br>Max</th>
                                    <th>Theory<br>Pass</th>
                                    <th>Oral<br>Max</th>
                                    <th>Oral<br>Pass</th>
                                    <th>Prac<br>Max</th>
                                    <th>Prac<br>Pass</th>
                                    <th class="total-header">Total<br>Max</th>
                                    <th class="total-header">Total<br>Pass</th>
                                    <th>Pass<br>%</th>
                                    <th>Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjectRows as $row)
                                    <tr>
                                        <td class="subject-cell">{{ $row->subject_name }}</td>

                                        @foreach(['theory_max_marks','theory_passing_marks',
                                                  'oral_max_marks','oral_passing_marks',
                                                  'practical_max_marks','practical_passing_marks'] as $f)
                                            <td>
                                                <input type="number" step="0.01" min="0"
                                                       name="rows[{{ $examType }}][{{ $row->subject_id }}][{{ $f }}]"
                                                       value="{{ $row->$f }}">
                                            </td>
                                        @endforeach

                                        {{-- ✅ Editable Total Max --}}
                                        <td class="total-cell">
                                            <input type="number" step="0.01" min="0"
                                                   name="rows[{{ $examType }}][{{ $row->subject_id }}][total_max_marks]"
                                                   value="{{ $row->total_max_marks }}">
                                        </td>

                                        {{-- ✅ Editable Total Pass --}}
                                        <td class="total-cell">
                                            <input type="number" step="0.01" min="0"
                                                   name="rows[{{ $examType }}][{{ $row->subject_id }}][total_passing_marks]"
                                                   value="{{ $row->total_passing_marks }}">
                                        </td>

                                        <td>
                                            <input type="number" step="0.01" min="0" max="100"
                                                   name="rows[{{ $examType }}][{{ $row->subject_id }}][passing_percentage]"
                                                   value="{{ $row->passing_percentage }}"
                                                   style="width:70px;">
                                        </td>
                                        <td>
                                            <input type="checkbox"
                                                   name="rows[{{ $examType }}][{{ $row->subject_id }}][is_active]"
                                                   value="1" {{ $row->is_active ? 'checked' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="es-alert err">
                    No academic subjects found. Map subjects in <strong>Subject Master</strong> first.
                </div>
            @endforelse

            <div class="es-actions">
                <button type="submit" class="es-btn-save" id="esSaveBtn">💾 Save Changes</button>
            </div>
        </form>

    </div>
</div>

<script>
document.getElementById('esForm').addEventListener('submit', function () {
    const b = document.getElementById('esSaveBtn');
    b.disabled = true;
    b.innerText = 'Saving...';
});
</script>

</x-app-layout>