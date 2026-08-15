@if(session('success'))

<div id="toast-message"
     style="
        position:fixed;
        top:20px;
left:50%;
transform:translateX(-50%);
        z-index:9999;
        background:#DCFCE7;
        color:#15803D;
        border:1px solid #22C55E;
        padding:12px 18px;
        border-radius:6px;
        box-shadow:0 4px 10px rgba(0,0,0,.15);
        font-weight:600;
     ">
    Success :- {{ session('success') }}
</div>

@endif

{{-- @if(session('error'))

<div id="toast-message"
     style="
        position:fixed;
        top:20px;
        right:20px;
        z-index:9999;
        background:#FEE2E2;
        color:#DC2626;
        border:1px solid #EF4444;
        padding:12px 18px;
        border-radius:6px;
        box-shadow:0 4px 10px rgba(0,0,0,.15);
        font-weight:600;
     ">
    Error :- {{ session('error') }}
</div>

@endif --}}

@if(session('error'))

<div id="errorPopup"
     style="
        position:fixed;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background:rgba(0,0,0,0.5);
        z-index:99999;
        display:flex;
        align-items:center;
        justify-content:center;
     ">

    <div style="
        background:white;
        width:450px;
        border-radius:8px;
        overflow:hidden;
        box-shadow:0 0 15px rgba(0,0,0,.3);
    ">

        <div style="
            background:#dc2626;
            color:white;
            padding:12px;
            font-weight:bold;
        ">
            Error
        </div>

        <div style="padding:20px;">
            {{ session('error') }}
        </div>

        <div style="
            padding:10px;
            text-align:right;
            border-top:1px solid #ddd;
        ">
            <button
                onclick="document.getElementById('errorPopup').remove();"
                style="
                    background:#dc2626;
                    color:white;
                    border:none;
                    padding:8px 20px;
                    cursor:pointer;
                ">
                OK
            </button>
        </div>

    </div>

</div>

@endif

<script>

document.addEventListener('DOMContentLoaded', function () {

    let toast =
        document.getElementById(
            'toast-message'
        );

    if (toast)
    {
        setTimeout(function () {

            toast.style.transition =
                'opacity 0.5s';

            toast.style.opacity = '0';

            setTimeout(function () {

                toast.remove();

            }, 500);

        }, 4000);
    }

});

</script>