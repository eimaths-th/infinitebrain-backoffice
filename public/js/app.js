var token = $('meta[name=csrf-token]').attr('content');

function formModal(route) {
    $.get(route, function (res) {
        $("#modal_form_content").empty();
        $('#modal_form_content').html(res);
        $("#modal_form").modal("show");

        $(".select2test").select2({

            maximumSelectionLength: 2,
            allowClear: true
        });
        $(".select2-paper_report").select2({

            allowClear: true
        });

        // $(".select3_single").select2({
        //     maximumSelectionLength: 1,
        //     // allowClear: true
        // });

        // $(".select2_multiple").select2({
        //     maximumSelectionLength: 2,
        //     //placeholder: "With Max Selection limit 4",
        //     allowClear: true
        // });
        // $(".timepicker").timepicker();
        $(".timepicker").timepicker({
            timeFormat: 'H:mm', // Use 'H:mm' for 24-hour format
            interval: 15,       // Set the time interval
            scrollbar: true     // Enable the scrollbar for easier time selection
        });

        $("#datepicker-autoClose").datepicker({
            todayHighlight: true,
            autoclose: true
        });
    });

}

function formModal_1(route) {
    $.get(route, function (res) {
        $("#modal_form_content").empty();
        $('#modal_form_content').html(res);
        $("#modal_form").modal("show");

        $(".timepicker").timepicker({
            timeFormat: 'H:mm', // Use 'H:mm' for 24-hour format
            interval: 15,       // Set the time interval
            scrollbar: true     // Enable the scrollbar for easier time selection
        });

        var currentTime = new Date();
        var hours = currentTime.getHours();
        var minutes = currentTime.getMinutes();
        $(".timepicker").timepicker("setTime", hours + ":" + minutes);


        $("#datepicker-autoClose").datepicker({
            todayHighlight: true,
            autoclose: true,
            format: 'yyyy-mm-dd',
        });
        $("#datepicker-autoClose").datepicker("setDate", new Date());

        $(document).on('change', '#status', function (e) {
            var status = $(this).val()
            console.log(status);
            if (status == 1 || status == 11 || status == 12) {
                console.log('true');

                $('#stime').prop('disabled', false);
                $('#etime').prop('disabled', false);
            } else {
                $('#stime').prop('disabled', true);
                $('#etime').prop('disabled', true);
            }

        })

    });

}
function formModal_2(route) {
    $.get(route, function (res) {
        $("#modal_form_content").empty();
        $('#modal_form_content').html(res);
        $("#modal_form").modal("show");

        $(".timepicker").timepicker({
            timeFormat: 'H:mm', // Use 'H:mm' for 24-hour format
            // scrollbar: true     // Enable the scrollbar for easier time selection
        });



        $("#datepicker-autoClose").datepicker({
            // todayHighlight: true,
            // autoclose: true,
            format: 'yyyy-mm-dd',
        });
        // $("#datepicker-autoClose").datepicker("setDate", new Date());

        $(document).on('change', '#status', function (e) {
            var status = $(this).val()
            console.log(status);
            if (status == 1 || status == 11 || status == 12) {
                console.log('true');

                $('#stime').prop('disabled', false);
                $('#etime').prop('disabled', false);
            } else {
                $('#stime').prop('disabled', true);
                $('#etime').prop('disabled', true);
            }

        })

    });

}

// //test
// function modalDes(data_do, delete_text) {
//     swal.fire({
//         title: "คุณแน่ใจไหม?",
//         text: 'ที่จะลบ Username : ' + delete_text,
//         icon: 'error',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         cancelButtonText: 'ยกเลิก',
//         confirmButtonText: 'ใช่, ลบเลย!',
//     }).then((result) => {
//         if (result.value) {
//             $.ajax({
//                 url: data_do, // url where to submit the request
//                 type: "DELETE", // type of action POST || GET
//                 data: {
//                     _token: token
//                 },
//                 dataType: 'json',
//                 cache: false,
//                 success: function (result) {
//                     //console.log(result);
//                     if (result.success == true) {
//                         Swal.fire({
//                             icon: 'success',
//                             title: result.msg,
//                             html: result.msg + ' กำลังโหลดหน้าใหม่... <b></b>',
//                             timer: 1000,
//                             timerProgressBar: true,
//                             onBeforeOpen: () => {
//                                 Swal.showLoading()
//                                 timerInterval = setInterval(() => {
//                                     Swal.getContent().querySelector('b')
//                                         .textContent = Swal.getTimerLeft()
//                                 }, 100)
//                             },
//                             onClose: () => {
//                                 location.reload();
//                             }
//                         });
//                     }
//                     else {
//                         Swal.fire({
//                             icon: "error",
//                             text: result.msg
//                         });
//                     }
//                 },
//                 error: function (xhr, resp, text) {
//                     console.log(xhr, resp, text);
//                 }
//             });
//         } else {
//             swal.close()
//         }
//     })
// }

