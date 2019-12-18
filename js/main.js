jQuery(document).ready(function( $ ) {
    $('#btn-print').on('click', function () {
        printElem("acf-group_order_info");
    });
});

function printElem(id)
{
    var mywindow = window.open('', 'PRINT', 'height=1024,width=768');

    mywindow.document.write('<html><head><title>' + document.title  + '</title>');
    mywindow.document.write('</head><body >');
    mywindow.document.write(document.getElementById(id).innerHTML);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10*/

    mywindow.print();
    // mywindow.close();

    return true;
}