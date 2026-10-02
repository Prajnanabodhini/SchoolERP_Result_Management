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
.lib-btn-sm { width: 64px; height: 28px; min-width: 64px; padding: 0; font-size: 12px; font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle; box-sizing: border-box; border-radius: 4px; text-decoration: none; cursor: pointer; border: 1px solid transparent; white-space: nowrap; }
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
.lib-status { display: inline-block; padding: 3px 9px; border-radius: 4px; font-size: 11px; font-weight: 700; letter-spacing: .3px; white-space: nowrap; }
.lib-status-issued   { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.lib-status-returned { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.lib-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; text-align: center; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; }
.lib-empty-title { font-size: 15px; font-weight: 700; color: #374151; margin-bottom: 4px; }
.lib-empty-subtitle { font-size: 13px; color: #6b7280; }
</style>

<div class="lib-page p-3">

    <div class="lib-header">
        <h2>Library — Book Categories</h2>
        <a href="{{ route('library.categories.create') }}" class="lib-btn lib-btn-primary">
            + Add Category
        </a>
    </div>

    @if(session('success'))
        <div class="lib-alert lib-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="lib-alert lib-alert-error">{{ session('error') }}</div>
    @endif

    <div class="lib-card">
        <form method="GET" action="{{ route('library.categories.index') }}">
            <div class="lib-filter">
                <div class="lib-filter-group">
                    <label class="lib-filter-label" for="search">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Category name or description" class="lib-input" style="min-width:260px;">
                </div>
                <div class="lib-filter-group">
                    <label class="lib-filter-label" for="status">Status</label>
                    <select name="status" id="status" class="lib-select" style="min-width:150px;">
                        <option value="">All</option>
                        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="lib-btn lib-btn-blue">Search</button>
                <a href="{{ route('library.categories.index') }}" class="lib-btn lib-btn-gray">Reset</a>
            </div>
        </form>
    </div>

    @if($categories->count())
        <div class="lib-table-wrap">
            <table class="lib-table">
                <colgroup>
                    <col style="width:5%;">
                    <col style="width:28%;">
                    <col style="width:35%;">
                    <col style="width:9%;">
                    <col style="width:9%;">
                    <col style="width:14%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="center">Sr.</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th class="center">Books</th>
                        <th class="center">Status</th>
                        <th class="center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $i => $cat)
                        @php
                            $rowNumber = ($categories->currentPage() - 1) * $categories->perPage() + $i + 1;
                        @endphp
                        <tr>
                            <td class="center" style="font-weight:700;">{{ $rowNumber }}</td>
                            <td><div class="lib-nowrap" style="font-weight:600;" title="{{ $cat->category_name }}">{{ $cat->category_name }}</div></td>
                            <td><div class="lib-nowrap" title="{{ $cat->description }}">{{ $cat->description ?: '-' }}</div></td>
                            <td class="center">{{ $cat->books_count }}</td>
                            <td class="center">
                                @if($cat->is_active)
                                    <span class="lib-status lib-status-returned">ACTIVE</span>
                                @else
                                    <span class="lib-status lib-status-issued">INACTIVE</span>
                                @endif
                            </td>
                            <td class="center">
                                <div style="display:flex;justify-content:center;align-items:center;gap:6px;flex-wrap:nowrap;">
                                    <button type="button"
                                            onclick="window.location.href='{{ route('library.categories.edit', $cat->id) }}';"
                                            class="lib-btn lib-btn-blue lib-btn-sm">Edit</button>

                                    <form method="POST" action="{{ route('library.categories.destroy', $cat->id) }}"
                                          onsubmit="return confirm('Delete this category?');"
                                          style="display:inline-flex;margin:0;padding:0;vertical-align:middle;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="lib-btn lib-btn-red lib-btn-sm">Del</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div style="margin-top:14px;display:flex;justify-content:center;">
                {{ $categories->onEachSide(3)->links() }}
            </div>
        @endif
    @else
        <div class="lib-empty">
            <div class="lib-empty-title">No book categories found</div>
            <div class="lib-empty-subtitle">Click <strong>+ Add Category</strong> to create the first one.</div>
        </div>
    @endif

</div>

</x-app-layout>