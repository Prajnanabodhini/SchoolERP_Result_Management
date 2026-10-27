<x-app-layout>

<style>
.lib-page, .lib-page * { font-family: Arial, sans-serif !important; font-size: 13px; box-sizing: border-box; }
.lib-page h2 { font-size: 21px !important; font-weight: 700 !important; color: #1D4ED8 !important; margin: 0; text-transform: uppercase; letter-spacing: 0.3px; }
.lib-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
.lib-card { background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,.05); margin-bottom: 14px; }
.lib-alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 14px; font-weight: 700; }
.lib-alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.lib-alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.lib-filter { display: flex; align-items: flex-end; flex-wrap: wrap; gap: 12px; }
.lib-filter-group { display: flex; flex-direction: column; gap: 4px; }
.lib-filter-label { font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.3px; }
.lib-input, .lib-select { height: 34px; padding: 4px 10px; border: 1px solid #9ca3af; border-radius: 5px; background: #fff; color: #111827; font-size: 13px; font-weight: 600; outline: none; min-width: 180px; }
.lib-input:focus, .lib-select:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.15); }

.lib-btn { display: inline-flex; align-items: center; justify-content: center; height: 34px; min-width: 110px; padding: 0 16px; border-radius: 5px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; border: 1px solid transparent; white-space: nowrap; box-sizing: border-box; transition: background .15s; }
.lib-btn-primary { background: #16a34a; color: #fff; border-color: #15803d; }
.lib-btn-primary:hover { background: #15803d; }
.lib-btn-blue { background: #2563eb; color: #fff; border-color: #1d4ed8; }
.lib-btn-blue:hover { background: #1d4ed8; }
.lib-btn-gray { background: #6b7280; color: #fff; border-color: #4b5563; }
.lib-btn-gray:hover { background: #4b5563; }
.lib-btn-red  { background: #dc2626; color: #fff; border-color: #b91c1c; }
.lib-btn-red:hover { background: #b91c1c; }

/* --------------------------------------------------------
   Inline row-action buttons — LOCKED size, identical for both
   -------------------------------------------------------- */
.lib-btn-sm {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;

    width: 76px !important;
    min-width: 76px !important;
    max-width: 76px !important;

    height: 30px !important;
    min-height: 30px !important;
    max-height: 30px !important;

    padding: 0 !important;
    margin: 0 !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    line-height: 1 !important;
    box-sizing: border-box !important;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s;
}

/* Form wrapper must not add any box of its own — becomes transparent
   so the inner <button> becomes a direct flex child of the outer div. */
.lib-inline-form {
    display: contents;
}

.lib-table-wrap { background: #fff; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
.lib-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; font-size: 13px; }
.lib-table thead tr { background: #1e3a8a; color: #fff; }
.lib-table th { padding: 9px 6px; font-size: 11px; font-weight: 700; text-align: left; border: 1px solid #d1d5db; color: #fff; white-space: nowrap; text-transform: uppercase; letter-spacing: 0.3px; }
.lib-table th.center { text-align: center; }
.lib-table td { padding: 8px 6px; border: 1px solid #d1d5db; vertical-align: middle; color: #111827; overflow: hidden; }
.lib-table tbody tr:nth-child(even) { background: #f9fafb; }
.lib-table tbody tr:hover { background: #fef9c3; }
.lib-table td.center { text-align: center; }

.lib-nowrap { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; display: block; }
.lib-wrap {
    display: block;
    white-space: normal;
    word-break: break-word;
    overflow-wrap: anywhere;
    line-height: 1.25;
}

.lib-status { display: inline-block; padding: 3px 9px; border-radius: 4px; font-size: 11px; font-weight: 700; letter-spacing: .3px; white-space: nowrap; }
.lib-status-active   { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.lib-status-inactive { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.lib-status-avail    { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
.lib-status-zero     { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.lib-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; text-align: center; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; }
.lib-empty-title { font-size: 15px; font-weight: 700; color: #374151; margin-bottom: 4px; }
.lib-empty-subtitle { font-size: 13px; color: #6b7280; }

/* ---- SweetAlert2 tweaks ---- */
.lib-swal-popup { border-radius: 10px !important; padding: 22px 20px 12px !important; }
.swal2-title { font-size: 18px !important; font-weight: 700 !important; color: #111827 !important; }
.swal2-actions { gap: 10px !important; }
.swal2-confirm, .swal2-cancel {
    font-size: 12.5px !important;
    font-weight: 700 !important;
    padding: 8px 20px !important;
    border-radius: 5px !important;
    min-width: 110px !important;
}
</style>

<div class="lib-page p-3">

    <div class="lib-header">
        <h2>Library — Books Master</h2>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('library.categories.index') }}" class="lib-btn lib-btn-gray">Categories</a>
            <a href="{{ route('library.books.create') }}" class="lib-btn lib-btn-primary">+ Add Book</a>
        </div>
    </div>

    @if(session('success'))
        <div class="lib-alert lib-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="lib-alert lib-alert-error">{{ session('error') }}</div>
    @endif

    <div class="lib-card">
        <form method="GET" action="{{ route('library.books.index') }}">
            <div class="lib-filter">
                <div class="lib-filter-group">
                    <label class="lib-filter-label" for="search">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Book name / code / author" class="lib-input" style="min-width:240px;">
                </div>
                <div class="lib-filter-group">
                    <label class="lib-filter-label" for="category_id">Category</label>
                    <select name="category_id" id="category_id" class="lib-select" style="min-width:180px;">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="lib-filter-group">
                    <label class="lib-filter-label" for="status">Status</label>
                    <select name="status" id="status" class="lib-select" style="min-width:150px;">
                        <option value="">All</option>
                        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                {{-- Pages dropdown --}}
                <div class="lib-filter-group">
                    <label class="lib-filter-label" for="per_page">Pages</label>
                    <select name="per_page" id="per_page" class="lib-select" style="min-width:90px;"
                            onchange="this.form.submit()">
                        @foreach([10, 25, 50, 100, 200] as $n)
                            <option value="{{ $n }}" {{ (int)($perPage ?? 50) === $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="lib-btn lib-btn-blue">Search</button>
                <a href="{{ route('library.books.index') }}" class="lib-btn lib-btn-gray">Reset</a>
            </div>
        </form>
    </div>

    @if($books->count())
        <div class="lib-table-wrap">
            <table class="lib-table">
                <colgroup>
                    <col style="width:4%;">
                    <col style="width:10%;">
                    <col style="width:20%;">
                    <col style="width:12%;">
                    <col style="width:12%;">
                    <col style="width:6%;">
                    <col style="width:7%;">
                    <col style="width:8%;">
                    <col style="width:21%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="center">Sr.</th>
                        <th>Book Code</th>
                        <th>Book Name</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th class="center">Total</th>
                        <th class="center">Avail.</th>
                        <th class="center">Status</th>
                        <th class="center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($books as $i => $book)
                        @php
                            $rowNumber  = ($books->currentPage() - 1) * $books->perPage() + $i + 1;
                            $availClass = $book->available_copies > 0 ? 'lib-status-avail' : 'lib-status-zero';
                            $issued     = max(0, $book->total_copies - $book->available_copies);
                        @endphp
                        <tr>
                            <td class="center" style="font-weight:700;">{{ $rowNumber }}</td>

                            <td>
                                <div class="lib-nowrap" style="font-weight:600;" title="{{ $book->book_code }}">
                                    {{ $book->book_code }}
                                </div>
                            </td>

                            <td>
                                <div class="lib-wrap" style="font-weight:700;">
                                    {{ $book->book_name }}
                                </div>
                            </td>

                            <td>
                                <div class="lib-nowrap" title="{{ $book->author }}">
                                    {{ $book->author ?: '-' }}
                                </div>
                            </td>

                            <td>
                                <div class="lib-nowrap" title="{{ optional($book->category)->category_name }}">
                                    {{ optional($book->category)->category_name ?: '-' }}
                                </div>
                            </td>

                            <td class="center">{{ $book->total_copies }}</td>

                            <td class="center">
                                <span class="lib-status {{ $availClass }}">{{ $book->available_copies }}</span>
                            </td>

                            <td class="center">
                                @if($book->is_active)
                                    <span class="lib-status lib-status-active">ACTIVE</span>
                                @else
                                    <span class="lib-status lib-status-inactive">INACTIVE</span>
                                @endif
                            </td>

                            <td class="center">
                                <div style="display:flex;justify-content:center;align-items:center;gap:8px;flex-wrap:nowrap;">

                                    <button type="button"
                                            onclick="window.location.href='{{ route('library.books.edit', $book->id) }}';"
                                            class="lib-btn lib-btn-blue lib-btn-sm">Edit</button>

                                    <form method="POST"
                                          action="{{ route('library.books.destroy', $book->id) }}"
                                          class="lib-delete-form lib-inline-form"
                                          data-book-name="{{ $book->book_name }}"
                                          data-book-code="{{ $book->book_code }}"
                                          data-issued="{{ $issued }}"
                                          data-total="{{ $book->total_copies }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="lib-btn lib-btn-red lib-btn-sm">Delete</button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination — centered, single "Showing …" from Laravel's built-in widget --}}
        @if($books->hasPages())
            <div style="margin-top:16px;display:flex;justify-content:center;">
                {{ $books->onEachSide(3)->links() }}
            </div>
        @endif

    @else
        <div class="lib-empty">
            <div class="lib-empty-title">No books found</div>
            <div class="lib-empty-subtitle">Click <strong>+ Add Book</strong> to add the first book.</div>
        </div>
    @endif

</div>

{{-- ============================================================
     DELETE CONFIRMATION — SweetAlert2
     ============================================================ --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.lib-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const bookName = form.dataset.bookName || 'this book';
            const bookCode = form.dataset.bookCode || '';
            const issued   = parseInt(form.dataset.issued || '0', 10);
            const total    = parseInt(form.dataset.total  || '1', 10);

            const hasIssued = issued > 0;

            const subtitle = bookCode
                ? `<div style="color:#6b7280;font-size:12.5px;margin-top:2px;">Code: ${bookCode}</div>`
                : '';

            const issuedWarning = hasIssued
                ? `<div style="margin-top:12px;padding:10px 12px;background:#fef2f2;border:1px solid #fca5a5;border-radius:6px;color:#991b1b;font-size:12.5px;text-align:left;">
                       <strong>⚠ Warning:</strong>
                       ${issued} of ${total} ${issued === 1 ? 'copy is' : 'copies are'} currently issued.<br>
                       Deletion will be blocked until they are returned.
                   </div>`
                : '';

            Swal.fire({
                title: 'Delete Book?',
                html: `
                    <div style="text-align:center;">
                        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px;">
                            "${bookName}"
                        </div>
                        ${subtitle}
                        ${issuedWarning}
                        <p style="margin-top:12px;color:#6b7280;font-size:12px;">
                            This action cannot be undone.
                        </p>
                    </div>
                `,
                icon: hasIssued ? 'warning' : 'question',
                iconColor: '#dc2626',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                focusCancel: true,
                customClass: { popup: 'lib-swal-popup' }
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

});
</script>
{{-- ============================================================
     /DELETE CONFIRMATION
     ============================================================ --}}

</x-app-layout>