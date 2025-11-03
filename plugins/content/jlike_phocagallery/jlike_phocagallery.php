<?php
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
/**
 * @package		jLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

// Import library dependencies
jimport ( 'joomla.plugin.plugin' );
require_once(JPATH_SITE.'/components/com_jlike/helper.php');

$lang =  Factory::getLanguage();
$lang->load('plg_jlike_phocagallery', JPATH_ADMINISTRATOR);

class plgContentJLike_phocagallery extends CMSPlugin {


	function onBeforeDisplaylike($context,$article,$params,$limitstart)
	{
		$app=Factory::getApplication();
		if($app->getName()!='site'){
			return;
		}

		$html='';
		$app = Factory::getApplication ();

		if ($app->scope != 'com_phocagallery') {
			return;
		}

		$uri= Factory::getURI();
		//$route='index.php?'.$uri->getQuery();
		$route=JURI::getInstance()->toString();
		$input=Factory::getApplication()->input;
		$catid=$input->get('cat_id','','INT');
		$cont_id	=	$input->get('id','','INT');

		$element	=	'';
		$input=Factory::getApplication()->input;
		$option=$input->get('option','','STRING');
		$view=$input->get('view','','STRING');
		$layout=$input->get('layout','','STRING');

		$show_like_buttons = 1;
		$show_comments=-1;
		$jlike_comments = $this->params->get('jlike_comments');

		if($jlike_comments)
		{
			if($view=='category')
			{
				//show comments
				$show_comments=1;
			}
		}

		if($option)
			$element	.=	$option;
		if($view)
			$element	.=	'.'.$view;
		if($layout)
			$element	.=	'.'.$layout;
			//print_r(array ('cont_id' => $cont_id, 'element' => $element, 'title' => $article->slug, 'url' => $route ));
		Factory::getApplication()->input->set ( 'data', json_encode ( array ('cont_id' => $cont_id, 'element' => $element, 'title' => $article->slug, 'url' => $route,'plg_name'=>'jlike_phocagallery','show_comments'=>$show_comments, 'show_like_buttons'=>$show_like_buttons) ) );

		require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
		$jlikehelperObj=new comjlikeHelper();
		$html = $jlikehelperObj->showlike();
		return $html;
   }

}
