var table;
$(function() {
    table = $('#article_tbl').DataTable({
        "order": [[ 1, "desc" ]], 
        "pageLength": 20, 
        "columnDefs": [
        { "orderable": false, "targets": [0,3] }],
        "processing": true,
        "serverSide": true,
        "ajax": "/article/ajax_list/"
    });
});
