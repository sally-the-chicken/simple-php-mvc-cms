tinymce.init({
    selector: 'textarea',
    body_class: 'Article',
    height: 500,
    plugins: [
      "advlist autolink autosave link image lists hr anchor pagebreak",
      "searchreplace wordcount code media nonbreaking",
      "table contextmenu directionality textcolor paste textcolor colorpicker textpattern"
    ],
    toolbar: 'insert | undo redo |  formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
    relative_urls: false,
    menubar: false,
    toolbar_items_size: 'small',
    image_predefined_styles: true,
    content_css: [
        '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
        'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css'
    ]
});

$(document).ready(function(){
    $("#status").select2();
});
