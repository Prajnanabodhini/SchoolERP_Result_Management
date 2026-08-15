<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeeMstStudent;

class StudentProfileController extends Controller
{
    public function index(Request $request)
    {
        $student = null;

        if ($request->filled('search')) {

            $search = trim($request->search);

            $studentQuery = FeeMstStudent::query();

            $studentQuery->where(function ($q) use ($search) {

                $q->where('studname', 'like', "%{$search}%")
                    ->orWhere('fathername', 'like', "%{$search}%");

                if (is_numeric($search)) {

                    $q->orWhere('Studentid', (int)$search)
                        ->orWhere('saralid', (int)$search)
                        ->orWhere('fathermobile', $search)
                        ->orWhere('mothermobile', $search);
                }
            });

            $student = $studentQuery->first();
        }

        return view(
            'student-profile.index',
            compact('student')
        );
    }
}
