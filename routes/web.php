<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
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

use App\Http\Controllers\Administrator\ResultGenerationController;
use App\Http\Controllers\Administrator\ResultRegisterController;
use App\Http\Controllers\Administrator\ReportCardController;
use App\Http\Controllers\Administrator\ResultSheetController;
use App\Http\Controllers\Administrator\AdminMarksController;
use App\Http\Controllers\Administrator\MarkAuditController;

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
| PUBLIC / EXPORT ROUTES
|--------------------------------------------------------------------------
*/

Route::get(
    '/administrator/result-sheet/export-excel',
    [
        ResultSheetController::class,
        'exportExcel'
    ]
)->name('result-sheet.export-excel');


/*
|--------------------------------------------------------------------------
| PUBLIC AJAX ROUTE
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
    |
    | IMPORTANT:
    |
    | Administrator:
    |     Opens DashboardController
    |
    | Teacher / non-admin:
    |     Redirects to Exam Progress Dashboard
    |
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        function () {

            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login');
            }


            /*
            |--------------------------------------------------------------------------
            | ADMINISTRATOR DETECTION
            |--------------------------------------------------------------------------
            */

            $isAdministrator = false;


            /*
            |--------------------------------------------------------------------------
            | SPATIE ROLE
            |--------------------------------------------------------------------------
            */

            if (
                method_exists($user, 'hasRole')
            ) {

                if (
                    $user->hasRole('Administrator') ||
                    $user->hasRole('admin')
                ) {

                    $isAdministrator = true;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | PLAIN ROLE COLUMN
            |--------------------------------------------------------------------------
            */

            if (!$isAdministrator) {

                $role =
                    strtolower(
                        trim(
                            (string) (
                                $user->role
                                ?? ''
                            )
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


            /*
            |--------------------------------------------------------------------------
            | ADMINISTRATOR
            |--------------------------------------------------------------------------
            */

            if ($isAdministrator) {

                return app(
                    DashboardController::class
                )->index(
                    request()
                );
            }


            /*
            |--------------------------------------------------------------------------
            | TEACHER / NON-ADMIN
            |--------------------------------------------------------------------------
            */

            return redirect()->route(
                'exam-progress.index'
            );
        }
    )->name('dashboard');


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
    | STUDENT SKILL SUBJECT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/student-skill-subject-allocation',
        [StudentSkillSubjectController::class, 'index']
    )->name('student-skill-subject-allocation.index');

    Route::post(
        '/student-skill-subject-allocation/save',
        [StudentSkillSubjectController::class, 'save']
    )->name('student-skill-subject-allocation.save');

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


    /*
    |--------------------------------------------------------------------------
    | EXAM MASTER AJAX
    |--------------------------------------------------------------------------
    */

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
    | EXAM PATTERN
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'exam-patterns',
        ExamPatternController::class
    );


    /*
    |--------------------------------------------------------------------------
    | EXAM PATTERN DETAILS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'exam-pattern-details',
        ExamPatternDetailController::class
    )->only([
        'index',
        'create',
        'store',
        'edit',
        'update',
        'destroy'
    ]);

    Route::get(
        '/exam-pattern-details/get-subjects/{standard}',
        [ExamPatternDetailController::class, 'getSubjects']
    )->name('exam-pattern-details.getSubjects');


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

    Route::prefix('administrator')
        ->group(function () {

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
        '/report-card/show',
        [ReportCardController::class, 'show']
    )->name('report-card.show');

    Route::get(
        '/report-card/print/{student}/{exam}/{year}',
        [ReportCardController::class, 'print']
    )->name('report-card.print');


    /*
    |--------------------------------------------------------------------------
    | ANALYTICS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/analytics',
        [AnalyticsController::class, 'index']
    )->name('analytics.index');


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


/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';