// function modalDesreq(data_do, delete_text) {
//     swal.fire({
//         title: "คุณแน่ใจไหม?",
//         text: 'ที่จะลบ คำร้องของ : ' + delete_text,
//         icon: 'error',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         cancelButtonText: 'ยกเลิก',
//         confirmButtonText: 'ใช่, ลบเลย!',
//     }).then((result) => {
//         if (result.value) {
//             $.ajax({
//                 url: data_do, // url where to submit the request
//                 type: "DELETE", // type of action POST || GET
//                 data: {
//                     _token: token
//                 },
//                 dataType: 'json',
//                 cache: false,
//                 success: function (result) {
//                     //console.log(result);
//                     if (result.success == true) {
//                         Swal.fire({
//                             icon: 'success',
//                             title: result.msg,
//                             html: result.msg + ' กำลังโหลดหน้าใหม่... <b></b>',
//                             timer: 1000,
//                             timerProgressBar: true,
//                             onBeforeOpen: () => {
//                                 Swal.showLoading()
//                                 timerInterval = setInterval(() => {
//                                     Swal.getContent().querySelector('b')
//                                         .textContent = Swal.getTimerLeft()
//                                 }, 100)
//                             },
//                             onClose: () => {
//                                 location.reload();
//                             }
//                         });
//                     } else {
//                         Swal.fire({
//                             icon: "error",
//                             text: result.msg
//                         });
//                     }
//                 },
//                 error: function (xhr, resp, text) {
//                     console.log(xhr, resp, text);
//                 }
//             });
//         } else {
//             swal.close()
//         }
//     })
// }

// function modalDeldetp(data_do, delete_text) {
//     swal.fire({
//         title: "คุณแน่ใจไหม?",
//         text: 'ที่จะลบ รายการ : ' + delete_text,
//         icon: 'error',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         cancelButtonText: 'ยกเลิก',
//         confirmButtonText: 'ใช่, ลบเลย!',
//     }).then((result) => {
//         if (result.value) {
//             $.ajax({
//                 url: data_do, // url where to submit the request
//                 type: "DELETE", // type of action POST || GET
//                 data: {
//                     _token: token
//                 },
//                 dataType: 'json',
//                 cache: false,
//                 success: function (result) {
//                     //console.log(result);
//                     if (result.success == true) {
//                         Swal.fire({
//                             icon: 'success',
//                             title: result.msg,
//                             html: result.msg + ' กำลังโหลดหน้าใหม่... <b></b>',
//                             timer: 1000,
//                             timerProgressBar: true,
//                             onBeforeOpen: () => {
//                                 Swal.showLoading()
//                                 timerInterval = setInterval(() => {
//                                     Swal.getContent().querySelector('b')
//                                         .textContent = Swal.getTimerLeft()
//                                 }, 100)
//                             },
//                             onClose: () => {
//                                 location.reload();
//                             }
//                         });
//                     } else {
//                         Swal.fire({
//                             icon: "error",
//                             text: result.msg
//                         });
//                     }
//                 },
//                 error: function (xhr, resp, text) {
//                     console.log(xhr, resp, text);
//                 }
//             });
//         } else {
//             swal.close()
//         }
//     })
// }


