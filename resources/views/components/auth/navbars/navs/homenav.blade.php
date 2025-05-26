@props(['p','btn', 'textColor','svgColor'])

<div class="container {{ $p }}">
    <nav class="navbar navbar-main navbar-expand-lg position-sticky mt-4 top-1 px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur" data-scroll="true">
        <div class="container-fluid py-1 px-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">        
                    <button class="btn btn-icon btn-3 btn-info" type="button" onclick="window.location.href='{{ route('sign-in')';">
                        <span class="btn-inner--icon"><i class="material-icons">favorite</i></span>
                        <span class="btn-inner--text"> Login</span>
                    </button>
                </ol>
            </nav>
        </div>
    </nav>
    
    
</div>
