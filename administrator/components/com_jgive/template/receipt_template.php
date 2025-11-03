<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

$emails_config=array('message_body'=>"<table width=\"100%\">
<tbody>
<tr>
<td colspan=\"2\" align=\"center\">
<h2><span style=\"text-decoration: underline;\"><strong>STAR FOUNDATION</strong></span></h2>
<p style=\"text-align: right;\"><span style=\"font-size: 10pt;\"><em>Love Life Do Good!</em></span></p>
<hr /></td>
</tr>
<tr>
<td colspan=\"2\" align=\"center\">
<p style=\"text-align: center;\"><strong>(TEST/1998/2020/PUNE)</strong></p>
</td>
</tr>
<tr>
<td>
<p style=\"text-align: left;\"><strong>Receipt No</strong>.:<span style=\"font-size: 10pt;\"> {order.receipt_no}</span></p>
</td>
<td>
<p style=\"text-align: right;\"><strong>Date</strong>: <span style=\"font-size: 10pt;\">{order.date_of_payment}</span></p>
</td>
</tr>
<tr>
<td>
<p style=\"text-align: left;\"><span style=\"font-size: 10pt;\"><strong>Donor Name</strong>: {donor.name}</span></p>
<p style=\"text-align: left;\"><span style=\"font-size: 10pt;\">{donor.country}, </span><span style=\"font-size: 10pt;\">{donor.state}, </span><span style=\"font-size: 10pt;\">{donor.city}</span></p>
<p style=\"text-align: left;\"><span style=\"font-size: 10pt;\"> {donor.address}</span></p>
<p style=\"text-align: left;\"><span style=\"font-size: 10pt;\"><strong>Email</strong>: {donor.email}</span></p>
<p style=\"text-align: left;\"><span style=\"font-size: 10pt;\"><strong>Contact No</strong>.: {donor.contact}</span></p>
<p style=\"text-align: left;\"><span style=\"font-size: 10pt;\"><strong>Tax ID</strong>.: {donor.taxnumber}</span></p>
</td>
<td>
<p style=\"text-align: right;\"><span style=\"font-size: 10pt;\"><strong>Donation Amount</strong>: {order.original_amount}</span></p>
<p style=\"text-align: right;\"><span style=\"font-size: 10pt;\"><strong>Amount in word</strong>: {order.original_amount_in_word}</span></p>
<p style=\"text-align: right;\"><span style=\"font-size: 10pt;\"><strong>Payment Process</strong>: {order.payment_mode}</span></p>
</td>
</tr>
<tr>
<td>
<p style=\"text-align: left;\"><span style=\"font-size: 8pt;\">Donation to the <strong>{campaign.title}</strong></span></p>
<p style=\"text-align: left;\"><span style=\"font-size: 8pt;\">Order No. Pn/CIT-L/806/109/2011-12/1614 dx 15-7-2011</span></p>
<p style=\"text-align: left;\"><span style=\"font-size: 8pt;\">Trust PAN: AAAAA1234A</span></p>
</td>
<td>
<p style=\"text-align: center;\"> </p>
<p style=\"text-align: right;\"><strong>Signature</strong></p>
</td>
</tr>
<tr>
<td colspan=\"2\" align=\"center\">
<p><strong><span style=\"font-size: 18px; font-family: verdana;\">Thank you for your generous support!</span></strong></p>
</td>
</tr>
</tbody>
</table>");
