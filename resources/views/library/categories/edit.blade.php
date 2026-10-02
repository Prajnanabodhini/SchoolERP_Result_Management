<x-app-layout>

<style>
.lib-form-page, .lib-form-page * { font-family: Arial, sans-serif !important; font-size: 13px; box-sizing: border-box; }
.lib-form-page h2 { font-size: 21px !important; font-weight: 700 !important; color: #1D4ED8 !important; margin: 0; text-transform: uppercase; letter-spacing: 0.3px; }
.lib-form-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
.lib-form-card { width: 100%; max-width: 720px; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.05); padding: 22px; }
.lib-row { display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 16px; }
.lib-group { display: flex; flex-direction: column; gap: 6px; }
.lib-label { font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .3px; }
.lib-label .req { color: #dc2626; }
.lib-control { height: 38px; padding: 6px 12px; border: 1px solid #9ca3af; border-radius: 5px; background: #fff; color: #111827; font-size: 13px; font-weight: 600; outline: none; width: 100%; }
.lib-control:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.15); }
.lib-check { display: inline-flex; align-items: center; gap: 6px; font-weight: 600; }
.lib-check input { width: 16px; height: 16px; accent-color: #2563eb; }
.lib-alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 14px; font-weight: 700; }
.lib-alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.lib-form-footer { display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
.lib-btn { display: inline-flex; align-items: center; justify-content: center; height: 38px; min-width: 130px; padding: 0 20px; border-radius: 5px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; border: 1px solid transparent; white-space: nowrap; box-sizing: border-box; transition: background .15s; }
.lib-btn-primary { background: #16a34a; color: #fff; border-color: #15803d; }
.lib-btn-primary:hover { background: #15803d; }
.lib-btn-gray { background: #6b7280; color: #fff; border-color: #4b5563; }
.lib-btn-gray:hover { background: #4b5563; }
.lib-back-link { display: inline-flex; align-items: center; gap: 6px; padding: 0 14px; height: 38px; border: 1px solid #d1d5db; border-radius: 5px; background: #f9fafb; color: #374151; font-size: 13px; font-weight: 700; text-decoration: none; transition: background .12s; }
.lib-back-link:hover { background: #f3f4f6; color: #111827; }
</style>

<div class="lib-form-page p-3">

    <div class="lib-form-header">
        <h2>Edit Book Category</h2>
        <a href="{{ route('library.categories.index') }}" class="lib-back-link">
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

    <form method="POST" action="{{ route('library.categories.update', $category->id) }}">
        @csrf
        @method('PUT')

        <div class="lib-form-card">

            <div class="lib-row">
                <div class="lib-group">
                    <label class="lib-label" for="category_name">Category Name <span class="req">*</span></label>
                    <input type="text" name="category_name" id="category_name"
                           value="{{ old('category_name', $category->category_name) }}"
                           class="lib-control" required>
                </div>

                <div class="lib-group">
                    <label class="lib-label" for="description">Description</label>
                    <input type="text" name="description" id="description"
                           value="{{ old('description', $category->description) }}"
                           class="lib-control">
                </div>

                <div class="lib-group">
                    <label class="lib-label">Status</label>
                    <label class="lib-check">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                        Active
                    </label>
                </div>
            </div>

            <div class="lib-form-footer">
                <a href="{{ route('library.categories.index') }}" class="lib-btn lib-btn-gray">Cancel</a>
                <button type="submit" class="lib-btn lib-btn-primary">Update Category</button>
            </div>

        </div>

    </form>

</div>

</x-app-layout>