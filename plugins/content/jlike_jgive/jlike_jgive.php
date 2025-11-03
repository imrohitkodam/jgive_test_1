<?php
/**
 * @package    JGive
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Component\ComponentHelper;

// Import library dependencies

$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

if (file_exists(JPATH_SITE . '/components/com_jlike/helper.php'))
{
	// Require_once $path;
	JLoader::register('ComjlikeHelper', $helperPath);
	JLoader::load('ComjlikeHelper');
}

// Load language file
$lang = Factory::getLanguage();
$lang->load('plg_jlike_jgive', JPATH_ADMINISTRATOR);

/**
 * JgiveViewReport
 *
 * @package     Jgive
 * @subpackage  Jgive report view class
 * @since       1.2.1
 */
class PlgContentjlike_Jgive extends CMSPlugin
{
	public $params;

	public $jlikehelperObj;

	/**
	 * Class constructor
	 *
	 * @param   string  &$subject  The subject
	 *
	 * @param   string  $config    The config params
	 */
	public function __construct(&$subject, $config)
	{
		$this->params = ComponentHelper::getParams('com_jlike');

		$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';
		$this->jlikehelperObj = '';

		if (file_exists(JPATH_SITE . '/components/com_jlike/helper.php'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeHelper', $helperPath);
			JLoader::load('ComjlikeHelper');

			$this->jlikehelperObj = new ComjlikeHelper;
		}

		parent::__construct($subject, $config);
	}

	/**
	 * Function to display the like / unlike buttons
	 *
	 * @param   string   $context          The context value
	 *
	 * @param   string   $campaign         The campaign data
	 *
	 * @param   integer  $showComments     Whether to show the comments or not
	 *
	 * @param   integer  $showLikeButtons  Whether to show the like buttons or not
	 *
	 * @return  string|null
	 */
	public function onBeforeDisplaylike($context, $campaign, $showComments=-1, $showLikeButtons=0)
	{
		$app = Factory::getApplication();

		if ($app->getName() != 'site')
		{
			return;
		}

		if ($app->scope != 'com_jgive')
		{
			return;
		}

		// Check view & layout to show comments
		$input  = $app->input;
		$view   = $input->get('view', '', 'STRING');
		$layout = $input->get('layout', '', 'STRING');

		if ($showComments != -1)
		{
			// Not to show anything related to commenting
			$showComments = -1;
			$jlikeComments = $this->params->get('jlike_comments');

			if ($jlikeComments)
			{
				if ($view == 'campaign' && $layout == 'default')
				{
					$showComments = 1;
				}
			}
		}

		$input->set('data',
			json_encode(
				array(
					'cont_id'           => $campaign['campaignid'],
					'element'           => $context,
					'title'             => $campaign['title'],
					'url'               => $campaign['url'],
					'plg_name'          => 'jlike_jgive',
					'show_comments'     => $showComments,
					'show_like_buttons' => $showLikeButtons,
					'plg_type'          => 'content'
				)
			)
		);

		$html = '';

		if ($this->jlikehelperObj)
		{
			$html = $this->jlikehelperObj->showlike();
		}

		return $html;
	}

	/**
	 * Function to display the details of the user that liked the content
	 *
	 * @param   integer  $contId  The content id
	 *
	 * @return  object
	 */
	public function getjlike_jgiveOwnerDetails($contId)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('creator_id'));
		$query->from($db->quoteName('#__jg_campaigns'));
		$query->where($db->quoteName('id') . ' = ' . (int) $contId);

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Function to display the comments area
	 *
	 * @param   string   $context  The context value
	 *
	 * @param   object   &$entity  The entity data
	 *
	 * @param   object   &$params  The params data
	 *
	 * @param   integer  $page     The page number value
	 *
	 * @param   string   $url      The entity urls
	 *
	 * @return  string
	 */
	public function onContentAfterDisplay($context, &$entity, &$params, $page = 0, $url = null)
	{
		$input           = Factory::getApplication()->input;
		$showComments    = -1;
		$showLikeButtons = 0;
		$jlike_comments  = $this->params->get('jlike_comments');

		if ($jlike_comments)
		{
			$showComments = 0;
			$view         = $input->get('view', '', 'STRING');

			if ($view == 'report')
			{
				$showComments = 1;
			}
		}

		$showAssignBtn = 0;
		$showRecommend = 0;

		// Here Id is content Id For e.g Report Id in JGive Case
		$contId	= (isset($entity->id)) ? $entity->id : 0;

		// Update report URL to non SEF for action log
		if ($url == '' && isset($entity->campaign_id))
		{
			$url = 'index.php?option=com_jgive&view=report&id=' . $contId . '&cid=' . $entity->campaign_id;
		}

		$input->set(
			'data',
			json_encode(
				array(
					'cont_id'           => $contId,
					'element'           => $context,
					'title'             => isset($entity->title) ? $entity->title : '',
					'url'               => $url,
					'plg_name'          => 'jlike_jgive',
					'show_comments'     => $showComments,
					'show_like_buttons' => $showLikeButtons,
					'showrecommendbtn'  => $showRecommend,
					'plg_type'          => 'content',
					'showassignbtn'     => $showAssignBtn,
					'show_reviews'      => 0
				)
			)
		);

		$html = '';

		if ($this->jlikehelperObj)
		{
			$html = $this->jlikehelperObj->showlike();
		}

		return $html;
	}
}
