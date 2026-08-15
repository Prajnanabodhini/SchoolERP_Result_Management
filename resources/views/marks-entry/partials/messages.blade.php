@if(session('success') == 'Marks Saved Successfully.')

<div style="
    margin-bottom:15px;
    padding:12px;
    background:#FEF3C7;
    border:1px solid #F59E0B;
    border-radius:5px;
    color:#92400E;
    font-weight:bold;
">

⚠ Marks are saved but NOT finally submitted.

Please click <b>Submit Final Marks</b> to complete mark submission.

Until final submission, marks can still be modified.

</div>

@endif

@if(!empty($error))

<div style="
    margin-bottom:15px;
    padding:10px;
    background:#FEE2E2;
    border:1px solid #EF4444;
    border-radius:4px;
">
    <span style="
        color:#DC2626;
        font-weight:bold;
    ">
        Error :-
    </span>

    <span style="color:#DC2626;">
        {{ $error }}
    </span>
</div>

@endif