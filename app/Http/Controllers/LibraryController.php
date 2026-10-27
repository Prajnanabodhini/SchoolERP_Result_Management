<?php

namespace App\Http\Controllers;

use App\Helpers\StudentHelper;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\LibraryBook;
use App\Models\LibraryIssue;
use App\Models\Standard;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class LibraryController extends Controller
{
    /* ===========================================================
     | LIST PAGES
     | =========================================================== */

    public function index(Request $request)
    {
        return $this->renderList($request);
    }

    public function pending(Request $request)
    {
        $request->merge(['status' => 'issued']);
        return $this->renderList($request);
    }

    public function overdue(Request $request)
    {
        $request->merge(['status' => 'overdue']);
        return $this->renderList($request);
    }

    protected function renderList(Request $request)
    {
        $query = LibraryIssue::query();

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('status', 'issued')
                      ->whereDate('due_date', '<', Carbon::today());
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('borrower_type')) {
            $query->where('borrower_type', $request->borrower_type);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('borrower_name', 'like', "%{$search}%")
                  ->orWhere('borrower_id', 'like', "%{$search}%")
                  ->orWhere('borrower_class', 'like', "%{$search}%")
                  ->orWhere('book_name', 'like', "%{$search}%")
                  ->orWhere('book_code', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $summary = [
            'total'    => LibraryIssue::count(),
            'issued'   => LibraryIssue::where('status', 'issued')->count(),
            'returned' => LibraryIssue::where('status', 'returned')->count(),
            'overdue'  => LibraryIssue::where('status', 'issued')
                                        ->whereDate('due_date', '<', Carbon::today())
                                        ->count(),
        ];

        /* ---------- How many rows per page ---------- */
        $perPage = (int) $request->input('per_page', 50);
        if (!in_array($perPage, [10, 25, 50, 100, 200], true)) {
            $perPage = 50;   // safe default
        }

        $issues = $query
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('library.index', compact('issues', 'summary', 'perPage'));
    }

    /* ===========================================================
     | CREATE
     | =========================================================== */

    public function create()
    {
        return view('library.create', $this->formLookups());
    }

    /* ===========================================================
     | EDIT
     | =========================================================== */

    public function edit($id)
    {
        $issue = LibraryIssue::findOrFail($id);
        return view('library.edit', array_merge($this->formLookups(), compact('issue')));
    }

    /* ===========================================================
     | Shared lookups for create/edit forms
     | =========================================================== */

    protected function formLookups(): array
    {
        return [
            'academicYears' => AcademicYear::orderByDesc('year_name')->get(),
            'standards'     => Standard::where('is_active', 1)->orderBy('display_order')->get(),
            'divisions'     => Division::where('is_active', 1)->orderBy('division_name')->get(),
            'teachers'      => User::orderBy('name')->get(['id', 'name']),

            /* ---- Books from Book Master ---- */
            'books'         => LibraryBook::where('is_active', 1)
                                            ->with('category')
                                            ->orderBy('book_name')
                                            ->get(),
        ];
    }

    /* ===========================================================
     | AJAX — students for a class
     | =========================================================== */

    public function getStudents(Request $request)
    {
        $yearId     = $request->input('academic_year_id');
        $standardId = $request->input('standard_id');
        $divisionId = $request->input('division_id');

        if (!$yearId || !$standardId || !$divisionId) {
            return response()->json([]);
        }

        $academicYear = AcademicYear::find($yearId);
        if (!$academicYear) {
            return response()->json([]);
        }

        $erpYearId = $academicYear->old_year_id
            ?? substr($academicYear->year_name, 0, 4);

        try {
            $students = StudentHelper::getStudentsDirectERP(
                $erpYearId, $standardId, $divisionId
            );

            return response()->json($students->map(function ($s) {
                return [
                    'id'     => (string) $s->Studentid,
                    'name'   => trim(($s->studname ?? '') . ' ' . ($s->fathername ?? '')),
                    'rollno' => (string) ($s->rollno ?? ''),
                ];
            })->values());

        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /* ===========================================================
     | AJAX — book search (for the "Book Name" search box)
     | =========================================================== */

    public function booksSearch(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        try {
            $query = LibraryBook::query()->where('is_active', 1);

            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('book_name', 'like', "%{$q}%")
                      ->orWhere('book_code', 'like', "%{$q}%")
                      ->orWhere('author',    'like', "%{$q}%");
                });
            }

            $books = $query->orderBy('book_name')->limit(50)->get();

            return response()->json($books->map(function ($b) {
                return [
                    'id'               => (string) $b->id,
                    'book_name'        => (string) $b->book_name,
                    'book_code'        => (string) ($b->book_code ?? ''),
                    'author'           => (string) ($b->author ?? ''),
                    'total_copies'     => $b->total_copies     ?? null,
                    'available_copies' => $b->available_copies ?? null,
                ];
            })->values());

        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /* ===========================================================
     | STORE
     | =========================================================== */

    public function store(Request $request)
    {
        $validated = $this->validateIssue($request);

        try {
            LibraryIssue::create(array_merge($validated, [
                'due_date'   => $validated['due_date']
                                  ?? Carbon::parse($validated['issue_date'])->addDays(14),
                'status'     => 'issued',
                'created_by' => Auth::id(),
            ]));

            return redirect()->route('library.index')
                ->with('success', 'Book issued successfully.');

        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /* ===========================================================
     | UPDATE
     | =========================================================== */

    public function update(Request $request, $id)
    {
        $issue = LibraryIssue::findOrFail($id);
        $validated = $this->validateIssue($request);

        try {
            $issue->update($validated);

            return redirect()->route('library.index')
                ->with('success', 'Record updated successfully.');

        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    protected function validateIssue(Request $request): array
    {
        return $request->validate([
            'borrower_type'  => 'required|in:student,teacher',
            'borrower_id'    => 'required|string|max:50',
            'borrower_name'  => 'required|string|max:255',
            'borrower_class' => 'nullable|string|max:50',

            // ⭐ allow storing the selected book id (nullable so edit still works)
            'book_id'        => 'nullable|integer|exists:library_books,id',

            'book_name'      => 'required|string|max:255',
            'book_code'      => 'nullable|string|max:50',
            'author'         => 'nullable|string|max:100',
            'issue_date'     => 'required|date',
            'due_date'       => 'nullable|date|after_or_equal:issue_date',
            'return_date'    => 'nullable|date',
            'remarks'        => 'nullable|string|max:255',
        ]);
    }

    /* ===========================================================
     | MARK RETURNED
     | =========================================================== */

    public function markReturned(Request $request, $id)
    {
        $issue = LibraryIssue::findOrFail($id);

        if ($issue->status === 'returned') {
            return back()->with('error', 'This book is already marked as returned.');
        }

        $returnDate = $request->input('return_date') ?: Carbon::today()->toDateString();

        $issue->update([
            'status'      => 'returned',
            'return_date' => $returnDate,
        ]);

        return back()->with('success', 'Book marked as returned.');
    }

    /* ===========================================================
     | TEACHERS search (legacy)
     | =========================================================== */

    public function searchTeachers(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        try {
            $query = User::query();
            if ($q !== '') {
                $query->where('name', 'like', "%{$q}%");
            }

            $users = $query->orderBy('name')->limit(50)->get(['id', 'name']);

            return response()->json($users->map(fn ($u) => [
                'id'   => (string) $u->id,
                'name' => $u->name,
            ]));

        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}