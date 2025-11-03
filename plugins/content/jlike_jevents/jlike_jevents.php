<?php
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * @package		jLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

// Import library dependencies

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

jimport ( 'joomla.plugin.plugin' );
require_once(JPATH_SITE.'/components/com_jlike/helper.php');

//Load language file
$lang =  Factory::getLanguage();
$lang->load('plg_jlike_jevents', JPATH_ADMINISTRATOR);

class plgContentjlike_jevents extends CMSPlugin {

	function onJEventsHeader($obj)
	{
		$app=Factory::getApplication();
		if($app->getName()!='site'){
			return;
		}

		$html='';
		$app = Factory::getApplication ();

		if ($app->scope != 'com_jevents') {
			return;
		}

		$route=Uri::getInstance()->toString();
		$input=Factory::getApplication()->input;
		$cont_id=$input->get('evid','','INT');
		$task=$input->get('task','','STRING');
		$option=$input->get('option','','STRING');
		$view=$input->get('view','','STRING');

		JLoader::register('JEventsDataModel', JPATH_SITE . "/components/com_jevents/libraries/datamodel.php");
		$dataModel  = new JEventsDataModel("JEventsAdminDBModel");
		$queryModel = new JEventsDBModel($dataModel);
		$cont_id      = intval($cont_id);

		if ($cont_id)
		{
			$eventData = $queryModel->listEventsById($cont_id, 1, "icaldb");
			$eventTitle = isset($eventData->_title) ? $eventData->_title : '';

			//Not to show anything related to commenting
			$show_comments=-1;
			$show_like_buttons =1 ;

			if($task=='icalevent.detail' or $task=='icalrepeat.detail' )
			{
				$element	=	'';

				$element	=	$option.'.icalevent.detail';

				Factory::getApplication()->input->set ( 'data', json_encode ( array ('cont_id' => $cont_id, 'element' => $element, 'title' => $eventTitle, 'url' => $route,'plg_name'=>'jlike_jevents','show_comments'=>$show_comments,'show_like_buttons'=>$show_like_buttons ) ) );

				require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
				$jlikehelperObj=new comjlikeHelper();
				$html = $jlikehelperObj->showlike();
				echo $html;
			}
		}
	}

	function onJEventsFooter()
	{

		$app=Factory::getApplication();
		if($app->getName()!='site'){
			return;
		}

		$html='';
		$app = Factory::getApplication ();

		if ($app->scope != 'com_jevents') {
			return;
		}

		$route=Uri::getInstance()->toString();
		$input=Factory::getApplication()->input;
		$cont_id=$input->get('evid','','INT');
		$task=$input->get('task','','STRING');
		$option=$input->get('option','','STRING');
		$view=$input->get('view','','STRING');
		$show_like_buttons =0;

		//Not to show anything related to commenting
		$show_comments=-1;
		$jlike_comments = $this->params->get('jlike_comments');

		if($jlike_comments)
		{
			//show comment count
			$show_comments=0;

			if($task=='icalevent.detail' or $task=='icalrepeat.detail' )
			{
				//show comments
				$show_comments=1;
			}
		}

		if($task=='icalevent.detail' or $task=='icalrepeat.detail' )
		{
			$element	=	'';

			$element	=	$option.'.icalevent.detail';

			JLoader::register('JEventsDataModel', JPATH_SITE . "/components/com_jevents/libraries/datamodel.php");
			$dataModel  = new JEventsDataModel("JEventsAdminDBModel");
			$queryModel = new JEventsDBModel($dataModel);
			$cont_id      = intval($cont_id);

			if ($cont_id)
			{
				$eventData = $queryModel->listEventsById($cont_id, 1, "icaldb");
				$eventTitle = isset($eventData->_title) ? $eventData->_title : '';

				Factory::getApplication()->input->set ( 'data', json_encode ( array ('cont_id' => $cont_id, 'element' => $element, 'title' => $eventTitle, 'url' => $route,'plg_name'=>'jlike_jevents','show_comments'=>$show_comments,'show_like_buttons'=>$show_like_buttons ) ) );

				require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
				$jlikehelperObj=new comjlikeHelper();
				$html = $jlikehelperObj->showlike();
				echo $html;
			}
		}
   }

}
