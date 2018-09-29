(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-specific JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
	 * the $ function reference to be used within the scope of this
	 * function.
	 *
	 * From here, you're able to define handlers for when the DOM is
	 * ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * Or when the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and so on.
	 *
	 * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
	 * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
	 * be doing this, we should try to minimize doing that in our own work.
	 */

	// Global Vars
	var datetime_index = 0; // Used to re-enable datetime when adding new item
	var removed_items = 0; // number of removed items
	var isScrolled = false; // Indication for first scroll (new trigger)
	var resetAllViewsCountText = 'Are you sure you want to reset the views count? \nThe data will no longer be available.';
	var analyticsLoadingContainerHTML = '<div class="analytics-loading-container"><div class="loading-spinner"></div></div>';
	var isAnalyticsRefreshLoading = false;
	var notRequiredFields = [ "time-date-pick-start-date",
							  "time-date-pick-end-date" ];

	var scheduleSettings = {        
	        // days: [2, 3, 4, 5, 6, 0, 1], 	
	        startTime: '0:00',
	        endTime: '24:00',
	        interval: 60
	      };

	// general helper functions

	/*
	 * returns true if needle is in haystack, false otherwise
	 */
	function isInArray(needle, haystack) {
  		return haystack.indexOf(needle) > -1;
	}


	// cookie funcs

	// Create cookie
	function createCookie(name, value, days) {
	    var expires;
	    if (days) {
	        var date = new Date();
	        date.setTime(date.getTime()+(days*24*60*60*1000));
	        expires = "; expires="+date.toGMTString();
	    }
	    else {
	        expires = "";
	    }
	    document.cookie = name+"="+value+expires+"; path=/";
	}

	// Read cookie
	function readCookie(name) {
	    var nameEQ = name + "=";
	    var ca = document.cookie.split(';');
	    for(var i=0;i < ca.length;i++) {
	        var c = ca[i];
	        while (c.charAt(0) === ' ') {
	            c = c.substring(1,c.length);
	        }
	        if (c.indexOf(nameEQ) === 0) {
	            return c.substring(nameEQ.length,c.length);
	        }
	    }
	    return null;
	}

	// Enable tooltip
	function activeTooltip(items) {	
		items = "." + items;

		$(document).tooltip({
			items: items,
			track: true,
			show: null, // show immediately
			open: function(event, ui)
			{
			    if (typeof(event.originalEvent) === 'undefined')
			    {
			        return false;
			    }

			    var $id = $(ui.tooltip).attr('id');

			    // close any lingering tooltips
			    $('div.ui-tooltip').not('#' + $id).remove();

			    // ajax function to pull in data and add it to the tooltip goes here
			},
			close: function(event, ui)
			{
			    ui.tooltip.hover(function()
			    {
			        $(this).stop(true).fadeTo(400, 1); 
			    },
			    function()
			    {
			        $(this).fadeOut('400', function()
			        {
			            $(this).remove();
			        });
			    });
			}
		});
	}

	function activateFreezeTooltip() {
		var freezeTooltipClass = ".ifso-freeze-overlay";
		var freezeTooltipStyleClass = "ifso_freeze_tooltip_styling";

		//setTooltipClass(freezeTooltipClass, freezetooltipStyleClass);
	

		$(freezeTooltipClass).tooltip({
			tooltipClass: freezeTooltipStyleClass,
			track: true,
			show: null, // show immediately
			open: function(event, ui)
			{
			    if (typeof(event.originalEvent) === 'undefined')
			    {
			        return false;
			    }

			    var $id = $(ui.tooltip).attr('id');

			    // close any lingering tooltips
			    $('div.ui-tooltip').not('#' + $id).remove();

			    // ajax function to pull in data and add it to the tooltip goes here
			},
			close: function(event, ui)
			{
			    ui.tooltip.hover(function()
			    {
			        $(this).stop(true).fadeTo(400, 1); 
			    },
			    function()
			    {
			        $(this).fadeOut('400', function()
			        {
			            $(this).remove();
			        });
			    });
			}
		});	

	}

	activeTooltip("ifso_tooltip");
	activateFreezeTooltip();
	 
	$(document).ready(function () {

		/* IfSo first-use instructions */
		if ( $("#ifso-modal-first-use").length ) {
			
			console.log("#ifso-modal-first-use exists");

			$(".ifso-first-use-images").modaal({
			    type: 'image'
			});

			$(".ifso-first-use-images").first().modaal('open');
		}

		/* Analytics */
		// Reset version view count
		$(document).on("click", ".reset_version_action", function() {

			if(!confirm(resetAllViewsCountText))
				return;

			// Visual
			var $views = $(this).closest('.version_statistics').find(".statistics");
			var currentStatisticsCount = parseInt($views.html());
			$views.html(0);

			// Programmatic
			var postId = $("#post_id").val();
			var versionIndex = $(this).data("version");

			var data = {
				"post_id": postId,
				"version_index": versionIndex
			};

			if (currentStatisticsCount > 0) {
				sendAjaxReq('reset_analytics_count', data);
			}
		});

		// Reset all versions views count
		$(document).on("click", ".reset-all-views-count", function() {

			if(!confirm(resetAllViewsCountText))
				return;

			// Visual
			$(".statistics_wrapper .version_statistics").each(function(index) {
				var $currentStatisticsCount = $(this).find(".statistics");
				$currentStatisticsCount.html(0);
			});

			// Programmatic
			var postId = $("#post_id").val();

			var data = {
				"post_id": postId
			};

			sendAjaxReq('reset_all_analytics_count', data);
		});

		function showAnalyticsLoadingBoxGUI($resetBtn) {
			if ( $(".analytics-loading-container").length ) {
				// the element already exists
				$(".analytics-loading-container").show();

			} else {
				// the element is not exist thus we create it
				// by pushing it to the DOM
				var $closestContainer = $resetBtn.closest('#ifso_statistics_metabox');
				$closestContainer.find(".inside").before(analyticsLoadingContainerHTML);
			}
		}

		function hideAnalyticsLoadingBoxGUI() {
			$(".analytics-loading-container").hide();
		}

		function updateVisualStatisticsCountForElem($elem, newCount) {
			var $statisticsCountContainer = $elem.find(".statistics");
			$statisticsCountContainer.text(newCount);
		}

		// Refresh the views
		$(document).on("mouseup", "#analytics-refresh-views", function() {
			if (isAnalyticsRefreshLoading) return;
			isAnalyticsRefreshLoading = true;

			var postId = $("#post_id").val();

			var data = {
				"post_id": postId
			};

			// Change visual appearance of the box
			showAnalyticsLoadingBoxGUI($(this));

			sendAjaxReq('refresh_analytics_count', data, function(response){
				// update the statistics table with the data
				var data = JSON.parse(response);

				// Update all versions's statistics count EXCEPT for default
				$(".statistics_wrapper .version_statistics").each(function(index) {
					updateVisualStatisticsCountForElem($(this), data[index]);
				});

				// Update default's statistics count
				var $defaultVersionStatistics = 
									$(".statistics_wrapper .version_statistics").last();

				updateVisualStatisticsCountForElem($defaultVersionStatistics,
												   data['default']);

				isAnalyticsRefreshLoading = false;
				hideAnalyticsLoadingBoxGUI();


				console.log($defaultVersionStatistics);
			});
		});
		
		 // Enable Time/Day Schedule
		 $(".date-time-schedule").dayScheduleSelector(scheduleSettings);

		// Enable DateTimePicker
		$('.ifsodatetimepicker').ifsodatetimepicker();
		
		$(".date-time-schedule").on('selected.artsy.dayScheduleSelector', function (e, selected) {
		  /* selected is an array of time slots selected this time. */
			//alert("HEY");
			//console.log(selected);
			// console.log($(this).data('artsy.dayScheduleSelector').serialize());
		});

		// create repeater
		$(document).on( 'click', '#reapeater-add', function() {
			var repeaterItemTemplate = $('#repeater-template').html();
			var index = $('.reapeater-item').length - 1 - removed_items;

			var versionInstructions = "";

			if (index == 0) {
				versionInstructions = "Select a condition, the content will be displayed only if it&apos;s met";
			} else if (index == 1) {
				versionInstructions = "Select a condition, the content will be displayed only if it&apos;s met and if version A is not realized";
			} else {
				versionInstructions = "Select a condition, the content will be displayed only if it&apos;s met and if versions A-"+String.fromCharCode(64+index)+" are not realized";
			}

			datetime_index += 1;
			
			repeaterItemTemplate = repeaterItemTemplate.replace('{version_number}', index+1);
			repeaterItemTemplate = repeaterItemTemplate.replace(/{datetime_number}/g, datetime_index);
			repeaterItemTemplate = repeaterItemTemplate.replace('{version_char}', String.fromCharCode(65+index));
			repeaterItemTemplate = repeaterItemTemplate.replace(/index_placeholder/g, (index));
			repeaterItemTemplate = repeaterItemTemplate.replace('{version_instructions}', versionInstructions);
			// repeaterItemTemplate = repeaterItemTemplate.replace('cloned-index', 'cloned'+(index));
			
			$('.reapeater-item').last().after(repeaterItemTemplate);
			var clonedElement = $('.reapeater-item-cloned').last();
			clonedElement.find('textarea').addClass('textarea'+index);

			// city
			var $clonedCityAutocompleteInput = clonedElement.find('input.autocomplete');
			// country
			var $clonedCountryAutocompleteInput = clonedElement.find('input.countries-autocomplete');
			// continent
			var $clonedContinentAutocompleteInput = clonedElement.find('input.continents-autocomplete');
			// state
			var $clonedStateAutocompleteInput = clonedElement.find('input.states-autocomplete');

			// country
			var $newCountryAutocompleteInput = $('<input>').attr({
				type: 'text',
				class: 'countries-autocomplete ifso-input-autocomplete',
				placeholder: 'Select a country',
				'data-symbol': 'COUNTRY'
			});

			// continent
			var $newContinentAutocompleteInput = $('<input>').attr({
				type: 'text',
				class: 'continents-autocomplete ifso-input-autocomplete',
				placeholder: 'Select continent',
				'data-symbol': 'CONTINENT'
			});

			// state
			var $newStateAutocompleteInput = $('<input>').attr({
				type: 'text',
				class: 'states-autocomplete ifso-input-autocomplete',
				placeholder: 'Select state',
				'data-symbol': 'STATE'
			});

			// country
			$clonedCountryAutocompleteInput.after($newCountryAutocompleteInput);
			$clonedCountryAutocompleteInput.remove();

			// continent
			$clonedContinentAutocompleteInput.after($newContinentAutocompleteInput);
			$clonedContinentAutocompleteInput.remove();

			// state
			$clonedStateAutocompleteInput.after($newStateAutocompleteInput);
			$clonedStateAutocompleteInput.remove();

			initCityAutocomplete($clonedCityAutocompleteInput[0]);
			initEasyAutocompletes();

			var data = {
				'action': 'load_tinymce_repeater',
				'nonce': nonce,
				'editor_id': (index)
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				clonedElement.find('.repeater-editor-wrap').append(response);

				var editors = ['repeatable_editor_content'+(index)];
				tinyMCE_bulk_init(editors);
				clonedElement.slideDown(function(){

					var selectedEditor = ( getUserSetting( 'editor' ) == 'html' ) ? 'html':
																					'tmce';
					$(".wp-editor-tabs").each(function(elem) {
						$(this).find('.switch-' + selectedEditor).trigger('click');
					});

					// var tmceButtonsWrapperElement = clonedElement.find('.wp-editor-tabs');
					
					// Click on the 'Text' button
					// tmceButtonsWrapperElement.find('.switch-html').trigger('click');
					// Click on the 'Visual' button
					// tmceButtonsWrapperElement.find('.switch-tmce').trigger('click');
				});
				// $('.post-type-ifso_triggers #post').validator('update');

				 $(".date-time-schedule").dayScheduleSelector(scheduleSettings);

				$(".date-time-schedule").on('selected.artsy.dayScheduleSelector', function (e, selected) {
				  /* selected is an array of time slots selected this time. */
					//console.log(selected);
					//console.log
				});

				// Re-Enable DateTimePicker
				$('.datetimepickercustom-' + datetime_index).ifsodatetimepicker();

				if (!isScrolled) {
					isScrolled = true;
					setTimeout(function() {
						scrollToElement(clonedElement);
					}, 300);
				} else {
					scrollToElement(clonedElement);
				}


				// Fix Visual/Text issue
				// by force switch to Text and then Visual

				// CSS Classes: switch-html - Text button
				//				switch-tmce - Visual button
				

				// var tmceButtonsWrapperElement = clonedElement.find('.wp-editor-tabs');
				
				// Click on the 'Text' button
				// tmceButtonsWrapperElement.find('.switch-html').trigger('click');
				// Click on the 'Visual' button
				// tmceButtonsWrapperElement.find('.switch-tmce').trigger('click');

			});
		});

		$(document).on('click', '.admin-trigger-wrap .switch-tmce, .rule-item .switch-tmce', function(){
			$(this).closest('.wp-editor-tabs').find('.switch-html').trigger('click');
		});

		// handle repeater item delete
		$(document).on( 'click', '.repeater-delete', function() {
			// Check if trying to remove testing-mode item
			var $repeaterParent = $(this).closest(".reapeater-item");

			if($repeaterParent.find(".circle-active").length)
				alert("A testing mode version cannot be deleted.");
			else if(confirm('Are you sure you want to delete this version?')) {
				removed_items++;
				var itemWrap = $(this).closest('.reapeater-item');
				itemWrap.slideUp( "slow", function() {
					// itemWrap.html("");
					// itemWrap.removeClass("reapeater-item");
					// itemWrap.removeClass("reapeater-item-cloned");
					itemWrap.find(".rule-toolbar-wrap").removeClass("rule-toolbar-wrap");
					itemWrap.find('select').remove();
					itemWrap.find('input').remove();
					itemWrap.find('.wp-editor-area').remove();
					// itemWrap.removeClass("reapeater-item-cloned");
					// itemWrap.remove();


					// Place in its own function - updates version's instructions + number
					$('.rule-toolbar-wrap').each(function(index){
						var newIndex = index - 1;
						var versionNumber = newIndex+1;
						var templateTitle = "Personalized Content – "+jsTranslations['Version']+" {version_char}";
						var versionInstructions;
						var switchWrap = $(this).closest('.rule-wrap');

						if (newIndex == 0) {
							versionInstructions = "Select a condition, the content will be displayed only if it's met:";
						} else if (newIndex == 1) {
							versionInstructions = "Appears only if option A is not realized:";
						} else {
							versionInstructions = "Appears only if option A-"+String.fromCharCode(65+newIndex)+" are not realized:";
						}

						if ($(this).find('.version-alpha').text() != templateTitle) {
							switchWrap.find('.versioninstructions').text(versionInstructions);
							$(this).find('.version-count').text(versionNumber);
							$(this).find('.version-alpha').text("Personalized Content – "+jsTranslations['Version']+' '+String.fromCharCode(65+newIndex));
						}
					});	
				});
			}
		});
		
		// toggle PHP code
		$(document).on( 'click', '.php-shortcode-toggle-link', function() {
			$('.php-shortcode-toggle-wrap').slideToggle( "slow", function() {
				
			});
		});
		
		$('.post-type-ifso_triggers #post').on('submit', function (e) {
			// Updating all the schedule data with their correspond hidden input
			$(".date-time-schedule").each(function() {
				var $elem = $(this);
				var $parent = $elem.parent();
				var scheudleInput = $parent.find(".schedule-input");

				scheudleInput.val(JSON.stringify($elem.data('artsy.dayScheduleSelector').serialize()));
			});


			if (e.isDefaultPrevented()) {
				// handle the invalid form...
			} else {
				// was removed in order to allow saving empty content
				/*var isValid = true;
				// everything looks good!
				$(".repeater .reapeater-item").each(function(){
					// check each trigger, if value exists check its equivalent wysiwyg
					var triggerTypeValue = $(this).find('.trigger-type').val();
					var triggerContent = $.trim($(this).find('iframe').contents().find('body').text());
					
					if(triggerContent == '' && triggerTypeValue != '') {
						$(this).find('.wp-editor-wrap').addClass('wysiwyg-not-valid');
						isValid = false;
					}
					else if(triggerContent != '' && triggerTypeValue == '') {
						//$(this).find('.wp-editor-wrap').addClass('wysiwyg-not-valid');
						isValid = false;
						var triggerType = $(this).find('.trigger-type');
						var triggerTypeWrap = triggerType.closest('.form-group');
						triggerTypeWrap.addClass('has-danger').addClass('has-error');
						triggerType.on( 'change', function() {
							triggerTypeWrap.removeClass('has-danger').removeClass('has-error');
						});
					}
				});
				if(!isValid) e.preventDefault();*/
			}
		})

		function platform_symbols($elem) {
			var selectedOptionLabel = $elem.find(':selected')[0].label;
			var switchWrap = $elem.closest('.rule-wrap');
			var platSymbol = switchWrap.find(".platform-symbol");

			if (selectedOptionLabel == "Facebook Ads") {
				platSymbol.html("");
			} else if (selectedOptionLabel == "Google Adwords"){
				platSymbol.html("{lpurl}?");
			}
		}
		
		$(document).on( 'change', '.advertising-platforms-option', function() {
			platform_symbols($(this));
		});



		function rawRecurrenceToVisual(recurrenceType) {
			var lowerRecurrenceType = recurrenceType.toLowerCase();

			if (lowerRecurrenceType.indexOf("none") != -1) {
				return "None";
			} else if (lowerRecurrenceType.indexOf("session") != -1) {
				return "Session";
			} else if (lowerRecurrenceType.indexOf("always") != -1) {
				return "Always";
			} else if (lowerRecurrenceType.indexOf("custom") != -1) {
				return "Custom";
			} else {
				return "unkown";
			}
		}

		$(document).on('change', '.rule-wrap input[type="radio"]', function() {
			var $recurrenceCustomSelectionContainer = $(this).closest('.recurrence-selection').find('.recurrence-custom-selection-container');
			var recurrenceType = null;

			if ($(this).hasClass("recurrence-custom-radio")) {
				// clicked on 'custom' selection
				$recurrenceCustomSelectionContainer.show();
				recurrenceType = "Custom";
			} else {
				$recurrenceCustomSelectionContainer.hide();
				recurrenceType = $(this).closest('.recurrence-option').find('.recurrence-option-title').text();
			}

			$(".current-recurrence-type").text(rawRecurrenceToVisual(recurrenceType));
		});

		$(document).on( 'change', '.rule-wrap select', function() {
			var selectedOption = $(this).find(':selected');
			var switchWrap = $(this).closest('.rule-wrap');
			var ruleToolbarWrap = switchWrap.find('.rule-toolbar-wrap');
			var nextFieldAttr = selectedOption.data('next-field');
			var resetFieldsDataAttr = selectedOption.data('reset');
			var closestLeftPanel = $(this).closest('.col-md-3');
			var textarea = switchWrap.find("textarea");

			// reset fields
			if (typeof resetFieldsDataAttr !== 'undefined') {
				var resetFields = resetFieldsDataAttr.split('|');
				$.each( resetFields, function( key, resetAttrValue ) {
					switchWrap.find("[data-field*='" + resetAttrValue + "']").hide();
					switchWrap.find("[data-field*='" + resetAttrValue + "']").val("").prop('selectedIndex', 0);
					switchWrap.find("[data-field*='" + resetAttrValue + "']").prop('required', false);

					// Treat special data-fields
					if (resetAttrValue == "advertising-platforms-selection") {
						// switchWrap.find("[data-field*='" + resetAttrValue + "']").trigger('change');
						var elem = switchWrap.find("[data-field*='" + resetAttrValue + "']");
						platform_symbols(elem);
					}
				});
			}
			
			// if (resetFieldsDataAttr.indexOf("locked-box") != -1) {
				// ruleToolbarWrap.removeClass("rule-toolbar-wrap-clear");
			// }

			if (typeof nextFieldAttr === 'undefined') return;

			var nextFields = nextFieldAttr.split('|');
			$.each( nextFields, function( key, nextAttrValue ) {
				console.log(nextAttrValue + ":");
				console.log(switchWrap.find("[data-field='" + nextAttrValue + "']"));
				switchWrap.find("[data-field='" + nextAttrValue + "']").show();

				var isRequired = !isInArray(nextAttrValue, notRequiredFields);
				switchWrap.find("[data-field='" + nextAttrValue + "']").prop('required', isRequired);
			});

			// if (nextFields.indexOf("locked-box") == -1) {
				// ruleToolbarWrap.removeClass("rule-toolbar-wrap-clear");
			// } else {
				// Locked trigger
				// ruleToolbarWrap.addClass("rule-toolbar-wrap-clear");
			// }

			var newTextAreaHeight = closestLeftPanel.height() - 60;
			if (newTextAreaHeight < 250) newTextAreaHeight = 250;
			// alert(newTextAreaHeight);
			textarea.css("height", newTextAreaHeight);

		});

		$(document).on('change', '.ifso-autocomplete-opener', function() {
			var $this = $(this);
			var effectRate = 250;

			// Handle already shown element
			var $currentShownElem = $('.ifso-geo-selected');
			$currentShownElem.stop(true).slideUp(effectRate);
			$currentShownElem.removeClass('ifso-geo-selected');

			// Handle new element
			var classNameOfElemToShow = $this.data("open");
			var $elemToShow = $("." + classNameOfElemToShow);
			$elemToShow.addClass('ifso-geo-selected');
			$elemToShow.stop(true).slideDown(effectRate);
		});
		
		// update query string text in the instruction box
		$(document).on( 'keyup', "input[data-field='url-custom']", function() {
			var inputValue = $(this).val();
			
			var isValid = true;
			$("input[data-field='url-custom']").not(this).each(function( index ) {
				if($(this).val() != '') {
					if(inputValue == $(this).val()) {
						// handle duplicated query string trigger
						isValid = false;
					}
				}
			});
			
			if(!isValid) {
				// handle invalid query string
				$(this).closest('.form-group').addClass('has-danger').addClass('has-error');
				$(this).after('<div class="help-block">'+jsTranslations['translatable_dupplicated_query_string_notification_trigger']+'</div>');
				
				$('#publishing-action').append('<div class="query-string-err-notification">'+jsTranslations['translatable_dupplicated_query_string_notification_publish']+'!</div>');
			}
			else {
				// query string is valid
				$(this).closest('.form-group').removeClass('has-danger').removeClass('has-error');
				$(this).closest('.form-group').find('.help-block').remove();
				$('#publishing-action .query-string-err-notification').remove();
			}
			
			var queryStringTyped = ($(this).val() == '') ? 'your-query-string' : $(this).val();
			$(this).closest('.rule-wrap').find('.instructions b').text(queryStringTyped);
		});

		// update query string text in the instruction box
		$(document).on( 'keyup', "input[data-field='advertising-platforms-selection']", function() {
			var inputValue = $(this).val();
			
			var isValid = true;
			$("input[data-field='advertising-platforms-selection']").not(this).each(function( index ) {
				if($(this).val() != '') {
					if(inputValue == $(this).val()) {
						// handle duplicated query string trigger
						isValid = false;
					}
				}
			});
			
			if(!isValid) {
				// handle invalid query string
				$(this).closest('.form-group').addClass('has-danger').addClass('has-error');
				$(this).after('<div class="help-block">'+jsTranslations['translatable_dupplicated_query_string_notification_trigger']+'</div>');
				
				$('#publishing-action').append('<div class="query-string-err-notification">'+jsTranslations['translatable_dupplicated_query_string_notification_publish']+'!</div>');
			}
			else {
				// query string is valid
				$(this).closest('.form-group').removeClass('has-danger').removeClass('has-error');
				$(this).closest('.form-group').find('.help-block').remove();
				$('#publishing-action .query-string-err-notification').remove();
			}
			
			var queryStringTyped = ($(this).val() == '') ? 'the-name-you-choose' : $(this).val();
			$(this).closest('.rule-wrap').find('.instructions b').text(queryStringTyped);
		});
		
		// set custom Add New link active
		if(window.location.href.indexOf("post-new.php?post_type=ifso_triggers") > -1) {
			$('a[href="'+window.location.href+'"]').closest('li').addClass('current');
		}
		
	});



	// define the skeleton of the overlay
	var overlayDivHTML = '<div class="ifso-tm-overlay"><span class="text">Testing Mode</span></div>';
	var overlayFreezeHTML = '<div class="ifso-freeze-overlay ifso_tooltip"><span class="text">Version is inactive</span></div>';
	var selectedTestingMode = false;

	function disableTestingMode($elem, $repeaterParent, isDefaultRepeater) {
		// before appending, removing all the 'ifso-tm-overlay' present
		// due to prior appending
		$(".ifso-tm-overlay").remove();
		$("#tm-input").attr("value", "");
	}

	function activateTestingMode($elem, $repeaterParent, isDefaultRepeater) {
		var versionIndex = 0;
		var i = 0;

		// append 'overlayDiv' to any version
		$(".reapeater-item").each(function() {
			// iterate over each 'rule-item' class
			// and append 'overlayDiv' at the end
			// * Skipping the current .rule-item
			// * to not overlay the selected Forcing Mode item

			var $elem = $(this);
			i++;

			if (!$elem.is($repeaterParent)) // if not the selected repeater
				$elem.append(overlayDivHTML);
			else
				versionIndex = i;
		});

		// append 'overlayDiv' to the default content
		// if not selected the default content
		if (!isDefaultRepeater)
			$(".default-repeater-item").append(overlayDivHTML);
		else
			versionIndex = 0; // indicating default content

		$("#tm-input").attr("value", versionIndex);
	}

	$(document).on("click", ".ifso-tm", function(e) {		
		var $elem = $(this);
		var $repeaterParent = null;
		var isDefaultRepeater = false;

		// check if active button already exist
		if ($(".circle-active").length)
			selectedTestingMode = true;

		// Check if it's the default repeater
		var defaultRepreaterParent = $(this).closest(".default-repeater-item");

		if (defaultRepreaterParent.length > 0) {
			isDefaultRepeater = true;
			$repeaterParent = defaultRepreaterParent[0];
		}
		else
			$repeaterParent = $(this).closest(".reapeater-item")[0];

		if (selectedTestingMode) {
			selectedTestingMode = false;
			$(".ifso-tm").removeClass("circle-active");
			disableTestingMode($elem, $repeaterParent, isDefaultRepeater);
		} else {
			selectedTestingMode = true;
			$(this).addClass("circle-active");
			activateTestingMode($elem, $repeaterParent, isDefaultRepeater);
		}
	});






	$(document).on("click", ".ifso-freezemode", function(e) {		
		var $elem = $(this);
		var $inptDom = $elem.parent().find(".freeze-mode-val");
		var isActive = ($inptDom.val() == "true") ? true : false;
		var $parent = $elem.parent();
		var $ancParent = $elem.closest('.reapeater-item');

		// Check if trying to freeze testing-mode item
		if($ancParent.find(".circle-active").length) {
			alert("A testing mode version cannot be deactivated.");
			return;
		}


		// Switch false <-> true
		if (isActive) $inptDom.val("false");
		else $inptDom.val("true");

		if (isActive) {
			// Handle deactive
			$ancParent.find(".ifso-freeze-overlay").remove();
			$parent.removeClass("freeze-overlay-active-container");
			$elem.find(".text").html('<i class="fa fa-pause" aria-hidden="true">');
		} else {
			// Handle  active
			$ancParent.append(overlayFreezeHTML);
			$parent.addClass("freeze-overlay-active-container");
			$elem.find(".text").html('<i class="fa fa-play" aria-hidden="true">');
			activeTooltip("ifso_tooltip");
			activateFreezeTooltip();
		}
	});

	$(document).on("click", ".recurrence-expander", function() {
		var $this = $(this);
		var $recSelectionContainer = $this.closest('.recurrence-container').find(".recurrence-selection");
		$recSelectionContainer.stop(true).toggle();
		
		if ($this.text().trim() == "+") {
			$this.text("-");
		} else {
			$this.text("+");
		}

		$this.toggleClass("recurrence-expander-show");
	});

	/* Set Time Info Functionality */
	$(document).on("click", ".settimeinstructions .closeX", function() {
		// write cookie to the client in order to prevent re-displaying of the info box
		createCookie("set_time_instructions", true, 712); // 712 days - 2 years

		// remove the box from the view
		$(this).closest(".set-time-info-container").remove();
	});


	/* Utils Funcs */

	function sendAjaxReq(action, data, cb) {
		data['action'] = action;
		data['nonce'] = nonce;

		console.log("Data", data);

		jQuery.post(ajaxurl, data, function(response) {
			if (cb)
				cb(response);
		});
	}

	function scrollToElement($elem) {
	    $('html, body').animate({
	        scrollTop: $elem.offset().top - 50
	    }, 1000);
	}

	/* Settings Tabs JS Related */
	$(document).on("click", ".ifso-settings-tabs-header .ifso-tab", function() {
		if ( $(this).hasClass("selected-tab") )
			return;

		var contentToShow = "." + $(this).data('tab');
		var $selectedTab = $(".selected-tab");
		var contentToHide = "." + $selectedTab.data("tab");

		// switch classes
		$selectedTab.removeClass("selected-tab");
		$(this).addClass("selected-tab");

		// switch contents
		$(contentToHide).stop(true).fadeOut('fast', function() {
			$(contentToShow).stop(true).fadeIn();
		});
	});

})( jQuery );

