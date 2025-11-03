/**
 * @package    JGive
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

/**
 * Front end JavaScript
 */
var jgive = {
	toggleDiv: function(spanId) {
		if (localStorage.getItem("flag") == 1) {
			localStorage.setItem("flag", 0);
		} else {
			localStorage.setItem("flag", 1);
		}

		if ( jQuery(window).width() < 767 ) {
			jQuery("#"+spanId).toggle();
		}

		jQuery(".campaign__filter").toggleClass( "af-d-none active" );

		if (spanId == 'displayFilterText') {
			if (jQuery('.tjBs5').length)
			{
				if (jQuery(".campaign__filter").hasClass('active'))
				{
					jQuery(".fa-filter").parent().removeClass( "text-black" );
				} else {
					jQuery(".fa-filter").parent().addClass( "text-black" );
				}
			} else {
				jQuery(".fa-filter").toggleClass( "btn btn-primary btn-sm p-10" );
			}
		}
	},

	toggleSearch: function(spanId) {
		if(localStorage.getItem("filter") == 0) {
			localStorage.setItem("filter", 1);
		} else {
			localStorage.setItem("filter", 0);
		}

		if (spanId == 'SearchFilterInputBox') {
			jQuery(".search__campaign").toggleClass( "active af-d-none" );
			jQuery(".fa-search").toggleClass( "btn btn-primary btn-sm p-10" );
		}
	},

	searchCampaigns: function (event) {
		if (event.keyCode == '13') {
			jgiveCommon.filters.submitFilters('adminForm4');
			return false;
		}
	},

	jgShowSortFilter: function () {
		if(localStorage.getItem("dropdown") == 1) {
			localStorage.setItem("dropdown", 0);
		} else {
			localStorage.setItem("dropdown", 1);
		}
		jQuery("#filter_order").toggle();
	},

	campaignDetails: {
		init: function() {
			techjoomla.jQuery(document).ready(function() {
				jgive.campaignDetails.onChangeGetDonationData();
				jgive.campaignDetails.onChangefun();
				jgive.campaignDetails.campaignImgPopup();
				jgive.campaignDetails.loadActivity();
			});
		},

		onChangeGetDonationData: function(){
			techjoomla.jQuery("#campaigns_graph_period").change(function() {
				var graph_filter_val = document.getElementById("campaigns_graph_period").value;
				var cid = document.getElementById('camp_id').value;
				var ajaxcall = techjoomla.jQuery.ajax({
					type:'GET',
					url:'?option=com_jgive&view=campaigns&task=campaign.getCampaignGraphData&format=json&cid='+cid+'&filtervalue='+graph_filter_val,
					dataType:'json',
					sucess:function(data) {
					},
					error:function(data) {
						console.log('error');
					}
				});

				ajaxcall.done(function (data) {
					var graphid = document.getElementById("mycampaign_graph").getContext('2d');
					var total_donationchart = new Chart(graphid, {
					  type: 'line',
					  data: {
						labels: data.data.donationDate,
						datasets: [{
						  label: data.data.totalDonation ? data.data.totalDonation : 0,
						  data: data.data.donationAmount ? data.data.donationAmount : 0,
						  backgroundColor: "rgba(203, 235, 230, 0.5)",
						  borderColor:"rgba(55, 179, 142, 1)",
						  lineTension:'0',
						  borderWidth:'2',
						  pointRadius:1,
						  pointBackgroundColor: "rgba(55, 179, 142, 1)",
						  pointBorderColor: "rgba(55, 179, 142, 1)",
						  pointHoverBackgroundColor: "rgba(55, 179, 142, 1)",
						  pointHoverBorderColor: "rgba(55, 179, 142, 1)"
						},{
						  label: data.data.avgDonation ? data.data.avgDonation : 0,
						  data: data.data.donationAvg ? data.data.donationAvg : 0,
						  backgroundColor: "rgba(216, 225, 180, 1)",
						  borderColor:"rgba(251, 214, 20, 0.90)",
						  lineTension:'0',
						  borderWidth:'2',
						  pointRadius:1,
						  pointBackgroundColor: "rgba(251, 214, 20, 0.90)",
						  pointBorderColor: "rgba(251, 214, 20, 0.90)",
						  pointHoverBackgroundColor: "rgba(251, 214, 20, 0.90)",
						  pointHoverBorderColor: "rgba(251, 214, 20, 0.90)"
						}]
					  }
					});
				});
			}).change();
		},

		campaignImgPopup: function(){
			techjoomla.jQuery('.popup-gallery').magnificPopup({
				delegate: 'a',
				type: 'image',
				tLoading: 'Loading image #%curr%...',
				mainClass: 'mfp-img-mobile',
				gallery: {
					enabled: true,
					navigateByImgClick: true,
					preload: [0,1]
				},
				image: {
					tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
					titleSrc: function(item) {
						return item.el.attr('title') + '<small></small>';
					}
				}
			});
		},

		onChangefun: function(){
			jQuery("#gallary_filter").change(function() {
				var filterVal = document.getElementById("gallary_filter").value;
				if(filterVal=="1") {
					jQuery("#videos").show();
					jQuery("#images").hide();
					jQuery(".videosText").show();
					jQuery(".imagesText").hide();
				} else if(filterVal=="2") {
					jQuery("#videos").hide();
					jQuery("#images").show();
					jQuery(".imagesText").show();
					jQuery(".videosText").hide();
				} else if(filterVal=="0") {
					jQuery("#videos").show();
					jQuery("#images").show();
					jQuery(".videosText").show();
					jQuery(".imagesText").show();
				}
			}).change();
		},

		loadActivity: function(){
			techjoomla.jQuery(window).load(function() {
				if (techjoomla.jQuery('#camp_activity .feed-item-cover').length == '1') {
					techjoomla.jQuery('.todays-activity .feed-item').css('border-left', '0px');
				}

				techjoomla.jQuery('#postactivity').attr('disabled',true);
				techjoomla.jQuery('#activity-post-text').on('input', function(){
					if (techjoomla.jQuery('#activity-post-text').val() == '') {
						techjoomla.jQuery('#postactivity').attr('disabled',true);
					} else {
						techjoomla.jQuery('#postactivity').attr('disabled',false);
					}
					var textMax = techjoomla.jQuery('#activity-post-text').attr('maxlength');
					var textLength = techjoomla.jQuery('#activity-post-text').val().length;
					var text_remaining = textMax - textLength;
					techjoomla.jQuery('#activity-post-text-length').html(text_remaining + ' ' + Joomla.JText._('COM_JGIVE_POST_TEXT_ACTIVITY_REMAINING_TEXT_LIMIT'));
				});
			});
		},
		playVideo: function(link){
			var width = jQuery(window).width();
			var height = jQuery(window).height();
			var wwidth = width-(width*0.10);
			var hheight = height-(height*0.10);

			if (typeof SqueezeBox === 'undefined')
			{
				var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
				var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
				var screenWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
				var screenHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

				var left = ((screenWidth / 2) - (wwidth / 2)) + dualScreenLeft;
				var top = ((screenHeight / 2) - (hheight / 2)) + dualScreenTop;

				var newWindowSize = 'width='+wwidth+','+'height='+hheight+','+'top='+top+','+'left='+left;
				window.open(link, '', newWindowSize);
			}
			else
			{
				SqueezeBox.open(link, {handler: 'iframe', size: {x: wwidth, y: hheight},classWindow: 'tjlms-modal'});
			}
		}
	},

	campaign: {
		/* Initialize campaign js*/
		init: function() {
			jQuery('#jform_goal_amount').change(function(){
				jgiveCommon.validateAmountAsPerCurrency(this);
			});

			jQuery(document).ready(function() {
				if (jQuery(window).width() < 767) {
					jQuery("#launchForm__nav").removeClass("d-flex");
					jQuery("#launchForm__nav").addClass("panel-group");
					jQuery("#launchForm__nav a").attr('data-toggle', 'collapse');
				}

				jQuery(document).ready(function () {
					jgiveCommon.lifetimeCampaign();
				});

				jQuery.each(mediaGallery, function(key, media) {
					tjMediaFile.previewFile(media, 1, '');
				});

				jQuery('#jform_amount_suggestions_tags').on('beforeItemAdd', function(event) {
					const [cancel, message] = jgiveCommon.campaignForm.validateAmountSuggestion(event.item);
					if(cancel){
						alert(message);
						event.cancel = cancel;
					}
				});
			});
		},

		searchCampDonors: function (){
			var searchInputfield = document.getElementById("SearchDonorsinputbox");

			if (searchInputfield.style.display == "inline") {
				searchInputfield.style.display = "none";
			} else {
				searchInputfield.style.display = "inline";
			}
			jQuery(".fa-search").toggleClass( "btn btn-primary btn-sm p-10" );
		},

		searchDonor: function (){
			var input, filter, table, tr, td, i;
			input = document.getElementById("donorInput");
			var noDataFoundDiv = document.getElementById("noDataFoundDiv");
			filter = input.value.toUpperCase();
			table = document.getElementById("singlecampaignDonor");
			tr = table.getElementsByTagName("tr");
			let dataAvailable = 0;

			for (i = 0; i < tr.length; i++) {
				td = tr[i].getElementsByTagName("td")[0];
				if (td) {
					if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
						jQuery("#btn_showMorePic").show();
						tr[i].style.display = "";
						dataAvailable = 1;
					} else {
						jQuery("#btn_showMorePic").hide();
						tr[i].style.display = "none";
					}
				}
			}

			noDataFoundDiv.style.display = 'block';

			if (dataAvailable) {
				noDataFoundDiv.style.display = 'none';
			}
		},

		showMoreDonors: function(){
			var cid = document.getElementById('camp_id').value;
			var parentDiv = techjoomla.jQuery('#jgiveWrapper');
			parentDiv.addClass('isloading');

			if(gbl_jgive_pro_pic == 0){
				gbl_jgive_pro_pic = document.getElementById('donors_pro_pic_index').value;
			}

			techjoomla.jQuery.ajax({
				url:jgive_baseurl+"index.php?option=com_jgive&format=json&task=campaign.showMoreDonors&tmpl=component",
				type:'POST',
				dataType:'json',
				data:
				{
					cid:cid,
					jgive_index:gbl_jgive_pro_pic
				},
				success:function(data) {
					gbl_jgive_pro_pic = data['jgive_index'];
					techjoomla.jQuery("#jgive_donors_pic ").append(data['records']);

					if(!data['records'] || orders_count <= gbl_jgive_pro_pic)
					{
						techjoomla.jQuery("#btn_showMorePic").hide();
					}
					parentDiv.removeClass('isloading');
				},
				error:function(data) {
					console.log('error');
				}
			});
		},

		featuredCamp: function(featuredData, cid){
			if (featuredData == 1) {
				var featureval = 0;
			} else {
				var featureval = 1;
			}

			techjoomla.jQuery.ajax({
				url:'?option=com_jgive&task=campaign.changeCampFeatureStatus&tmpl=component',
				type:'POST',
				dataType:'json',
				data:
				{
					cid:cid,
					featured:featureval
				},
				success:function(data) {
					if (data == true)
					{
						window.location.reload();
					}
				},
				error:function(data) {
					console.log('error');
				}
			});
		},

		getJSGroupField: function() {
			/** global: integration */
			if (integration === 'jomsocial') {
				var user=document.getElementById('jform_creator_id_id').value;
				var userObject = {};
				userObject["user"] = user;
				jQuery('#jform_js_groupid, .chzn-results').empty();
				JSON.stringify(userObject);
				jQuery.ajax({
					type     : "POST",
					data     : userObject,
					dataType :"json",
					url      : Joomla.getOptions('system.paths').baseFull + "index.php?option=com_jgive&format=json&task=campaignform.getJSGroups",

					success:function(data) {
						jQuery("#jform_js_groupid").trigger("liszt:updated");
						jQuery("#jform_js_groupid").trigger("chosen:updated");

						if (data.data == '') {
							var op="<option value='0' selected='selected'>" + Joomla.JText._('COM_JGIVE_NO_JS_GROUPS') + "</option>" ;
							jQuery('#jform_js_groupid').append(op);
							jQuery("#jform_js_groupid").trigger("liszt:updated");
							jQuery("#jform_js_groupid").trigger("chosen:updated");
						} else {
							for(var index = 0; index < data.data.length; ++index) {
								var selected = '';

								/** global: jsGroupId */
								if(jsGroupId === data.data[index]['id']) {
									selected = "selected";
									op="<option value='"+data.data[index]['id']+"' selected='"+selected+"' > " + data.data[index]['title'] + "</option>" ;
								} else {
									op="<option value='"+data.data[index]['id']+"'> " + data.data[index]['title'] + "</option>" ;
								}

								jQuery('#jform_js_groupid').append(op);
								jQuery("#jform_js_groupid").trigger("liszt:updated");
								jQuery("#jform_js_groupid").trigger("chosen:updated");
							}
						}
					},
				});
			}
		}
	},

	report: {
		showMoreReports: function() {
			let campaignId  = parseInt(jQuery('#campaign_id').val());
			let limitOffset = parseInt(jQuery('#limit_offset').val());

			techjoomla.jQuery.ajax({
				url: jgive_baseurl+"index.php?option=com_jgive&task=reports.showMoreReports",
				type: 'POST',
				dataType: 'json',
				data: {campaign_id:campaignId, limit_offset:limitOffset},
				success:function(response) {
					let setLimitOffset = limitOffset+5;
					techjoomla.jQuery('#limit_offset').val(setLimitOffset);
					techjoomla.jQuery('#reports_list').append(response.data.layout);
					let count = response.data.count;

					if(count <= setLimitOffset) {
						techjoomla.jQuery('#btn_showMorePic').hide();
					}
				},
				error:function(err) {
					console.log(err);
				}
			});
		},
		delete: function(task, currentElement, jtoken) {
			if(confirm(Joomla.JText._('COM_JGIVE_CONFIRM_DELETE_REPORT')) == true) {
				let campaignId = jQuery(currentElement).attr('data-cid');
				let reportId   = jQuery(currentElement).attr('data-rid');
				window.location = jgive_baseurl + 'index.php?option=com_jgive&task=' + task + '&' + jtoken + '=1&cid=' + campaignId + '&id=' + reportId;
			} else {
				return false;
			}
		},
		deleteReportAttachment: function(task, currentElement, jtoken) {
			if(confirm(Joomla.JText._('COM_JGIVE_CONFIRM_DELETE_REPORT_ATTACHMENT')) == true) {
				let campaignId = jQuery(currentElement).attr('data-cid');
				let reportId   = jQuery(currentElement).attr('data-rid');
				let mediaId    = jQuery(currentElement).attr('data-mid');
				var url = jgive_baseurl + "index.php?option=com_jgive&format=json&task=" + task + "&cid=" + campaignId + "&id=" + reportId + "&mid=" + mediaId + "&" + jtoken + "=1";

				jQuery.ajax({
					url: url,
					type: 'GET',
					dataType:'JSON',
					success: function(data) {
						let msg = data.message;
						if (data.success === true)
						{
							Joomla.renderMessages({'success': [msg]});
							jQuery("html, body").animate({
								scrollTop: 0
							}, 500);
						}
						else
						{
							Joomla.renderMessages({'error': [msg]});
							jQuery("html, body").animate({
								scrollTop: 0
							}, 500);
						}
						setTimeout(function(){
							window.location.reload(1);
						}, 600);
					}
				});
			} else {
				return false;
			}
		},
		addAttachment: function (){
			currentAttachmentCount = attachmentMaxLimit - reportAttachmentCount - uploadFileDivId;

			/** global: uploadFileDivId */
			if (currentAttachmentCount > 0) {
				uploadFileDivId++;
				let clone = techjoomla.jQuery('#jgiveWrapper .jgreports__attachemts-clone-div .jgreports__attachemts-clone').clone(false);
				clone.find('#jform_attachments').attr("id", "jform_attachments"+uploadFileDivId);
				clone.find('#jform_attachmentsvalue').attr("id", "jform_attachments"+uploadFileDivId+"value");
				clone.find('#jform_removeAttachment').attr("id", "jform_removeAttachment"+uploadFileDivId);
				jQuery('.jgreports__attachemts').append(clone);
			} else {
				let amountValidationFailedMsg =  Joomla.JText._('COM_JGIVE_REACHED_MAX_REPORT_ATTACHMENT_LIMIT');
				Joomla.renderMessages({'error':[amountValidationFailedMsg]});
				jQuery("html, body").animate({
					scrollTop: 0
				}, 500);

				return false;
			}
		},
		removeAttachment: function (thisfile, jtoken, client, clientId){
			let fieldNum = thisfile.id.charAt(thisfile.id.length-1);
			let mediaId  = jQuery("#jform_attachments"+fieldNum+"value").val();

			/*Here we are removing uploaded file as well as upload file field clone*/
			if (mediaId) {
				let url = Joomla.getOptions('system.paths').baseFull + "index.php?option=com_jgive&format=json&task=campaignform.deleteMedia" + "&" + jtoken + "=1";

				if (!confirm(Joomla.JText._('COM_JGIVE_CONFIRM_DELETE_MEDIA'))) {
					return false;
				}

				jQuery.ajax({
					type: "POST",
					url: url,
					dataType: 'JSON',
					data: {
						id: mediaId,
						client: client,
						clientId:clientId
					},
					success: function(data) {
						/*After deleting the media from media_fiels table remove the file clone*/
						jQuery(thisfile).parents('.jgreports__attachemts-clone').remove();
						uploadFileDivId--;
					}
				});
			} else {
				jQuery(thisfile).parents('.jgreports__attachemts-clone').remove();
				uploadFileDivId--;
			}
		},
		validateFile: function(thisFile, isGallary) {
			/** Validation is for file field only */
			if (jQuery(thisFile).attr('type') != 'file') {
				return false;
			}

			/** Clear error message */
			jQuery('#system-message-container').empty();

			let uploadedfile = jQuery(thisFile)[0].files[0];
			let fileType = uploadedfile.type.split("/");
			let fileExtension = uploadedfile.name.split(".");

			/** global: allowedAttachments */
			let allowedExtensionsArray = allowedAttachments.split(",");
			let invalid = 0;
			let errorMsg = new Array();

			if (fileExtension[fileExtension.length-1] !== '' || fileExtension[fileExtension.length-1] !== null) {
				if (jQuery.inArray(fileExtension[fileExtension.length-1] , allowedExtensionsArray) == -1) {
					invalid = "1";
					errorMsg.push(Joomla.JText._('COM_JGIVE_FILE_TYPE_ERROR'));
				}

				if (!invalid) {
					var checkMimeOfFile;
					var url = jgive_baseurl + "index.php?option=com_jgive&format=json&task=reportform.checkMimeType";

					jQuery.ajax({
						url: url,
						type: 'POST',
						dataType:'JSON',
						async: false,
						data: {fileExtension: fileExtension[fileExtension.length-1]},
						success: function(data) {
							checkMimeOfFile = data;
						}
					});

					if ((checkMimeOfFile !== null || checkMimeOfFile !== '') && uploadedfile.type === checkMimeOfFile) {
					} else {
						invalid = "1";
						errorMsg.push(Joomla.JText._('COM_JGIVE_FILE_TYPE_ERROR'));
					}
				}
			} else {
				invalid = "1";
				errorMsg.push(Joomla.JText._('COM_JGIVE_FILE_TYPE_ERROR'));
			}

			let uploadedFileSize       = uploadedfile.size;

			/** global: attachmentMaxSize */
			if (uploadedFileSize > attachmentMaxSize * 1024 *1024) {
				invalid = "1";
				errorMsg.push(Joomla.JText._('COM_JGIVE_FILE_SIZE_ERROR'));
			}

			if (invalid) {
				jQuery(thisFile)[0].value = '';
				Joomla.renderMessages({'error': errorMsg});

				jQuery("html, body").animate({
					scrollTop: 0
				}, 500);

				return false;
			}

			jgive.report.uploadFile(thisFile, isGallary);
		},
		uploadFile: function(thisFile, isGallary) {
			let fileData = new FormData();
			const token = jQuery("#reportFormtoken input").attr('name');
			let uploadedfile = jQuery(thisFile)[0].files[0];
			fileData.append('file', uploadedfile);
			fileData.append('isGallary', isGallary);
			fileData.append(token, 1);
			var url = jgive_baseurl + "index.php?option=com_jgive&format=json&task=reportform.uploadFile";

			this.ajaxObj = jQuery.ajax({
				type: "POST",
				url: url,
				dataType: 'JSON',
				contentType: false,
				processData: false,
				data: fileData,
				xhr: function() {
					var myXhr = jQuery.ajaxSettings.xhr();

					if (myXhr.upload) {
						myXhr.upload.addEventListener('progress', function(e) {
							if (e.lengthComputable) {
								var percentage = Math.floor((e.loaded / e.total) * 100);
								jgive.report.progressBar.updateStatus(thisFile.id, percentage);
							}
						}, false);
					}

					return myXhr;
				},
				beforeSend: function(x) {
					jgive.report.progressBar.init(thisFile.id, '');
				},
				success: function(data) {
					if (data.data.mediaId) {
						jQuery('#'+thisFile.id+'value').val(data.data.mediaId);
						jgive.report.progressBar.statusMsg(thisFile.id, 'success', data.message);
					} else {
						jQuery(thisFile).val('');
						jQuery('#' + thisFile.id).siblings('.progress').remove();
						jgive.report.progressBar.statusMsg(thisFile.id, 'error', data.message);
					}
				},
				error: function(xhr, status, error) {
					jgive.report.progressBar.statusMsg(thisFile.id, 'error', Joomla.JText._('COM_JGIVE_FILE_ERROR'));
				}
			});
		},
		progressBar: {
			init: function(divId, msg) {
				jQuery('#' + divId).siblings('.alert').remove();
				jQuery('#' + divId).siblings('.progress').remove();
				this.progress = jQuery("<div class='progress progress-striped active' style='height:24px'><div class='bar' style='width:0%;background-color:#4CAF50;'></div><button onclick='return tjexport.abort();' class='btn btn-danger btn-small pull-right'>"+Joomla.JText._('COM_JGIVE_FILE_UPLOAD_ABORT')+"</button></div>");
				this.statusBar = this.progress.find('.bar');
				this.abort = jQuery("<div class='abort'><span>"+Joomla.JText._('COM_JGIVE_FILE_UPLOAD_ABORT')+"</span></div>").appendTo(this.statusbar);
				jQuery('#'+divId).after(this.progress);
			},
			updateStatus: function(divId, percentage) {
				this.statusBar.css("width", percentage + '%');
				this.statusBar.width(percentage + '%');
				this.statusBar.text(percentage + '%');
			},
			abort: function() {
				if (!confirm(Joomla.JText._('COM_JGIVE_FILE_UPLOAD_CONFIRM_ABORT'))) {
					return false;
				}

				this.ajaxObj.abort();
			},
			statusMsg: function(divId, alert, msg) {
				setTimeout(function() {
					jQuery('#' + divId).siblings('.progress').remove();
				}, 2000);

				let closeBtn = "<a href='#' class='close pull-right' data-dismiss='alert' aria-label='close' title='"+Joomla.JText._('COM_JGIVE_FILE_UPLOAD_ABORT')+"'>×</a>";
				let msgDiv = jQuery("<br><br><div class='alert alert-" + alert + "'><strong>" + msg + "</strong>" + closeBtn + "</div>");
				jQuery('#' + divId).parent().after(msgDiv);
			}
		}
	},

	dashboard: {
		init: function(){
			techjoomla.jQuery(document).ready(function() {
				if(localStorage.getItem("filter") == 0) {
					jQuery(".search__campaign").toggleClass( "af-d-none active" );
				}

				jgive.dashboard.onChangeGetDashboardDonationData();
			});
		},

		onChangeGetDashboardDonationData: function(){
			techjoomla.jQuery("#dashboardcampaignsOption").change(function() {
				var graph_filter_val = document.getElementById("dashboardcampaignsOption").value;
				var userId = document.getElementById('user_id').value;

				var ajaxcall = techjoomla.jQuery.ajax({
					type:'GET',
					url: jgive_baseurl+'index.php?option=com_jgive&view=dashboard&task=dashboard.getDashboardGraphData&userId='+userId+'&filtervalue='+graph_filter_val,
					dataType:'json',
					sucess:function(data) {
					},
					error:function(data) {
						console.log('error');
					}
				});

				ajaxcall.done(function (data) {
					var graphid = document.getElementById("dashboardCampaign_graph").getContext('2d');
					var total_donationchart = new Chart(graphid, {
					  type: 'line',
					  data: {
						labels: data.donationDate,
						datasets: [{
						  label: data.totalDonation ? data.totalDonation : 0,
						  data: data.donationAmount ? data.donationAmount : 0,
						  backgroundColor: "rgba(203, 235, 230, 0.5)",
						  borderColor:"rgba(55, 179, 142, 1)",
						  lineTension:'0',
						  borderWidth:'2',
						  pointRadius:1,
						  pointBackgroundColor: "rgba(55, 179, 142, 1)",
						  pointBorderColor: "rgba(55, 179, 142, 1)",
						  pointHoverBackgroundColor: "rgba(55, 179, 142, 1)",
						  pointHoverBorderColor: "rgba(55, 179, 142, 1)"
						},{
						  label: data.avgDonation ? data.avgDonation : 0,
						  data: data.donationAvg ? data.donationAvg : 0,
						  backgroundColor: "rgba(216, 225, 180, 1)",
						  borderColor:"rgba(251, 214, 20, 0.90)",
						  lineTension:'0',
						  borderWidth:'2',
						  pointRadius:1,
						  pointBackgroundColor: "rgba(251, 214, 20, 0.90)",
						  pointBorderColor: "rgba(251, 214, 20, 0.90)",
						  pointHoverBackgroundColor: "rgba(251, 214, 20, 0.90)",
						  pointHoverBorderColor: "rgba(251, 214, 20, 0.90)"
						}]
					  }
					});
				});
			}).change();
		},

		toggleDiv: function(spanId) {
			if ( jQuery(window).width() < 767 ) {
				jQuery("#"+spanId).toggle();
			}
			jQuery(".campaigns__filter").toggleClass( "af-d-none active" );

			if (spanId == 'SearchFilterInputBox') {
				jQuery(".search__campaign").toggleClass( "active af-d-none" );
				jQuery(".fa-search").toggleClass( "btn btn-primary btn-sm p-10" );
				jQuery(".campaign__filter").toggleClass( "af-d-none" );
				jQuery(".list__separation").toggleClass( "af-d-none" );
			}
		},

		clearFilter: function(){
			window.location = window.location.href;
		},
	},

	campaigns: {
		campaignSearch: function() {
			var filter_search = document.getElementById('filter_search').value;
			var camps_quick = jgive_baseurl + 'index.php?option=com_jgive&view=campaigns&layout=all&filter_search=' +filter_search+ '&Itemid=' + menuItemId;
			window.location.assign(camps_quick);
		},

		campaignSearchClear: function() {
			localStorage.setItem("filter", 1);
			document.getElementById('filter_search').value = "";
			jgiveCommon.filters.submitFilters('adminForm4');
		},

		myCampaignFilterCampType: function() {
			var filter_campaign_type = document.getElementById('filter_campaign_type').value;
			var camps_quick = jgive_baseurl + 'index.php?option=com_jgive&view=campaigns&layout=my&filter_campaign_type=' +filter_campaign_type+ '&Itemid=' + menuItemId;
			window.location.assign(camps_quick);
		},

		myCampaignCategoryFilter: function() {
			var filter_campaign_cat = document.getElementById('filter_campaign_cat').value;
			var camps_quick = jgive_baseurl + 'index.php?option=com_jgive&view=campaigns&layout=my&filter_campaign_cat=' +filter_campaign_cat+ '&Itemid=' + menuItemId;
			window.location.assign(camps_quick);
		},

		myCampaignOrgIndTypeFilter: function() {
			/** global: menuItemId */
			/** global: jgive_baseurl */
			var filter_org_ind_type = document.getElementById('filter_org_ind_type').value;
			var camps_quick = jgive_baseurl + 'index.php?option=com_jgive&view=campaigns&layout=my&filter_org_ind_type=' +filter_org_ind_type+ '&Itemid=' + menuItemId;
			window.location.assign(camps_quick);
		}
	},

	donation: {
		retryPayment: function (paymentId){
			let gateway = jQuery("input[name='gateways']:checked");
			let gatewayName = gateway.val();
			let single_gateway = gateway.attr('data-single-gateway');

			single_gateway = 0;

			if (typeof single_gateway !== typeof undefined && single_gateway == "1") {
				single_gateway = 1;
			}

			let url = jgive_baseurl+"index.php?option=com_jgive&tmpl=component&task=donations.retryPayment&order="+paymentId+"&gateway_name="+gatewayName+"&single_gateway="+single_gateway;

			jQuery('#html-container').empty().html(Joomla.JText._("COM_JGIVE_LOADER_LOADING"));
			jQuery.ajax({
				url: url,
				type: 'GET',
				dataType: 'json',
				beforeSend:function () {
					jQuery('html, body').animate({
						scrollTop: jQuery("#html-container").offset().top
					}, 1000);
				},
				success: function(response) {
					jQuery('#html-container').removeClass('ajax-loading').html(response);
				}
			});
		},

		// Hide/Show Payment gateways option
		showPaymentGetways: function (status, msg) {
			/* If only one payment gateway then do nlt show option to select the gateway*/
			let gateWayCount = jQuery('#jgiveWrapper #gatewaysContent div.radio').length;

			if (gateWayCount === 1) {
				jQuery('#jgiveWrapper #gatewaysContent .form-group').hide();
				jQuery('#jgiveWrapper #gatewaysContent .form-group input:first-child').attr("data-single-gateway", "1");
				jQuery('#jgiveWrapper #gatewaysContent .form-group input:first-child').click();
			}

			if (document.getElementById('gatewaysContent').style.display=='none') {
				if(status == '1') {
					alert(msg);
				} else if(status == '0') {
					document.getElementById('gatewaysContent').style.display='block';
				}
			}

			return false;
		}
	},

	donations: {
		init: function(){
			techjoomla.jQuery(document).ready(function() {
				com_jgive.UI.Donation.updatePlatformFee(document.getElementById("donation_amount"),cid);

				// Show PAN field on conditional basis
				if(allowPanVerification == 'yes')
				{
					if(panAmountLimit > 0)
					{
						if (parseInt(jQuery('#donation_amount').val()) < panAmountLimit)
						{
							jQuery('.pan').addClass('d-none');
							jQuery('#pannumber').removeClass('required');
						}
						else
						{
							jQuery('.pan').removeClass('d-none');
							jQuery('#pannumber').addClass('required');
						}
					}
				}
				else
				{
					jQuery('.pan').addClass('d-none');
					jQuery('#pannumber').removeClass('required');
				}				
			});
		},

		/**
		 * Validate Donation Checkout Form Fields
		 *
		 * @params void
		 *
		 * @return  void
		 *
		 * @since  1.7
		 */
		validatePANFields: function(){			
			var panNumber = techjoomla.jQuery('#pannumber').val();
			if (panNumber !== null) {
				let regex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
				if(!(panNumber.match(regex))) {
					alert(Joomla.JText._('COM_JGIVE_PAN_VERIFICATION_VALIDATION_ERROR'));
                    jQuery('#pannumber').val('');
					return false;
				} else {
					jgiveCommon.getPanVerification(panNumber);
					return true;
				}
			}
			else {

			}
		},

		/**
		 * Disply selected giveback amount in donation amount field
		 *
		 * @params   float  amount to fill in donation amount field
		 *
		 * @since   1.7
		 */
		populateSelected_GivebackAmount: function(giveback_minamount, ref) {
			/** Update amount in donation amount field */
			if (giveback_minamount) {
				/** Remove old active giveback style **/
				techjoomla.jQuery("#jgive_givebacks ul .jgive_active").removeClass("jgive_active");

				/** Add new active giveback style **/
				techjoomla.jQuery(ref).closest( "li" ).addClass( "jgive_active");

				/** Populate giveback amount in donation amount field **/
				techjoomla.jQuery('#donation_amount').attr('value',giveback_minamount);
				com_jgive.UI.Donation.updatePlatformFee(document.getElementById("donation_amount"),cid);
			} else {
				/** Default populate 0 amount in donation amount field **/
				techjoomla.jQuery('#donation_amount').attr('value',0);
				com_jgive.UI.Donation.updatePlatformFee(document.getElementById("donation_amount"),cid);
			}
		},

		/**
		 * Send request to place order
		 *
		 * @params void
		 *
		 * @return   boolean/HTML  false if failed validation or string html if order placed
		 *
		 * @since 1.7
		 **/
		placeQuickPayment: function() {
			var form_values = techjoomla.jQuery('#payment_quick').serialize();

			/** Donation Amount currency validation */
			let form_values_array = techjoomla.jQuery('#payment_quick').serializeArray();
			let donationAmount = form_values_array[0].value;
			let returnValue = jgiveCommon.validateAmountAsPerCurrency(jQuery('#donation_amount'));

			if (returnValue === false) {
				return false;
			}

			var order_id=techjoomla.jQuery('#order_id').val();
			var requiredFieldValueMiss = 0;

			/** global: donationTermsCondition */
			/** global: donationTermsConditionArticle */
			if (donationTermsCondition != 0 && donationTermsConditionArticle != 0) {
				if (document.payment_quick.terms_condition.checked === false) {
					alert(Joomla.JText._('COM_JGIVE_CHECK_TERMS'));
					return false;
				}
			}

			techjoomla.jQuery("#jgive_form_field .required").each(function() {
				if(!techjoomla.jQuery(this).val()) {
					techjoomla.jQuery(this).focus();
					alert(Joomla.JText._("COM_JGIVE_FILL_MANDATORY_FIELDS_DATA"));
					requiredFieldValueMiss = 1;
					return false;
				}
			});

			// Check payment gateways are displayed then applied the validation while place payment request
			if(techjoomla.jQuery("#payment_quick input[name='gateways']").length)
			{
				if(techjoomla.jQuery("#payment_quick input[name='gateways']:checked").length < 1) {
					techjoomla.jQuery('#payment_quick #jgive_footer').focus();
					alert(Joomla.JText._("COM_JGIVE_DONATION_VALIDATION_WRONG_PAYMENT_METHOD"));
					requiredFieldValueMiss = 1;
					return false;
				}
			}

			if (requiredFieldValueMiss === 1) {
				return false;
			}

			// Email validation
			if (!jgive.donations.validateEmail(document.getElementById("paypal_email").value)) {
				techjoomla.jQuery('#paypal_email').focus();
				return false;
			}

			/** Validate donation amount */
			if (!jgive.donations.donationAmountValidation()) {
				return false;
			}

			/** Send request to place order **/
			techjoomla.jQuery.ajax({
				url:jgive_baseurl+"index.php?option=com_jgive&task=donations.placeOrder&tmpl=component",
				type:"POST",
				dataType:"json",
				async:true,
				data:form_values,
				beforeSend:function(){
					jgive.donations.requestProcessingImg();

					/** Blur editable fields */
					techjoomla.jQuery("#jgive_form_field").addClass("jgive-blur-fields");
					techjoomla.jQuery("#jgive_givebacks").addClass("jgive-blur-fields");

					/** JGive disabled fields */
					techjoomla.jQuery("#jgive_disabled_fields input").each(function(){
						techjoomla.jQuery(this).attr("readonly","readonly");
						techjoomla.jQuery("#payment_quick input:radio").attr('disabled',true);
					});

					techjoomla.jQuery( "input:radio[name=givebacks]").attr("readonly","readonly");

					/** Show edit button */
					techjoomla.jQuery("#jgive_edit_button").removeClass("jgive_edit_button");
				},
				complete: function(data){
					jgive.donations.hiderequestProcessingImg();
				},
				success:function(data){
					jgive.donations.hiderequestProcessingImg();

					if(data['success'] == 1) {
						techjoomla.jQuery('#jgive_continue_btn').hide();
						techjoomla.jQuery('#payment_tab_table_html').html(data['payhtml']);
						techjoomla.jQuery('#payment_tab_table_html').html(data['gatewayhtml']);
						techjoomla.jQuery('#payment_tab_table_html').show();
						techjoomla.jQuery('html,body').animate({scrollTop: techjoomla.jQuery("#payment_tab_table_html").offset().top},'slow');
					} else {
						if (data.success_msg.length > 0) {
							for (x of data.success_msg) {
								Joomla.renderMessages({'error':[x]});
								jQuery('html, body').animate({scrollTop: 0}, 500);
							}
						}
						techjoomla.jQuery('#jgive_continue_btn').show();
					}
				},
				error:function(){
					alert(Joomla.JText._("COM_JGIVE_ORDER_PLACING_ERROR"));
					techjoomla.jQuery('#jgive_continue_btn').show();
					jgive.donations.hiderequestProcessingImg();
				},
			});
		},

		/**
		 * Show request processing image
		 *
		 * @params void
		 *
		 * @return   html  adding request processing image to jgive_content div
		 *
		 * @since  1.7
		 */
		requestProcessingImg: function() {
			if (jQuery('#jgiveWrapper .tjBs5').length)
			{
				techjoomla.jQuery('<div id="jgive_processing"></div>')
					.css({
						"background": "rgba(255, 255, 255, 0.8) url('" + jgive_baseurl + "media/com_jgive/images/ajax.gif') 50% 15% no-repeat",
						"top": techjoomla.jQuery('#jgive_content').position().top,
						"left": techjoomla.jQuery('#jgive_content').position().left,
						"width": techjoomla.jQuery('#jgive_content').outerWidth(),
						"height": techjoomla.jQuery('#jgive_content').outerHeight(),
						"position": "absolute",
						"z-index": "1000",
						"opacity": "0.80"
					})
					.appendTo('#jgive_content');

					// Optional: Show loader
				techjoomla.jQuery("#jgive_processing").show();
			}
			else
			{
				var width = techjoomla.jQuery("#jgive_content").width();
				var height = techjoomla.jQuery("#jgive_content").height();
				techjoomla.jQuery('<div id="jgive_processing"></div>')
				.css("background", "rgba(255, 255, 255, .8) url('"+jgive_baseurl+"media/com_jgive/images/ajax.gif') 50% 15% no-repeat")
				.css("top", techjoomla.jQuery('#jgive_content').position().top - techjoomla.jQuery("#jgive_content").scrollTop())
				.css("left", techjoomla.jQuery('#jgive_content').position().left - techjoomla.jQuery("#jgive_content").scrollLeft())
				.css("width", "100%")
				.css("height", "100%")
				.css("position", "fixed")
				.css("z-index", "1000")
				.css("opacity", "0.80")
				.css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)")
				.css("filter", "alpha(opacity = 80)")
				.appendTo('#jgive_content');
			}
		},

		/**
		 * Remove request processing image
		 *
		 * @return   undefined
		 *
		 * @since  1.7
		 */
		hiderequestProcessingImg: function(){
			techjoomla.jQuery('#jgive_processing').remove();
		},

		/**
		 * Donation amount validation
		 *
		 * @params   void
		 *
		 * @return   boolean  result in true/false with alert message if needed
		 *
		 * @since  1.7
		 */
		donationAmountValidation: function(){
			var donation_amount = document.getElementById('donation_amount').value;
			donation_amount = parseFloat(donation_amount);

			if(donation_amount) {
				total_commission_amount = 0;

				if(!send_payments_to_owner) {
					total_commission_amount = fixed_commissionfee;

					if(commission_fee) {
						total_commission_amount = ((donation_amount*commission_fee)/100)+fixed_commissionfee;
					}
				}

				if(total_commission_amount < minimum_amount) {
					total_commission_amount = minimum_amount;
				}

				if(total_commission_amount > donation_amount) {
					alert(Joomla.JText._("COM_JGIVE_MINIMUM_DONATION_AMOUNT")+ minimum_amount);
					return false;
				}

				var response = jgive.donations.validateGiveBackAmount(donation_amount);

				if(!response) {
					return false;
				}
			} else {
				alert(Joomla.JText._("COM_JGIVE_MINIMUM_DONATION_AMOUNT")+ minimum_amount);
				return false;
			}
			return true;
		},

		/**
		 * Validate if selected giveback matching entered donation amount
		 *
		 * @params   integer  amount to donate
		 *
		 * @return   boolean  result in true/false with alert message if needed
		 *
		 * @since  1.7
		 */
		validateGiveBackAmount: function(donation_amount){
			var givebackId = techjoomla.jQuery( "input:radio[name=givebacks]:checked" ).val();

			if(givebackId !=0 ) {
				for(var index = 0; index < givebackDetails.length; index++) {
					if(givebackDetails[index]['id'] == givebackId) {
						var givebackAmount = givebackDetails[index]['amount'];

						if(donation_amount >= givebackAmount) {
							return true;
						} else {
							alert(Joomla.JText._("COM_JGIVE_AMOUNT_SHOULD_BE")+givebackAmount);
							return false;
						}
					}
				}
			}
			return true;
		},

		/**
		 * Email validation
		 *
		 * @param  string  mail
		 *
		 * @return  boolean
		 */
		validateEmail: function(mail) {
			if (/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(mail)){
				return (true)
			}

			alert(Joomla.JText._("COM_JGIVE_EMAIL_VALIDATION"));
			return (false)
		},

		/**
		 * Unlock jgive donations fields to edit placed order
		 *
		 * @params void
		 *
		 * @return  void
		 *
		 * @since  1.7
		 */
		editDetails: function() {
			/** Hide payment gateways html */
			techjoomla.jQuery('#payment_tab_table_html').html('');
			techjoomla.jQuery('#payment_tab_table_html').hide();

			/** JGive disabled fields */
			techjoomla.jQuery("#jgive_disabled_fields input").each(function(){
				techjoomla.jQuery(this).removeAttr("readonly");
				techjoomla.jQuery("#payment_quick input:radio").attr('disabled',false);
			});

			techjoomla.jQuery( "input:radio[name=givebacks]").removeAttr("readonly");

			/** Remove blur editable fields */
			techjoomla.jQuery("#jgive_form_field").removeClass("jgive-blur-fields");
			techjoomla.jQuery("#jgive_givebacks").removeClass("jgive-blur-fields");

			/** Hide edit details button */
			techjoomla.jQuery("#jgive_edit_button").addClass("jgive_edit_button");

			/** Show continue btn */
			techjoomla.jQuery('#jgive_continue_btn').show();
		},

		/**
		 * Validate Donation Checkout Form Fields
		 *
		 * @params void
		 *
		 * @return  void
		 *
		 * @since  1.7
		 */
		validateCheckoutFields: function(){
			var paypal_email = techjoomla.jQuery('#paypal_email').val();

			if (paypal_email !== null) {
				let email = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

				if(!(paypal_email.match(email))) {
					var message = {};
					message.error = [];
					message.error.push(Joomla.JText._("COM_JGIVE_EMAIL_VALIDATION"));
					Joomla.renderMessages(message);
					techjoomla.jQuery('#paypal_email').focus();
					return false;
				} else {
					return true;
				}
			}
		},

		validateAmount: function(fieldId, fieldMsg){
			switch(fieldId) {
				case 'max_donors':
				case 'minimum_amount':
					if((techjoomla.jQuery('#'+fieldId).val() !=='') && (! parseInt(techjoomla.jQuery('#'+fieldId).val(),10) > 1 )) {
						alert(fieldMsg);
						techjoomla.jQuery('#'+fieldId).val('');
						techjoomla.jQuery('#'+fieldId).focus();
						return false;
					}

					if((techjoomla.jQuery('#'+fieldId).val() !=='') && (techjoomla.jQuery('#'+fieldId).val() < 0)) {
						alert(fieldMsg);
						techjoomla.jQuery('#'+fieldId).val('');
						techjoomla.jQuery('#'+fieldId).focus();
						return false;
					}
				break;

				case 'recurring_count':
					if((techjoomla.jQuery('#'+fieldId).val() !=='') && (! parseInt(techjoomla.jQuery('#'+fieldId).val(),10) > 2 )) {
						alert(fieldMsg);
						techjoomla.jQuery('#'+fieldId).val('');
						techjoomla.jQuery('#'+fieldId).focus();
						return false;
					}

					if((techjoomla.jQuery('#'+fieldId).val() !=='') && (techjoomla.jQuery('#'+fieldId).val() < 2)) {
						alert(fieldMsg);
						techjoomla.jQuery('#'+fieldId).val('');
						techjoomla.jQuery('#'+fieldId).focus();
						return false;
					}
				break;

				default:
					// To fix separated amount issue
					// issue was : suppose user entered amount 10,200, Now in db amount column type is float so the amount 10.20 is only get stored
					var replace = '.';

					if(decimal_separator == '.') {
						replace = ',';
					}

					var ans = techjoomla.jQuery('#'+fieldId).val().replace(replace, '');
					techjoomla.jQuery('#'+fieldId).val(ans);

					if((techjoomla.jQuery('#'+fieldId).val() !=='') && (! parseInt(techjoomla.jQuery('#'+fieldId).val(),10) > 0)) {
						alert(fieldMsg);
						techjoomla.jQuery('#'+fieldId).val('');
						techjoomla.jQuery('#'+fieldId).focus();
						return false;
					}

					if((techjoomla.jQuery('#'+fieldId).val() !=='') && (techjoomla.jQuery('#'+fieldId).val() < 0)) {
						alert(fieldMsg);
						techjoomla.jQuery('#'+fieldId).val('');
						techjoomla.jQuery('#'+fieldId).focus();
						return false;
					}
			}
		},

		noGiveBack: function(){
			var no_giveback=techjoomla.jQuery( "input:checkbox[name=no_giveback]:checked" ).val();

			if(no_giveback==undefined) {
				techjoomla.jQuery("#hide_giveback").show("slow");
				techjoomla.jQuery("#hide_giveback_desc").show("slow");
			} else {
				techjoomla.jQuery("#hide_giveback").hide("slow");
				techjoomla.jQuery("#hide_giveback_desc").hide("slow");
			}
		},

		populateGiveback: function(){
			var giveBackid = techjoomla.jQuery('#givebacks').val();
			var cid = techjoomla.jQuery('#cid').val();

			if(giveBackid=='edit_amount') {
				techjoomla.jQuery('#giveback_des').html(Joomla.JText._("COM_JGIVE_GIVE_NOT_AVIL_FOR_THIS_AMOUNT"));
				techjoomla.jQuery('#hide_giveback_desc').removeClass('d-none');
			}

			if (giveBackid == 0) {
				techjoomla.jQuery('#donation_amount').attr('value',minimum_amount);
				techjoomla.jQuery('#hide_giveback_desc').addClass('d-none');
				com_jgive.UI.Donation.updatePlatformFee(document.getElementById("donation_amount"),cid);
			}

			for(var index = 0; index < givebackDetails.length; index++) {
				if(givebackDetails[index]['id']==giveBackid) {
					// Update amount in donation amount field
					techjoomla.jQuery('#donation_amount').attr('value',parseFloat(givebackDetails[index]['amount']));

					// Update giveback description
					techjoomla.jQuery('#giveback_des').html(Joomla.JText._('COM_JGIVE_GIVEBACK_DESC')+' : '+givebackDetails[index]['description']);
					techjoomla.jQuery('#hide_giveback_desc').removeClass('d-none');
					com_jgive.UI.Donation.updatePlatformFee(document.getElementById("donation_amount"),cid);
				}
			}
		},

		otherCity: function(){
			if(document.jGivePaymentForm.other_city_check.checked===true) {
				jQuery("#other_city").show('slow');
				jQuery("#hide_city").parent().parent().hide('slow');
				jQuery("#other_city_check").appendTo("#other_city_lbl");
				jQuery("#other_city_check").css('min-height', '0px');

				if (jQuery('#jgiveWrapper .tjBs5').length)
				{
					jQuery("#other_city_control").removeClass('form-check');
					jQuery("#other_city_check").addClass('mt-1');
				}

			} else {
				jQuery("#other_city_check").prependTo("#other_city_control");

				if (!jQuery('#jgiveWrapper .tjBs5').length)
				{
					jQuery("#other_city_check").css('min-height', '34px');
				}
				jQuery("#hide_city").parent().parent().show('slow');
				jQuery("#other_city").val('');
				jQuery("#other_city").hide('slow');

				if (jQuery('#jgiveWrapper .tjBs5').length)
				{
					jQuery("#other_city_control").addClass('form-check');
					jQuery("#other_city_check").removeClass('mt-1');
				}
			}
		},

		jgive_toggleOrder: function(selecteddiv){
			techjoomla.jQuery('#'+selecteddiv).toggle();
		},

		jgive_hideshowTabs: function(obj){
			jgive.donations.jgive_hideAllEditLinks()
			techjoomla.jQuery('.checkout-content').slideUp('slow');
			techjoomla.jQuery(obj).hide();
			techjoomla.jQuery('#payment_tab_table_html').html();
			techjoomla.jQuery(obj).parent().parent().find('.checkout-content').slideDown('slow');
		},

		jgive_hideAllEditLinks: function(){
			techjoomla.jQuery(".jgive_editTab").hide();
		},

		jgive_showAllEditLinks: function(){
			techjoomla.jQuery(".jgive_editTab").show();
		},

		/** This is a functions that scrolls to #{blah}link*/
		goToByScroll: function(id){
			techjoomla.jQuery('html,body').animate({
			scrollTop: techjoomla.jQuery("#"+id).offset().top},'slow');
		},

		addEditLink: function(selectorObj,currentstepname){
			techjoomla.jQuery('#'+currentstepname.toString()+' .checkout-heading .badge').remove();
			techjoomla.jQuery(selectorObj).append('<span class=" badge badge-success pull-right" id="jgive_success_icon"><i class="fa fa-check"></i></span><a class="jgive_editTab" onclick="jgive.donations.jgive_hideshowTabs(this)">' + Joomla.JText._('COM_JGIVE_EDIT') + ' &nbsp;&nbsp;</a>');
		},

		chkmail: function(email){
			techjoomla.jQuery.ajax({
				url: '?option=com_jgive&task=donations.chkmail&email='+email+'&tmpl=component',
				type: 'GET',
				dataType: 'json',
				success: function(data) {
					if(data[0] == 1){
						alert(data[1]);
						techjoomla.jQuery("#button-billing-info").attr("disabled", "disabled");
					} else {
						techjoomla.jQuery('#email_reg').html('');
						techjoomla.jQuery("#button-billing-info").removeAttr("disabled");
					}
				}
			});
		},

		paymentFormValidateGiveBackAmount: function(donation_amount){
			var no_giveback=techjoomla.jQuery( "input:checkbox[name=no_giveback]:checked" ).val();

			// Get the value from a dropdown select
			var givebackId = techjoomla.jQuery( "#givebacks").val();

			if(no_giveback==undefined) {
				for(index = 0; index < givebackDetails.length; index++) {
					if(givebackDetails[index]['id']==givebackId) {
						var givebackAmount = givebackDetails[index]['amount'];

						if(donation_amount>=givebackAmount) {
							return true;
						} else {
							if(campaign_type === 'donation') {
								alert(Joomla.JText._('COM_JGIVE_DONATION_AMOUNT_SHOULD_BE') + givebackAmount + currency_symbol);
							} else {
								alert(Joomla.JText._('COM_JGIVE_INVESTMENT_AMOUNT_SHOULD_BE') + givebackAmount + currency_symbol);
							}
							return false;
						}
					}
				}
			}
			return true;
		},

		jgive_login: function(){
			techjoomla.jQuery.ajax({
				url: jgive_baseurl+'?option=com_jgive&task=donations.login_validate',
				type: 'post',
				data: techjoomla.jQuery('#user-info-tab #login :input'),
				dataType: 'json',
				beforeSend: function() {
					techjoomla.jQuery('#button-login').attr('disabled', true);
					techjoomla.jQuery('#button-login').after('<span class="wait">&nbsp;Loading..</span>');
				},
				complete: function() {
					techjoomla.jQuery('#button-login').attr('disabled', false);
					techjoomla.jQuery('.wait').remove();
				},
				success: function(json) {
					techjoomla.jQuery('.warning, .j2error').remove();

					if (json['error']) {
						techjoomla.jQuery('#login').prepend('<div class="warning alert alert-danger" >' + json['error']['warning'] + '<button data-dismiss="alert" class="close" type="button">×</button></div>');
						techjoomla.jQuery('.warning').fadeIn('slow');
					} else if (json['redirect']) {
						location = json['redirect'];
					}
				},
				error: function(){
					alert(Joomla.JText._("COM_JGIVE_CHECKOUT_ERROR_LOGIN"));
				}
			});
		},
		useSuggestedAmount: function(suggestedAmount, element) {
			techjoomla.jQuery('#donation_amount').val(suggestedAmount);
			jQuery(".amount-suggestions .item-amount-suggestion").each(function(){
				jQuery(this).removeClass("active");
			})
			jQuery(element).addClass("active");

		},
	},

	individualform: {
		init: function() {
			jQuery(document).ready(function() {
				com_jgive.UI.Individual.hideUserCheckbox(user_id);
			});

			Joomla.submitbutton = function(task) {
				if (task == 'individualform.save') {
					var validData = document.formvalidator.isValid(document.getElementById('adminForm'));
					if (validData == true){
						Joomla.submitform(task, document.getElementById('adminForm'));
					}
				} else if (task == 'individualform.cancel') {
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
				else {
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		}
	},

	organizationform: {
		init: function() {
			techjoomla.jQuery(document).ready(function() {
				com_jgive.UI.Organization.contact(0, 'jform_vendor_id');
				com_jgive.UI.Organization.otherCity(other_city_check,other_city_value);
				com_jgive.UI.Common.generateStates('jform_country', 1, region, city);
			});
		}
	},

	individuals: {
		individualsSubmit: function(task){
			if (task == 'individuals.delete') {
				var r = confirm(Joomla.JText._('COM_JGIVE_ARE_YOU_SURE_YOU_TO_DELETE_THE_CONTACTS'));

				if (r !== true) {
					return;
				}
			}

			Joomla.submitform(task);
		}
	}
};

/**
 * Backend end JavaScript
 */
var jgiveAdmin = {
	campaign: {
		/* Initialize campaign js*/
		init: function() {
			jQuery('#jform_goal_amount').change(function(){
				jgiveCommon.validateAmountAsPerCurrency(this);
			});

			Joomla.submitbutton = function(task) {
				if (task == "") {
					return false;
				} else {
					var isValid=true;
					var action = task.split(".");

					if (action[1] == "cancel" || action[1] == "close") {
						Joomla.submitform(task);
					}

					if (action[1] != "cancel" && action[1] != "close") {
						var forms = jQuery("form.form-validate");
						for (var i = 0; i < forms.length; i++) {
							if (!document.formvalidator.isValid(forms[i])) {
								isValid = false;
								break;
							}
						}

						// validate suggestion amount before saving
						const amountSuggItems = jQuery('#jform_amount_suggestions_tags').val().trim();

						if (amountSuggItems !== "") {
							const amountSuggItemsArr = amountSuggItems.split(",");
							for (var i = 0; i < amountSuggItemsArr.length; i++) {
								const [cancel, message] = jgiveCommon.campaignForm.validateAmountSuggestion(amountSuggItemsArr[i]);
								if (cancel) {
									alert(message);
									isValid = false;
									break;
								}
							}
						}
					}

					if (isValid) {
						jgiveCommon.campaignForm.save(task);
						return true;
					} else {
						return false;
					}
				}
			}

			jQuery(document).ready(function () {
				jgiveCommon.lifetimeCampaign();
			});

			jQuery(document).ready(function() {
				var campaignId = jQuery("#jform_id").val();

				jQuery.each(mediaGallery, function(key, media) {
					tjMediaFile.previewFile(media, 1, '');
				});
				jgive.campaign.getJSGroupField();

				jQuery('#jform_amount_suggestions_tags').on('beforeItemAdd', function(event) {
					const [cancel, message] = jgiveCommon.campaignForm.validateAmountSuggestion(event.item);
					if (cancel) {
						alert(message);
						event.cancel = cancel;
					}
				});
			});
		}
	},

	donations: {
		getGiveBackAgainstCampaign: function(cid){
			techjoomla.jQuery(document).ready(function(){
				var select = techjoomla.jQuery('#givebacks');
				select.find('option').remove().end();

				techjoomla.jQuery.ajax({
					url:'index.php?option=com_jgive&task=donations.getGiveBackAgainstCampaign&tmpl=component',
					type:'POST',
					dataType:'json',
					data:{
						cid:cid
					},
					success:function(response) {
						var desc_flag  = 0;
						var desc_index = 0;

						var op = "<option value='0' >"+Joomla.JText._('COM_JGIVE_GIVEBACKS_TOOLTIP')+"</option>";
						techjoomla.jQuery('#givebacks').append(op);

						for (var i = 0;i<response.length;i++) {
							if(response[i]['sold_out']==0) {
								var op = "<option value="+response[i]['id']+" >"+response[i]['title']+"</option>";
								techjoomla.jQuery('#givebacks').append(op);
								if(desc_flag == 0) {
									desc_flag = 1;
									desc_index = i;
								}
							}
						}

						givebackDetails= response;

						if (desc_flag ==1) {
							techjoomla.jQuery("#show_giveback_box").show();
							if (response[desc_index]['description'] != '') {
								techjoomla.jQuery("#hide_giveback_desc").removeClass('d-none');
							}
						} else {
							techjoomla.jQuery("#show_giveback_box").hide();
						}
					},
					error:function() { 
					}
				});
			});
		},

		fillprofiledata: function(data){
			if(data.first_name!='') {
				jQuery('#first_name').val(data.first_name);
			}

			if(data.last_name!='') {
				jQuery('#last_name').val(data.last_name);
			}

			if(data.paypal_email!='') {
				jQuery('#paypal_email').val(data.paypal_email);
			}

			if(data.address!='') {
				jQuery('#address').val(data.address);
			}

			if(data.address2!='') {
				jQuery('#address2').val(data.address2);
			}

			if(data.paypal_email!='') {
				jQuery('#address2').val(data.paypal_email);
			}

			if(data.address2!='') {
				jQuery('#address2').val(data.address2);
			}

			if(data.zip!='') {
				jQuery('#zip').val(data.zip);
			}

			if(data.phone!='') {
				jQuery('#phone').val(data.phone);
			}
		},

		otherCity: function(){
			if(document.adminForm.other_city_check.checked===true) {
				jQuery("#other_city").show('slow');
				jQuery("#hide_city").hide('slow');
			} else {
				jQuery("#hide_city").show('slow');
				jQuery("#other_city").hide('slow');
			}
		},

		/** Populate selectd giveback amount in donation amount field */
		populateGiveback: function(){
			var giveBackid= techjoomla.jQuery('#givebacks').val();
			var cid= techjoomla.jQuery('#campaign_id').val();

			if(giveBackid == 'edit_amount') {
				techjoomla.jQuery('#giveback_des').text(Joomla.JText._('COM_JGIVE_GIVE_NOT_AVIL_FOR_THIS_AMOUNT'));
			}

			if (giveBackid != 0) {
				for(var index = 0; index < givebackDetails.length; index++) {
					if(givebackDetails[index]['id']==giveBackid) {
						// Update amount in donation amount field
						techjoomla.jQuery('#donation_amount').val(parseFloat(givebackDetails[index]['amount']));

						// Update giveback description
						techjoomla.jQuery('#giveback_des').text(Joomla.JText._('COM_JGIVE_GIVEBACK_DESC')+' : '+givebackDetails[index]['description']);
						com_jgive.UI.Donation.updatePlatformFee(document.getElementById("donation_amount"),cid);
					}
				}
			} else {
				techjoomla.jQuery('#donation_amount').val(window.minimun_amount);
				techjoomla.jQuery('#hide_giveback_desc').addClass('d-none');
				com_jgive.UI.Donation.updatePlatformFee(document.getElementById("donation_amount"),cid);
			}
		},

		noGiveBack: function(){
			var no_giveback=techjoomla.jQuery( "input:checkbox[name=no_giveback]:checked" ).val();

			if(no_giveback==undefined) {
				techjoomla.jQuery("#hide_giveback").show("slow");
				techjoomla.jQuery("#hide_giveback_desc").show("slow");
			} else {
				techjoomla.jQuery("#hide_giveback").hide("slow");
				techjoomla.jQuery("#hide_giveback_desc").hide("slow");
			}
		},

		validateGiveBackAmount: function(donation_amount){
			var no_giveback=techjoomla.jQuery( "input:checkbox[name=no_giveback]:checked" ).val();

			// Get the value from a dropdown select
			var givebackId = techjoomla.jQuery( "#givebacks").val();

			if(no_giveback == undefined) {
				/** global: givebackDetails */
				for(var index = 0; index < givebackDetails.length; index++) {
					if(givebackDetails[index]['id']==givebackId) {
						var givebackAmount = givebackDetails[index]['amount'];

						if(donation_amount>=givebackAmount) {
							return true;
						} else {
							alert(Joomla.JText._("COM_JGIVE_AMOUNT_SHOULD_BE") + givebackAmount);
							return false;
						}
					}
				}
			}
			return true;
		},

		jGive_toggle_checkout: function(radio){
			if(parseInt(radio)==0) {
				techjoomla.jQuery('.jgive_select_user').show();
				techjoomla.jQuery('#donor_name').addClass('required');
				techjoomla.jQuery('#donor_id').addClass('required');
			} else if(parseInt(radio)==1){
				techjoomla.jQuery('.jgive_select_user').hide();
				techjoomla.jQuery('#donor_name').removeClass('required');
				techjoomla.jQuery('#donor_id').removeClass('required');
				techjoomla.jQuery('#donor_name').removeClass('invalid');
				techjoomla.jQuery('#donor_id').removeClass('invalid');
				techjoomla.jQuery('#donor_id').val();
				techjoomla.jQuery('#donor_name').val();
			}
		},

		jGive_RecurDonation: function(radio_option){
			if(radio_option==0) {
				techjoomla.jQuery('#recurring_freq_div').hide('slow');
				techjoomla.jQuery('#recurring_count_div').hide('slow');
				techjoomla.jQuery('#recurring_count').removeClass('required');
			} else if(radio_option==1) {
				techjoomla.jQuery('#recurring_count').addClass('required');
				techjoomla.jQuery('#recurring_freq_div').show('slow');
				techjoomla.jQuery('#recurring_count_div').show('slow');
			}
		},

		chkmail: function(email){
			techjoomla.jQuery.ajax({
				url: '?option=com_jgive&task=donations.chkmail&email='+email+'&tmpl=component&format=raw',
				type: 'GET',
				dataType: 'json',
				success: function(data) {
					if(data[0] == 1){
						techjoomla.jQuery('#email_reg').html(data[1]);
						techjoomla.jQuery("#button-billing-info").attr("disabled", "disabled");
					} else {
						techjoomla.jQuery('#email_reg').html('');
						techjoomla.jQuery("#button-billing-info").removeAttr("disabled");
					}
				}
			});
		},
	},
};

/**
 * Common Function which is used Frontend backend both
 */
var jgiveCommon = {
	selectstatusorder: function(appid,ele) {
		var selInd=ele.selectedIndex;
		var status =ele.options[selInd].value;
		document.getElementById('hidid').value = appid;
		document.getElementById('hidstat').value = status;
		Joomla.submitform('donations.save', document.getElementById('adminForm'));

		return;
	},

	lifetimeCampaign: function () {
		const startDateField = document.querySelector("input[name='jform[start_date]']");
		const endDateField = document.querySelector("input[name='jform[end_date]']");
		const endDateWrapper = endDateField.closest('.form-group') || endDateField.closest('div.control-group');
		const form = document.querySelector('form');

		function disableValidation(field) {
			field.removeAttribute("required");
			field.classList.remove("validate-enddateverify");
		}

		function enableValidation(field) {
			field.setAttribute("required", "true");
			field.classList.add("validate-enddateverify");
		}

		function setLifetimeEndDate() {
			const startDateValue = startDateField.value;
			const baseDate = startDateValue ? new Date(startDateValue) : new Date();
			baseDate.setFullYear(baseDate.getFullYear() + 100);

			const year = baseDate.getFullYear();
			const month = String(baseDate.getMonth() + 1).padStart(2, '0');
			const day = String(baseDate.getDate()).padStart(2, '0');
			const hours = String(baseDate.getHours()).padStart(2, '0');
			const minutes = String(baseDate.getMinutes()).padStart(2, '0');
			const endDateValue = `${year}-${month}-${day} ${hours}:${minutes}`;

			endDateField.value = endDateValue;

			if (endDateWrapper.style.display === 'none') {
				removeHiddenEndDate();
				const hiddenInput = document.createElement('input');
				hiddenInput.type = 'hidden';
				hiddenInput.name = 'jform[end_date]';
				hiddenInput.value = endDateValue;
				form.appendChild(hiddenInput);
			}
		}

		function removeHiddenEndDate() {
			const hiddenField = document.querySelector('input[type="hidden"][name="jform[end_date]"]');
			if (hiddenField) {
				hiddenField.remove();
			}
		}

		function toggleEndDateField() {
			const selectedValue = document.querySelector("input[name='jform[lifetimecamp]']:checked")?.value;

			if (selectedValue === "1") {
				setLifetimeEndDate();
				disableValidation(endDateField);
				endDateWrapper.style.display = 'none';
			} else {
				enableValidation(endDateField);
				endDateWrapper.style.display = '';

				const currentEndDate = new Date(endDateField.value);
				const now = new Date();
				const hundredYearsFromNow = new Date();
				hundredYearsFromNow.setFullYear(now.getFullYear() + 99);

				if (currentEndDate.getFullYear() >= hundredYearsFromNow.getFullYear()) {
					endDateField.value = '';
				}

				removeHiddenEndDate();
			}
		}

		document.querySelectorAll("input[name='jform[lifetimecamp]']").forEach(option => {
			option.addEventListener("change", toggleEndDateField);
		});

		startDateField.addEventListener("change", () => {
			const selectedValue = document.querySelector("input[name='jform[lifetimecamp]']:checked")?.value;
			if (selectedValue === "1") {
				setLifetimeEndDate();
			}
		});

		if (form) {
			form.addEventListener("submit", () => {
				const selectedValue = document.querySelector("input[name='jform[lifetimecamp]']:checked")?.value;
				if (selectedValue === "1") {
					setLifetimeEndDate();
					console.log("Lifetime campaign submitted. End Date:", endDateField.value);
				} else {
					console.log("Normal campaign submitted. End Date:", endDateField.value);
				}
			});
		}

		toggleEndDateField();
	},

	copyShareLink: function(){
		let copyText = document.getElementById('campaignLink')
		copyText.select();
		document.execCommand('copy');
		document.getElementById('copyCampaignLinkMsg').style.display = "block";
	},

	getRoundedAmount: function(amount, jgiveBaseUrl) {
		var errorMsg = '';
		jQuery.ajax({
			type: "GET",
			dataType: "json",
			async:false,
			url: jgiveBaseUrl + "index.php?option=com_jgive&task=donations.getRoundedValue&amount=" + amount,
			success:function(result) {
				// TODO - Commented the code below as the amount do not match for some currencies like naira - we might need to revert this in future

				/*
				if (result.data != amount) {
					roundedAmount = result.data;
					errorMsg = Joomla.JText._('COM_JGIVE_VALIDATE_ROUNDED_AMOUNT').concat(roundedAmount);
				}*/
			},
		});

		return errorMsg;
	},

	validateAmountAsPerCurrency: function (inputField, allowPAN="no", pan_amount_limit=0) {
		if(allowPAN == 'yes')
		{
			if(pan_amount_limit > 0)
			{
				if (parseInt(jQuery(inputField).val()) < pan_amount_limit)
				{
					jQuery('.pan').addClass('d-none');
					jQuery('#pannumber').removeClass('required');

				}
				else
				{
					jQuery('#pannumber').addClass('required');
					jQuery('.pan').removeClass('d-none');
				}
			}
		}

		//code to check if high value donations are allowed. if no then model will be displayed to donor and allowed to submit contact form.
		//admin/site owner will receive email and site owner will contact donor manually.

		hidden_allow_high_value_donation = jQuery("#hidden_allow_high_value_donation").val();
		hidden_max_allow_high_donation_value = jQuery("#hidden_max_allow_high_donation_value").val();
		if (jQuery(inputField).hasClass('check-amount-limit') && hidden_allow_high_value_donation == 0)
        {
            if (parseInt(jQuery(inputField).val()) > hidden_max_allow_high_donation_value)
            {
                jQuery('#amoutExceedModal').modal('show');
                jQuery(inputField).val('');

                return;
            }
        }
		
		let returnValue = jgiveCommon.getRoundedAmount(jQuery(inputField).val(), jgiveBaseUrl);

		if (returnValue) {
			jQuery(inputField.id).focus();
			let amountValidationFailedMsg =  Joomla.JText._('COM_JGIVE_INVALID_FIELD').concat(returnValue);
			Joomla.renderMessages({'error':[amountValidationFailedMsg]});
			jQuery("html, body").animate({
				scrollTop: 0
			}, 500);

			return false;
		}

		return true;
	},

	validations: {
		validationEndDate: function(endDateObj) {
			var endDate   = jQuery(endDateObj).val();
			var startDate = jQuery('#jform_start_date').val();

			jQuery(document).ready(function(){
				document.formvalidator.setHandler('enddateverify', function (value) {
					return (startDate < endDate);
				});
			});
		},
		validationAmount: function(){
			var goal_amount = jQuery('#jform_goal_amount').val();
			var minimum_amount = jQuery('#jform_minimum_amount').val();

			jQuery(document).ready(function(){
				document.formvalidator.setHandler('Goalamount', function () {
					return (Number(goal_amount) > Number(minimum_amount));
				});
				document.formvalidator.setHandler('minimumamount', function () {
					return (Number(goal_amount) > Number(minimum_amount));
				});
			});
		},
		validationGiveawayAmount: function(giveaway){
			var minimum_amount = jQuery('#jform_minimum_amount').val();

			if (minimum_amount > 0) {
				jQuery(document).ready(function(){
					document.formvalidator.setHandler('giveawayAmount', function () {
						return (Number(minimum_amount) <= Number(giveaway.value));
					});
				});
			}
		}
	},

	campaignForm: {
		save: function(task){
			var goalAmount      = jQuery('#jform_goal_amount').val();
			var minDonation     = jQuery('#jform_minimum_amount').val();
			var maxDonors       = jQuery('#jform_max_donors').val();
			var endDate         = jQuery('#jform_end_date').val();
			var startDate       = jQuery('#jform_start_date').val();
			var oldImage        = jQuery('#jform_campaign_old_image').val();
			var newImage        = jQuery('#jform_campaign_image').val();
			var daysLimit       = jQuery('#jform_days_limit').val();
			var currentImage    = jQuery('#jform_image').val();
			var flag            = true;
			var termsConditions = jQuery('#jform_terms_condition').is(":checked");
			if (task == "campaign.cancel" || task == "campaign.close") {
				return false;
			}

			/** global: isAdmin */
			if (isAdmin == 0) {
				/** global: termsConditionsConfig */
				/** global: termsConditionsArticleId */
				if (termsConditionsConfig != 0 && termsConditionsArticleId != 0) {
					if(termsConditions === false) {
						let termsAndConditonsFailureMsg = Joomla.JText._('COM_JGIVE_CHECK_TERMS');
						Joomla.renderMessages({'error':[termsAndConditonsFailureMsg]});
						jQuery("html, body").animate({
							scrollTop: 0
						}, 500);

						return false;
					}
				}
			}

			jQuery('input,textarea,select').filter('[required]:visible').not(':input[type=file]').each(function(){
				var currentVal = jQuery(this).val();

				if (!currentVal) {
					flag = false;
				}
			});

			if (flag === false) {
				var campaignCreateFailureMsg = Joomla.JText._('COM_JGIVE_CREATE_CAMPAIGN_VALIDATION_FAIL_MSG');
				Joomla.renderMessages({'error':[campaignCreateFailureMsg]});
				jQuery("html, body").animate({
					scrollTop: 0
				}, 500);

				return false;
			} else {
				jgiveCommon.validateAmountAsPerCurrency(jQuery('#jform_goal_amount'));

				if (Number(goalAmount) < Number(minDonation)) {
					var goalAmountFailureMsg = Joomla.JText._('COM_JGIVE_CREATE_CAMPAIGN_MINDONATION_FAIL_MSG');
					Joomla.renderMessages({'error':[goalAmountFailureMsg]});
					jQuery("html, body").animate({
						scrollTop: 0
					}, 500);

					return false;
				}

				if (((maxDonors != undefined || maxDonors != null) && Number(maxDonors) < 0) || ((maxDonors != undefined || maxDonors != null) && Number(minDonation) < 0 )) {
					var negativeNumberFailureMsg = Joomla.JText._('COM_JGIVE_CREATE_CAMPAIGN_NEGATIVE_NUMBER_ERROR_MSG');
					Joomla.renderMessages({'error':[negativeNumberFailureMsg]});
					jQuery("html, body").animate({
						scrollTop: 0
					}, 500);

					return false;
				}

				if (startDate > endDate) {
					var dateFailureMsg = Joomla.JText._('COM_JGIVE_CREATE_CAMPAIGN_DATEVALIDATION_FAIL_MSG');
					Joomla.renderMessages({'error':[dateFailureMsg]});
					jQuery("html, body").animate({
						scrollTop: 0
					}, 500);

					return false;
				}

				if (minDonation > 0) {
					var donationFlagError = false;

					jQuery('.subform_givebacks_amount').each(function(){
						var giveAwayValue = jQuery(this).val();

						if (parseFloat(minDonation) > parseFloat(giveAwayValue)) {
							var givebackFailureMsg = Joomla.JText._('COM_JGIVE_CREATE_CAMPAIGN_GIVEAWAY_FAIL_MSG');
							Joomla.renderMessages({'error':[givebackFailureMsg]});
							jQuery("html, body").animate({
								scrollTop: 0
							}, 500);

							return false;
						}
					});

					if (donationFlagError) {
						var minimumAmountFailureMsg = Joomla.JText._('COM_JGIVE_CREATE_CAMPAIGN_GIVEAWAY_FAIL_MSG');
						Joomla.renderMessages({'error':[minimumAmountFailureMsg]});
						jQuery("html, body").animate({
							scrollTop: 0
						}, 500);

						return false;
					}
				}

				if(isNaN(daysLimit) || parseInt(daysLimit) <= 0) {
					var dayLimitFailureMsg = Joomla.JText._('COM_JGIVE_CAMPAIGNFORM_END_DATE_DAYS_LIMIT_INVALID_INPUT');
					Joomla.renderMessages({'error':[dayLimitFailureMsg]});
					jQuery("html, body").animate({
						scrollTop: 0
					}, 500);

					return false;
				}

				// validate suggestion amount before saving
				const amountSuggItems = jQuery('#jform_amount_suggestions_tags').val().trim();

				if (amountSuggItems !== "") {
					const amountSuggItemsArr = amountSuggItems.split(",");
					var isValid = true;
					for (var i = 0; i < amountSuggItemsArr.length; i++) {
						const [cancel, message] = jgiveCommon.campaignForm.validateAmountSuggestion(amountSuggItemsArr[i]);
						if (cancel) {
							alert(message);
							isValid = false;
							break;
						}
					}
					if(!isValid){
						return isValid;
					}
				}

				Joomla.submitform(task);
				return true;
			}
		},

		validateAmountSuggestion: function(item) {
			var tag = item;
			if ( /^\d+$/.test(tag)) {
				// amount is in valid format
					
				var min_donation_amount = jQuery("#jform_minimum_amount").val();
				var max_donation_amount = jQuery("#jform_goal_amount").val();
	
				if (min_donation_amount != 0 && parseInt(tag) < min_donation_amount) {
					// amount suggestion should be greater than min donation amount
					
					return [true, Joomla.JText._('COM_JGIVE_AMOUNT_SUGGESTION_VALIDATION_FAILED_MESSAGE_MIN_AMOUNT')];
				} 
				else if (max_donation_amount != 0 && parseInt(tag) > max_donation_amount) {
					// amount suggestion should be less than donation goal amount
	
					return [true, Joomla.JText._('COM_JGIVE_AMOUNT_SUGGESTION_VALIDATION_FAILED_MESSAGE_MAX_AMOUNT')];
				}
				else{
					// amount is valid
	
					return [false, ''];
				}
			}
			else {
				// amount is in invalid format
				
				return [true, Joomla.JText._('COM_JGIVE_AMOUNT_SUGGESTION_VALIDATION_FAIL_MESSAGE')];
			}
		}

	},

	reportForm: {
		submit: function(task){
			if (task == 'reportform.cancel') {
				Joomla.submitform(task, document.getElementById('adminForm'));
			} else if (task == 'reportform.save' && document.formvalidator.isValid(document.getElementById('adminForm'))) {
				Joomla.submitform(task, document.getElementById('adminForm'));
			} else {
				return false;
			}
		}
	},

	filters:{
		submitFilters: function(form){
			// Get current URL
			let currentUrl = window.location.href;

			// Get data from form
			let formData = jQuery('#'+form).serializeArray();
			var urlParams = currentUrl.split('?');
			let submitUrl = urlParams[0];
			var paramsObj = {};

			if (urlParams[1] != undefined) {
				urlParams = urlParams[1].split('&');

				jQuery.each(urlParams, function(index, param){
					let tmp = param.split('=');

					if (tmp[1] != undefined) {
						if (tmp[1]) {
							paramsObj[tmp[0]] = tmp[1];
						}
					}
				});
			}

			jQuery.each(formData, function(index, filter){
				/* Variable 'tjListFilters' is defined in the layout file from which filters are rendered*/
				let valid = jQuery.inArray(filter.name, tjListFilters);

				if (valid != '-1') {
					if (filter.value != '') {
						paramsObj[filter.name] = filter.value;
					} else {
						if(filter.name == 'filter_campaign_countries'){
							delete paramsObj['filter_campaign_states'];
							delete paramsObj['filter_campaign_city'];
						}
						else if(filter.name == 'filter_campaign_states'){
							delete paramsObj['filter_campaign_city'];
						}
						else
						{ 
							delete paramsObj[filter.name];
						}
					}
				}
			});

			var queryString = '';
			let count = 0;

			jQuery.each(paramsObj, function(param, value) {
				if (count == 0) {
					queryString += '?'+param+'='+value;
				} else {
					queryString += '&'+param+'='+value;
				}

				count++;
			});

			window.location = submitUrl+queryString;
		}
	},
	getPanVerification: function(pannumber) {
		var errorMsg = '';
		jQuery.ajax({
			type: "GET",
			dataType: "json",
			async:false,
			url: jgiveBaseUrl + "index.php?option=com_jgive&task=donations.panVerification&pannumber=" + pannumber,
			success:function(result) {
				jgive.donations.hiderequestProcessingImg();
				if(result.data.length > 0) {
					const responseData = result.data[0];
					if(responseData.status === "success") {
						alert(responseData.message);
						
					}
					else if( responseData.status === "error") {
						alert(responseData.message);
						jQuery('#pannumber').val('');
						return false;
					}

				}
				else {
					const responseData = result.data;
					if ( responseData.status == "error") {
						alert(responseData.message);
						jQuery('#pannumber').val('');
						return false;
					}

				}
			}
		});

		return errorMsg;
	},
};

/* Media Store. update, delete*/
var tjMediaFile = {
	// Checks image upload limit
	validateImageUploadLimit: function() {
		let existingImages = jQuery('*[class*=media_image_width]:visible').length;

		/** global: allowedMediaCount */
		if (existingImages < allowedMediaCount) {
			return true;
		}

		return false;
	},

	// // Checks video upload limit
	validateVideoUploadLimit: function() {
		let existingVideos = jQuery('*[class*=media_video_width]:visible').length;

		/** global: allowedVideoCount */
		if (existingVideos < allowedVideoCount) {
			return true;
		}

		return false;
	},

	// Checks file validations
	validateFile: function(thisFile, isGallary, isAdmin) {
		var uploadType = jQuery(thisFile).attr('type');
		jQuery('#system-message-container').empty();
		var invalid = 0;

		// Checks to differrentiate between the uploaded file and the link
		if (uploadType == 'file') {
			var uploadedfile = jQuery(thisFile)[0].files[0];

			if (typeof(uploadedfile) !== 'undefined') {
				var uploadedFileType = uploadedfile.type;
				var fileType = uploadedFileType.split("/");

				/** global: allowedFileExtensions */
				var allowedExtensionsArray = allowedFileExtensions.split(",");

				if(fileType[0] === "image") {

					if(tjMediaFile.validateImageUploadLimit() === false) {
						invalid = "imageUploadLimitExceed"
					}

					mediaSize = imageUploadLimit;
					var uploadedFileSize = uploadedfile.size/1000;

					if(uploadedFileSize > mediaSize) {
						invalid = "imageSizeLimitExceed";
					}
				} else if(fileType[0] === "video") {
					if(tjMediaFile.validateVideoUploadLimit() == false) {
						var jmsgs = [Joomla.JText._('COM_JGIVE_VIDEO_UPLOAD_LIMIT_EXCEED')];
						Joomla.renderMessages({
							'warning': jmsgs
						});

						jQuery("html, body").animate({
							scrollTop: 0
						}, 500);

						return false;
					}

					mediaSize = videoUploadLimit;
					var uploadedFileSize = uploadedfile.size/1000000;
					if(uploadedFileSize > mediaSize || jQuery.inArray(fileType[1], allowedExtensionsArray) === -1) {
						invalid = "videoSizeLimitExceed";
					}
				} else {
					var jmsgs = [Joomla.JText._('COM_JGIVE_MEDIA_INVALID_FILE_TYPE')];
					Joomla.renderMessages({
						'warning': jmsgs
					});

					jQuery("html, body").animate({
						scrollTop: 0
					}, 500);
				}

				if (invalid != '') {
					if(invalid === "imageUploadLimitExceed") {
						var jmsgs = [Joomla.JText._('COM_JGIVE_IMAGE_UPLOAD_LIMIT_EXCEED')];
						Joomla.renderMessages({
							'warning': jmsgs
						});

						jQuery("html, body").animate({
							scrollTop: 0
						}, 500);
					} else {
						jmsgs = [Joomla.JText._('COM_JGIVE_MEDIA_UPLOAD_ERROR')];
						Joomla.renderMessages({
							'warning': jmsgs
						});

						jQuery("html, body").animate({
							scrollTop: 0
						}, 500);
					}

					return false;
				} else {
					tjMediaFile.uploadFile(uploadedfile, thisFile, uploadType, isGallary, isAdmin);
				}
			}
		}
		else
		{
			if(tjMediaFile.validateVideoUploadLimit() === false) {
				invalid = "videoUploadLimitExceed";
			}

			if(invalid === "videoUploadLimitExceed") {
				jmsgs = [Joomla.JText._('COM_JGIVE_VIDEO_UPLOAD_LIMIT_EXCEED')];
				Joomla.renderMessages({
					'warning': jmsgs
				});

				jQuery("html, body").animate({
					scrollTop: 0
				}, 500);

				return false;
			}

			var fileLink = jQuery('#jform_gallery_link').val();
			fileLink = tjMediaFile.validateUrl(fileLink);

			if (!fileLink) {
				var jmsgs = [Joomla.JText._('COM_TJMEDIA_VALIDATE_URL')];
				Joomla.renderMessages({
					'warning': jmsgs
				});

				jQuery("html, body").animate({
					scrollTop: 0
				}, 500);

				return false;
			}

			tjMediaFile.uploadFile(fileLink, thisFile, 'link', isGallary, isAdmin);
		}
	},

	// File upload code
	uploadFile: function(uploadedfile, thisFile, uploadType, isGallary, isAdmin) {
		var mediaformData = new FormData();

		// Code for uploading file and link
		if (uploadType == 'file') {
			mediaformData.append('file[]', uploadedfile);
			mediaformData.append('upload_type', uploadType);
			mediaformData.append('isGallary', isGallary);
		} else if (uploadType == 'link') {
			mediaformData.append('upload_type', uploadType);
			mediaformData.append('name', uploadedfile['link']);
			mediaformData.append('type', uploadedfile['type']);
		}

		var url = Joomla.getOptions('system.paths').base + "/index.php?option=com_jgive&format=json&task=campaignform.uploadMedia&tmpl=component";

		this.ajaxObj = jQuery.ajax({
			type: "POST",
			url: url,
			dataType: 'JSON',
			contentType: false,
			processData: false,
			data: mediaformData,
			xhr: function() {
				var myXhr = jQuery.ajaxSettings.xhr();

				if (myXhr.upload) {
					myXhr.upload.addEventListener('progress', function(e) {

						if (e.lengthComputable) {
							var percentage = Math.floor((e.loaded / e.total) * 100);
							tjMediaFile.progressBar.updateStatus(thisFile.id, percentage);
						}
					}, false);
				}

				return myXhr;
			},

			beforeSend: function(x) {
				tjMediaFile.progressBar.init(thisFile.id, '');
			},

			success: function(data) {
				if (isGallary == 1) {
					if (data.data[0].type == 'video.youtube' || data.data[0].type === 'video.vimeo') {
						jQuery('#jform_gallery_link').val('');
					} else {
						jQuery(thisFile).val('');
					}
				}

				if (data.success) {
					var string = thisFile.id;
					var substring = "givebacks";
					var substringStories = "beneficiaryStories";
					var theForm = document.forms['adminForm'];

					if (string.includes(substring)) {
						jQuery(theForm).append('<input type="hidden" name="' + thisFile.name + '" value="' + data.data[0].id + '" />');
					}
					// Handle beneficiary stories
					if (string.includes(substringStories)) {
						jQuery(theForm).append('<input type="hidden" name="' + thisFile.name + '" value="' + data.data[0].id + '" />');
					}
					if (jQuery('#jform_title').length > 0) {
						jQuery(theForm).append('<input type="hidden" name="jform[media_id]" value="' + data.data[0].id + '" />');
					}
					tjMediaFile.previewFile(data.data[0], isGallary, thisFile);
					tjMediaFile.progressBar.statusMsg(thisFile.id, 'success', data.message);
				} else {
					jQuery(thisFile).val('');
					jQuery('#' + thisFile.id).siblings('.progress').remove();
					tjMediaFile.progressBar.statusMsg(thisFile.id, 'error', data.message);
				}
			},

			error: function(xhr, status, error) {
				if (isAdmin === 0) {
					var jmsgs = [Joomla.JText._('COM_JGIVE_MEDIA_INVALID_FILE_TYPE')];
					Joomla.renderMessages({
						'warning': jmsgs
					});

					jQuery("html, body").animate({
						scrollTop: 0
					}, 500);

					return false;
				} else {
					tjMediaFile.progressBar.statusMsg(thisFile.id, 'error', error);
				}
			}
		});
	},

	// Displaying the media data
	previewFile: function(data, isGallary, thisFile) {
		if (data.id) {
			if (isGallary == 1) {
				tjMediaFile.tjMediaGallery.appendMediaToGallary(data);
			} else {
				var string = thisFile.id;
				var substring = "jform_image";
				if (string.includes(substring)) {
					//jQuery('#uploaded_media').attr('src', jgive_baseurl+data[campaignMainImage]);
					jQuery('#uploaded_media').attr('src', data[campaignMainImage]);
					jQuery('#uploaded_media').closest('.thumbnails').removeClass('hide_jgdiv');
					jQuery('#jform_campaign_old_image').val(jQuery('#jform_campaign_image').val());
					jQuery('#jform_campaign_image').val(data.id);
				}
			}
		}

		return false;
	},

	// Setting default values to selected media
	defaultMedia: function(currentDiv,cid) {
		var mediaId = jQuery(currentDiv).attr("data-info");
		var galleryMediaData = [];

		jQuery('#gallery_media input[data-info]').each(function(){
			var currentMediaId = jQuery(this).attr('data-info');

			if (typeof currentMediaId != undefined) {
				if (currentMediaId !== mediaId) {
					// Set default value to 0 for unselected media
					galleryMediaData.push({
						"mediaId": currentMediaId,
						"default":  0
					});
				} else {
					galleryMediaData.push({
						"mediaId": mediaId,
						"default":  1
					});
				}
			}
		});

		tjMediaFile.setDefaultMedia(galleryMediaData,cid);
	},

	// Remove default media
	setDefaultMedia: function(galleryMediaData,cid) {
		url = Joomla.getOptions('system.paths').baseFull + "index.php?option=com_jgive&format=json&task=campaignform.setDefaultMedia";

		jQuery.ajax({
			type: "POST",
			url: url,
			dataType: 'JSON',
			data: {
				mediaIds: galleryMediaData,
				cid: cid
			},
			success: function(data) {
				alert(Joomla.JText._('COM_TJMEDIA_MEDIA_SET_TO_DEFAULT'));
			},
			error: function(xhr){
				alert(Joomla.JText._('COM_TJMEDIA_MEDIA_SET_TO_DEFAULT_ERROR'));
			}
		});
	},

	// Validating the URL
	validateUrl: function(url) {
		if (url != undefined || url != '') {
			var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
			var match = url.match(regExp);
			var urlData = [];

			if (match && match[2].length == 11) {
				urlData['link'] = 'https://www.youtube.com/embed/' + match[2] + '?enablejsapi=1';
				urlData['type'] = 'youtube';
				return urlData;
			} else {
				var regVimeoExp = /(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:[a-zA-Z0-9_\-]+)?/i;
				var match = url.match(regVimeoExp);

				if (match) {
					urlData['link'] ='https://player.vimeo.com/video/' + match[1];
					urlData['type'] ='vimeo';
					return urlData;
				} else {
					return false;
				}
			}
		}
	},

	// Progress bar code while file upload
	progressBar: {
		init: function(divId, msg) {

			if (divId !== '')
			{
				jQuery('#' + divId).siblings('.alert').remove();
				jQuery('#' + divId).siblings('.progress').remove();
			}

			this.progress = jQuery("<div class='progress progress-striped progress-bar-success active'><div class='bar'></div><button onclick='return tjexport.abort();' class='btn btn-danger btn-small pull-right'>Abort</button></div>");
			this.statusBar = this.progress.find('.bar');
			this.abort = jQuery("<div class='abort'><span>Abort</span></div>").appendTo(this.statusbar);
			jQuery('.creatcamp__imgprogress').html(this.progress);

			if (divId == 'jform_image') {
				if (jQuery('#campaign-progress-bar').length > 0){
					jQuery('#campaign-progress-bar').removeClass('d-none');
				}else{
					jQuery('.creatcamp__imgprogress').parent().removeClass('d-none');
				}
			}
		},

		updateStatus: function(divId, percentage) {
			this.statusBar.css("width", percentage + '%');
			this.statusBar.text(percentage + '%');
		},

		abort: function() {
			if (!confirm(Joomla.JText._('LIB_TECHJOOMLA_CSV_EXPORT_CONFIRM_ABORT'))) {
				return false;
			}

			this.ajaxObj.abort();
		},

		statusMsg: function(divId, alert, msg) {
			setTimeout(function() {
				if (divId !== '')
				{
					jQuery('#' + divId).siblings('.progress').remove();
				}
			}, 2000);

			var closeBtn = "<a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>";
			var msgDiv = jQuery("<div class='alert alert-" + alert + "'><strong>" + msg + "</strong>" + closeBtn + "</div>");

			if (divId !== '')
			{
				jQuery('#' + divId).closest('.controls').append(msgDiv);
			}
		}
	},

	tjMediaGallery: {
		// Displaying media in the gallary
		appendMediaToGallary: function(mediaData) {
			var $newMedia = jQuery('.media_gallary_parent .clone_media:first-child').clone();
			var type = mediaData.type.split('.');
			var mediaTag = '';

			if (type[0] === 'video') {
				mediaTag = "<video width='100%' height='100%' class='media_video_width' preload='none' controls><source src="+ mediaData.media + "></video>";

				if (type[1] === 'youtube' || type[1] === 'vimeo') {
					mediaTag = "<iframe width='100%' class='media_video_width' height='100%' src=" + mediaData.source + "> </iframe>";
				}
			} else if (type[0] === 'image') {
				mediaTag = "<div class='media_image_width af-d-block bg-center af-bg-contain af-bg-repn af-responsive-embed af-responsive-embed-16by9' style='background-image: url(" + mediaData.media + ");'></div>";
			}

			$newMedia.removeClass('hide_jgdiv');
			$newMedia.find('.thumbnail').append(mediaTag);
			$newMedia.find(".media_field_value").val(mediaData.id);
			$newMedia.find(".media_field_value").attr('id', 'media_id_' + mediaData.id);
			$newMedia.find("#jform_default_video").attr('data-info', mediaData.id);

			if (mediaData.hasOwnProperty('params') && mediaData.params !== '' && JSON.parse(mediaData.params).default == 1) {
				$newMedia.find("#jform_default_video").attr('checked', true);
			}
			$newMedia.find("#delete_media").attr('data-info', mediaData.id);
			$newMedia.appendTo('.media_gallary_parent');
		},

		// Deleting media
		deleteMedia: function(currentDiv, isAdmin, jtoken, client, clientId) {
			let $currentDiv = jQuery(currentDiv);
			let mediaId = jQuery(currentDiv).attr("data-info");
			let url = Joomla.getOptions('system.paths').baseFull + "index.php?option=com_jgive&format=json&task=campaignform.deleteMedia&"+ jtoken + "=1";

			if (isAdmin) {
				url = Joomla.getOptions('system.paths').baseFull + "index.php?option=com_jgive&format=json&task=campaignform.deleteMedia&" + jtoken + "=1";
			}

			if (!confirm(Joomla.JText._('COM_JGIVE_CONFIRM_DELETE_MEDIA'))) {
				return false;
			}

			jQuery.ajax({
				type: "POST",
				url: url,
				dataType: 'JSON',
				data: {
					id: mediaId,
					client: client,
					clientId:clientId
				},
				success: function(data) {
					$currentDiv.closest('.clone_media').remove();
				},
				error: function(xhr, status, error) {
					tjMediaFile.progressBar.statusMsg(currentDiv.id, 'error', error);
				}
			});
		}
	}
};
