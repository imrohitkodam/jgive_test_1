<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

class JgiveModelVendorReport extends ListModel
{
    public function getListQuery()
    {
        $db = $this->getDbo();
        
        $app   = Factory::getApplication();
        $input = $app->input;
        $filterType = $input->get('filter_type', '', 'string');

        $query = $db->getQuery(true);
        $query->select([
            'd.campaign_id',
            'c.title AS campaign_title',
            'v.vendor_id',
            'v.vendor_title AS vendor_name',
            'user.email AS vendor_email',
            'dn.first_name AS donor_name',
            'dn.email AS donor_email',
            'o.amount',
            'o.cdate'
        ])
        ->from('#__jg_donations AS d')
        ->join('INNER', '#__jg_campaigns AS c ON c.id = d.campaign_id')
        ->join('INNER', '#__jg_donors AS dn ON dn.id = d.donor_id')
        ->join('INNER', '#__tjvendors_vendors AS v ON v.vendor_id = c.vendor_id')
        ->join('INNER', '#__jg_orders AS o ON o.donor_id = d.donor_id')
        ->join('INNER', '#__users AS user ON user.id = v.user_id')
        
        ->where("o.status = 'C'")
        ->order('v.vendor_id, d.campaign_id, o.cdate');
        
        switch ($filterType) {
            case 'monthly':
                $query->where('MONTH(o.cdate) = MONTH(CURRENT_DATE())')
                ->where('YEAR(o.cdate) = YEAR(CURRENT_DATE())');
                break;
            case 'quarterly':
                $query->where('QUARTER(o.cdate) = QUARTER(CURRENT_DATE())')
                ->where('YEAR(o.cdate) = YEAR(CURRENT_DATE())');
                break;
            case 'yearly':
                $query->where('YEAR(o.cdate) = YEAR(CURRENT_DATE())');
                break;

        }
        return $query;
    }
}