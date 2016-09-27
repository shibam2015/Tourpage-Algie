$(function() {
    appendModalInBody();
    //check if store was set up completely
    $.ajax({
        url: '/vendor/index/checkAccountSetUp',
        success:function(res) {
            if (res == '0' || res == 0) {
                $('#modal').modal('show');
            }
        }
    });
});

function appendModalInBody() {
    var modal = '<div id="modal" class="modal fade" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Warning!</h4></div><div class="modal-body"><p>Hi, it seems like you have not completed your store set up. Please click <a href="/vendor/account/edit">here</a> to go to your store settings.</p></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div></div></div></div>';
    $('body').append(modal);
}
