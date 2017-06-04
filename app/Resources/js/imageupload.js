$(document).ready(function(){
    var $uploader = $('.js-cloudinary');
    var targetfield = null;
    var previewfield = null;
    var $clearbutton = null;
    if($uploader.length > 0) {
        $uploader.click(function(e) {
            var cloudname = $(this).data('cloudname');
            var cloudinary_key = $(this).data('cloudinary-key');
            var preset = $(this).data('preset');
            var multiple = $(this).data('multiple');
            var max = $(this).data('max');
            var cropping = $(this).data('cropping');
            var aspect = $(this).data('aspect-ratio');
            var pubid = $(this).data('pubid');
            var config = {};
            config.cloud_name = cloudname;
            config.upload_preset = preset;
            config.multiple = multiple;
            config.max_files = max;
            config.cropping = cropping;
            config.cropping_aspect_ratio = aspect;
            config.public_id = pubid;
            targetfield = $(this).data('fieldid');
            previewfield = $(this).data('preview');
            e.preventDefault();
            cloudinary.openUploadWidget(config, 
                function (error, result) {
                }
            );
        });

        $(document).on('cloudinarywidgetsuccess', function(e, data) {
            var $input = $('#' + targetfield);
            var $preview = $('#' +  previewfield);
            if(data[0]) {
                console.log(data);
                $input.val(data[0].url);
                $preview.attr('src',data[0].url);
            }
        });

        $clearbutton = $('.image-clear');        
        $clearbutton.click( function () {
            var target = $(this).data('target');
            if(confirm('Are you sure you want to remove this image?')) {
                $(target).val('');
                $(target + '_imagepreview').attr('src','/dist/img/noimage.png');
            }
        } );
    }
});
