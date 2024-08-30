<div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true" id="CreateModal">
    <div class="modal-dialog modal-lg" role="document">
        <form class="form" action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Add Numbers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">

                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="form-group">
                        <label for="Name">Type:</label>
                        <select class="form-control" id="AddType" name="type">
                            <option value="" selected>Select type</option>
                            <option value="0">โต๊ด</option>
                            <option value="1">เต็ง</option>
                        </select>
                    </div>
                    <div id="length-contrainer">
                        <div class="form-group">
                            <label for="length">length:</label>
                            <input type="number" class="form-control" name="length" id="Addlength" min="0" max="3" step="1"title="Please enter only numbers">
                        </div>
                    </div>
                    <div id="number-contrainer">
                        <div class="form-group">
                            <label for="3numbers">numbers:</label>
                            <input type="text" class="form-control" name="numbers" id="AddNumbers" maxlength="3"
                                value="" pattern="\d*" title="Please enter only numbers">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="max">max:</label>
                        <input type="text" class="form-control" name="max" id="AddMax" pattern="\d*"
                            value="">
                    </div>
                    <div class="form-group">
                        <label for="Name">date:</label>
                        <input type="text" class="form-control" name="date" required autocomplete="date"
                            id="AddDate" value="{{ date('d-m-Y') }}">
                    </div>


                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="SubmitCreateForm">Save</button>
                    <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
    </div>
</div>
