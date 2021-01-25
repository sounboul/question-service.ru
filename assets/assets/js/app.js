// assets/frontend/js/app.js

const $ = require('jquery');

// bootstrap
require('bootstrap');

// отображать выбранный файл в bootstrap
$('input[type="file"]').change(function(e){
    $('.custom-file-input').html(e.target.files[0].name);
});
