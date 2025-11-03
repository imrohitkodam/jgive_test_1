<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die();
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;


// Load jgive list Controller for list views
require_once __DIR__ . '/jgivelist.php';

require_once JPATH_ADMINISTRATOR . '/components/com_jgive/models/campaign.php';

/**
 * Countries list controller class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.7
 */
class JgiveControllerCampaigns extends JgiveControllerJgivelist
{
	/**
	 * Create New Campaign
	 *
	 * @return void
	 *
	 * @since 1.6
	 */
	public function addNew()
	{
		$redirect = Route::_('index.php?option=com_jgive&view=campaign&layout=edit', false);
		$this->setRedirect($redirect);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   array   $config  A named array of configuration variables.
	 *
	 * @return  JModel
	 *
	 * @since   1.7
	 */
	public function getModel($name = 'Campaign', $prefix = 'JgiveModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * To delete actual file  - Added by Nidhi
	 *
	 * @param   Array  $galleryVideoToDelPath  A named array of thumbnail and video file path
	 *
	 * @return  JModel
	 *
	 * @since   1.7
	 */
	public function deleteFile($galleryVideoToDelPath)
	{
		// If to delete video file then we will need to specify video file location but in-case of image full location we are getting from db itself
		$thumbpath = JPATH_SITE . $galleryVideoToDelPath->thumb_path;
		$videopath = JPATH_SITE . $galleryVideoToDelPath->path;

		if (File::exists($thumbpath) && File::exists($videopath))
		{
			File::delete($thumbpath);
			File::delete($videopath);
		}
	}

	/**
	 * Method to toggle the featured setting of a list of campaigns.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function featured()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app = Factory::getApplication();

		$user   = Factory::getUser();
		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('featured' => 1, 'unfeatured' => 0);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_jgive'))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				$app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');
			}
		}

		if (empty($ids))
		{
			$app->enqueueMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'error');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Publish the items.
			if (!$model->featured($ids, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}

			PluginHelper::importPlugin('actionlog');

			if ($value == 1)
			{
				PluginHelper::importPlugin('jgive');

				// Value 3 = featured
				Factory::getApplication()->triggerEvent('onAfterJGCampaignChangeState', array('com_jgive', $ids, 3));

				$message = Text::plural('COM_JGIVE_N_ITEMS_FEATURED', count($ids));
			}
			else
			{
				PluginHelper::importPlugin('jgive');

				// Value -3 = unfeatured
				Factory::getApplication()->triggerEvent('onAfterJGCampaignChangeState', array('com_jgive', $ids, -3));
				$message = Text::plural('COM_JGIVE_N_ITEMS_UNFEATURED', count($ids));
			}
		}

		$this->setRedirect(Route::_('index.php?option=com_jgive&view=campaigns', false), $message);
	}

	/**
	 * Method to publish a list of campaigns.
	 *
	 * @return  void
	 *
	 * @since   3.3.0
	 */
	public function publish()
	{
		$app   = Factory::getApplication();
		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');
		$model = $this->getModel('campaign');

		$result = $model->publish($ids, $value);

		if ($result)
		{
			$state = ($task == 'publish') ? 1 : 0;

			// Redirect to the list screen.
			if ($state)
			{
				$this->setMessage(Text::sprintf(Text::_('COM_JGIVE_N_ITEMS_PUBLISHED'),count($ids)));
			}
			else 
			{
				$this->setMessage(Text::sprintf(Text::_('COM_JGIVE_N_ITEMS_UNPUBLISHED'),count($ids)));
			} 
		
			PluginHelper::importPlugin('finder');
			Factory::getApplication()->triggerEvent('onFinderChangeState', array('com_jgive.campaign', $ids, $value));
		}
		else
		{
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}
		}

		$this->setRedirect(Route::_('index.php?option=com_jgive&view=campaigns', false));
	}
}
