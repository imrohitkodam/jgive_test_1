if (typeof(techjoomla) == 'undefined')
{
	var techjoomla = {};
}

if (typeof techjoomla.jQuery == "undefined")
{
	techjoomla.jQuery = jQuery;
}

var databaseFunctions = ['migrateMedia', 'migrateActivities', 'migrateVendorData'];
var fc = 0;
var eachfunprogress = parseFloat(100/databaseFunctions.length,10);

var jgMigration = {

	fixDatabase: function (){
		fc = 0;
		jQuery('.tjBs3').hide();
		jQuery('.fix-database-info').html('<div class="progress-container"></div>');
		jQuery('.fix-database-info').show();
		jQuery('#toolbar-refresh button').prop('disabled', true);

		/* Hide all alerts msgs */
		var obj = jQuery('.fix-database-info .progress-container');
		var status = new this.createProgressbar(obj);

		var eachfunprogress = parseFloat(100/databaseFunctions.length,10);

		databaseFunctions.forEach(function(functiontocall)
		{
			statusdiv = "<div class='"+ functiontocall +" alert alert-plain'>" +
							"<span class='before'>" + Joomla.JText._('COM_JGIVE_TOOLBAR_DATABASE_FIX_' + functiontocall.toUpperCase()) + "</span>" +
							"<span class='after'>" + Joomla.JText._('COM_JGIVE_TOOLBAR_DATABASE_FIX_SUCCESS_MSG')+ "</span>" +
						"</div>";
			jQuery('.fix-database-info').append(statusdiv);
		});

		jgMigration.extecuteFunctions(status);

		return false;
	},

	extecuteFunctions:function (status){
		var functiontocall = databaseFunctions[fc];
		jQuery.ajax({
			url: 'index.php?option=com_jgive&task=migration.' + functiontocall + '&tmpl=component&format=json',
			type: 'POST',
			dataType:'json',
			success: function(response)
			{
				var progressper = parseInt(eachfunprogress * (fc+1));
				status.setProgress(progressper);

				jQuery('.fix-database-info .' + functiontocall).removeClass('alert-plain').addClass('alert-success');
				jQuery('.fix-database-info .' + functiontocall + ' .after').show();

				fc++;

				if (fc < databaseFunctions.length)
				{
					jgMigration.extecuteFunctions(status);
				}
				else
				{
					jQuery('#toolbar-refresh button').prop('disabled', false);
				}
			},
			error: function(jqXHR, textStatus, errorThrown)
			{
				jQuery('.fix-database-info .' + functiontocall).removeClass('alert-plain').addClass('alert-error');
				jQuery('.fix-database-info .' + functiontocall + ' .after').html(jqXHR.responseText);
				jQuery('.fix-database-info .' + functiontocall + ' .after').show();
			}
		});
	},

	createProgressbar: function(obj, bartitle){
		bartitle = bartitle ? bartitle : Joomla.JText._('COM_JGIVE_TOOLBAR_DATABASE_FIX');
		this.statusbar = jQuery("<div></div>");
		this.progressBar = jQuery('<div class="progress progress-striped active progress-bar"><span class="bar progress-bar">' + bartitle + ' <b class="progress_per"></b></div>').appendTo(this.statusbar);

		obj.append(this.statusbar);

		this.setProgress = function(progress)
		{
			this.statusbar.show();
			this.progressBar.show();
			var progressBarWidth =progress*this.progressBar.width()/ 100;
			this.progressBar.find('.progress-bar').animate({ width: progressBarWidth }, 10);
			this.progressBar.find('.progress_per').html(progress + "% ");
		}
	}
}
