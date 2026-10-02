<x-app-layout>

<style>
.lib-form-page, .lib-form-page * { font-family: Arial, sans-serif !important; font-size: 13px; box-sizing: border-box; }
.lib-form-page h2 { font-size: 21px !important; font-weight: 700 !important; color: #1D4ED8 !important; margin: 0; text-transform: uppercase; letter-spacing: 0.3px; }
.lib-form-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
.lib-form-card { width: 100%; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.05); padding: 22px; }
.lib-row { display: grid; gap: 16px 20px; margin-bottom: 16px; }
.lib-row.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.lib-row.cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.lib-group { display: flex; flex-direction: column; gap: 6px; }
.lib-label { font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .3px; }
.lib-label .req { color: #dc2626; }
.lib-select-wrap { position: relative; }
.lib-control { height: 38px; padding: 6px 12px; border: 1px solid #9ca3af; border-radius: 5px; background: #fff; color: #111827; font-size: 13px; font-weight: 600; outline: none; width: 100%; }
.lib-control.text { padding-right: 12px; cursor: text; }
select.lib-control { appearance: none; -webkit-appearance: none; -moz-appearance: none; padding-right: 32px; cursor: pointer; }
.lib-control:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.15); }
.lib-arrow { position: absolute; top: 50%; right: 12px; transform: translateY(-50%); width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid #6b7280; pointer-events: none; }
.lib-check { display: inline-flex; align-items: center; gap: 6px; font-weight: 600; }
.lib-check input { width: 16px; height: 16px; accent-color: #2563eb; }
.lib-alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 14px; font-weight: 700; }
.lib-alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.lib-note { font-size: 12px; color: #6b7280; margin-top: 4px; }
.lib-form-footer { display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
.lib-btn { display: inline-flex; align-items: center; justify-content: center; height: 38px; min-width: 130px; padding: 0 20px; border-radius: 5px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; border: 1px solid transparent; white-space: nowrap; box-sizing: border-box; transition: background .15s; }
.lib-btn-primary { background: #16a34a; color: #fff; border-color: #15803d; }
.lib-btn-primary:hover { background: #15803d; }
.lib-btn-gray { background: #6b7280; color: #fff; border-color: #4b5563; }
.lib-btn-gray:hover { background: #4b5563; }
.lib-back-link { display: inline-flex; align-items: center; gap: 6px; padding: 0 14px; height: 38px; border: 1px solid #d1d5db; border-radius: 5px; background: #f9fafb; color: #374151; font-size: 13px; font-weight: 700; text-decoration: none; transition: background .12s; }
.lib-back-link:hover { background: #f3f4f6; color: #111827; }
@media (max-width: 900px) {
    .lib-row.cols-2, .lib-row.cols-3 { grid-template-columns: 1fr; }
}
</style>

<div class="lib-form-page p-3">

    <div class="lib-form-header">
        <h2>Edit Book</h2>
        <a href="{{ route('library.books.index') }}" class="lib-back-link">
            <span style="font-size:16px;line-height:1;">←</span> Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="lib-alert lib-alert-error">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('library.books.update', $book->id) }}">
        @csrf
        @method('PUT')

        <div class="lib-form-card">

            <div class="lib-row cols-3">
                <div class="lib-group">
                    <label class="lib-label" for="book_code">Book Code <span class="req">*</span></label>
                    <input type="text" name="book_code" id="book_code"
                           value="{{ old('book_code', $book->book_code) }}"
                           class="lib-control text" required>
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="book_name">Book Name <span class="req">*</span></label>
                    <input type="text" name="book_name" id="book_name"
                           value="{{ old('book_name', $book->book_name) }}"
                           class="lib-control text" required>
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="category_id">Category</label>
                    <div class="lib-select-wrap">
                        <select name="category_id" id="category_id" class="lib-control">
                            <option value="">— Select Category —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ (string) old('category_id', $book->category_id) === (string) $cat->id ? 'selected' : '' }}>
                                    {{ $cat->category_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="lib-arrow"></span>
                    </div>
                </div>
            </div>

            <div class="lib-row cols-3">
                <div class="lib-group">
                    <label class="lib-label" for="author">Author</label>
                    <input type="text" name="author" id="author"
                           value="{{ old('author', $book->author) }}" class="lib-control text">
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="publisher">Publisher</label>
                    <input type="text" name="publisher" id="publisher"
                           value="{{ old('publisher', $book->publisher) }}" class="lib-control text">
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="shelf_location">Shelf Location</label>
                    <input type="text" name="shelf_location" id="shelf_location"
                           value="{{ old('shelf_location', $book->shelf_location) }}"
                           class="lib-control text">
                </div>
            </div>

            <div class="lib-row cols-3">
                <div class="lib-group">
                    <label class="lib-label" for="total_copies">Total Copies <span class="req">*</span></label>
                    <input type="number" name="total_copies" id="total_copies"
                           value="{{ old('total_copies', $book->total_copies) }}"
                           min="1" class="lib-control text" required>
                    <div class="lib-note">
                        Currently issued: <strong>{{ $book->total_copies - $book->available_copies }}</strong> ·
                        Available: <strong>{{ $book->available_copies }}</strong>
                    </div>
                </div>

                <div class="lib-group">
                    <label class="lib-label">Status</label>
                    <label class="lib-check">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $book->is_active) ? 'checked' : '' }}>
                        Active
                    </label>
                </div>
            </div>

            <div class="lib-form-footer">
                <a href="{{ route('library.books.index') }}" class="lib-btn lib-btn-gray">Cancel</a>
                <button type="submit" class="lib-btn lib-btn-primary">Update Book</button>
            </div>

        </div>

    </form>

</div>

</x-app-layout>