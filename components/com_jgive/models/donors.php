<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * JgiveModelDonors model class.
 *
 * @package  JGive
 * @since    1.8.1
 */
class JgiveModelDonors extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'user_id', 'a.user_id',
				'campaign_id', 'a.campaign_id',
				'email', 'a.email',
				'first_name', 'a.first_name',
				'last_name', 'a.last_name',
				'address', 'a.address',
				'address2', 'a.address2',
				'city', 'a.city',
				'state', 'a.state',
				'country', 'a.country',
				'zip', 'a.zip',
				'phone', 'a.phone',
				'created_by', 'a.created_by',
				'giveback_id', 'g.id',
				'donation_amount', 'o.amount',
				'cdate', 'o.cdate',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   String  $ordering   Sorting Order
	 * @param   String  $direction  Direction
	 *
	 * @return void
	 *
	 * @since    1.8.1
	 */
	protected function populateState($ordering = 'a.id', $direction = 'DESC')
	{
		$app  = Factory::getApplication();

		if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', null, 'array'))
		{
			foreach ($filters as $name => $value)
			{
				$this->setState('filter.' . $name, $value);
			}
		}

		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 *
	 * @since    1.8.1
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$user = Factory::getUser();

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query
			->select(
				$this->getState(
					'list.select', 'DISTINCT a.*'
				)
			);

		$query->from('`#__jg_donors` AS a');
		$query->select('c.title AS campaigns_title');
		$query->select('g.id AS gid');
		$query->select('g.description AS gdesc');
		$query->select('o.cdate AS cdate');
		$query->select('o.payment_received_date');
		$query->select('d.is_recurring');
		$query->select('o.processor');
		$query->select('SUM(o.original_amount) AS amount');
		$query->join('LEFT', '#__jg_campaigns AS c ON c.id = a.campaign_id');
		$query->join('LEFT', '#__jg_donations AS d ON d.donor_id = a.id');
		$query->join('LEFT', '#__jg_campaigns_givebacks AS g ON g.id = d.giveback_id');
		$query->join('LEFT', '#__jg_orders AS o ON o.donor_id = a.id');
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));
		$query->where($db->qn('c.creator_id') . ' = ' . (int) $user->id);
		$query->group($db->qn('a.contributor_id'));
		$query->group($db->qn('a.donor_type'));

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				// Search record
				$search = $db->Quote('%' . $db->escape(trim($search), true) . '%');

				$query->where('( a.first_name LIKE ' . $search .
						'  OR  a.last_name LIKE ' . $search .
						'  OR  a.email LIKE ' . $search .
						'  OR  a.org_name LIKE ' . $search .
						'  OR  CONCAT(a.first_name, " ", a.last_name )' . ' LIKE ' . $search . ' )'
				);
			}
		}

		// Filtering campaign_id
		$filter_campaign_id = $this->state->get("filter.campaign_id");

		if ($filter_campaign_id)
		{
			// Code for filter record
			$query->where("a.campaign_id = '" . $db->escape($filter_campaign_id) . "'");
		}

		// Filtering by donor type
		$filter_donor_type = $this->state->get("filter.donor_type");

		if ($filter_donor_type)
		{
			// Code for filter record
			$query->where("a.donor_type = '" . $db->escape($filter_donor_type) . "'");
		}

		// Filter for promoter_id
		$filter_promoter_id = $this->state->get("filter.creator_id");

		if ($filter_promoter_id)
		{
			$query->where("c.creator_id = '" . (int) $db->escape($filter_promoter_id) . "'");
		}

		// Filter for start date
		if (!empty($this->getState('filter.from_date')))
		{
			$query->where('DATE(' . $db->quoteName('o.cdate') . ')' . ' >= ' . $db->quote($this->getState('filter.from_date')));
		}

		// Filter for end date
		if (!empty($this->getState('filter.end_date')))
		{
			$query->where('DATE(' . $db->quoteName('o.cdate') . ')' . ' <= ' . $db->quote($this->getState('filter.end_date')));
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method used to get item data for displaying on donor list view page
	 *
	 * @return $items Data
	 *
	 * @since	1.8.1
	 */
	public function getItems()
	{
		$items   = parent::getItems();
		$user    = Factory::getUser();
		$user_id = $user->id;
		$db = Factory::getDbo();

		foreach ($items as $item)
		{
			if (isset($item->campaign_id) && $item->campaign_id != '')
			{
				if (is_object($item->campaign_id))
				{
					$item->campaign_id = ArrayHelper::fromObject($item->campaign_id);
				}

				$values = (is_array($item->campaign_id)) ? $item->campaign_id : explode(',', $item->campaign_id);
				$textValue = array();

				foreach ($values as $value)
				{
					// Fetch campaign title by id
					$query = $db->getQuery(true);
					$query->select($db->quoteName('title'))
							->from('`#__jg_campaigns`')
							->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results)
					{
						$textValue[] = $results->title;
					}
				}

				$item->campaign_title = !empty($textValue) ? implode(', ', $textValue) : $item->campaign_id;
			}
		}

		return $items;
	}

	/**
	 * Method loadFormData
	 *
	 * @return loadForm Data
	 *
	 * @since	1.8.1
	 */
	protected function loadFormData()
	{
		$app              = Factory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;

		foreach ($filters as $key => $value)
		{
			if (strpos($key, '_dateformat') && !empty($value) && !$this->isValidDate($value))
			{
				$filters[ $key ]  = '';
				$error_dateformat = true;
			}
		}

		if ($error_dateformat)
		{
			$app->enqueueMessage(Text::_("COM_JGIVE_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

	/**
	 * Method isValidDate
	 *
	 * @param   String  $date  Date
	 *
	 * @return void
	 *
	 * @since	1.8.1
	 */
	private function isValidDate($date)
	{
		return preg_match("/^(19|20)\d\d[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])$/", $date) && date_create($date);
	}

	/**
	 * Method to get email-id from donors table by using id.
	 *
	 * @param   Array  $donorsId  Pass the Donor Id for getting email
	 *
	 * @return Array
	 *
	 * @since    1.8.1
	 */
	public function getDonorsEmail($donorsId)
	{
		$user        = Factory::getUser();
		$db          = Factory::getDbo();
		$email_array = array();
		$modifiedDonorIds = implode(",", $donorsId);

		if (!empty($modifiedDonorIds))
		{
				$query = $db->getQuery(true);
				$query->select('DISTINCT d.email');
				$query->select('d.first_name');
				$query->select('d.last_name');
				$query->from($db->qn('#__jg_donors', 'd'));
				$query->join('INNER', $db->qn('#__jg_campaigns', 'c') . 'ON (' . $db->qn('c.id') . ' = ' . $db->qn('d.campaign_id') . ')');
				$query->where($db->qn('d.id') . ' IN(' . $modifiedDonorIds . ')');
				$query->where($db->qn('c.creator_id') . ' = ' . (int) $user->id);
				$db->setQuery($query);
				$email_array = $db->loadAssocList();
		}

		return $email_array;
	}

	/**
	 * Method to get mail from controller for send mail to user.
	 *
	 * @param   String  $email_id        Email-Id
	 * @param   String  $subject         Email Subject
	 * @param   String  $message         Email Message
	 * @param   String  $attachmentPath  Attachment FIle
	 *
	 * @return result
	 *
	 * @since    1.8.1
	 */
	public function emailtoSelected($email_id, $subject, $message, $attachmentPath = '')
	{
		$donationhelper = new DonationsHelper;
		$com_params     = ComponentHelper::getParams('com_jgive');
		$replytoemail   = $com_params->get('reply_to');

		$where     = '';
		$app       = Factory::getApplication();
		$mailfrom  = $app->getCfg('mailfrom');
		$fromname  = $app->getCfg('fromname');
		$sitename  = $app->getCfg('sitename');

		if (isset($replytoemail))
		{
			$replytoemail = explode(",", $replytoemail);
		}

		$result = $this->sendMailToDonors($email_id, $subject, $message);

		return $result;
	}

	/**
	 * Method to get number donors per campaign
	 *
	 * @param   Integer  $camp_id  Campaign Id
	 *
	 * @return array
	 *
	 * @since    2.0
	 */
	public function getDonorsPerCamp($camp_id)
	{
		if (!$camp_id)
		{
			return false;
		}

		$camp_id = (int) $camp_id;
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);

		// Donor Count who has registered
		$query->select('COUNT(DISTINCT d.user_id)');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('LEFT', $db->qn('#__jg_donors', 'd') . ' ON (' . $db->qn('d.id') . ' = ' . $db->qn('o.donor_id') . ')');
		$query->join('INNER', $db->qn('#__jg_campaigns', 'c') . ' ON (' . $db->qn('o.campaign_id') . ' = ' . $db->qn('c.id') . ')');
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));
		$query->where($db->qn('d.user_id') . '<>' . $db->quote('0'));
		$query->where($db->qn('c.id') . ' = ' . $db->quote($camp_id));

		$db->setQuery($query);
		$donarCountreg = $db->loadResult();

		$query   = $db->getQuery(true);

		// Donor Count who has guest
		$query->select('COUNT(DISTINCT d.email)');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('LEFT', $db->qn('#__jg_donors', 'd') . ' ON (' . $db->qn('d.id') . ' = ' . $db->qn('o.donor_id') . ')');
		$query->join('INNER', $db->qn('#__jg_campaigns', 'c') . ' ON (' . $db->qn('o.campaign_id') . ' = ' . $db->qn('c.id') . ')');
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));
		$query->where($db->qn('d.user_id') . '=' . $db->quote('0'));
		$query->where($db->qn('c.id') . ' = ' . $db->quote($camp_id));

		$db->setQuery($query);
		$donarCountguest = $db->loadResult();

		return $donarCountreg + $donarCountguest;
	}

	/**
	 * Sending mail to donors in bulk
	 *
	 * @param   string  $recipient   Email
	 * @param   string  $subject     Email sub
	 * @param   string  $body        Email body
	 * @param   string  $bcc_string  Email bcc
	 * @param   string  $action      Mail to send for action e.g donation.made, campaign.create etc
	 *
	 * @return  boolean
	 */
	public function sendMailToDonors($recipient, $subject, $body, $bcc_string = '', $action = "donation.made")
	{
		$mainframe   = Factory::getApplication();
		$from        = $mainframe->getCfg('mailfrom');
		$fromname    = $mainframe->getCfg('fromname');
		$recipient   = trim($recipient);
		$mode        = 1;
		$cc          = null;
		$bcc         = explode(',', $bcc_string);
		$attachment  = null;
		$replyto     = null;
		$replytoname = null;

		$mailer = Factory::getMailer();
		$mailer->setSender(array($from, $fromname));
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->addRecipient($recipient);
		$mailer->AddCC($cc);

		if ($bcc_string != null)
		{
			if (count($bcc))
			{
				if ($bcc[0])
				{
					$mailer->addBCC($bcc);
				}
			}
		}

		$mailer->addAttachment($attachment);

		if (is_array($replyto))
		{
			$numReplyTo = count($replyto);

			for ($i = 0; $i < $numReplyTo; $i++)
			{
				$mailer->addReplyTo(array($replyto[$i], $replytoname[$i]));
			}
		}
		elseif (isset($replyto))
		{
			$mailer->addReplyTo(array($replyto, $replytoname));
		}

		$mailer->isHtml(true);
		$mailer->AddCustomHeader("X-Custom-Header:" . $action);

		$mail_result = $mailer->Send();

		return $mail_result;
	}

	/**
	 * This function will return the donor count on basis of week,month,year
	 *
	 * @return integer  returns the new donor count
	 *
	 * @since  2.5.0
	 */
	public function getNewDonorCount()
	{
		$groupBy        = strtoupper($this->getState('filter.group_by'));
		$db             = Factory::getDbo();
		$query          = $db->getQuery(true);
		$subQuery       = $db->getQuery(true);
		$subQuery->select('d.contributor_id, min(o.cdate) as minorderdate');
		$subQuery->from($db->qn('#__jg_orders', 'o'));
		$subQuery->join('INNER', '#__jg_donors AS d ON o.donor_id = d.id');
		$subQuery->join('LEFT', '#__jg_campaigns AS c ON o.campaign_id = c.id');
		$subQuery->where($db->quoteName('c.creator_id') . ' = ' . (int) $this->getState('filter.creator_id'));

		if (!empty($this->getState('filter.from_date')))
		{
			$subQuery->where('DATE(o.cdate)' . ' >= ' . $db->quote($this->getState('filter.from_date')));
		}

		if (!empty($this->getState('filter.end_date')))
		{
			$subQuery->where('DATE(o.cdate)' . ' <= ' . $db->quote($this->getState('filter.end_date')));
		}

		$subQuery->group('d.contributor_id');

		$query->select('count(*) as donors');
		$query->from('(' . $subQuery . ') as subquery ');
		$db->setQuery($query);

		$resultArray = $db->loadAssocList();
		$total = 0;

		foreach ($resultArray as $data)
		{
			$total += $data['donors'];
		}

		return (int) $total;
	}
}