// function modalDesOt(data_do, delete_text) {
//     swal.fire({
//         title: "คุณแน่ใจไหม?",
//         text: 'ต้องการที่จะ ลบรายการ : ' + delete_text,
//         icon: 'error',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         cancelButtonText: 'ยกเลิก',
//         confirmButtonText: 'ใช่, ลบเลย!',
//     }).then((result) => {
//         if (result.value) {
//             $.ajax({
//                 url: data_do, // url where to submit the request
//                 type: "DELETE", // type of action POST || GET
//                 data: {
//                     _token: token
//                 },
//                 dataType: 'json',
//                 cache: false,
//                 success: function (result) {
//                     //console.log(result);
//                     if (result.success == true) {
//                         Swal.fire({
//                             icon: 'success',
//                             title: result.msg,
//                             html: result.msg + ' กำลังโหลดหน้าใหม่... <b></b>',
//                             timer: 1000,
//                             timerProgressBar: true,
//                             onBeforeOpen: () => {
//                                 Swal.showLoading()
//                                 timerInterval = setInterval(() => {
//                                     Swal.getContent().querySelector('b')
//                                         .textContent = Swal.getTimerLeft()
//                                 }, 100)
//                             },
//                             onClose: () => {
//                                 location.reload();
//                             }
//                         });
//                     } else {
//                         Swal.fire({
//                             icon: "error",
//                             text: result.msg
//                         });
//                     }
//                 },
//                 error: function (xhr, resp, text) {
//                     console.log(xhr, resp, text);
//                 }
//             });
//         } else {
//             swal.close()
//         }
//     })
// }


// var handleGritterNotification = function () {

//     $('[data-click="swal-danger"]').click(function (e) {
//         e.preventDefault();
//         swal({
//             title: 'Are you sure?',
//             text: 'You will not be able to recover this imaginary file!',
//             icon: 'error',
//             buttons: {
//                 cancel: {
//                     text: 'Cancel',
//                     value: null,
//                     visible: true,
//                     className: 'btn btn-default',
//                     closeModal: true,
//                 },
//                 confirm: {
//                     text: 'Warning',
//                     value: true,
//                     visible: true,
//                     className: 'btn btn-danger',
//                     closeModal: true
//                 }
//             }
//         });
//     });

// }

// function modalPref(data_do, delete_text) {
//     swal.fire({
//         title: "คุณแน่ใจไหม?",
//         text: 'ที่จะลบ คำนำหน้า : ' + delete_text,
//         icon: 'error',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         cancelButtonText: 'ยกเลิก',
//         confirmButtonText: 'ใช่, ลบเลย!',
//     }).then((result) => {
//         if (result.value) {
//             $.ajax({
//                 url: data_do, // url where to submit the request
//                 type: "DELETE", // type of action POST || GET
//                 data: {
//                     _token: token
//                 },
//                 dataType: 'json',
//                 cache: false,
//                 success: function (result) {
//                     //console.log(result);
//                     if (result.success == true) {
//                         Swal.fire({
//                             icon: 'success',
//                             title: result.msg,
//                             html: result.msg + ' กำลังโหลดหน้าใหม่... <b></b>',
//                             timer: 1000,
//                             timerProgressBar: true,
//                             onBeforeOpen: () => {
//                                 Swal.showLoading()
//                                 timerInterval = setInterval(() => {
//                                     Swal.getContent().querySelector('b')
//                                         .textContent = Swal.getTimerLeft()
//                                 }, 100)
//                             },
//                             onClose: () => {
//                                 location.reload();
//                             }
//                         });
//                     } else {
//                         Swal.fire({
//                             icon: "error",
//                             text: result.msg
//                         });
//                     }
//                 },
//                 error: function (xhr, resp, text) {
//                     console.log(xhr, resp, text);
//                 }
//             });
//         } else {
//             swal.close()
//         }
//     })
// }
// function modalRate(data_do, delete_text) {
//     swal.fire({
//         title: "คุณแน่ใจไหม?",
//         text: 'ที่จะลบ ประเภทวันรายการนี้ : ' + delete_text,
//         icon: 'error',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         cancelButtonText: 'ยกเลิก',
//         confirmButtonText: 'ใช่, ลบเลย!',
//     }).then((result) => {
//         if (result.value) {
//             $.ajax({
//                 url: data_do, // url where to submit the request
//                 type: "DELETE", // type of action POST || GET
//                 data: {
//                     _token: token
//                 },
//                 dataType: 'json',
//                 cache: false,
//                 success: function (result) {
//                     //console.log(result);
//                     if (result.success == true) {
//                         Swal.fire({
//                             icon: 'success',
//                             title: result.msg,
//                             html: result.msg + ' กำลังโหลดหน้าใหม่... <b></b>',
//                             timer: 1000,
//                             timerProgressBar: true,
//                             onBeforeOpen: () => {
//                                 Swal.showLoading()
//                                 timerInterval = setInterval(() => {
//                                     Swal.getContent().querySelector('b')
//                                         .textContent = Swal.getTimerLeft()
//                                 }, 100)
//                             },
//                             onClose: () => {
//                                 location.reload();
//                             }
//                         });
//                     } else {
//                         Swal.fire({
//                             icon: "error",
//                             text: result.msg
//                         });
//                     }
//                 },
//                 error: function (xhr, resp, text) {
//                     console.log(xhr, resp, text);
//                 }
//             });
//         } else {
//             swal.close()
//         }
//     })
// }

