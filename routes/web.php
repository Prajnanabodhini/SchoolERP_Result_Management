<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubjectTypeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StandardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\StandardSubjectController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\SelectionController;
use App\Http\Controllers\TeacherClassAllocationController;
use App\Http\Controllers\TeacherSubjectAllocationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentSkillSubjectController;
use App\Http\Controllers\ExamProgressController;

/*
|--------------------------------------------------------------------------
| ADMINISTRATOR CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Administrator\ResultGenerationController;
use App\Http\Controllers\Administrator\ResultRegisterController;
use App\Http\Controllers\Administrator\ReportCardController;
use App\Http\Controllers\Administrator\ResultSheetController;
use App\Http\Controllers\Marks\AdminMarksController;
use App\Http\Controllers\Administrator\MarkAuditController;

/*
|--------------------------------------------------------------------------
| OTHER CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\ErpSyncController;
use App\Http\Controllers\ErpStudentSyncController;
use App\Http\Controllers\AnalyticsController;

use App\Http\Controllers\Marks\MarkEntryController;
use App\Http\Controllers\Marks\MarkSaveController;
use App\Http\Controllers\Marks\MarkViewController;
use App\Http\Controllers\Marks\MarkEditController;
use App\Http\Controllers\Marks\MarkSubmitController;

use App\Http\Controllers\TeacherBulkAllocationController;
use App\Http\Controllers\ExamMasterController;
use App\Http\Controllers\ExamPatternController;
use App\Http\Controllers\ExamSubjectController;
use App\Http\Controllers\ExamPatternDetailController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\UserDesignationController;
use App\Http\Controllers\StudentSkillMarkController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\LibraryBookController;
use App\Http\Controllers\LibraryBookCategoryController;
use App\Http\Controllers\StudentSkillObservationController;
use App\Http\Controllers\ExamStructureController;
use App\Http\Controllers\SkillTeacherAllocationController;


Route::get('/student-skill-observations/print-entry',
    [App\Http\Controllers\StudentSkillObservationController::class, 'printEntry'])
    ->name('student-skill-observations.print-entry');

Route::get('/student-skill-observations/export-entry-excel',
    [App\Http\Controllers\StudentSkillObservationController::class, 'exportEntryExcel'])
    ->name('student-skill-observations.export-entry-excel');

Route::middleware(['auth'])->group(function () {
    // ... your existing student-skill-observations routes ...

    // NEW — Excel export
    Route::get(
        '/student-skill-observations/export-excel',
        [StudentSkillObservationController::class, 'exportExcel']
    )->name('student-skill-observations.export-excel');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/student-skill-observations',
        [StudentSkillObservationController::class, 'index']
    )->name('student-skill-observations.index');

    Route::post('/student-skill-observations/save',
        [StudentSkillObservationController::class, 'save']
    )->name('student-skill-observations.save');

    Route::get('/student-skill-observations/summary',
        [StudentSkillObservationController::class, 'summary']
    )->name('student-skill-observations.summary');

    Route::post('/student-skill-observations/auto-save', [App\Http\Controllers\StudentSkillObservationController::class, 'autoSave'])->name('student-skill-observations.auto-save');
});

/*
|--------------------------------------------------------------------------
| STUDENT SKILL SUBJECT ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get(
        '/student-skill-subject-allocation',
        [StudentSkillSubjectController::class, 'index']
    )->name('student-skill-subject-allocation.index');

    Route::post(
        '/student-skill-subject-allocation/save',
        [StudentSkillSubjectController::class, 'save']
    )->name('student-skill-subject-allocation.save');

    Route::post(
        '/student-skill-subject-allocation/bulk-allocate',
        [StudentSkillSubjectController::class, 'bulkAllocate']
    )->name('student-skill-subject-allocation.bulk-allocate');

    Route::post(
        '/student-skill-subject-allocation/bulk-remove',
        [StudentSkillSubjectController::class, 'bulkRemove']
    )->name('student-skill-subject-allocation.bulk-remove');
});


/*
|--------------------------------------------------------------------------
| STUDENT SKILL MARKS ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get(
        '/student-skill-marks',
        [StudentSkillMarkController::class, 'index']
    )->name('student-skill-marks.index');

    Route::post(
        '/student-skill-marks/save',
        [StudentSkillMarkController::class, 'save']
    )->name('student-skill-marks.save');

    Route::post('/student-skill-marks/auto-save', [App\Http\Controllers\StudentSkillMarkController::class, 'autoSave'])
    ->name('student-skill-marks.auto-save');
});


/*
|--------------------------------------------------------------------------
| USER DESIGNATION ASSIGNMENT
|--------------------------------------------------------------------------
*/

