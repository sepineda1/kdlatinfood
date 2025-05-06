<div wire:ignore.self class="modal fade" id="theModal" tabindex="-1" role="dialog" style="backdrop-filter: blur(10px);">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header " style="background: #ff5100;">
                <h5 class="modal-title text-white">
                    <b>{{$componentName}}</b> 
                </h5>


                <button type="button" wire:loading class="btn btn-success close-btn text-info">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                        stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span style="color:white">PLEASE WAIT</span>
                </button>

            </div>
            <div class="modal-body">