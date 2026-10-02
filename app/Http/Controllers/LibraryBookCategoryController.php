<?php

namespace App\Http\Controllers;

use App\Models\LibraryBookCategory;
use Illuminate\Http\Request;
use Throwable;

class LibraryBookCategoryController extends Controller
{
    /* ===========================================================
     | INDEX
     | =========================================================== */

    public function index(Request $request)
    {
        $query = LibraryBookCategory::withCount('books');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('category_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $categories = $query->orderBy('category_name')->paginate(25)->withQueryString();

        return view('library.categories.index', compact('categories'));
    }

    /* ===========================================================
     | CREATE
     | =========================================================== */

    public function create()
    {
        return view('library.categories.create');
    }

    /* ===========================================================
     | STORE
     | =========================================================== */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:library_book_categories,category_name',
            'description'   => 'nullable|string|max:255',
            'is_active'     => 'nullable',
        ]);

        try {
            LibraryBookCategory::create([
                'category_name' => trim($validated['category_name']),
                'description'   => $validated['description'] ?? null,
                'is_active'     => $request->has('is_active') ? 1 : 0,
            ]);

            return redirect()->route('library.categories.index')
                ->with('success', 'Book category added successfully.');

        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /* ===========================================================
     | EDIT
     | =========================================================== */

    public function edit($id)
    {
        $category = LibraryBookCategory::findOrFail($id);
        return view('library.categories.edit', compact('category'));
    }

    /* ===========================================================
     | UPDATE
     | =========================================================== */

    public function update(Request $request, $id)
    {
        $category = LibraryBookCategory::findOrFail($id);

        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:library_book_categories,category_name,' . $category->id,
            'description'   => 'nullable|string|max:255',
            'is_active'     => 'nullable',
        ]);

        try {
            $category->update([
                'category_name' => trim($validated['category_name']),
                'description'   => $validated['description'] ?? null,
                'is_active'     => $request->has('is_active') ? 1 : 0,
            ]);

            return redirect()->route('library.categories.index')
                ->with('success', 'Book category updated successfully.');

        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /* ===========================================================
     | DESTROY
     | =========================================================== */

    public function destroy($id)
    {
        $category = LibraryBookCategory::findOrFail($id);

        if ($category->books()->exists()) {
            return back()->with('error', 'Cannot delete — this category has books assigned.');
        }

        $category->delete();

        return redirect()->route('library.categories.index')
            ->with('success', 'Book category deleted.');
    }
}