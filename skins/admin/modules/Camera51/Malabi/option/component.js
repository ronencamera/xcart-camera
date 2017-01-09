/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * file uploader controller
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

define('fileuploader/background-remove-option', [], function () {


    BackgroundRemoveOption = Object.extend({
        base: null,
        currentTrackId: null,

        constructor: function BackgroundRemoveOption(base) {
            BackgroundRemoveOption.superclass.constructor.apply(this, arguments);
            this.base = jQuery(base);
            jQuery('.remove-background-upload', this.base).click(_.bind(this.removeClickHandler, this));
            jQuery('.remove-background-edit', this.base).click(_.bind(this.editImage, this));
            jQuery('.remove-background-save', this.base).click(_.bind(this.editImage, this));

        },

        isValidImageUrl: function (url, callback) {
            var image = new Image();
            image.onerror = function () {
                callback(false, image);
            };
            image.onload = function () {
                callback(true, image);
            };
            image.src = url;
        },
        editImage: function (ev) {

            var trackId = ev.target.parentElement.getAttribute("track-id");
            if (is_null(trackId)) {
                trackId = ev.target.getAttribute("track-id");
            }
            var self = this;
            if (!is_null(trackId)) {
                $("#malabiEditor").dialog("open");

                window.camera51.obj.callbackFuncSave = function (resultUrl) {
                    $('#malabiEditor').dialog('close');
                    self.uploadFile(resultUrl, trackId);

                };
                camera51.openEditorWithTrackId({
                    customerId: "223",
                    trackId: trackId.toString()
                });
                this.currentTrackId = trackId;
            }
        },
        saveImage: function (ev) {
            var trackId = ev.target.parentElement.getAttribute("track-id");
            if (is_null(trackId)) {
                trackId = ev.target.getAttribute("track-id");
            }
            if (!is_null(trackId)) {
                url = ev.target.getAttribute("url-id");
                console.log(url, trackId);
            }
        },

        removeClickHandler: function () {
            this.shadeBlock();

            jQuery('.dropdown').click();

            this.remove(_.bind(function (resultUrl, trackId, error) {
                if (resultUrl) {
                    var self = this;
                    var tryUpload = _.bind(this.tryGetFile, this);

                    tryUpload(resultUrl, function () {
                        core.trigger('message', {
                            type: 'info',
                            message: core.t('Background removal completed successfully, uploading the resulting image')
                        });
                        self.uploadFile(resultUrl, trackId);
                        self.unshadeBlock();
                    }, function () {
                        core.trigger('message', {
                            type: 'error',
                            message: core.t('Background removal has failed. Result image in inaccessible')
                        });
                        self.unshadeBlock();
                    });

                    this.base.addClass('background-removed');

                } else {
                    if(error){
                        core.trigger('message', {
                            type: 'error',
                            message: error
                        });
                    }
                    this.unshadeBlock();
                }

            }, this));


            return false;
        },

        tryGetFile: function (url, onSuccess, onError) {
            var counter = 1;
            var self = this;

            var tryUploadCallback = function (isValid, image) {
                if (isValid) {
                    onSuccess();
                } else if (counter < 10) {
                    _.delay(function () {
                        counter++;
                        self.isValidImageUrl(url, tryUploadCallback)
                    }, 1000);
                }

                if (counter >= 10) {
                    onError();
                }
            };

            self.isValidImageUrl(url, tryUploadCallback);
        },

        remove: function (callback) {
            var data = {
                malabiUrl: this.base.find('.link').attr('href')
            };

            core.post(
                {
                    target: 'image_processor',
                    action: 'remove_background'
                },
                null,
                data,
                {
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            callback(data.resultUrl, data.trackId);

                        } else {
                            callback(false, null, data.error);
                        }
                    },
                    error: function () {
                        callback(false);
                    }
                }
            );
        },

        shadeBlock: function () {
            this.base.addClass('shaded');
            this.base.append('<div class="spinner shaded-spinner"></div>');
        },

        unshadeBlock: function () {
            this.base.removeClass('shaded');
            this.base.find('.shaded-spinner').remove();
        },

        uploadFile: function (url, trackId) {
            var uploader = jQuery(this.base).data('controller');
            //console.log(uploader);
            var formData = new FormData();
            uploader.commonData.action = 'uploadFromURL';
            formData.append('copy', 1);
            formData.append('uploadedUrl', url.replace(/^\/\//, 'https://'));
            formData.append('withoutBackground', true);
            formData.append('trackId', trackId);
            uploader.request(formData, false);
        },
    });

    return BackgroundRemoveOption;
});

define('fileuploader/background-remove-controller', ['ready', 'fileuploader/background-remove-option'], function (readyMark, BackgroundRemoveOption) {


    var assignControllers = function () {
        jQuery('.file-uploader').each(function (index, el) {
            // TODO add option only for not processed images
            if (_.isUndefined(el.backgroundOptionInstance)) {
                el.backgroundOptionInstance = new BackgroundRemoveOption(el);
            }
        });
    };

    assignControllers();

    core.bind('loader.loaded', function (event, widget) {
        var widgetBase = jQuery(widget.base);
        if (_.isUndefined(widgetBase.data('controller'))) {
            widgetBase.data('controller', widget);
        }
        assignControllers();
    });

    var self = this;

    $.getScript("https://assets-malabi.s3.amazonaws.com/version/v2/assets/camera51.js", function () {
        $('head').append('<link rel="stylesheet" href="//fonts.googleapis.com/icon?family=Material+Icons" type="text/css" />');

        window.initCamera51({
            "userId": 211,
            "elementId": "camera51Iframe",
            "apiUrl": "//api.malabi.co",
            "showTutorial": true
        });

        $("#malabiEditor").dialog(
            {
                autoOpen: false,
                draggable: false,
                "option": "classes.ui-dialog",
                modal: true,
                resizable: false,
                width: 1000,
                title: false
            }
        ).siblings('.ui-dialog-titlebar').remove();

    });
});

