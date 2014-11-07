$(document).ready(function () {

    //Set main picture
    $('.ricoImages a.thumbnail').click(function (e) {
        e.preventDefault();

        var thisThumb = $(this);
        var jqxhr = $.ajax({
            //Нужен ид модели, ид картинки
            type: "GET",
            url: ricoSetMainUrl + '?imageId=' +  $(this).attr('id') + '&objectId=' + $('.modelId').val()
        })
            .done(function () {
                //alert( "success" );
                $('.ricoImages a.thumbnail').removeClass('selectedImg');
                thisThumb.addClass('selectedImg');
            })
            .fail(function () {
                alert("error");
            })
            .always(function () {
                //alert( "complete" );
            });
    });

    //removeImage

    $('.ricoImages a.glyphicon-remove').click(function (e) {
        e.preventDefault();
        var $toRemove = $(this).parent();
        var thisThumb = $(this);
        var jqxhr = $.ajax({
            //Нужен ид модели, ид картинки
            type: "GET",
            url: ricoRemoveImagesUrl +  "?imageId=" + $(this).next().attr('id')
        })
            .done(function () {
                //alert( "success" );
                $toRemove.remove();
            })
            .fail(function () {
                alert("error");
            })
            .always(function () {
                //alert( "complete" );
            });
    });


});