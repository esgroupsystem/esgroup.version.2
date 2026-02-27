<div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Crop Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="cropper-wrap">
                    <img id="cropperImage" src="" alt="Cropper">
                </div>

                <div class="d-flex gap-2 mt-3 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="zoomIn">Zoom +</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="zoomOut">Zoom -</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="rotateLeft">Rotate</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="resetCrop">Reset</button>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="button" id="applyCrop">Apply Crop</button>
            </div>

        </div>
    </div>
</div>