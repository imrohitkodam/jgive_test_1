<?php
/**
 * @version    SVN: <svn_id>
 * @package    JGive Campaigns
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2024 TechJoomla. All rights reserved.
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

$lang = Factory::getLanguage();
$lang->load('plg_system_jgivecampaigns', JPATH_ADMINISTRATOR);

/**
 * JGive Campaigns
 *
 * @package     JGive Campaigns
 * @subpackage  site
 * @since       1.0
 */
class PlgFinderJGiveCampaigns extends Adapter 
{
    /**
     * The plugin identifier.
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'Campaigns';

    /**
     * The extension name.
     *
     * @var    string
     * @since  2.5
     */
    protected $extension = 'com_jgive';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.5
     */
    protected $layout = 'default';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  2.5
     */
    protected $type_title = 'Campaign';

    /**
     * The table name.
     *
     * @var    string
     * @since  2.5
     */
    protected $table = '#__jg_campaigns';

    /**
     * The field the published state is stored in.
     *
     * @var    string
     * @since  2.5
     */
    protected $state_field = 'published';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    protected function setup()
    {
        return true;
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   string  $context  The context of the action being performed.
     * @param   Table   $table    A Table object containing the record to be deleted
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderDelete($context, $table)
    {
        if ($context === 'com_jgive.campaign') {
            $id = $table->id;
        } elseif ($context === 'com_finder.index') {
            $id = $table->link_id;
        } else {
            return true;
        }

        // Remove item from the index.
        return $this->remove($id);
    }

    /**
     * Smart Search after save campaign method.
     * Reindexes the link information for a campaign that has been saved.
     * It also makes adjustments if the access level of the campaign has changed.
     *
     * @param   string   $context  The context of the campaign passed to the plugin.
     * @param   Table    $row      A Table object.
     * @param   boolean  $isNew    True if the campaign has just been created.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderAfterSave($context, $row, $isNew): void
    {
        // We only want to handle campaigns here.
        if ($context === 'com_jgive.campaign' || $context === 'com_jgive.campaignform') {
            // Check if the access levels are different.
            $this->reindex($row->id);
        }
    }

    /**
     * Smart Search before campaign save method.
     * This event is fired before the data is actually saved.
     *
     * @param   string   $context  The context of the campaign passed to the plugin.
     * @param   Table    $row      A Table object.
     * @param   boolean  $isNew    True if the campaign is just about to be created.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderBeforeSave($context, $row, $isNew)
    {
        return true;
    }

    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   string   $context  The context for the events passed to the plugin.
     * @param   array    $pks      An array of primary key ids of the events that has changed state.
     * @param   integer  $value    The value of the state that the events has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderChangeState($context, $pks, $value)
    {
        // We only want to handle campaign here.
        if ($context === 'com_jgive.campaign' || $context == 'com_jgive.campaignform') 
        {
            /*
             * The events published state is tied to the parent events
             * published state so we need to look up all published states
             * before we change anything.
             */
            foreach ($pks as $pk) {
                $pk    = (int) $pk;
                $db = Factory::getDbo();
                $query = clone $this->getStateQuery();

                $query->where($db->quoteName('a.id') . ' = :plgFinderJgiveCampaignId')
                    ->bind(':plgFinderJgiveCampaignId', $pk, ParameterType::INTEGER);

                $db->setQuery($query);
                $item = $db->loadObject();

                // Translate the state.
                $state = $item->state;

                $temp = $this->translateState($value, $state);

                // Update the item.
                $this->change($pk, 'state', $temp);

                // Reindex the item.
                $this->reindex($pk);
            }
        }

