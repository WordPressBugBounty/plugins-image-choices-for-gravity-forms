!(function (e) {
    jQuery(document).ready(function ($) {

        var imagePickerAdminLite = {
            init: function () {
                imagePickerAdminLite.formEditorInit();
                imagePickerAdminLite.intiColorPicker();
                imagePickerAdminLite.openMediaLibray();
                imagePickerAdminLite.removeMediaImage();
                $(document).on("change", "#gfic_enable_imgchoice", function () {
                    imagePickerAdminLite.toggleSettingsOptions();
                });
            },
            formEditorInit: function () {
                if ( typeof fieldSettings === 'undefined' ) {
                    return;
                }

                fieldSettings.radio += ", .img_choice_field_setting";
			    fieldSettings.checkbox += ", .img_choice_field_setting";

                jQuery(document).bind("gform_load_field_settings", function(event, field, form) {

                    jQuery("#gfic_enable_imgchoice").prop('checked', Boolean(rgar(field, 'initImageGField')));
                    // jQuery("#pcafe_imgp_new_style").prop('checked', Boolean(rgar(field, 'pcafeNewStyle')));
                    jQuery("#gfimp_theme").val(field["gfimp_theme"]);
                    jQuery("#pcafe_imgp_column").val(field["pcafeImgpColumn"]);
                    jQuery("#gfimp_column_medium").val(field["gfimp_column_medium"]);
                    jQuery("#gfimp_column_small").val(field["gfimp_column_small"]);

                    var choiceParent = $("#field_choices");
                    imagePickerAdminLite.insertChoiceHtml(choiceParent);

                    console.log(field);

                    $("#gfic_enable_imgchoice").on("click", function () {
                        if ($(this).is(":checked")) {
                            choiceParent.removeClass('hide_media_library');
                        } else {
                            choiceParent.addClass('hide_media_library');
                        }
                    });

                    if( imagePickerAdminLite.isImagePickerEnabled(field) ) {
                        choiceParent.removeClass('hide_media_library');
                    } else {
                        choiceParent.addClass('hide_media_library');
                    }

                    imagePickerAdminLite.hideImagePickerClass(field);
                    imagePickerAdminLite.toggleSettingsOptions();
                });
                

                gform.addAction("gform_load_field_choices", function ( fields ) {
                    var legacy_theme    = ( field.pcafeNewStyle && field.gfimp_theme === undefined ) ? 'card' : field.gfimp_theme;
                    var theme           = ( field.gfimp_theme !== undefined ) ? legacy_theme : 'form_setting';
                    var large_column    = ( field.pcafeImgpColumn !== undefined ) ? field.pcafeImgpColumn : 'form_setting';
                    var medium_column   = ( field.gfimp_column_medium !== undefined ) ? field.gfimp_column_medium : 'form_setting';
                    var small_column    = ( field.gfimp_column_small !== undefined ) ? field.gfimp_column_small : 'form_setting';

                    imagePickerAdminLite.themeSetting( theme );
                    imagePickerAdminLite.largeColumnSetting( large_column );
                    imagePickerAdminLite.mediumColumnSetting( medium_column );
                    imagePickerAdminLite.smallColumnSetting( small_column );

                    imagePickerAdminLite.updateFieldPreview(field);
                });

                jQuery('.choices_setting').on('input propertychange', '.field-choice-image-id', function() {
					var $this = jQuery(this);
					var i = $this.closest('li.field-choice-row').data('index');

					field = GetSelectedField();
					field.choices[i].imageId = $this.val();
				});

                jQuery('.choices_setting').on('input propertychange', '.field-choice-image-url', function() {
                    var $this = jQuery(this);
                    var i = $this.closest('li.field-choice-row').data('index');

                    field = GetSelectedField();
                    field.choices[i].imageUrl = $this.val();
                });

                gform.addFilter('gform_append_field_choice_option', function(str, field, i) {
                    var inputType = GetInputType(field);
                    var imageId = field.choices[i].imageId ? field.choices[i].imageId : '';
                    var imageUrl = field.choices[i].imageUrl ? field.choices[i].imageUrl : '';

                    
                    if (field['type'] === "radio" || field['type'] === "checkbox") {
                        return imagePickerAdminLite.imagePickerButton(i, imageUrl, imageId, inputType)     
                    }
                    return str;
                });
            },
            hideImagePickerClass: function( field ) {
                var $field = $('#field_'+field.id);
                if( imagePickerAdminLite.isImagePickerEnabled(field)) {
                    $field.addClass('pcafe_imgp_admin');
                } else {
                    $field.removeClass('pcafe_imgp_admin');
                }
            },
            openMediaLibray: function() {
                $("#field_choices").on("click", ".pc_image_media_upload", function (event) {
                    event.preventDefault();
                    var self = $(this);
                    var attachment;

                    var mediaUploader = wp.media({
                        title: 'Select or Upload Media', // Title of the media modal
                        button: {
                            text: 'Use this image' // Text for the select button
                        },
                        multiple: false // Set to true if you want to allow multiple selections
                    });

                    mediaUploader.open();

                    mediaUploader.on("select", function (event) {
                        attachment = mediaUploader.state().get("selection").first().toJSON();

                        self.parent().siblings(".field-choice-image-url").val(attachment.url).trigger("propertychange");
                        self.parent().siblings(".field-choice-image-id").val(attachment.id).trigger("propertychange");
                        self.hide();
                        self.parent().find(".image_preview_box").show();
                        self.parent().find(".img_pick_preview").css("background-image", "url(" + attachment.url + ")");

                        imagePickerAdminLite.updateFieldPreview(field);
                    });
                });
            },
            updateFieldPreview: function( field ) {
    
                var $field = $('#field_'+field.id);

                imagePickerAdminLite.fieldPreviewHtml($field).each(function( i ) {
                    var $choice = $(this);

                    var $label = $choice.find('label');

                    var labelText = ($label.find('.pcafe_imgp_text').length) ? $label.find('.pcafe_imgp_text').html() : $label.html();

                    if (field.initImageGField) {
						if (field.choices.length > i) {
							var img = (field.choices[i].imageUrl !== undefined) ? field.choices[i].imageUrl : '';
                            $label.html(imagePickerAdminLite.fieldImagePreview(i, labelText, img));
						}
					}
					else {
						$label.html(labelText);
					}

                });
            },
            fieldPreviewHtml: function( field ) {
                if ( typeof field === 'undefined' || field instanceof jQuery === false) {
                    return [];
                }
                var choicesSelector = '.ginput_container .gfield_radio div[class*="gchoice"], .ginput_container .gfield_checkbox .gchoice:not(.gchoice_select_all)';// GF 2.5+

                return field.find(choicesSelector);
            },
            fieldImagePreview: function( index, labelText, image ) {
                var i = (index !== undefined) ? index : 0;
                var label = (labelText !== undefined) ? labelText : '';
                var img = (image !== undefined) ? image : '';
                return [
                    '<span class="pcafe_imgp_wrap">',
                    '<img src="'+ img +'" />',
                    '</span>',
                    '<span class="pcafe_imgp_text">'+label+'</span>'
                ].join('');
            },
            removeMediaImage: function() {
                $("#field_choices").on("click", ".remove_pick_img", function (event) {
                    event.preventDefault();
                    var self = $(this);

                    self.parent().parent().parent().find(".field-choice-image-url").val("").trigger("propertychange");
                    self.parent().parent().parent().find(".field-choice-image-id").val("").trigger("propertychange");

                    self.parent().parent().find(".image_preview_box").hide();
                    self.parent().parent().find(".pc_image_media_upload").show();

                    var field = GetSelectedField();
                    imagePickerAdminLite.updateFieldPreview(field);
                });
            },
            imagePickerButton: function(index, imageUrl, imageId, inputType) {
                var i = (index !== undefined) ? index : 0,
                    buttonLabel = '<i class="image_picker_icon"></i>',
                    hide_btn = imageId ? 'style="display:none;"' : '',
                    hide_preview = imageId ? '' : 'style="display:none;"';

                return [
                    "<input type='hidden' id='" + inputType + "_choice_image_id_" + i + "' value='" + imageId + "' class='field-choice-input field-choice-image-id' />",
                    "<input type='hidden' id='" + inputType + "_choice_image_url_" + i + "' value='" + imageUrl + "' class='field-choice-input field-choice-image-url' />",
                    "<div class='show_hide_trigger'>",
                    "<button type='button' class='pc_image_media_upload' "+ hide_btn + ">",
                    "<i class='dashicons dashicons-format-image'></i>",
                    "</button>",
                    "<span class='image_preview_box' "+ hide_preview + ">",
                    "<span class='img_pick_preview' style='background-image:url("+ imageUrl +")'></span>",
                    "<span class='remove_pick_img'>",
                    "<i class='dashicons dashicons-no'></i>",
                    "</span>",
                    "</span>",
                    "</div>"
                ].join("");
            },
            insertChoiceHtml: function( buttonHtml) {
                var field = GetSelectedField();
                if( imagePickerAdminLite.fieldCanUseImages(field) ) {
                    buttonHtml.addClass('hide_media_library');
                }
            },
            fieldCanUseImages: function( field ){
                if ( typeof field === 'undefined' || !field.hasOwnProperty('type') ) {
                    return false;
                }

                return ( field['type'] === "radio" || field['type'] === "checkbox" );
            },
            isImagePickerEnabled: function(field){
                field = field || GetSelectedField();

                return ( imagePickerAdminLite.fieldCanUseImages(field) && field.initImageGField === true);
            },
            themeSetting: function( value ) {
                SetFieldProperty( 'gfimp_theme', value );
            },
            largeColumnSetting: function( value ) {
                SetFieldProperty( 'pcafeImgpColumn', value );
            },
            mediumColumnSetting: function( value ) {
                SetFieldProperty( 'gfimp_column_medium', value );
            },
            smallColumnSetting: function( value ) {
                SetFieldProperty( 'gfimp_column_small', value );
            },
            intiColorPicker: function() {
                jQuery('#pcafe_imgp_color').wpColorPicker();
            },
            toggleSettingsOptions: function() {
                var isEnabled = $("#gfic_enable_imgchoice").is(":checked");

                if( isEnabled ) {
                    $(".gfimp_options").show();
                } else {
                    $(".gfimp_options").hide();
                }
            }
        }

        imagePickerAdminLite.init(); 
    });

})(jQuery);