$(document).ready(function () {
    $('[data-toggle="popover"]').popover();
});

function writeHTMLasJS() {
    document.write("<div id=\"malabiEditor\" class=\"  \"  style=\"padding: 0px;display:none\">");
    document.write("  <div class=\"modal-content\" style=\"padding-top: 20px;padding-bottom: 5px;background-color: #393b4b;\">");
    document.write("    <a style=\"position: absolute; right: 11px; top: 16px;\"");
    document.write("       class=\"btn\">");
    document.write("      <i style=\"font-size: 24px;color: white\" class=\"material-icons right\" onclick=\" $('#malabiEditor').dialog('close'); \" >close<\/i>");
    document.write("    <\/a>");
    document.write("    <div style=\"margin-left: 16px;font-family: Roboto, sans-serif; font-size: 24px; color: white;margin-bottom: 6px;\">Background Remover Edit");
    document.write("    <\/div>");
    document.write("    <div style=\"padding: 0px 25px;\">");
    document.write(" <a data-toggle=\"popover\" data-trigger=\"hover\" data-placement=\"bottom\" data-content=\"Draw lines to mark areas you want to keep in the image.\" data-html=\"true\" id=\"camera51-btn-mark-object\" class=\"btn roboto tooltip-main \"");
    document.write("       data-delay=\"50\" ");
    document.write("       style=\"color:#7cb342;font-size: 14px;padding-right: 10px; padding-left: 10px;\" onclick=\"camera51.setColor('colorFG');\">");
    document.write("      <i class=\"material-icons left\" style=\"color:#7cb342;padding-right: 0px;margin-right: 10px;font-size: 24px;\">add_circle<\/i>");
    document.write("      Mark Object");
    document.write("    <\/a>");
    document.write("    <a id=\"camera51-btn-mark-background\" class=\"btn roboto tooltipped\" data-toggle=\"popover\" data-trigger=\"hover\" data-placement=\"bottom\" data-content=\"Draw lines to mark areas you want to remove from the image.\" data-html=\"true\"");
    document.write("       data-position=\"bottom\" data-delay=\"50\" data-tooltip=\"Draw lines to mark areas you want to remove from the image\"");
    document.write("       style=\"margin-left:10px;color:#f44336;font-size: 14px;padding-right: 10px; padding-left: 10px;\" onclick=\"camera51.setColor('colorBG');\">");
    document.write("      <i class=\"material-icons left\" style=\"color:#f44336;padding-right: 0px;margin-right: 10px;font-size: 24px;\">remove_circle<\/i>");
    document.write("      Mark Background");
    document.write("    <\/a>");
    document.write("    <a id=\"camera51-btn-undo\" class=\"btn roboto\" style=\"color: white;font-size: 14px;\"");
    document.write("       onclick=\"camera51.undo()\">UNDO<\/a></div>");
    document.write("    ");
    document.write("    <a id=\"camera51-zoom-in\" class=\"btn transparent\"");
    document.write("       style=\"position: absolute;top: 141px;left: 36px\" onmousedown=\"camera51.onclickLongZoomIn()\"");
    document.write("       onmouseup=\"camera51.onmouseupLongZoomIn()\">");
    document.write("      <i class=\"material-icons right small zoomRippel\" style=\"color:#393b4b;\">zoom_in<\/i>");
    document.write("    <\/a>");
    document.write("    <a id=\"camera51-zoom-out\" class=\"btn transparent\"");
    document.write("       style=\"position: absolute;top: 194px;left: 36px\" onmousedown=\"camera51.onclickLongZoomOut()\"");
    document.write("       onmouseup=\"camera51.onmouseupLongZoomOut()\">");
    document.write("      <i class=\"material-icons right small zoomRippel\" style=\"color:#393b4b;\">zoom_out<\/i>");
    document.write("    <\/a>");
    document.write("  <\/div>");
    document.write("  <div class=\"progress\" style=\"margin: 0px;visibility: hidden;height: 3px\" id=\"camera51-loader\">");
    document.write("    <div class=\"indeterminate\"><\/div>");
    document.write("  <\/div>");
    document.write("  <div>");
    document.write("    <div id=\"camera51Iframe\" style=\"height:500px;width:100%\"><\/div>");
    document.write("  <\/div>");
    document.write("  <div class=\"modal-footer\" style=\"background-color: white \">");
    document.write("    <a id=\"camera51-btn-show-result\" class=\" btn \"");
    document.write("       style=\"font-size: 14px; margin-left: 10px;\"");
    document.write("       onclick=\"camera51.showResult()\">Preview Result<\/a>");
    document.write("    <a id=\"camera51-btn-save-image\" class=\" btn white-text malabiBG \"");
    document.write("       onclick=\"camera51.saveImage()\"");
    document.write("       style=\"font-size: 14px;margin-right: 9px; margin-left: 23px;background-color:#393b4b; color:white;\">Save Changes<\/a>");

    document.write("  <\/div>");
    document.write("<\/div>");
}


writeHTMLasJS();


