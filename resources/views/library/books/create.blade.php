<x-app-layout>

<style>
.lib-form-page, .lib-form-page * { font-family: Arial, sans-serif !important; box-sizing: border-box; }
.lib-form-page { font-size: 12px; }
.lib-form-page h2 { font-size: 18px !important; font-weight: 700 !important; color: #1D4ED8 !important; margin: 0; text-transform: uppercase; letter-spacing: 0.3px; }
.lib-form-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
.lib-form-card { width: 100%; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.05); padding: 16px 18px; }

/* --- 4 equal columns, FLAT grid (no column wrappers) --- */
.lib-4col {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  grid-auto-rows: auto;
  align-items: start;          /* every row starts at the same top */
  gap: 12px 18px;
}

/* Each field: label (fixed height) + control (fixed height) */
.lib-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}

/* Fixed label height so all labels occupy identical vertical space */
.lib-label {
  font-size: 10.5px;
  font-weight: 700;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: .3px;
  height: 14px;
  line-height: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.lib-label .req { color: #dc2626; }

.lib-select-wrap { position: relative; }

/* --- EVERY control identical: same height, padding, border, font --- */
.lib-control,
select.lib-control,
textarea.lib-control,
input.lib-control {
  display: block;
  height: 32px !important;
  min-height: 32px !important;
  max-height: 32px !important;
  padding: 0 10px !important;
  line-height: 30px;
  border: 1px solid #9ca3af;
  border-radius: 5px;
  background: #fff;
  color: #111827;
  font-size: 12.5px;
  font-weight: 600;
  outline: none;
  width: 100%;
  transition: border-color .12s, box-shadow .12s;
}
textarea.lib-control {
  line-height: 1.2;
  padding: 6px 10px !important;
  resize: none;                /* keep exact height */
  overflow: hidden;
}
select.lib-control {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  padding-right: 28px !important;
  cursor: pointer;
}
.lib-control:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.15); }

