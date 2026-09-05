<?php

namespace App\Http\Controllers\Marks;

use Illuminate\Http\Request;
use App\Helpers\EditMarkHelper;

/**
 * Thin compatibility facade for administrator marks entry.
 *
 * All business logic remains in EditMarkHelper so existing routes/callers keep
 * the same public API while this class stays small and maintainable.
 */
class AdminMarksEntryService
{
    public function loadSelectedData(
        Request $request,
        $exams,
        $subjectService
    ) {
        return EditMarkHelper::loadSelectedDataForAdminEntry(
            $request,
            $exams,
            $subjectService
        );
    }

    public function update(
        Request $request,
        $subjectService
    ) {
        return EditMarkHelper::updateAdminMarks(
            $request,
            $subjectService
        );
    }

    public function reopen(
        Request $request,
        $subjectService
    ) {
        return EditMarkHelper::reopenAdminMarks(
            $request,
            $subjectService
        );
    }
}