// function modalPlace(data_do, delete_text) {
//     swal.fire({
//         title: "คุณแน่ใจไหม?",
//         text: 'ที่จะลบ สถานที่นี้ : ' + delete_text,
//         icon: 'error',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         cancelButtonText: 'ยกเลิก',
//         confirmButtonText: 'ใช่, ลบเลย!',
//     }).then((result) => {
//         if (result.value) {
//             $.ajax({
//                 url: data_do, // url where to submit the request
//                 type: "DELETE", // type of action POST || GET
//                 data: {
//                     _token: token
//                 },
//                 dataType: 'json',
//                 cache: false,
//                 success: function (result) {
//                     //console.log(result);
//                     if (result.success == true) {
//                         Swal.fire({
//                             icon: 'success',
//                             title: result.msg,
//                             html: result.msg + ' กำลังโหลดหน้าใหม่... <b></b>',
//                             timer: 1000,
//                             timerProgressBar: true,
//                             onBeforeOpen: () => {
//                                 Swal.showLoading()
//                                 timerInterval = setInterval(() => {
//                                     Swal.getContent().querySelector('b')
//                                         .textContent = Swal.getTimerLeft()
//                                 }, 100)
//                             },
//                             onClose: () => {
//                                 location.reload();
//                             }
//                         });
//                     } else {
//                         Swal.fire({
//                             icon: "error",
//                             text: result.msg
//                         });
//                     }
//                 },
//                 error: function (xhr, resp, text) {
//                     console.log(xhr, resp, text);
//                 }
//             });
//         } else {
//             swal.close()
//         }
//     })
// }

// function modalHol(data_do, delete_text) {
//     swal.fire({
//         title: "คุณแน่ใจไหม ?",
//         text: 'ที่จะลบ วันหยุดเทส : ' + delete_text,
//         icon: 'error',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         cancelButtonText: 'ยกเลิก',
//         confirmButtonText: 'ใช่, ลบเลย!',
//     }).then((result) => {
//         if (result.value) {
//             $.ajax({
//                 url: data_do, // url where to submit the request
//                 type: "DELETE", // type of action POST || GET
//                 data: {
//                     _token: token
//                 },
//                 dataType: 'json',
//                 cache: false,
//                 success: function (result) {
//                     //console.log(result);
//                     if (result.success == true) {
//                         Swal.fire({
//                             icon: 'success',
//                             title: result.msg,
//                             html: result.msg + ' กำลังโหลดหน้าใหม่... <b></b>',
//                             timer: 1000,
//                             timerProgressBar: true,
//                             onBeforeOpen: () => {
//                                 Swal.showLoading()
//                                 timerInterval = setInterval(() => {
//                                     Swal.getContent().querySelector('b')
//                                         .textContent = Swal.getTimerLeft()
//                                 }, 100)
//                             },
//                             onClose: () => {
//                                 location.reload();
//                             }
//                         });
//                     } else {
//                         Swal.fire({
//                             icon: "error",
//                             text: result.msg
//                         });
//                     }
//                 },
//                 error: function (xhr, resp, text) {
//                     console.log(xhr, resp, text);
//                 }
//             });
//         } else {
//             swal.close()
//         }
//     })
// }
