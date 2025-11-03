"use strict";
/** global: com_jgive */

window.com_jgive.UI.Donations = {
	
		showHide:function(divId){
            let divIdArr = divId.split("_");
            if(document.getElementById(divId).style.display == 'none' || document.getElementById(divId).style.display == '')
            {
                document.getElementById(divId).style.display='block';
                jQuery(".manage-comment-more_"+divIdArr[1]).hide();
                jQuery(".manage-comment-less_"+divIdArr[1]).show();
            }
            else
            {
                document.getElementById(divId).style.display = 'none';
                jQuery(".manage-comment-more_"+divIdArr[1]).show();
                jQuery(".manage-comment-less_"+divIdArr[1]).hide();
            }
        }
	
 
}
