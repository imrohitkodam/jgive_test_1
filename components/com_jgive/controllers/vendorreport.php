<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Dompdf\Dompdf;
use Dompdf\Options;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;

class JgiveControllerVendorReport extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        $this->input->set('view', 'vendorreport');
        parent::display($cachable, $urlparams);
    }

    /**
     * Method generatePdf to generate pdf file and save in public accessible location
     * 
     * @param array pdfGenData : contains data, fromDate, toDate
     * 
     * @since  4.1.0
     * 
     */

    public function generatePdf($pdfGenData)
    {
        require_once JPATH_SITE . '/libraries/techjoomla/dompdf/autoload.inc.php';
        $params = ComponentHelper::getParams('com_jgive');
        $pageSize    = $params->get('vendor_report_page_size', 'A4');
		$orientation = $params->get('vendor_report_orientation', 'portrait');

		// If the pagesize is custom then get the correct size and width.
		if ($pageSize === 'custom')
		{
			$height   = $params->get('vendor_report_pdf_width', '15') * 28.3465;
			$width    = $params->get('vendor_report_pdf__height', '400') * 28.3465;
			$pageSize = array(0, 0, $width, $height);
		}

		$fontFamily   = $params->get('pdf_vendor_report_font', 'dejavu sans');;

        $totalAmount = array_reduce($pdfGenData['data'], function ($carry, $item) {
            return $carry + $item->amount;
        }, 0);
    
        // Build PDF content
        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head>
        <style type="text/css">* {font-family: "'. $fontFamily .'" !important}</style>
        <style>
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            h3 { color: #333; }
            .total { font-weight: bold; margin-top: 10px; }
        </style></head><body>';
    
        // Include the correct date range
        $html .= "<div><strong>Report Duration:</strong> From <strong>{$pdfGenData['fromDate']}</strong> to <strong>{$pdfGenData['toDate']}</strong></div><br>";
    
        $html .= "<table>
            <thead>
                <tr>
                    <th>".Text::_('COM_JGIVE_VENDOR_REPORT_HEADING_CAMPAIGN_TITLE') . "</th>
                    <th>".Text::_('COM_JGIVE_VENDOR_REPORT_HEADING_DONOR_NAME') . "</th>
                    <th>".Text::_('COM_JGIVE_VENDOR_REPORT_HEADING_DONOR_EMAIL') . "</th>
                    <th>".Text::_('COM_JGIVE_VENDOR_REPORT_HEADING_AMOUNT') . "</th>
                    <th>".Text::_('COM_JGIVE_VENDOR_REPORT_HEADING_DATE') . "</th>
                </tr>
            </thead><tbody>";
    
        foreach ($pdfGenData['data'] as $entry) {
            $html .= "<tr>
                <td>{$entry->campaign_title}</td>
                <td>{$entry->donor_name}</td>
                <td>{$entry->donor_email}</td>
                <td>{$entry->amount}</td>
                <td>{$entry->cdate}</td>
            </tr>";
        }
    
        $html .= "</tbody></table>";
        $html .= "<div class='total'>Total Amount: {$totalAmount}</div>";
        $html .= '</body></html>';
    
        // Generate and save PDF
        // Set font for the pdf download.
		$options = new Options();
		$options->setDefaultFont($fontFamily);
		$options->set('isRemoteEnabled', true);

		$dompdf = new DOMPDF($options);
		$dompdf->loadHTML($html, 'UTF-8');

		// Set the page size and oriendtation.
		$dompdf->setPaper($pageSize, $orientation);
        $dompdf->render();

        $mediaDir = 'media/com_jgive/vendor_reports/';
        $pdfDir = JPATH_ROOT .'/'. $mediaDir;

        $fileNameArr  = [];
        $fileNameArr[] = 'vendor_report';
        $fileNameArr[] = $pdfGenData['vendorEmail'];
        $fileNameArr[] = time();

        $baseFilename = implode("-",$fileNameArr);

        $pdfPath = $pdfDir . $baseFilename . ".pdf";
        file_put_contents($pdfPath, $dompdf->output());
        
        return $mediaDir. $baseFilename . ".pdf";
    }
    

    /**
     * Method sendAllVendorReports main controller function to generate report, created pdf and email to vendors
     * 
     * @param array none
     * 
     * @since  4.1.0
     * 
     */
    public function sendAllVendorReports()
    {
        Log::add("--- prepare vendor report start ---", Log::INFO, "reportlog");

        JLoader::register('JGiveMailsHelper', JPATH_SITE . '/components/com_jgive/helpers/mails.php');
        
        $app = Factory::getApplication();
        $jinput = $app->input;
        $period = $jinput->get("period", "");
        Log::add("--- vendor report check period ---: ".$period,Log::INFO, "reportlog");
        switch($period) {
            case "monthly":
                Log::add("--- vendor report monthly ---",Log::INFO, "reportlog");
                $this->getMonthlyReportSendEmail();
                break;
            case "quarterly":
                Log::add("--- vendor report quarterly ---",Log::INFO, "reportlog");
                $this->getQuarterlyReportSendEmail();
                break;
            case "yearly":
                Log::add("--- vendor report yearly ---",Log::INFO, "reportlog");
                $this->getYearlyReportSendEmail();
                break;
        }
    }

    /**
     * Method getMonthlyReportSendEmail function to fetch 1 month data and generate report, created pdf and email to vendors
     * 
     * @param array none
     * 
     * @since  4.1.0
     * 
     */    
    private function getMonthlyReportSendEmail() {

        Log::add("--- monthly get data ---",Log::INFO, "reportlog");

        $input = Factory::getApplication()->input;
        $input->set('filter_type', 'monthly');
        $model = $this->getModel('VendorReport');
        $items = $model->getItems();
        if (empty($items)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_JGIVE_VENDOR_REPORT_NO_DATA'), 'warning');
            return;
        }

        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item->vendor_email][] = $item;
        }
        
        $fromDate = date('Y-m-01'); // First day of current month    
        $toDate = date('Y-m-d'); // Today's date
        
        $this->sendEmail($grouped, $fromDate, $toDate, 'monthly');
    }


    /**
     * Method getQuarterlyReportSendEmail function to fetch 3months data and generate report, created pdf and email to vendors
     * 
     * @param array none
     * 
     * @since  4.1.0
     * 
     */
    private function getQuarterlyReportSendEmail() {

        $input = Factory::getApplication()->input;
        $input->set('filter_type', 'quarterly');

        $model = $this->getModel('VendorReport');
        $items = $model->getItems();
        if (empty($items)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_JGIVE_VENDOR_REPORT_NO_DATA'), 'warning');
            return;
        }

        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item->vendor_email][] = $item;
        }
        
        $fromDate = date('Y-m-d', strtotime('-3 months', strtotime(date('Y-m-01')))); // Last 3 months
        $toDate = date('Y-m-d'); // Today's date
        
        $this->sendEmail($grouped, $fromDate, $toDate, 'quarterly');
    }


    /**
     * Method getYearlyReportSendEmail function to fetech yearly data and generate report, created pdf and email to vendors
     * 
     * @param array none
     * 
     * @since  4.1.0
     * 
     */
    private function getYearlyReportSendEmail() {

        $input = Factory::getApplication()->input;
        $input->set('filter_type', 'yearly');

        $model = $this->getModel('VendorReport');
        $items = $model->getItems();
        if (empty($items)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_JGIVE_VENDOR_REPORT_NO_DATA'), 'warning');
            return;
        }

        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item->vendor_email][] = $item;
        }
        
        $fromDate = date('Y-01-01'); // Last 1 year
        $toDate = date('Y-m-d'); // Today's date
        
        $this->sendEmail($grouped, $fromDate, $toDate, 'yearly');
    }


    /**
     * Method sendEmail generate pdf and email to vendors
     * 
     * @param array data: data to generate pdf
     * @param array fromdate: report start date
     * @param array todate: report end date
     * @param array period: period like monthly, quarterly and yearly
     * 
     * @since  4.1.0
     * 
     */
    private function sendEmail($groupedData, $fromDate, $toDate, $period){
        $mailHelper = new JGiveMailsHelper();
        $vendorReportPDFGen = [];
        $vendorReportPDFGen['fromDate'] = $fromDate;
        $vendorReportPDFGen['toDate'] = $toDate;
        foreach ($groupedData as $vendorEmail => $vendorReportData) {
            $vendorReportPDFGen['data'] = $vendorReportData;
            $vendorReportPDFGen['vendorEmail'] = $vendorEmail;
            
            Log::add("--- prepare pdf ---",Log::INFO, "reportlog");
            
            $pdfLink = $this->generatePDF($vendorReportPDFGen);
            $pdfPublicUrl = Uri::root() .$pdfLink;
            
            Log::add("--- pdf link --- ".$pdfPublicUrl,Log::INFO, "reportlog");

            Log::add("--- send email ---",Log::INFO, "reportlog");
            $mailHelper->sendReportToVendor(['vendorEmail' => $vendorEmail, 'pdfUrl' => $pdfPublicUrl, 'fromDate' =>$fromDate, 'toDate' => $toDate ,'period' => $period]);
        }
    }
}