        // Handle when the plugin is disabled.
        if ($context === 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Method to index an item. The item must be a Result object.
     *
     * @param   Result  $item  The item to index as a Result object.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    protected function index(Result $item)
    {
        // Check if the extension is enabled.
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        $item->setLanguage();

        // $extension = ucfirst(substr($extension_element, 4));

        // Initialize the item parameters.
        $item->params = new Registry($item->params);

        $item->metadata = new Registry($item->metadata);

        /*
         * Add the metadata processing instructions based on the campaign's
         * configuration parameters.
         */

        // Add the meta author.
        $item->metaauthor = $item->metadata->get('author');

        // Add vendor_title to the summary
        if (!empty($item->vendor_title)) {
            $item->summary .= ' ' . $item->vendor_title;  // Vendor title added to summary
        }

        // Handle the link to the metadata.
        $item->addInstruction(Indexer::META_CONTEXT, 'link');
        $item->addInstruction(Indexer::META_CONTEXT, 'metakey');
        $item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
        $item->addInstruction(Indexer::META_CONTEXT, 'metaauthor');
        $item->addInstruction(Indexer::META_CONTEXT, 'author');

        // Deactivated Methods
        // $item->addInstruction(Indexer::META_CONTEXT, 'created_by_alias');

        // Trigger the onContentPrepare event.
        $item->summary = Helper::prepareContent($item->summary, $item->params);

        $jgiveFrontendHelper = new jgiveFrontendHelper;
        $link = "index.php?option=com_jgive&view=campaign&layout=default&id=" . $item->id;

		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
        $link .= '&Itemid=' . $itemId;
        $item->url = $link;

        $item->route = $link;

        // Get the menu title if it exists.
        $title = $this->getItemMenuTitle($item->url);

        // Adjust the title if necessary.
        if (!empty($title) && $this->params->get('use_menu_title', true)) {
            $item->title = $title;
        }

        // Index the item.
        $this->indexer->index($item);
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  A DatabaseQuery object or null.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   2.5
     */
    protected function getListQuery($query = null)
    {
        $db = Factory::getDbo();

        // Check if we can use the supplied SQL query.
        $query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true);

        $query->select(
            $db->quoteName(
                [
                    'a.id',
                    'a.title',
                    'a.alias',
                    'a.meta_data',
                    'a.meta_desc',
                    'a.creator_id',
                    'a.modified',
                    'a.published',
                    'v.vendor_title'
				]
            )
        )
            ->select(
                $db->quoteName(
                    [
                        'a.short_description',
                        'a.created',
                        'a.published',
                        'a.published'
                    ],
                    [
                        'summary',
                        'start_date',
                        'access',
                        'state',
                    ]
                )
            );

        // Handle the alias CASE WHEN portion of the query.
        $case_when_item_alias = ' CASE WHEN ';
        $case_when_item_alias .= $query->charLength($db->quoteName('a.alias'), '!=', '0');
        $case_when_item_alias .= ' THEN ';
        $a_id = $query->castAsChar($db->quoteName('a.id'));
        $case_when_item_alias .= $query->concatenate([$a_id, 'a.alias'], ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id . ' END AS slug';

        $query->select($case_when_item_alias)
            ->from($db->quoteName('#__jg_campaigns', 'a'))
            ->join(
                'LEFT',
                $db->quoteName('#__tjvendors_vendors', 'v'),
                $db->quoteName('v.vendor_id') . ' = ' . $db->quoteName('a.vendor_id')  // join on vendor_id
            )
            ->where($db->quoteName('a.id') . ' >= 1');

        return $query;
    }

    /**
     * Method to get a SQL query to load the published and access states for
     * a campaign and its parents.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   2.5
     */
    protected function getStateQuery()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select(
            $db->quoteName(
                [
                    'a.id',
                    'a.published',
                ]
            )
        )
            ->select(
                $db->quoteName(
                    [
                        'a.' . $this->state_field
                    ],
                    [
                        'state'
                    ]
                )
            )
            ->from($db->quoteName('#__jg_campaigns', 'a'))
            ->join(
                'INNER',
                $db->quoteName('#__jg_campaigns', 'c'),
                $db->quoteName('c.id') . ' = ' . $db->quoteName('a.id')
            );

        return $query;
    }
	
}
