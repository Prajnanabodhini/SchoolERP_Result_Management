<?php

namespace App\Http\Controllers;

use App\Models\LibraryBook;
use App\Models\LibraryBookCategory;
use Illuminate\Http\Request;
use Throwable;

class LibraryBookController extends Controller
{
    /* ===========================================================
     | INDEX
     | =========================================================== */

    public function index(Request $request)
    {
        $query = LibraryBook::with('category');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('book_name', 'like', "%{$search}%")
                  ->orWhere('book_code', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $books      = $query->orderBy('book_name')->paginate(25)->withQueryString();
        $categories = LibraryBookCategory::where('is_active', 1)->orderBy('category_name')->get();

        return view('library.books.index', compact('books', 'categories'));
    }

    /* ===========================================================
     | CREATE
     | =========================================================== */

    public function create()
    {
        $categories = LibraryBookCategory::where('is_active', 1)
            ->orderBy('category_name')
            ->get();

        return view('library.books.create', compact('categories'));
    }

    /* ===========================================================
     | STORE
     | =========================================================== */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_code'        => 'required|string|max:50|unique:library_books,book_code',
            'book_name'        => 'required|string|max:255',
            'author'           => 'nullable|string|max:255',
            'publisher'        => 'nullable|string|max:255',
            'category_id'      => 'nullable|exists:library_book_categories,id',
            'total_copies'     => 'required|integer|min:1',
            'shelf_location'   => 'nullable|string|max:50',
            'is_active'        => 'nullable',
        ]);

        try {
            LibraryBook::create([
                'book_code'        => strtoupper(trim($validated['book_code'])),
                'book_name'        => trim($validated['book_name']),
                'author'           => $validated['author'] ?? null,
                'publisher'        => $validated['publisher'] ?? null,
                'category_id'      => $validated['category_id'] ?? null,
                'total_copies'     => (int) $validated['total_copies'],
                'available_copies' => (int) $validated['total_copies'],
                'shelf_location'   => $validated['shelf_location'] ?? null,
                'is_active'        => $request->has('is_active') ? 1 : 0,
            ]);

            return redirect()->route('library.books.index')
                ->with('success', 'Book added successfully.');

        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /* ===========================================================
     | EDIT
     | =========================================================== */

    public function edit($id)
    {
        $book       = LibraryBook::findOrFail($id);
        $categories = LibraryBookCategory::where('is_active', 1)
            ->orderBy('category_name')
            ->get();

        return view('library.books.edit', compact('book', 'categories'));
    }

    /* ===========================================================
     | UPDATE
     | =========================================================== */

    public function update(Request $request, $id)
    {
        $book = LibraryBook::findOrFail($id);

        $validated = $request->validate([
            'book_code'      => 'required|string|max:50|unique:library_books,book_code,' . $book->id,
            'book_name'      => 'required|string|max:255',
            'author'         => 'nullable|string|max:255',
            'publisher'      => 'nullable|string|max:255',
            'category_id'    => 'nullable|exists:library_book_categories,id',
            'total_copies'   => 'required|integer|min:1',
            'shelf_location' => 'nullable|string|max:50',
            'is_active'      => 'nullable',
        ]);

        try {
            $oldTotal        = $book->total_copies;
            $newTotal        = (int) $validated['total_copies'];
            $currentlyIssued = max(0, $oldTotal - $book->available_copies);
            $newAvailable    = max(0, $newTotal - $currentlyIssued);

            $book->update([
                'book_code'        => strtoupper(trim($validated['book_code'])),
                'book_name'        => trim($validated['book_name']),
                'author'           => $validated['author'] ?? null,
                'publisher'        => $validated['publisher'] ?? null,
                'category_id'      => $validated['category_id'] ?? null,
                'total_copies'     => $newTotal,
                'available_copies' => $newAvailable,
                'shelf_location'   => $validated['shelf_location'] ?? null,
                'is_active'        => $request->has('is_active') ? 1 : 0,
            ]);

            return redirect()->route('library.books.index')
                ->with('success', 'Book updated successfully.');

        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /* ===========================================================
     | DESTROY
     | =========================================================== */

    public function destroy($id)
    {
        $book = LibraryBook::findOrFail($id);

        // Prevent deletion if this book still has copies issued
        if ($book->available_copies < $book->total_copies) {
            return back()->with('error', 'Cannot delete — copies of this book are currently issued.');
        }

        $book->delete();

        return redirect()->route('library.books.index')
            ->with('success', 'Book deleted successfully.');
    }
}