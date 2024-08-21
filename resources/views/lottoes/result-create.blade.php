
<div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="CreateModal2">
        <div class="modal-dialog modal-lg" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Add Result lotto</h5>
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
                            <label for="Name">date:</label>
                            <input type="text" class="form-control" name="date" required autocomplete="off"  id="AddDate2">
                        </div>
                        <div class="form-group">
                            <label for="1st">1st:</label>
                            <input type="text" class="form-control numeric-only" name="1st" id="add1st" maxlength="6" value="" pattern="\d*" title="Please enter only numbers">
                        </div>
                        <div class="form-group">
                            <label for="3front_1">3front_1:</label>
                            <input type="text" class="form-control numeric-only" maxlength="3" pattern="\d*" name="3front_1" id="add3front_1"   value="">
                        </div>
                        <div class="form-group">
                            <label for="3front_2">3front_2:</label>
                            <input type="text" class="form-control numeric-only" maxlength="3" pattern="\d*" name="3front_2" id="add3front_2" value="">
                        </div>
                        <div class="form-group">
                            <label for="3back_1">3back_1:</label>
                            <input type="text" class="form-control numeric-only" maxlength="3" pattern="\d*" name="3back_1" id="add3back_1" value="">
                        </div>
                        <div class="form-group">
                            <label for="3back_2">3back_2:</label>
                            <input type="text" class="form-control numeric-only" maxlength="3" pattern="\d*" name="3back_2" id="add3back_2" value="">
                        </div>
                        <div class="form-group">
                            <label for="2numbers">2numbers:</label>
                            <input type="text" class="form-control numeric-only" maxlength="2" pattern="\d*" name="2numbers" id="add2numbers" value="">
                        </div>


                        {{-- <div class="form-group">
                            <label for="3front_2">3front_2:</label>
                            <select class="form-control" id="add3front_2" name="3front_2">
                                <option value="" selected>Select type</option>
                                <option value="0">โต๊ด</option>
                            </select>
                        </div> --}}
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="SubmitCreateForm2">Save</button>
                        <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
        </div>
    </div>
