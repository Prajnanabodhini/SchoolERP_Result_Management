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

                // Text columns — safe as strings
                $q->where('studname',   'like', "%{$search}%")
                  ->orWhere('fathername', 'like', "%{$search}%");

                // Numeric-ish columns stored as NVARCHAR in SQL Server.
                // Use LIKE so both sides are treated as strings — this
                // prevents SQL Server from casting the column to INT and
                // overflowing on large values like '2011272509174050015'.
                $q->orWhere('Studentid',    'like', "%{$search}%")
                  ->orWhere('saralid',      'like', "%{$search}%")
                  ->orWhere('fathermobile', 'like', "%{$search}%")
                  ->orWhere('mothermobile', 'like', "%{$search}%");
            });

            $student = $studentQuery->first();
        }

        return view(
            'student-profile.index',
            compact('student')
        );
    }
}