function tinyMCE_bulk_init( editor_ids ) {
    var init, ed, qt, first_init, DOM, el, i, qInit;

    if ( typeof(tinymce) == 'object' ) {

        var editor;
        for ( e in tinyMCEPreInit.mceInit ) {
            editor = e;
            break;
        }
        for ( i in editor_ids ) {
            var ed_id = editor_ids[i];
            tinyMCEPreInit.mceInit[ed_id] = tinyMCEPreInit.mceInit[editor];
            tinyMCEPreInit.mceInit[ed_id]['elements'] = ed_id;
            tinyMCEPreInit.mceInit[ed_id]['body_class'] = ed_id;
            tinyMCEPreInit.mceInit[ed_id]['succesful'] =  false;
			tinyMCEPreInit.mceInit[ed_id]['height'] =  '220';
			
			// init qTags
			function getTemplateWidgetId( id ){
				var form = jQuery( 'textarea[id="' + id + '"]' ).closest( 'form' );
				var id_base = form.find( 'input[name="id_base"]' ).val();
				var widget_id = form.find( 'input[name="widget-id"]' ).val();
				return id.replace( widget_id, id_base + '-__i__' );
			}
			
			var qInit;
			if( typeof tinyMCEPreInit.qtInit[ ed_id ] == 'undefined' ){
				qInit = tinyMCEPreInit.qtInit[ ed_id ] = jQuery.extend( {}, tinyMCEPreInit.qtInit[ getTemplateWidgetId( ed_id ) ] );
				qInit['id'] = ed_id;
			}else{
				qInit = tinyMCEPreInit.qtInit[ ed_id ];
			}
			
			if ( typeof(QTags) == 'function' ) {
				jQuery( '[id="wp-' + ed_id + '-wrap"]' ).unbind( 'onmousedown' );
				jQuery( '[id="wp-' + ed_id + '-wrap"]' ).bind( 'onmousedown', function(){
					wpActiveEditor = ed_id;
				});
				QTags( tinyMCEPreInit.qtInit[ ed_id ] );
				QTags._buttonsInit();
				// alert(getUserSetting( 'editor' ));
				// switchEditors.go( $( 'textarea[id="' + editor_id + '"]' ).closest( '.widget-mce' ).find( '.wp-switch-editor.switch-' + ( getUserSetting( 'editor' ) == 'html' ? 'html' : 'tmce' ) )[0] );
			}
			// END - init qTags
        }

        for ( ed in tinyMCEPreInit.mceInit ) {
            // check if there is an adjacent span with the class mceEditor
            if ( ! jQuery('#'+ed).next().hasClass('mceEditor') ) {
                init = tinyMCEPreInit.mceInit[ed];
				// jQuery( document ).triggerHandler( 'quicktags-init', [ ed ] );
                try {
                    tinymce.init(init);
                    tinymce.execCommand( 'mceAddEditor', true, ed_id );
                } catch(e){
                    console.log('failed');
                    console.log( e );
                }
            }
        }
    }
}