.lib-arrow { position: absolute; top: 50%; right: 10px; transform: translateY(-50%); width: 0; height: 0; border-left: 4px solid transparent; border-right: 4px solid transparent; border-top: 5px solid #6b7280; pointer-events: none; }

/* Status checkbox — same height slot as controls */
.lib-check {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 700;
  height: 32px;
  font-size: 12px;
}
.lib-check input { width: 15px; height: 15px; accent-color: #2563eb; }

.lib-alert { padding: 8px 12px; border-radius: 6px; margin-bottom: 10px; font-weight: 700; font-size: 12px; }
.lib-alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

.lib-form-footer { display: flex; justify-content: flex-end; align-items: center; gap: 10px; padding-top: 12px; margin-top: 14px; border-top: 1px solid #e5e7eb; }
.lib-btn { display: inline-flex; align-items: center; justify-content: center; height: 34px; min-width: 120px; padding: 0 18px; border-radius: 5px; font-size: 12.5px; font-weight: 700; text-decoration: none; cursor: pointer; border: 1px solid transparent; white-space: nowrap; box-sizing: border-box; transition: background .15s; }
.lib-btn-primary { background: #16a34a; color: #fff; border-color: #15803d; }
.lib-btn-primary:hover { background: #15803d; }
.lib-btn-gray { background: #6b7280; color: #fff; border-color: #4b5563; }
.lib-btn-gray:hover { background: #4b5563; }
.lib-back-link { display: inline-flex; align-items: center; gap: 6px; padding: 0 12px; height: 32px; border: 1px solid #d1d5db; border-radius: 5px; background: #f9fafb; color: #374151; font-size: 12px; font-weight: 700; text-decoration: none; transition: background .12s; }
.lib-back-link:hover { background: #f3f4f6; color: #111827; }

@media (max-width: 1100px) {
  .lib-4col { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
  .lib-4col { grid-template-columns: 1fr; }
}
</style>

<div class="lib-form-page p-2">

    <div class="lib-form-header">
        <h2>Add Book</h2>
        <a href="{{ route('library.books.index') }}" class="lib-back-link">
            <span style="font-size:14px;line-height:1;">←</span> Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="lib-alert lib-alert-error">
            <ul style="margin:0;padding-left:16px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('library.books.store') }}">
        @csrf

        <div class="lib-form-card">

            <div class="lib-4col">

                {{-- ============ ROW 1 ============ --}}
                <div class="lib-group">
                    <label class="lib-label" for="book_code">Book No. <span class="req">*</span></label>
                    <input type="text" name="book_code" id="book_code"
                           value="{{ old('book_code') }}"
                           placeholder="e.g. 1010001" title="Format: Language(1) + Subject(2) + Serial(4)"
                           class="lib-control" required>
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="publisher">Publisher</label>
                    <input type="text" name="publisher" id="publisher"
                           value="{{ old('publisher') }}" class="lib-control">
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="total_copies">Total Copies <span class="req">*</span></label>
                    <input type="number" name="total_copies" id="total_copies"
                           value="{{ old('total_copies', 1) }}"
                           min="1" class="lib-control" required>
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="bill_no">Bill No.</label>
                    <input type="text" name="bill_no" id="bill_no"
                           value="{{ old('bill_no') }}" class="lib-control">
                </div>

                {{-- ============ ROW 2 ============ --}}
                <div class="lib-group">
                    <label class="lib-label" for="book_name">Book Name <span class="req">*</span></label>
                    <input type="text" name="book_name" id="book_name"
                           value="{{ old('book_name') }}" class="lib-control" required>
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="publication_year">Publication Year</label>
                    <input type="number" name="publication_year" id="publication_year"
                           value="{{ old('publication_year') }}"
                           min="1000" max="{{ date('Y') + 1 }}"
                           placeholder="e.g. 2015" class="lib-control">
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="shelf_location">Shelf Location</label>
                    <input type="text" name="shelf_location" id="shelf_location"
                           value="{{ old('shelf_location') }}"
                           placeholder="e.g. A-3" class="lib-control">
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="bill_date">Bill Date</label>
                    <input type="date" name="bill_date" id="bill_date"
                           value="{{ old('bill_date') }}" class="lib-control">
                </div>

                {{-- ============ ROW 3 ============ --}}
                <div class="lib-group">
                    <label class="lib-label" for="category_id">Category</label>
                    <div class="lib-select-wrap">
                        <select name="category_id" id="category_id" class="lib-control">
                            <option value="">— Select —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->category_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="lib-arrow"></span>
                    </div>
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="edition">Edition</label>
                    <input type="text" name="edition" id="edition"
                           value="{{ old('edition') }}"
                           placeholder="e.g. 2" class="lib-control">
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="printed_price">Printed Price</label>
                    <input type="text" name="printed_price" id="printed_price"
                           value="{{ old('printed_price') }}"
                           placeholder="e.g. 300" class="lib-control">
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="remarks">Remarks</label>
                    <textarea name="remarks" id="remarks" class="lib-control" rows="1">{{ old('remarks') }}</textarea>
                </div>

                {{-- ============ ROW 4 ============ --}}
                <div class="lib-group">
                    <label class="lib-label" for="author">Author</label>
                    <input type="text" name="author" id="author"
                           value="{{ old('author') }}" class="lib-control">
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="total_pages">Total Pages</label>
                    <input type="number" name="total_pages" id="total_pages"
                           value="{{ old('total_pages') }}"
                           min="1" class="lib-control">
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="donor_name">Donor / Seller</label>
                    <input type="text" name="donor_name" id="donor_name"
                           value="{{ old('donor_name') }}" class="lib-control">
                </div>

                <div class="lib-group">
                    <label class="lib-label">Status</label>
                    <label class="lib-check">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        Active
                    </label>
                </div>

            </div>

            <div class="lib-form-footer">
                <a href="{{ route('library.books.index') }}" class="lib-btn lib-btn-gray">Cancel</a>
                <button type="submit" class="lib-btn lib-btn-primary">Save Book</button>
            </div>

        </div>

    </form>

</div>

</x-app-layout>