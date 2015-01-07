<?php
/**
* @copyright	Copyright (C) 2009 - 2014 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		support+rb@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_AdaptJ16Html extends JHtml
{	
	/**
	 * Copied from J3.x
	 */
	protected static function includeRelativeFiles($folder, $file, $relative, $detect_browser, $detect_debug)
	{
		// If http is present in filename
		if (strpos($file, 'http') === 0)
		{
			$includes = array($file);
		}
		else
		{
			// Extract extension and strip the file
			$strip = JFile::stripExt($file);
			$ext   = JFile::getExt($file);

			// Prepare array of files
			$includes = array();

			// Detect browser and compute potential files
			if ($detect_browser)
			{
				$navigator = JBrowser::getInstance();
				$browser = $navigator->getBrowser();
				$major = $navigator->getMajor();
				$minor = $navigator->getMinor();

				// Try to include files named filename.ext, filename_browser.ext, filename_browser_major.ext, filename_browser_major_minor.ext
				// where major and minor are the browser version names
				$potential = array($strip, $strip . '_' . $browser,  $strip . '_' . $browser . '_' . $major,
					$strip . '_' . $browser . '_' . $major . '_' . $minor);
			}
			else
			{
				$potential = array($strip);
			}

			// If relative search in template directory or media directory
			if ($relative)
			{
				// Get the template
				$template = JFactory::getApplication()->getTemplate();

				// For each potential files
				foreach ($potential as $strip)
				{
					$files = array();

					// Detect debug mode
					if ($detect_debug && JFactory::getConfig()->get('debug'))
					{
						/*
						 * Detect if we received a file in the format name.min.ext
						 * If so, strip the .min part out, otherwise append -uncompressed
						 */
						if (strrpos($strip, '.min', '-4'))
						{
							$position = strrpos($strip, '.min', '-4');
							$filename = str_replace('.min', '.', $strip, $position);
							$files[]  = $filename . $ext;
						}
						else
						{
							$files[] = $strip . '-uncompressed.' . $ext;
						}
					}

					$files[] = $strip . '.' . $ext;

					/*
					 * Loop on 1 or 2 files and break on first found.
					 * Add the content of the MD5SUM file located in the same folder to url to ensure cache browser refresh
					 * This MD5SUM file must represent the signature of the folder content
					 */
					foreach ($files as $file)
					{
						// If the file is in the template folder
						$path = JPATH_THEMES . "/$template/$folder/$file";

						if (file_exists($path))
						{
							$md5 = dirname($path) . '/MD5SUM';
							$includes[] = JUri::base(true) . "/templates/$template/$folder/$file" .
								(file_exists($md5) ? ('?' . file_get_contents($md5)) : '');

							break;
						}
						else
						{
							// If the file contains any /: it can be in an media extension subfolder
							if (strpos($file, '/'))
							{
								// Divide the file extracting the extension as the first part before /
								list($extension, $file) = explode('/', $file, 2);

								// If the file yet contains any /: it can be a plugin
								if (strpos($file, '/'))
								{
									// Divide the file extracting the element as the first part before /
									list($element, $file) = explode('/', $file, 2);

									// Try to deal with plugins group in the media folder
									$path = JPATH_ROOT . "/media/$extension/$element/$folder/$file";

									if (file_exists($path))
									{
										$md5 = dirname($path) . '/MD5SUM';
										$includes[] = JUri::root(true) . "/media/$extension/$element/$folder/$file" .
											(file_exists($md5) ? ('?' . file_get_contents($md5)) : '');

										break;
									}

									// Try to deal with classical file in a a media subfolder called element
									$path = JPATH_ROOT . "/media/$extension/$folder/$element/$file";

									if (file_exists($path))
									{
										$md5 = dirname($path) . '/MD5SUM';
										$includes[] = JUri::root(true) . "/media/$extension/$folder/$element/$file" .
											(file_exists($md5) ? ('?' . file_get_contents($md5)) : '');

										break;
									}

									// Try to deal with system files in the template folder
									$path = JPATH_THEMES . "/$template/$folder/system/$element/$file";

									if (file_exists($path))
									{
										$md5 = dirname($path) . '/MD5SUM';
										$includes[] = JUri::root(true) . "/templates/$template/$folder/system/$element/$file" .
											(file_exists($md5) ? ('?' . file_get_contents($md5)) : '');

										break;
									}

									// Try to deal with system files in the media folder
									$path = JPATH_ROOT . "/media/system/$folder/$element/$file";

									if (file_exists($path))
									{
										$md5 = dirname($path) . '/MD5SUM';
										$includes[] = JUri::root(true) . "/media/system/$folder/$element/$file" .
											(file_exists($md5) ? ('?' . file_get_contents($md5)) : '');

										break;
									}
								}
								else
								{
									// Try to deals in the extension media folder
									$path = JPATH_ROOT . "/media/$extension/$folder/$file";

									if (file_exists($path))
									{
										$md5 = dirname($path) . '/MD5SUM';
										$includes[] = JUri::root(true) . "/media/$extension/$folder/$file" .
											(file_exists($md5) ? ('?' . file_get_contents($md5)) : '');

										break;
									}

									// Try to deal with system files in the template folder
									$path = JPATH_THEMES . "/$template/$folder/system/$file";

									if (file_exists($path))
									{
										$md5 = dirname($path) . '/MD5SUM';
										$includes[] = JUri::root(true) . "/templates/$template/$folder/system/$file" .
											(file_exists($md5) ? ('?' . file_get_contents($md5)) : '');

										break;
									}

									// Try to deal with system files in the media folder
									$path = JPATH_ROOT . "/media/system/$folder/$file";

									if (file_exists($path))
									{
										$md5 = dirname($path) . '/MD5SUM';
										$includes[] = JUri::root(true) . "/media/system/$folder/$file" .
											(file_exists($md5) ? ('?' . file_get_contents($md5)) : '');

										break;
									}
								}
							}
							// Try to deal with system files in the media folder
							else
							{
								$path = JPATH_ROOT . "/media/system/$folder/$file";

								if (file_exists($path))
								{
									$md5 = dirname($path) . '/MD5SUM';
									$includes[] = JUri::root(true) . "/media/system/$folder/$file" .
											(file_exists($md5) ? ('?' . file_get_contents($md5)) : '');

									break;
								}
							}
						}
					}
				}
			}
			// If not relative and http is not present in filename
			else
			{
				foreach ($potential as $strip)
				{
					$files = array();

					// Detect debug mode
					if ($detect_debug && JFactory::getConfig()->get('debug'))
					{
						/*
						 * Detect if we received a file in the format name.min.ext
						 * If so, strip the .min part out, otherwise append -uncompressed
						 */
						if (strrpos($strip, '.min', '-4'))
						{
							$position = strrpos($strip, '.min', '-4');
							$filename = str_replace('.min', '.', $strip, $position);
							$files[]  = $filename . $ext;
						}
						else
						{
							$files[] = $strip . '-uncompressed.' . $ext;
						}
					}

					$files[] = $strip . '.' . $ext;

					/*
					 * Loop on 1 or 2 files and break on first found.
					 * Add the content of the MD5SUM file located in the same folder to url to ensure cache browser refresh
					 * This MD5SUM file must represent the signature of the folder content
					 */
					foreach ($files as $file)
					{
						$path = JPATH_ROOT . "/$file";

						if (file_exists($path))
						{
							$md5 = dirname($path) . '/MD5SUM';
							$includes[] = JUri::root(true) . "/$file" .
								(file_exists($md5) ? ('?' . file_get_contents($md5)) : '');

							break;
						}
					}
				}
			}
		}

		return $includes;
	}
	
	/**
	 * Write a <script></script> element
	 *
	 * @param   string   $file            path to file
	 * @param   boolean  $framework       load the JS framework
	 * @param   boolean  $relative        path to file is relative to /media folder
	 * @param   boolean  $path_only       return the path to the file only
	 * @param   boolean  $detect_browser  detect browser to include specific browser js files
	 * @param   boolean  $detect_debug    detect debug to search for compressed files if debug is on
	 *
	 * @return  mixed  nothing if $path_only is false, null, path or array of path if specific js browser files were detected
	 *
	 * @see     JHtml::stylesheet
	 * @since   11.1
	 */
	public static function script($file, $framework = false, $relative = false, $path_only = false, $detect_browser = true, $detect_debug = true)
	{
		// Need to adjust for the change in API from 1.5 to 1.6.
		// function script($filename, $path = 'media/system/js/', $mootools = true)
		if (is_string($framework))
		{
			JLog::add('The used parameter set in JHtml::script() is deprecated.', JLog::WARNING, 'deprecated');
			// Assume this was the old $path variable.
			$file = $framework . $file;
			$framework = $relative;
		}

		// Include MooTools framework
		if ($framework)
		{
			JHtml::_('behavior.framework');
		}

		$includes = self::includeRelativeFiles('js', $file, $relative, $detect_browser, $detect_debug);

		// If only path is required
		if ($path_only)
		{
			if (count($includes) == 0)
			{
				return null;
			}
			elseif (count($includes) == 1)
			{
				return $includes[0];
			}
			else
			{
				return $includes;
			}
		}
		// If inclusion is required
		else
		{
			$document = JFactory::getDocument();
			foreach ($includes as $include)
			{
				$document->addScript($include);
			}
		}
	}
	
	public static function stylesheet($file, $attribs = array(), $relative = false, $path_only = false, $detect_browser = true, $detect_debug = true)
	{
		// Need to adjust for the change in API from 1.5 to 1.6.
		// Function stylesheet($filename, $path = 'media/system/css/', $attribs = array())
		if (is_string($attribs))
		{
			JLog::add('The used parameter set in JHtml::stylesheet() is deprecated.', JLog::WARNING, 'deprecated');
			// Assume this was the old $path variable.
			$file = $attribs . $file;
		}

		if (is_array($relative))
		{
			// Assume this was the old $attribs variable.
			$attribs = $relative;
			$relative = false;
		}

		$includes = self::includeRelativeFiles('css', $file, $relative, $detect_browser, $detect_debug);

		// If only path is required
		if ($path_only)
		{
			if (count($includes) == 0)
			{
				return null;
			}
			elseif (count($includes) == 1)
			{
				return $includes[0];
			}
			else
			{
				return $includes;
			}
		}
		// If inclusion is required
		else
		{
			$document = JFactory::getDocument();
			foreach ($includes as $include)
			{
				$document->addStylesheet($include, 'text/css', null, $attribs);
			}
		}
	}

	/**
	 * Write a <img></img> element
	 *
	 * @param   string   $file       The relative or absolute URL to use for the src attribute
	 * @param   string   $alt        The alt text.
	 * @param   string   $attribs    The target attribute to use
	 * @param   array    $relative   An associative array of attributes to add
	 * @param   boolean  $path_only  If set to true, it tries to find an override for the file in the template
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public static function image($file, $alt, $attribs = null, $relative = false, $path_only = false)
	{
		if (is_array($attribs))
		{
			$attribs = JArrayHelper::toString($attribs);
		}

		$includes = self::includeRelativeFiles('images', $file, $relative, false, false);

		// If only path is required
		if ($path_only)
		{
			if (count($includes))
			{
				return $includes[0];
			}
			else
			{
				return null;
			}
		}
		else
		{
			return '<img src="' . (count($includes) ? $includes[0] : '') . '" alt="' . $alt . '" ' . $attribs . ' />';
		}
	}
}
