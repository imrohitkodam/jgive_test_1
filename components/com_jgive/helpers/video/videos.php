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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;

jimport('techjoomla.tjmedia.media');

/**
 * JgiveModelVideos for campaign
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JgiveVideos
{
	/**
	 * Upload Video
	 *
	 * @return  Array  Response
	 */
	public static function videoUpload()
	{
		$response['validate']        = new stdclass;
		$response['validate']->error = 0;
		$response['fileUpload']      = new stdclass;

		// Check if request is GET and the requested chunk exists or not. this makes testChunks work
		if ($_SERVER['REQUEST_METHOD'] === 'GET')
		{
			$temp_dir   = JPATH_SITE . '/tmp/' . $_GET['resumableIdentifier'];
			$chunk_file = $temp_dir . '/' . $_GET['resumableFilename'] . '.part' . $_GET['resumableChunkNumber'];

			if (file_exists($chunk_file))
			{
				header("HTTP/1.0 200 Ok");
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
			}
		}

		if (!empty($_FILES))
		{
			foreach ($_FILES as $file)
			{
				// Check the error status
				if ($file['error'] != 0)
				{
					$response['validate']->error = 1;
					continue;
				}

				// Init the destination file (format <filename.ext>.part<#chunk> The file is stored in a temporary directory
				$temp_dir  = JPATH_SITE . '/tmp/' . $_POST['resumableIdentifier'];
				$dest_file = $temp_dir . '/' . $_POST['resumableFilename'] . '.part' . $_POST['resumableChunkNumber'];

				// Create the temporary directory
				if (!is_dir($temp_dir))
				{
					mkdir($temp_dir, 0744, true);
				}

				// Move the temporary file
				if (!move_uploaded_file($file['tmp_name'], $dest_file))
				{
					$response['validate']->error = 1;
				}
				else
				{
					// Check if all the parts present, and create the final destination file
					$filePath = self::createFileFromChunks($temp_dir, $_POST['resumableFilename'], $_POST['resumableChunkSize'], $_POST['resumableTotalSize']);

					if ($filePath)
					{
						$response['fileUpload']->complete = 1;
						$response['fileUpload']->filePath = $filePath;
					}
					else
					{
						$response['fileUpload']->complete = 0;
					}
				}
			}
		}

		return $response;
	}

	/**
	 * Check if all the parts exist, and gather all the parts of the file together
	 *
	 * @param   String  $temp_dir   The temporary directory holding all the parts of the file
	 * @param   String  $fileName   The original file name
	 * @param   String  $chunkSize  Each chunk size (in bytes)
	 * @param   String  $totalSize  Original file size (in bytes)
	 *
	 * @return void
	 */
	public static function createFileFromChunks($temp_dir, $fileName, $chunkSize, $totalSize)
	{
		// Count all the parts of this file
		$total_files = 0;

		foreach (scandir($temp_dir) as $file)
		{
			if (stripos($file, $fileName) !== false)
			{
				$total_files++;
			}
		}

		// Check that all the parts are present
		// The size of the last part is between chunkSize and 2*$chunkSize
		if ($total_files * $chunkSize >= ($totalSize - $chunkSize + 1))
		{
			// Create the final destination file
			if (($fp = fopen(JPATH_SITE . '/tmp/' . $fileName, 'w')) !== false)
			{
				for ($i = 1; $i <= $total_files; $i++)
				{
					fwrite($fp, file_get_contents($temp_dir . '/' . $fileName . '.part' . $i));
				}

				fclose($fp);
			}
			else
			{
				return false;
			}

			// Rename the temporary directory (to avoid access from other
			// Concurrent chunks uploads) and than delete it
			if (rename($temp_dir, $temp_dir . '_UNUSED'))
			{
				self::rrmdir($temp_dir . '_UNUSED');
			}
			else
			{
				self::rrmdir($temp_dir);
			}
		}

		// Lets make a unique safe file name for each upload
		$name     = JPATH_SITE . '/tmp/' . $fileName;
		$fileInfo = pathinfo($name);

		// File extension
		$fileExt = $fileInfo['extension'];

		// Base name
		$fileBase = $fileInfo['filename'];

		// Add logggedin userid to file name
		$fileBase = Factory::getUser()->id . '_' . $fileBase;

		/* Add timestamp to file name
		 * http://www.php.net/manual/en/function.microtime.php
		 * http://php.net/manual/en/function.uniqid.php
		 * http://php.net/manual/en/function.uniqid.php
		 * Microtime â�� Return current Unix timestamp with microseconds
		 * Uniqid â�� Generate a unique ID
		 */

		$timestamp = microtime();

		$fileBase = $fileBase . '_' . $timestamp;

		// Clean up filename to get rid of strange characters like spaces etc
		$fileBase = File::makeSafe($fileBase);

		// Lose any special characters in the filename
		$fileBase = preg_replace("/[^A-Za-z0-9]/i", "_", $fileBase);

		// Use lowercase
		$fileBase = strtolower($fileBase);

		$fileName = $fileBase . '.' . $fileExt;

		rename($name, JPATH_SITE . '/tmp/' . $fileName);

		return $fileName;
	}

	/**
	 * Delete a directory RECURSIVELY
	 *
	 * @param   String  $dir  directory path
	 *
	 * @return  void
	 */
	public static function rrmdir($dir)
	{
		if (is_dir($dir))
		{
			$objects = scandir($dir);

			foreach ($objects as $object)
			{
				if ($object != "." && $object != "..")
				{
					if (filetype($dir . "/" . $object) == "dir")
					{
						self::rrmdir($dir . "/" . $object);
					}
					else
					{
						unlink($dir . "/" . $object);
					}
				}
			}

			reset($objects);
			rmdir($dir);
		}
	}
}