Route::resource(
    'user-designations',
    UserDesignationController::class
)->except([
    'show',
]);


/*
|--------------------------------------------------------------------------
| DESIGNATIONS
|--------------------------------------------------------------------------
*/

Route::resource(
    'designations',
    DesignationController::class
)->except([
    'show',
]);


/*
|--------------------------------------------------------------------------
| TEACHER BULK ALLOCATION - PUBLIC AJAX
|--------------------------------------------------------------------------
*/

Route::get(
    '/teacher-bulk-allocation/exam-details',
    [TeacherBulkAllocationController::class, 'getExamDetails']
)->name(
    'teacher-bulk-allocation.exam-details'
);


/*
|--------------------------------------------------------------------------
| LOGIN REDIRECT
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| TEST PAGE
|--------------------------------------------------------------------------
*/

Route::get('/test-page', function () {
    return 'TEST PAGE WORKING';
});


/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $isAdministrator = false;

        if (method_exists($user, 'hasRole')) {

            if (
                $user->hasRole('Administrator') ||
                $user->hasRole('admin')
            ) {
                $isAdministrator = true;
            }
        }

        if (!$isAdministrator) {

            $role = strtolower(
                trim(
                    (string) ($user->role ?? '')
                )
            );

            if (
                in_array(
                    $role,
                    [
                        'administrator',
                        'admin',
                    ],
                    true
                )
            ) {
                $isAdministrator = true;
            }
        }

        if ($isAdministrator) {

            return app(
                DashboardController::class
            )->index(
                request()
            );
        }

        return redirect()->route(
            'exam-progress.index'
        );

    })->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | SELECTION
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/selection',
        [SelectionController::class, 'index']
    )->name('selection.index');

    Route::get(
        '/selection/{yearid}/{sectionid}',
        [SelectionController::class, 'select']
    )->name('selection.select');


    /*
    |--------------------------------------------------------------------------
    | ACADEMIC YEARS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'academic-years',
        AcademicYearController::class
    );


    /*
    |--------------------------------------------------------------------------
    | USERS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'users',
        UserController::class
    );


    /*
    |--------------------------------------------------------------------------
    | ROLES
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'roles',
        RoleController::class
    );


    /*
    |--------------------------------------------------------------------------
    | SUBJECT TYPES
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'subject-types',
        SubjectTypeController::class
    );


    /*
    |--------------------------------------------------------------------------
    | ROLE PERMISSIONS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/role-permissions',
        [RolePermissionController::class, 'index']
    )->name('role-permissions.index');

    Route::post(
        '/role-permissions',
        [RolePermissionController::class, 'store']
    )->name('role-permissions.store');


    /*
    |--------------------------------------------------------------------------
    | STUDENTS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'students',
        StudentController::class
    );

    Route::get(
        '/students/get-divisions',
        [StudentController::class, 'getDivisions']
    )->name('students.getDivisions');


    /*
    |--------------------------------------------------------------------------
    | STANDARDS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'standards',
        StandardController::class
    );


    /*
    |--------------------------------------------------------------------------
    | DIVISIONS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'divisions',
        DivisionController::class
    );


    /*
    |--------------------------------------------------------------------------
    | SECTIONS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'sections',
        SectionController::class
    );


    /*
    |--------------------------------------------------------------------------
    | SUBJECTS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'subjects',
        SubjectController::class
    );

    Route::get('subjects/edit-master/{subject}',   [SubjectController::class, 'editMaster'])
    ->name('subjects.edit-master');
Route::put('subjects/update-master/{subject}', [SubjectController::class, 'updateMaster'])
    ->name('subjects.update-master');

    /*
    |--------------------------------------------------------------------------
    | STANDARD SUBJECT ALLOCATION
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/standard-subject-allocation',
        [StandardSubjectController::class, 'index']
    )->name('standard-subject-allocation.index');

    Route::post(
        '/standard-subject-allocation/save',
        [StandardSubjectController::class, 'save']
    )->name('standard-subject-allocation.save');


    /*
    |--------------------------------------------------------------------------
    | STUDENT PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/student-profile',
        [StudentProfileController::class, 'index']
    )->name('student-profile.index');


    /*
    |--------------------------------------------------------------------------
    | STUDENT SKILL SUBJECT (LEGACY ALIASES)
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/student-skill-subjects',
        [StudentSkillSubjectController::class, 'index']
    )->name('student-skill-subjects.index');

    Route::post(
        '/student-skill-subjects/save',
        [StudentSkillSubjectController::class, 'save']
    )->name('student-skill-subjects.save');


    /*
    |--------------------------------------------------------------------------
    | TEACHER CLASS ALLOCATION
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/teacher-class-allocation',
        [TeacherClassAllocationController::class, 'index']
    )->name('teacher-class-allocation.index');

    Route::get(
        '/teacher-class-allocation/create',
        [TeacherClassAllocationController::class, 'create']
    )->name('teacher-class-allocation.create');

    Route::post(
        '/teacher-class-allocation',
        [TeacherClassAllocationController::class, 'store']
    )->name('teacher-class-allocation.store');


    /*
    |--------------------------------------------------------------------------
    | TEACHER SUBJECT ALLOCATION
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/teacher-subject-allocation',
        [TeacherSubjectAllocationController::class, 'index']
    )->name('teacher-subject-allocation.index');

    Route::get(
        '/teacher-subject-allocation/create',
        [TeacherSubjectAllocationController::class, 'create']
    )->name('teacher-subject-allocation.create');

    Route::post(
        '/teacher-subject-allocation',
        [TeacherSubjectAllocationController::class, 'store']
    )->name('teacher-subject-allocation.store');

    Route::get(
        '/teacher-subject-allocation/subjects/{id}',
        [TeacherSubjectAllocationController::class, 'getSubjects']
    )->name('teacher-subject-allocation.subjects');


    /*
    |--------------------------------------------------------------------------
    | TEACHER BULK ALLOCATION
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/teacher-bulk-allocation',
        [TeacherBulkAllocationController::class, 'index']
    )->name('teacher-bulk-allocation.index');

    Route::get(
        '/teacher-bulk-allocation/create',
        [TeacherBulkAllocationController::class, 'create']
    )->name('teacher-bulk-allocation.create');

    Route::post(
        '/teacher-bulk-allocation/store',
        [TeacherBulkAllocationController::class, 'store']
    )->name('teacher-bulk-allocation.store');

    Route::get(
        '/teacher-bulk-allocation/{id}/edit',
        [TeacherBulkAllocationController::class, 'edit']
    )->name('teacher-bulk-allocation.edit');

    Route::put(
        '/teacher-bulk-allocation/{id}',
        [TeacherBulkAllocationController::class, 'update']
    )->name('teacher-bulk-allocation.update');

    Route::delete(
        '/teacher-bulk-allocation/{id}',
        [TeacherBulkAllocationController::class, 'destroy']
    )->name('teacher-bulk-allocation.destroy');


    /*
    |--------------------------------------------------------------------------
    | TEACHER BULK ALLOCATION AJAX
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/teacher-bulk-allocation/get-standards',
        [TeacherBulkAllocationController::class, 'getStandards']
    )->name('teacher-bulk-allocation.standards');

    Route::post(
        '/teacher-bulk-allocation/get-subjects',
        [TeacherBulkAllocationController::class, 'getSubjects']
    )->name('teacher-bulk-allocation.subjects');


    /*
    |--------------------------------------------------------------------------
    | EXAM MASTER
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'exam-masters',
        ExamMasterController::class
    );

    Route::get(
        '/exam-masters/load-subjects/{standardId}',
        [ExamMasterController::class, 'loadSubjects']
    )->name('exam-masters.load-subjects');

    Route::get(
        '/exam-master-subjects/{standard}',
        [ExamMasterController::class, 'getSubjects']
    )->name('exam-master-subjects');


    /*
    |--------------------------------------------------------------------------
    | EXAM SUBJECTS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/exam-subjects',
        [ExamSubjectController::class, 'index']
    )->name('exam-subjects.index');

    Route::post(
        '/exam-subjects/save',
        [ExamSubjectController::class, 'save']
    )->name('exam-subjects.save');


    /*
    |--------------------------------------------------------------------------
    | EXAM PROGRESS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/exam-progress',
        [ExamProgressController::class, 'index']
    )->name('exam-progress.index');

    Route::post('/exam-progress/generate-skill-marks', [ExamProgressController::class, 'generateSkillMarksAllocations'])
    ->name('exam-progress.generate-skill-marks')
    ->middleware(['auth']);


    /*
    |--------------------------------------------------------------------------
    | LIBRARY
    |--------------------------------------------------------------------------
    */

    Route::prefix('library')->name('library.')->group(function () {

        /* ---------- Issues ---------- */

        Route::get('/',                [LibraryController::class, 'index'])          ->name('index');
        Route::get('/create',          [LibraryController::class, 'create'])         ->name('create');
        Route::get('/pending',         [LibraryController::class, 'pending'])        ->name('pending');
        Route::get('/overdue',         [LibraryController::class, 'overdue'])        ->name('overdue');

        Route::post('/',               [LibraryController::class, 'store'])          ->name('store');

        Route::get('/{id}/edit',       [LibraryController::class, 'edit'])           ->name('edit');
        Route::put('/{id}',            [LibraryController::class, 'update'])         ->name('update');

        Route::post('/{id}/return',    [LibraryController::class, 'markReturned'])   ->name('return');

        /* ---------- AJAX helpers ---------- */

        Route::get('/get-students',    [LibraryController::class, 'getStudents'])    ->name('get-students');
        Route::get('/search-teachers', [LibraryController::class, 'searchTeachers']) ->name('search-teachers');

        /* ---------- ⭐ AJAX: Book search (fixes RouteNotFoundException) ---------- */
        Route::get('/books/search',    [LibraryController::class, 'booksSearch'])    ->name('books.search');

        /* ---------- Book Categories ---------- */

        Route::get   ('/categories',           [LibraryBookCategoryController::class, 'index'])   ->name('categories.index');
        Route::get   ('/categories/create',    [LibraryBookCategoryController::class, 'create'])  ->name('categories.create');
        Route::post  ('/categories',           [LibraryBookCategoryController::class, 'store'])   ->name('categories.store');
        Route::get   ('/categories/{id}/edit', [LibraryBookCategoryController::class, 'edit'])    ->name('categories.edit');
        Route::put   ('/categories/{id}',      [LibraryBookCategoryController::class, 'update'])  ->name('categories.update');
        Route::delete('/categories/{id}',      [LibraryBookCategoryController::class, 'destroy']) ->name('categories.destroy');

        /* ---------- Books ---------- */

        Route::get   ('/books',                [LibraryBookController::class, 'index'])           ->name('books.index');
        Route::get   ('/books/create',         [LibraryBookController::class, 'create'])          ->name('books.create');
        Route::post  ('/books',                [LibraryBookController::class, 'store'])           ->name('books.store');
        Route::get   ('/books/{id}/edit',      [LibraryBookController::class, 'edit'])            ->name('books.edit');
        Route::put   ('/books/{id}',           [LibraryBookController::class, 'update'])          ->name('books.update');
        Route::delete('/books/{id}',           [LibraryBookController::class, 'destroy'])         ->name('books.destroy');
    });


    /*
    |--------------------------------------------------------------------------
    | MARKS ENTRY
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/marks-entry',
        [MarkEntryController::class, 'index']
    )->name('marks-entry.index');

    Route::post(
        '/marks-entry/save',
        [MarkSaveController::class, 'save']
    )->name('marks-entry.save');

    Route::post(
        '/marks-entry/autosave',
        [MarkSaveController::class, 'autoSave']
    )->name('marks-entry.autosave');

    Route::post(
        '/marks-entry/absent-warning',
        [MarkEntryController::class, 'absentWarning']
    )->name('marks-entry.absent-warning');

    Route::post(
        '/marks-entry/submit',
        [MarkSubmitController::class, 'submitFinal']
    )->name('marks-entry.submit');


    /*
    |--------------------------------------------------------------------------
    | MARKS VIEW
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/marks-entry/view',
        [MarkViewController::class, 'viewMarks']
    )->name('marks-entry.view');

    Route::post(
        '/marks-view/search',
        [MarkViewController::class, 'searchMarks']
    )->name('marks-view.search');


    /*
    |--------------------------------------------------------------------------
    | MARKS EDIT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/marks-entry/edit',
        [MarkEditController::class, 'edit']
    )->name('marks-entry.edit');

    Route::post(
        '/marks-entry/update',
        [MarkEditController::class, 'updateMarks']
    )->name('marks-entry.update');


    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATOR MARKS CORRECTION
    |--------------------------------------------------------------------------
    */

    Route::prefix('administrator')->group(function () {

        Route::get(
            '/marks-correction',
            [AdminMarksController::class, 'index']
        )->name('result-generation.admin-marks.index');

        Route::get(
            '/marks-correction/edit',
            [AdminMarksController::class, 'edit']
        )->name('result-generation.admin-marks.edit');

        Route::post(
            '/marks-correction/update',
            [AdminMarksController::class, 'update']
        )->name('result-generation.admin-marks.update');

        Route::put(
            '/marks-correction/update',
            [AdminMarksController::class, 'update']
        )->name('admin-marks.update');

        Route::post(
            '/marks-correction/reopen',
            [AdminMarksController::class, 'reopen']
        )->name('admin-marks.reopen');

        Route::get(
            '/mark-audit',
            [MarkAuditController::class, 'index']
        )->name('mark-audit.index');
    });


    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATOR MARKS SUBJECT AJAX
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/marks-correction/subjects',
        [AdminMarksController::class, 'getSubjects']
    )->name('admin-marks.subjects');


    /*
    |--------------------------------------------------------------------------
    | RESULT GENERATION
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/administrator/result-generation',
        [ResultGenerationController::class, 'index']
    )->name('administrator.result-generation.index');

    Route::post(
        '/administrator/result-generation/generate',
        [ResultGenerationController::class, 'generate']
    )->name('administrator.result-generation.generate');


    /*
    |--------------------------------------------------------------------------
    | RESULT SHEET
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/administrator/result-sheet',
        [ResultSheetController::class, 'index']
    )->name('result-sheet.index');

    Route::post(
        '/administrator/result-sheet',
        [ResultSheetController::class, 'search']
    )->name('result-sheet.search');

    Route::get(
        '/result-sheet-print',
        [ResultSheetController::class, 'print']
    )->name('result-sheet.print');

    Route::get(
        '/administrator/result-sheet/export-excel',
        [ResultSheetController::class, 'exportExcel']
    )->name('result-sheet.export-excel');


    /*
    |--------------------------------------------------------------------------
    | RESULT REGISTER
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/administrator/result-register',
        [ResultRegisterController::class, 'index']
    )->name('administrator.result-register');

    Route::post(
        '/administrator/result-register',
        [ResultRegisterController::class, 'search']
    )->name('administrator.result-register.search');

    Route::get(
        '/result-register',
        [ResultRegisterController::class, 'index']
    )->name('result-register.index');

    Route::post(
        '/result-register/search',
        [ResultRegisterController::class, 'search']
    )->name('result-register.search');


    /*
    |--------------------------------------------------------------------------
    | REPORT CARD
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/report-card',
        [ReportCardController::class, 'index']
    )->name('report-card.index');

    Route::match(
        ['get', 'post'],
        '/report-card/search',
        [ReportCardController::class, 'search']
    )->name('report-card.search');

    Route::post(
        '/report-card/exams-by-standard',
        [ReportCardController::class, 'examsByStandard']
    )->name('report-card.exams-by-standard');

    Route::post(
        '/report-card/show',
        [ReportCardController::class, 'show']
    )->name('report-card.show');

    Route::get(
        '/report-card/print/{student}/{exam}/{year}',
        [ReportCardController::class, 'print']
    )->name('report-card.print');


    /*
    |--------------------------------------------------------------------------
    | PROGRESS CARD
    |--------------------------------------------------------------------------
    */

    Route::get(
        'report-card/progress-card/{studentId}/{examId}/{yearId}',
        [ReportCardController::class, 'printProgressCard']
    )->name('report-card.progress-card');

    Route::get(
        'report-card/progress-card/{studentId}/{examId}/{yearId}/edit',
        [ReportCardController::class, 'editProgressDetails']
    )->name('report-card.progress-card.edit');

    Route::post(
        'report-card/progress-card/{studentId}/{examId}/{yearId}/save',
        [ReportCardController::class, 'saveProgressDetails']
    )->name('report-card.progress-card.save');


    /*
    |--------------------------------------------------------------------------
    | ANALYTICS
    |--------------------------------------------------------------------------
    */

    Route::get('/analytics/print', [AnalyticsController::class, 'printReport'])
     ->name('analytics.print');
     
    Route::get(
        '/analytics',
        [AnalyticsController::class, 'index']
    )->name('analytics.index');

    Route::get(
        '/analytics/student/{studentId}',
        [AnalyticsController::class, 'studentDetail']
    )->name('analytics.student');

    Route::get(
        '/analytics/subject/{subjectId}',
        [AnalyticsController::class, 'subjectDetail']
    )->name('analytics.subject');

    Route::get(
        '/analytics/comparison',
        [AnalyticsController::class, 'comparison']
    )->name('analytics.comparison');

    Route::get(
        '/analytics/comparison/student/{studentId}',
        [AnalyticsController::class, 'comparisonStudentDetail']
    )->name('analytics.comparison.student');

    Route::get(
        '/analytics/year-wise',
        [AnalyticsController::class, 'yearWise']
    )->name('analytics.year-wise');


    /*
    |--------------------------------------------------------------------------
    | ERP SYNC
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/erp-student-sync/{year}',
        [ErpStudentSyncController::class, 'sync']
    );

    Route::get(
        '/erp-sync/students/{year}',
        [ErpSyncController::class, 'syncStudents']
    )->name('erp-sync.students');

});

Route::middleware(['auth'])->prefix('exam-structures')->name('exam-structures.')->group(function () {
    Route::get('/',                   [ExamStructureController::class, 'index'])->name('index');
    Route::get('/{standardKey}/edit', [ExamStructureController::class, 'edit'])->name('edit');
    Route::put('/{standardKey}',      [ExamStructureController::class, 'update'])->name('update');
});


Route::prefix('student-skill-marks')->name('student-skill-marks.')->group(function () {
    Route::get('/',             [StudentSkillMarkController::class, 'index'])->name('index');
    Route::post('/save',        [StudentSkillMarkController::class, 'save'])->name('save');
    Route::get('/print',        [StudentSkillMarkController::class, 'print'])->name('print');
    Route::get('/export-excel', [StudentSkillMarkController::class, 'exportExcel'])->name('export-excel');
});


Route::prefix('skill-teacher-allocation')->name('skill-teacher-allocation.')->group(function () {
    Route::get('/', [SkillTeacherAllocationController::class, 'index'])->name('index');
    Route::get('/create', [SkillTeacherAllocationController::class, 'create'])->name('create');
    Route::post('/', [SkillTeacherAllocationController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [SkillTeacherAllocationController::class, 'edit'])->name('edit');
    Route::put('/{id}', [SkillTeacherAllocationController::class, 'update'])->name('update');
    Route::delete('/{id}', [SkillTeacherAllocationController::class, 'destroy'])->name('destroy');
});


// Existing routes...
Route::get('/skill-teacher-allocation', [SkillTeacherAllocationController::class, 'index'])->name('skill-teacher-allocation.index');

// Add this new route for generating allocations
Route::post('/skill-teacher-allocation/generate-skill-marks', [SkillTeacherAllocationController::class, 'generateSkillMarksAllocations'])
    ->name('skill-teacher-allocation.generate-skill-marks')
    ->middleware(['auth']);

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';