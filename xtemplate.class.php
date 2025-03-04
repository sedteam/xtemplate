<?php

// When developing uncomment the line below, re-comment before making public
//error_reporting(E_ALL);

/**
 * XTemplate PHP templating engine
 *
 * @package XTemplate
 * @author Barnabas Debreceni [cranx@users.sourceforge.net]
 * @copyright Barnabas Debreceni 2000-2001
 * @author Jeremy Coates [cocomp@users.sourceforge.net]
 * @copyright Jeremy Coates 2002-2007
 * @see license.txt LGPL / BSD license
 * @since PHP 5
 * @link $HeadURL: https://xtpl.svn.sourceforge.net/svnroot/xtpl/trunk/xtemplate.class.php $
 * @version $Id: xtemplate.class.php 21 2007-05-29 18:01:15Z cocomp $
 *
 * 
 * XTemplate class - http://www.phpxtemplate.org/ (x)html / xml generation with templates - fast & easy
 * Latest stable & Subversion versions available @ http://sourceforge.net/projects/xtpl/
 * License: LGPL / BSD - see license.txt
 * Changelog: see changelog.txt
 */
class XTemplate
{
	/**
	 * Properties
	 */

	/**
	 * Raw contents of the template file
	 *
	 * @access public
	 * @var string
	 */
	public $filecontents = '';

	/**
	 * Unparsed blocks
	 *
	 * @access public
	 * @var array
	 */
	public $blocks = array();

	/**
	 * Parsed blocks
	 *
	 * @var unknown_type
	 */
	public $parsed_blocks = array();

	/**
	 * Preparsed blocks (for file includes)
	 *
	 * @access public
	 * @var array
	 */
	public $preparsed_blocks = array();

	/**
	 * Block parsing order for recursive parsing
	 * (Sometimes reverse :)
	 *
	 * @access public
	 * @var array
	 */
	public $block_parse_order = array();

	/**
	 * Store sub-block names
	 * (For fast resetting)
	 *
	 * @access public
	 * @var array
	 */
	public $sub_blocks = array();

	/**
	 * Variables array
	 *
	 * @access public
	 * @var array
	 */
	public $vars = array();

	/**
	 * File variables array
	 *
	 * @access public
	 * @var array
	 */
	public $filevars = array();

	/**
	 * Filevars' parent block
	 *
	 * @access public
	 * @var array
	 */
	public $filevar_parent = array();

	/**
	 * File caching during duration of script
	 * e.g. files only cached to speed {FILE "filename"} repeats
	 *
	 * @access public
	 * @var array
	 */
	public $filecache = array();

	/**
	 * Location of template files
	 *
	 * @access public
	 * @var string
	 */
	public $tpldir = '';

	/**
	 * Filenames lookup table
	 *
	 * @access public
	 * @var null
	 */
	public $files = null;

	/**
	 * Template filename
	 *
	 * @access public
	 * @var string
	 */
	public $filename = '';

	// NEW: Added in Seditio version for regex delimiter customization
	/**
	 * Delimiter used in preg regular expressions
	 *
	 * @access public
	 * @var string
	 */
	public $preg_delimiter = '`';

	// moved to setup method so uses the tag_start & end_delims
	/**
	 * RegEx for file includes
	 *
	 * "/\{FILE\s*\"([^\"]+)\"\s*\}/m";
	 *
	 * @access public
	 * @var string
	 */
	public $file_delim = '';

	/**
	 * RegEx for file include variable
	 *
	 * "/\{FILE\s*\{([A-Za-z0-9\._]+?)\}\s*\}/m";
	 *
	 * @access public
	 * @var string
	 */
	public $filevar_delim = '';

	/**
	 * RegEx for file includes with newlines
	 *
	 * "/^\s*\{FILE\s*\{([A-Za-z0-9\._]+?)\}\s*\}\s*\n/m";
	 *
	 * @access public
	 * @var string
	 */
	public $filevar_delim_nl = '';

	/**
	 * Template block start delimiter
	 *
	 * @access public
	 * @var string
	 */
	public $block_start_delim = '<!-- ';

	/**
	 * Template block end delimiter
	 *
	 * @access public
	 * @var string
	 */
	public $block_end_delim = '-->';

	/**
	 * Template block start word
	 *
	 * @access public
	 * @var string
	 */
	public $block_start_word = 'BEGIN:';

	/**
	 * Template block end word
	 *
	 * The last 3 properties and this make the delimiters look like:
	 * @example <!-- BEGIN: block_name -->
	 * if you use the default syntax.
	 *
	 * @access public
	 * @var string
	 */
	public $block_end_word = 'END:';

	/**
	 * Template tag start delimiter
	 *
	 * This makes the delimiters look like:
	 * @example {tagname}
	 * if you use the default syntax.
	 *
	 * @access public
	 * @var string
	 */
	public $tag_start_delim = '{';

	/**
	 * Template tag end delimiter
	 *
	 * This makes the delimiters look like:
	 * @example {tagname}
	 * if you use the default syntax.
	 *
	 * @access public
	 * @var string
	 */
	public $tag_end_delim = '}';

	/**
	 * Regular expression element for comments within tags and blocks
	 *
	 * @example {tagname#My Comment}
	 * @example {tagname #My Comment}
	 * @example <!-- BEGIN: blockname#My Comment -->
	 * @example <!-- BEGIN: blockname #My Comment -->
	 *
	 * @access public
	 * @var string
	 */
	public $comment_preg = '( ?#.*?)?';

	// NEW: Added in Seditio version for callback delimiters and regex
	/**
	 * Delimiter used for callback functions in tags
	 *
	 * @access public
	 * @var string
	 */
	public $comment_delim = '#';

	// NEW: Added in Seditio version for callback delimiters
	/**
	 * Delimiter used to separate callback functions in tags
	 *
	 * @access public
	 * @var string
	 */
	public $callback_delim = '|';

	// NEW: Added in Seditio version for callback support
	/**
	 * Regular expression pattern for callback functions
	 *
	 * @access public
	 * @var string
	 */
	public $callback_preg = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\(.*?\))?';

	// NEW: Added in Seditio version to enable/disable callbacks
	/**
	 * Flag to allow callback functions in templates
	 *
	 * @access public
	 * @var boolean
	 */
	public $allow_callbacks = true;

	// NEW: Added in Seditio version to define allowed callback functions
	/**
	 * Array of allowed callback functions
	 *
	 * @access public
	 * @var array
	 */
	public $allowed_callbacks = [
		// Simple string modifiers
		'strtoupper', 'strtolower', 'ucwords', 'ucfirst', 'strrev', 'str_word_count', 'strlen',
		// String replacement modifiers
		'str_replace', 'str_ireplace', 'preg_replace', 'strip_tags', 'stripcslashes', 'stripslashes', 'substr',
		'str_pad', 'str_repeat', 'strtr', 'trim', 'ltrim', 'rtrim', 'nl2br', 'wordwrap', 'printf', 'sprintf',
		'addslashes', 'addcslashes',
		// Encoding / decoding modifiers
		'htmlentities', 'html_entity_decode', 'htmlspecialchars', 'htmlspecialchars_decode',
		'urlencode', 'urldecode',
		// Date / time modifiers
		'date', 'idate', 'strtotime', 'strftime', 'getdate', 'gettimeofday',
		// Number modifiers
		'number_format', 'money_format',
		// Miscellaneous modifiers
		'var_dump', 'print_r'
	];

	/**
	 * Default main template block name
	 *
	 * @access public
	 * @var string
	 */
	public $mainblock = 'main';

	/**
	 * Script output type
	 *
	 * @access public
	 * @var string
	 */
	public $output_type = 'HTML';

	// NEW: Added in Seditio version to force global variable initialization
	/**
	 * Flag to force initialization of global variables even with JIT enabled
	 *
	 * @access public
	 * @var boolean
	 */
	public $force_globals = true;

	/**
	 * Debug mode
	 *
	 * @access public
	 * @var boolean
	 */
	public $debug = false;

	// NEW: Added in Seditio version for HTML output compression
	/**
	 * Flag to enable HTML output compression (minification)
	 *
	 * @access public
	 * @var boolean
	 */
	public $compress_output = false; // HTML minify sed 177 by Amro

	/**
	 * Null string for unassigned vars
	 *
	 * @access protected
	 * @var array
	 */
	protected $_null_string = array('' => '');

	/**
	 * Null string for unassigned blocks
	 *
	 * @access protected
	 * @var array
	 */
	protected $_null_block = array('' => '');

	/**
	 * Errors
	 *
	 * @access protected
	 * @var string
	 */
	protected $_error = '';

	/**
	 * Auto-reset sub blocks
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_autoreset = true;

	/**
	 * Set to FALSE to generate errors if a non-existent blocks is referenced
	 *
	 * @author NW
	 * @since 2002/10/17
	 * @access protected
	 * @var boolean
	 */
	protected $_ignore_missing_blocks = true;

	/**
	 * PHP 5 Constructor - Instantiate the object
	 *
	 * @param string $file Template file to work on (changed in Seditio to $options)
	 * @param string/array $tpldir Location of template files (useful for keeping files outside web server root)
	 * @param array $files Filenames lookup
	 * @param string $mainblock Name of main block in the template
	 * @param boolean $autosetup If true, run setup() as part of constructor
	 * @return XTemplate
	 */
	public function __construct($options, $tpldir = '', $files = null, $mainblock = 'main', $autosetup = true)
	{
		// NEW: Seditio version accepts an options array or string, with additional handling
		if (!is_array($options)) {
			$options = array('file' => $options, 'path' => $tpldir, 'files' => $files, 'mainblock' => $mainblock, 'autosetup' => $autosetup);
		}

		if (!isset($options['tag_start'])) {
			$options['tag_start'] = $this->tag_start_delim;
		}
		if (!isset($options['tag_end'])) {
			$options['tag_end'] = $this->tag_end_delim;
		}

		$this->restart($options);
	}

	/**
	 * Restart the class - allows one instantiation with several files processed by restarting
	 * e.g. $xtpl = new XTemplate('file1.xtpl');
	 * $xtpl->parse('main');
	 * $xtpl->out('main');
	 * $xtpl->restart('file2.xtpl');
	 * $xtpl->parse('main');
	 * $xtpl->out('main');
	 * (Added in response to sf:641407 feature request)
	 *
	 * @param string $file Template file to work on (changed in Seditio to $options)
	 * @param string/array $tpldir Location of template files
	 * @param array $files Filenames lookup
	 * @param string $mainblock Name of main block in the template
	 * @param boolean $autosetup If true, run setup() as part of restarting
	 * @param string $tag_start {
	 * @param string $tag_end }
	 */
	public function restart($options, $tpldir = '', $files = null, $mainblock = 'main', $autosetup = true, $tag_start = '{', $tag_end = '}')
	{
		// NEW: Seditio version processes options as an array or string with extended functionality
		if (is_array($options)) {
			foreach ($options as $option => $value) {
				switch ($option) {
					case 'path':
					case 'tpldir':
						$tpldir = $value;
						break;

					case 'callbacks':
						$this->allow_callbacks = true;
						$this->allowed_callbacks = array_merge($this->allowed_callbacks, (array) $value);
						break;

					case 'debug':
						$this->debug = $value;
						break;

					case 'file':
					case 'files':
					case 'mainblock':
					case 'autosetup':
					case 'tag_start':
					case 'tag_end':
						$$option = $value;
						break;
				}
			}

			$this->filename = $file;
		} else {
			$this->filename = $options;
		}

		if (isset($tpldir)) {
			$this->tpldir = $tpldir;
		}
		if (defined('XTPL_DIR') && empty($this->tpldir)) {
			$this->tpldir = XTPL_DIR;
		}

		if (isset($files) && is_array($files)) {
			$this->files = $files;
		}

		if (isset($mainblock)) {
			$this->mainblock = $mainblock;
		}

		if (isset($tag_start)) {
			$this->tag_start_delim = $tag_start;
		}

		if (isset($tag_end)) {
			$this->tag_end_delim = $tag_end;
		}

		$this->filecontents = '';

		$this->blocks = array();
		$this->parsed_blocks = array();
		$this->preparsed_blocks = array();
		$this->block_parse_order = array();
		$this->sub_blocks = array();
		$this->vars = array();
		$this->filevars = array();
		$this->filevar_parent = array();
		$this->filecache = array();

		// NEW: Seditio version adds callback regex preparation
		if ($this->allow_callbacks) {
			$delim = preg_quote($this->callback_delim);
			if (mb_strlen($this->callback_delim) < mb_strlen($delim)) {
				$delim = preg_quote($delim);
			}
			$this->callback_preg = preg_replace($this->preg_delimiter . '^\(' . $delim . '(.*)\)\*$' . $this->preg_delimiter, '\\1', $this->callback_preg);
		}

		if (!isset($autosetup) || $autosetup) {
			$this->setup();
		}
	}

	/**
	 * setup - the elements that were previously in the constructor
	 *
	 * @access public
	 * @param boolean $add_outer If true is passed when called, it adds an outer main block to the file
	 */
	public function setup($add_outer = false)
	{
		$this->tag_start_delim = preg_quote($this->tag_start_delim);
		$this->tag_end_delim = preg_quote($this->tag_end_delim);

		// Setup the file delimiters
		// NEW: Seditio version uses preg_delimiter for regex patterns
		$this->file_delim = $this->preg_delimiter . $this->tag_start_delim . "FILE\s*\"([^\"]+)\"" . $this->comment_preg . $this->tag_end_delim . $this->preg_delimiter . 'm';
		$this->filevar_delim = $this->preg_delimiter . $this->tag_start_delim . "FILE\s*" . $this->tag_start_delim . "([A-Za-z0-9\._\x7f-\xff]+?)" . $this->comment_preg . $this->tag_end_delim . $this->comment_preg . $this->tag_end_delim . $this->preg_delimiter . 'm';
		$this->filevar_delim_nl = $this->preg_delimiter . "^\s*" . $this->tag_start_delim . "FILE\s*" . $this->tag_start_delim . "([A-Za-z0-9\._\x7f-\xff]+?)" . $this->comment_preg . $this->tag_end_delim . $this->comment_preg . $this->tag_end_delim . "\s*\n" . $this->preg_delimiter . 'm';

		// NEW: Seditio version adds callback regex setup
		$this->callback_preg = '(' . preg_quote($this->callback_delim) . $this->callback_preg . ')*';

		if (empty($this->filecontents)) {
			$this->filecontents = $this->_r_getfile($this->filename);
		}

		if ($add_outer) {
			$this->_add_outer_block();
		}

		$this->blocks = $this->_maketree($this->filecontents, '');
		$this->filevar_parent = $this->_store_filevar_parents($this->blocks);
		$this->scan_globals();
	}

	/***************************************************************************/
	/***[ public stuff ]********************************************************/
	/***************************************************************************/

	/**
	 * assign a variable
	 *
	 * @example Simplest case:
	 * @example $xtpl->assign('name', 'value');
	 * @example {name} in template
	 *
	 * @example Array assign:
	 * @example $xtpl->assign(array('name' => 'value', 'name2' => 'value2'));
	 * @example {name} {name2} in template
	 *
	 * @example Value as array assign:
	 * @example $xtpl->assign('name', array('key' => 'value', 'key2' => 'value2'));
	 * @example {name.key} {name.key2} in template
	 *
	 * @example Reset array:
	 * @example $xtpl->assign('name', array('key' => 'value', 'key2' => 'value2'));
	 * @example // Other code then:
	 * @example $xtpl->assign('name', array('key3' => 'value3'), false);
	 * @example {name.key} {name.key2} {name.key3} in template
	 *
	 * @access public
	 * @param string $name Variable to assign $val to
	 * @param string / array $val Value to assign to $name
	 * @param boolean $reset_array Reset the variable array if $val is an array
	 */
	public function assign($name, $val = '', $reset_array = true)
	{
		// NEW: Seditio version adds support for objects in addition to arrays
		if (is_array($name) || is_object($name)) {
			foreach ($name as $k => $v) {
				$this->vars[$k] = $v;
			}
		} elseif (is_array($val) || is_object($val)) {
			if ($reset_array) {
				$this->vars[$name] = array();
			}
			foreach ($val as $k => $v) {
				$this->vars[$name][$k] = $v;
			}
		} else {
			$this->vars[$name] = $val;
		}
	}

	/**
	 * assign a file variable
	 *
	 * @access public
	 * @param string $name Variable to assign $val to
	 * @param string / array $val Values to assign to $name
	 */
	public function assign_file($name, $val = '')
	{
		if (is_array($name)) {
			foreach ($name as $k => $v) {
				$this->_assign_file_sub($k, $v);
			}
		} else {
			$this->_assign_file_sub($name, $val);
		}
	}

	/**
	 * parse a block
	 *
	 * @access public
	 * @param string $bname Block name to parse
	 */
	public function parse($bname)
	{
		if (isset($this->preparsed_blocks[$bname])) {
			$copy = $this->preparsed_blocks[$bname];
		} elseif (isset($this->blocks[$bname])) {
			$copy = $this->blocks[$bname];
		} elseif ($this->_ignore_missing_blocks) {
			// ------------------------------------------------------
			// NW : 17 Oct 2002. Added default of ignore_missing_blocks
			//      to allow for generalised processing where some
			//      blocks may be removed from the HTML without the
			//      processing code needing to be altered.
			// ------------------------------------------------------
			// JRC: 3/1/2003 added set error to ignore missing functionality
			$this->_set_error("parse: blockname [$bname] does not exist");
			return;
		} else {
			$this->_set_error("parse: blockname [$bname] does not exist");
		}

		if (!isset($copy)) {
			die('Block: ' . $bname);
		}

		$copy = preg_replace($this->filevar_delim_nl, '', $copy);

		$var_array = array();

		// NEW: Seditio version uses preg_delimiter and adds callback support in regex
		preg_match_all($this->preg_delimiter . $this->tag_start_delim . '([A-Za-z0-9\._\x7f-\xff]+?' . $this->callback_preg . $this->comment_preg . ')' . $this->tag_end_delim . $this->preg_delimiter, $copy, $var_array);

		$var_array = $var_array[1];

		foreach ($var_array as $k => $v) {
			$orig_v = $v;

			// NEW: Seditio version uses comment_delim instead of # directly
			$comment = '';
			$any_comments = explode($this->comment_delim, $v);
			if (count($any_comments) > 1) {
				$comment = array_pop($any_comments);
			}
			$v = rtrim(implode($this->comment_delim, $any_comments));

			// NEW: Seditio version adds callback function processing
			if ($this->allow_callbacks) {
				$callback_funcs = explode($this->callback_delim, $v);
				$v = rtrim($callback_funcs[0]);
				unset($callback_funcs[0]);
			}

			$sub = explode('.', $v);

			if ($sub[0] == '_BLOCK_') {
				unset($sub[0]);
				$bname2 = implode('.', $sub);
				$var = isset($this->parsed_blocks[$bname2]) ? $this->parsed_blocks[$bname2] : '';
				$nul = (!isset($this->_null_block[$bname2])) ? $this->_null_block[''] : $this->_null_block[$bname2];

				if ($var === '') {
					if ($nul == '') {
						$copy = preg_replace($this->preg_delimiter . $this->tag_start_delim . $v . $this->tag_end_delim . $this->preg_delimiter . 'm', '', $copy);
					} else {
						$copy = preg_replace($this->preg_delimiter . $this->tag_start_delim . $v . $this->tag_end_delim . $this->preg_delimiter . 'm', "$nul", $copy);
					}
				} else {
					// NEW: Seditio version uses mb_substr instead of substr for multibyte support
					switch (true) {
						case preg_match($this->preg_delimiter . "^\n" . $this->preg_delimiter, $var) && preg_match($this->preg_delimiter . "\n$" . $this->preg_delimiter, $var):
							$var = mb_substr($var, 1, -1);
							break;
						case preg_match($this->preg_delimiter . "^\n" . $this->preg_delimiter, $var):
							$var = mb_substr($var, 1);
							break;
						case preg_match($this->preg_delimiter . "\n$" . $this->preg_delimiter, $var):
							$var = mb_substr($var, 0, -1);
							break;
					}

					$var = str_replace('\\', '\\\\', $var);
					$var = str_replace('$', '\\$', $var);
					$var = str_replace('\\|', '|', $var);
					$copy = preg_replace($this->preg_delimiter . $this->tag_start_delim . $v . $this->tag_end_delim . $this->preg_delimiter . 'm', "$var", $copy);

					if (preg_match($this->preg_delimiter . "^\n" . $this->preg_delimiter, $copy) && preg_match($this->preg_delimiter . "\n$" . $this->preg_delimiter, $copy)) {
						$copy = mb_substr($copy, 1, -1);
					}
				}
			} else {
				$var = $this->vars;

				foreach ($sub as $v1) {
					// NEW: Seditio version adds object support and uses mb_strlen
					switch (true) {
						case is_array($var):
							if (!isset($var[$v1]) || (is_string($var[$v1]) && mb_strlen($var[$v1]) == 0)) {
								if (defined($v1)) {
									$var[$v1] = constant($v1);
								} else {
									$var[$v1] = null;
								}
							}
							$var = $var[$v1];
							break;
						case is_object($var):
							if (!isset($var->$v1) || (is_string($var->$v1) && mb_strlen($var->$v1) == 0)) {
								if (defined($v1)) {
									$var->$v1 = constant($v1);
								} else {
									$var->$v1 = null;
								}
							}
							$var = $var->$v1;
							break;
					}
				}

				// NEW: Seditio version adds callback function execution
				if ($this->allow_callbacks) {
					if (is_array($callback_funcs) && !empty($callback_funcs)) {
						foreach ($callback_funcs as $callback) {
							if (preg_match($this->preg_delimiter . '\((.*?)\)' . $this->preg_delimiter, $callback, $matches)) {
								$parameters = array();
								if (preg_match_all($this->preg_delimiter . '(?#
                                    match optional comma, optional other stuff, then
                                    apostrophes / quotes then stuff followed by comma or
                                    closing bracket negative look behind for an apostrophe
                                    or quote not preceeded by an escaping back slash
                                    )[,?\s*?]?[\'|"](.*?)(?<!\\\\)(?<=[\'|"])[,|\)$](?#
                                    OR match optional comma, optional other stuff, then
                                    multiple word \w with look behind % for our %s followed
                                    by comma or closing bracket
                                    )|,?\s*?([\w(?<!\%)]+)[,|\)$]' . $this->preg_delimiter, $matches[1] . ')', $param_matches)) {
									$parameters = $param_matches[0];
								}
								if (count($parameters)) {
									array_walk($parameters, array($this, 'trim_callback'));
									if (($key = array_search('%s', $parameters)) !== false) {
										$parameters[$key] = $var;
									} else {
										array_unshift($parameters, $var);
									}
								} else {
									unset($parameters);
								}
							}

							$callback = preg_replace($this->preg_delimiter . '\(.*?\)' . $this->preg_delimiter, '', $callback);

							if (is_subclass_of($this, 'XTemplate') && method_exists($this, $callback) && is_callable(array($this, $callback))) {
								if (isset($parameters)) {
									$var = call_user_func_array(array($this, $callback), $parameters);
									unset($parameters);
								} else {
									$var = call_user_func(array($this, $callback), $var);
								}
							} elseif (in_array($callback, $this->allowed_callbacks) && function_exists($callback) && is_callable($callback)) {
								if (isset($parameters)) {
									$var = call_user_func_array($callback, $parameters);
									unset($parameters);
								} else {
									$var = call_user_func($callback, isset($var) ? $var : '');
								}
							}
						}
					}
				}

				$nul = (!isset($this->_null_string[$v])) ? ($this->_null_string[""]) : ($this->_null_string[$v]);
				$var = (!isset($var)) ? $nul : $var;

				// NEW: Seditio version adds special handling for string variables
				if (is_string($var)) {
					if ($var === '') {
						$copy = preg_replace($this->preg_delimiter . $this->tag_start_delim . preg_quote($orig_v) . $this->tag_end_delim . $this->preg_delimiter . 'm', '', $copy);
					} else {
						$var = str_replace('\\', '\\\\', $var);
						$var = str_replace('$', '\\$', $var);
						$var = str_replace('\\|', '|', $var);
					}
				}

				$copy = preg_replace($this->preg_delimiter . $this->tag_start_delim . preg_quote($orig_v) . $this->tag_end_delim . $this->preg_delimiter . 'm', "$var", $copy);

				if (preg_match($this->preg_delimiter . "^\n" . $this->preg_delimiter, $copy) && preg_match($this->preg_delimiter . "\n$" . $this->preg_delimiter, $copy)) {
					$copy = mb_substr($copy, 1);
				}
			}
		}

		if (isset($this->parsed_blocks[$bname])) {
			$this->parsed_blocks[$bname] .= $copy;
		} else {
			$this->parsed_blocks[$bname] = $copy;
		}

		if ($this->_autoreset && (!empty($this->sub_blocks[$bname]))) {
			reset($this->sub_blocks[$bname]);
			foreach ($this->sub_blocks[$bname] as $k => $v) {
				$this->reset($v);
			}
		}
	}

	/**
	 * returns the parsed text for a block, including all sub-blocks.
	 *
	 * @access public
	 * @param string $bname Block name to parse
	 */
	public function rparse($bname)
	{
		if (!empty($this->sub_blocks[$bname])) {
			reset($this->sub_blocks[$bname]);
			foreach ($this->sub_blocks[$bname] as $k => $v) {
				if (!empty($v)) {
					$this->rparse($v);
				}
			}
		}
		$this->parse($bname);
	}

	/**
	 * inserts a loop ( call assign & parse )
	 *
	 * @access public
	 * @param string $bname Block name to assign
	 * @param string $var Variable to assign values to
	 * @param string / array $value Value to assign to $var
	 */
	public function insert_loop($bname, $var, $value = '')
	{
		$this->assign($var, $value);
		$this->parse($bname);
	}

	/**
	 * parses a block for every set of data in the values array
	 *
	 * @access public
	 * @param string $bname Block name to loop
	 * @param string $var Variable to assign values to
	 * @param array $values Values to assign to $var
	 */
	public function array_loop($bname, $var, &$values)
	{
		if (is_array($values)) {
			foreach ($values as $v) {
				$this->insert_loop($bname, $var, $v);
			}
		}
	}

	// NEW: Seditio version adds compress method for HTML minification
	/**
	 * Compresses HTML output by removing comments, newlines, and extra spaces
	 *
	 * @access public
	 * @param string $out The HTML content to compress
	 * @return string Compressed HTML content
	 */
	public function compress($out)
	{
		$out = preg_replace("/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/", "", $out);
		$out = str_replace(array("\r\n", "\r", "\n", "\t", "  ", "    ", "    "), "", $out);
		return $out;
	}

	/**
	 * returns the parsed text for a block
	 *
	 * @access public
	 * @param string $bname Block name to return
	 * @return string
	 */
	public function text($bname = '')
	{
		$text = '';

		if ($this->debug && $this->output_type == 'HTML') {
			// NEW: Seditio version enhances debug output with more detailed path info
			$text .= '<!-- XTemplate debug TEXT: ' . $bname . ' ';
			if (is_array($this->tpldir)) {
				foreach ($this->tpldir as $dir) {
					if (is_readable($dir . DIRECTORY_SEPARATOR . $this->filename)) {
						$text .= realpath($dir . DIRECTORY_SEPARATOR . $this->filename);
						break;
					}
				}
			} elseif (!empty($this->tpldir)) {
				$text .= realpath($this->tpldir . DIRECTORY_SEPARATOR . $this->filename);
			} else {
				$text .= $this->filename;
			}
			$text .= " -->\n";
		}

		$bname = !empty($bname) ? $bname : $this->mainblock;

		$text .= isset($this->parsed_blocks[$bname]) ? $this->parsed_blocks[$bname] : $this->get_error();

		return $text;
	}

	/**
	 * prints the parsed text
	 *
	 * @access public
	 * @param string $bname Block name to echo out
	 */
	public function out($bname)
	{
		$out = $this->text($bname);
		// NEW: Seditio version adds trimming and optional compression
		$out = preg_replace('/\s+$/m', '', $out); // fix Amro 04.11.2017
		if ($this->compress_output) {
			$out = $this->compress($out);
		}
		echo trim($out);
	}

	/**
	 * prints the parsed text to a specified file
	 *
	 * @access public
	 * @param string $bname Block name to write out
	 * @param string $fname File name to write to
	 */
	public function out_file($bname, $fname)
	{
		if (!empty($bname) && !empty($fname) && is_writeable($fname)) {
			$fp = fopen($fname, 'w');
			fwrite($fp, $this->text($bname));
			fclose($fp);
		}
	}

	/**
	 * resets the parsed text
	 *
	 * @access public
	 * @param string $bname Block to reset
	 */
	public function reset($bname)
	{
		$this->parsed_blocks[$bname] = '';
	}

	/**
	 * returns true if block was parsed, false if not
	 *
	 * @access public
	 * @param string $bname Block name to test
	 * @return boolean
	 */
	public function parsed($bname)
	{
		return (!empty($this->parsed_blocks[$bname]));
	}

	/**
	 * sets the string to replace in case the var was not assigned
	 *
	 * @access public
	 * @param string $str Display string for null block
	 * @param string $varname Variable name to apply $str to
	 */
	public function set_null_string($str, $varname = '')
	{
		$this->_null_string[$varname] = $str;
	}

	/**
	 * Backwards compatibility only
	 *
	 * @param string $str
	 * @param string $varname
	 * @deprecated Change to set_null_string to keep in with rest of naming convention
	 */
	public function SetNullString($str, $varname = '')
	{
		$this->set_null_string($str, $varname);
	}

	/**
	 * sets the string to replace in case the block was not parsed
	 *
	 * @access public
	 * @param string $str Display string for null block
	 * @param string $bname Block name to apply $str to
	 */
	public function set_null_block($str, $bname = '')
	{
		$this->_null_block[$bname] = $str;
	}

	/**
	 * Backwards compatibility only
	 *
	 * @param string $str
	 * @param string $bname
	 * @deprecated Change to set_null_block to keep in with rest of naming convention
	 */
	public function SetNullBlock($str, $bname = '')
	{
		$this->set_null_block($str, $bname);
	}

	/**
	 * sets AUTORESET to 1. (default is 1)
	 * if set to 1, parse() automatically resets the parsed blocks' sub blocks
	 * (for multiple level blocks)
	 *
	 * @access public
	 */
	public function set_autoreset()
	{
		$this->_autoreset = true;
	}

	/**
	 * sets AUTORESET to 0. (default is 1)
	 * if set to 1, parse() automatically resets the parsed blocks' sub blocks
	 * (for multiple level blocks)
	 *
	 * @access public
	 */
	public function clear_autoreset()
	{
		$this->_autoreset = false;
	}

	/**
	 * scans global variables and assigns to PHP array
	 *
	 * @access public
	 */
	public function scan_globals()
	{
		// NEW: Seditio version enhances global scanning with force_globals and specific handling
		$GLOB = array();

		if ($this->force_globals && ini_get('auto_globals_jit') == true) {
			$tmp = $_SERVER;
			$tmp = $_ENV;
			$tmp = $_REQUEST;
			unset($tmp);
		}

		foreach ($GLOBALS as $k => $v) {
			$GLOB[$k] = array();

			switch ($k) {
				case 'GLOBALS':
					break;
				case '_COOKIE':
				case '_SESSION':
					$GLOB[$k] = array_merge($GLOB[$k], $v);
					break;
				case '_ENV':
				case '_FILES':
				case '_GET':
				case '_POST':
				case '_REQUEST':
				case '_SERVER':
				default:
					$GLOB[$k] = $v;
					break;
			}
		}

		$this->assign('PHP', $GLOB);
	}

	/**
	 * gets error condition / string
	 *
	 * @access public
	 * @return boolean / string
	 */
	public function get_error()
	{
		// JRC: 3/1/2003 Added ouptut wrapper and detection of output type for error message output
		$retval = false;

		if ($this->_error != '') {
			switch ($this->output_type) {
				case 'HTML':
				case 'html':
					$retval = '<b>[XTemplate]</b><ul>' . nl2br(str_replace('* ', '<li>', str_replace(" *\n", "</li>\n", $this->_error))) . '</ul>';
					break;
				default:
					$retval = '[XTemplate] ' . str_replace(' *\n', "\n", $this->_error);
					break;
			}
		}

		return $retval;
	}

	/***************************************************************************/
	/***[ private stuff ]*******************************************************/
	/***************************************************************************/

	/**
	 * generates the array containing to-be-parsed stuff:
	 * $blocks["main"],$blocks["main.table"],$blocks["main.table.row"], etc.
	 * also builds the reverse parse order.
	 *
	 * @access public - aiming for private
	 * @param string $con content to be processed
	 * @param string $parentblock name of the parent block in the block hierarchy
	 */
	public function _maketree($con, $parentblock = '')
	{
		$blocks = array();

		$con2 = explode($this->block_start_delim, $con);

		if (!empty($parentblock)) {
			$block_names = explode('.', $parentblock);
			$level = sizeof($block_names);
		} else {
			$block_names = array();
			$level = 0;
		}

		// JRC 06/04/2005 Added block comments (on BEGIN or END) <!-- BEGIN: block_name#Comments placed here -->
		$patt = "(" . $this->block_start_word . "|" . $this->block_end_word . ")\s*(\w+)" . $this->comment_preg . "\s*" . $this->block_end_delim . "(.*)";

		foreach ($con2 as $k => $v) {
			$res = array();

			// NEW: Seditio version uses preg_delimiter in pattern matching
			if (preg_match_all($this->preg_delimiter . "$patt" . $this->preg_delimiter . 'ims', $v, $res, PREG_SET_ORDER)) {
				$block_word = $res[0][1];
				$block_name = $res[0][2];
				$comment = $res[0][3];
				$content = $res[0][4];

				// NEW: Seditio version uses mb_strtoupper instead of strtoupper
				if (mb_strtoupper($block_word) == $this->block_start_word) {
					$parent_name = implode('.', $block_names);
					$block_names[++$level] = $block_name;
					$cur_block_name = implode('.', $block_names);
					$this->block_parse_order[] = $cur_block_name;
					$blocks[$cur_block_name] = isset($blocks[$cur_block_name]) ? $blocks[$cur_block_name] . $content : $content;
					$blocks[$parent_name] .= str_replace('\\', '', $this->tag_start_delim) . '_BLOCK_.' . $cur_block_name . str_replace('\\', '', $this->tag_end_delim);
					$this->sub_blocks[$parent_name][] = $cur_block_name;
					$this->sub_blocks[$cur_block_name][] = '';
				} else if (mb_strtoupper($block_word) == $this->block_end_word) {
					unset($block_names[$level--]);
					$parent_name = implode('.', $block_names);
					$blocks[$parent_name] .= $content;
				}
			} else {
				$tmp = implode('.', $block_names);
				if ($k) {
					$blocks[$tmp] .= $this->block_start_delim;
				}
				$blocks[$tmp] = isset($blocks[$tmp]) ? $blocks[$tmp] . $v : $v;
			}
		}

		return $blocks;
	}

	/**
	 * Sub processing for assign_file method
	 *
	 * @access private
	 * @param string $name
	 * @param string $val
	 */
	private function _assign_file_sub($name, $val)
	{
		if (isset($this->filevar_parent[$name])) {
			if ($val != '') {
				$val = $this->_r_getfile($val);

				foreach ($this->filevar_parent[$name] as $parent) {
					if (isset($this->preparsed_blocks[$parent]) && !isset($this->filevars[$name])) {
						$copy = $this->preparsed_blocks[$parent];
					} elseif (isset($this->blocks[$parent])) {
						$copy = $this->blocks[$parent];
					}

					$res = array();

					preg_match_all($this->filevar_delim, $copy, $res, PREG_SET_ORDER);

					if (is_array($res) && isset($res[0])) {
						foreach ($res as $v) {
							if ($v[1] == $name) {
								// NEW: Seditio version uses preg_delimiter in replacement
								$copy = preg_replace($this->preg_delimiter . preg_quote($v[0]) . $this->preg_delimiter, "$val", $copy);
								$this->preparsed_blocks = array_merge($this->preparsed_blocks, $this->_maketree($copy, $parent));
								$this->filevar_parent = array_merge($this->filevar_parent, $this->_store_filevar_parents($this->preparsed_blocks));
							}
						}
					}
				}
			}
		}

		$this->filevars[$name] = $val;
	}

	/**
	 * store container block's name for file variables
	 *
	 * @access public - aiming for private
	 * @param array $blocks
	 * @return array
	 */
	public function _store_filevar_parents($blocks)
	{
		$parents = array();

		foreach ($blocks as $bname => $con) {
			$res = array();
			preg_match_all($this->filevar_delim, $con, $res);
			foreach ($res[1] as $k => $v) {
				$parents[$v][] = $bname;
			}
		}
		return $parents;
	}

	/**
	 * Set the error string
	 *
	 * @access private
	 * @param string $str
	 */
	private function _set_error($str)
	{
		// JRC: 3/1/2003 Made to append the error messages
		$this->_error .= '* ' . $str . " *\n";
		// JRC: 3/1/2003 Removed trigger error, use this externally if you want it eg. trigger_error($xtpl->get_error())
	}

	/**
	 * returns the contents of a file
	 *
	 * @access protected
	 * @param string $file
	 * @return string
	 */
	protected function _getfile($file)
	{
		if (!isset($file)) {
			$this->_set_error('!isset file name!' . $file);
			return '';
		}

		if (isset($this->files)) {
			if (isset($this->files[$file])) {
				$file = $this->files[$file];
			}
		}

		if (!empty($this->tpldir)) {
			if (is_array($this->tpldir)) {
				foreach ($this->tpldir as $dir) {
					if (is_readable($dir . DIRECTORY_SEPARATOR . $file)) {
						$file = $dir . DIRECTORY_SEPARATOR . $file;
						break;
					}
				}
			} else {
				$file = $this->tpldir . DIRECTORY_SEPARATOR . $file;
			}
		}

		$file_text = '';

		if (isset($this->filecache[$file])) {
			$file_text .= $this->filecache[$file];
			// NEW: Seditio version enhances debug output for cached files
			if ($this->debug && $this->output_type == 'HTML') {
				$file_text = '<!-- XTemplate debug CACHED: ' . realpath($file) . ' -->' . "\n" . $file_text;
			}
		} else {
			if (is_file($file) && is_readable($file)) {
				if (filesize($file)) {
					if (!($fh = fopen($file, 'r'))) {
						$this->_set_error('Cannot open file: ' . realpath($file));
						return '';
					}
					$file_text .= fread($fh, filesize($file));
					fclose($fh);
				}
				if ($this->debug && $this->output_type == 'HTML') {
					$file_text = '<!-- XTemplate debug: ' . realpath($file) . ' -->' . "\n" . $file_text;
				}
			} elseif (str_replace('.', '', phpversion()) >= '430' && $file_text = @file_get_contents($file, true)) {
				if ($file_text === false) {
					$this->_set_error("[" . realpath($file) . "] ($file) does not exist");
					// NEW: Seditio version adjusts error output based on output_type
					if ($this->output_type == 'HTML') {
						$file_text = "<b>__XTemplate fatal error: file [$file] does not exist in the include path__</b>";
					}
				} elseif ($this->debug && $this->output_type == 'HTML') {
					$file_text = '<!-- XTemplate debug (via include path): ' . realpath($file) . ' -->' . "\n" . $file_text;
				}
			} elseif (!is_file($file)) {
				$this->_set_error("[" . realpath($file) . "] ($file) does not exist");
				if ($this->output_type == 'HTML') {
					$file_text .= "<b>__XTemplate fatal error: file [$file] does not exist__</b>";
				}
			} elseif (!is_readable($file)) {
				$this->_set_error("[" . realpath($file) . "] ($file) is not readable");
				if ($this->output_type == 'HTML') {
					$file_text .= "<b>__XTemplate fatal error: file [$file] is not readable__</b>";
				}
			}

			$this->filecache[$file] = $file_text;
		}

		return $file_text;
	}

	/**
	 * recursively gets the content of a file with {FILE "filename.tpl"} directives
	 *
	 * @access public - aiming for private
	 * @param string $file
	 * @return string
	 */
	public function _r_getfile($file)
	{
		$text = $this->_getfile($file);

		$res = array();

		// NEW: Seditio version uses preg_delimiter in file inclusion
		while (preg_match($this->file_delim, $text, $res)) {
			$text2 = $this->_getfile($res[1]);
			$text = preg_replace($this->preg_delimiter . preg_quote($res[0]) . $this->preg_delimiter, $text2, $text);
		}

		return $text;
	}

	// NEW: Seditio version adds trim_callback for processing callback parameters
	/**
	 * Trims and processes callback parameters
	 *
	 * @access protected
	 * @param string &$value Reference to the parameter value to trim
	 */
	protected function trim_callback(&$value)
	{
		$value = preg_replace($this->preg_delimiter . "^.*(%s).*$" . $this->preg_delimiter, '\\1', trim($value));
		$value = preg_replace($this->preg_delimiter . '^,?\s*?(.*?)[,|\)]?$' . $this->preg_delimiter, '\\1', trim($value));
		$value = preg_replace($this->preg_delimiter . '^[\'|"]?(.*?)[\'|"]?$' . $this->preg_delimiter, '\\1', trim($value));
		$value = preg_replace($this->preg_delimiter . '\\\\(?=\'|")' . $this->preg_delimiter, '', $value);
		// Deal with escaped commas (beta)
		$value = preg_replace($this->preg_delimiter . '\\\,' . $this->preg_delimiter, ',', $value);
	}

	/**
	 * add an outer block delimiter set useful for rtfs etc - keeps them editable in word
	 *
	 * @access private
	 */
	private function _add_outer_block()
	{
		$before = $this->block_start_delim . $this->block_start_word . ' ' . $this->mainblock . ' ' . $this->block_end_delim;
		$after = $this->block_start_delim . $this->block_end_word . ' ' . $this->mainblock . ' ' . $this->block_end_delim;

		$this->filecontents = $before . "\n" . $this->filecontents . "\n" . $after;
	}

	/**
	 * Debug function - var_dump wrapped in '<pre></pre>' tags
	 *
	 * @access private
	 * @param multiple var_dumps all the supplied arguments
	 */
	private function _pre_var_dump($args)
	{
		if ($this->debug) {
			echo '<pre>';
			var_dump(func_get_args());
			echo '</pre>';
		}
	}

	// NEW: Seditio version adds _ob_var_dump for buffered debug output
	/**
	 * Debug function with output buffering
	 *
	 * @access protected
	 * @param multiple $args Arguments to var_dump
	 * @return string Buffered debug output
	 */
	protected function _ob_var_dump($args)
	{
		if ($this->debug) {
			ob_start();
			$this->_pre_var_dump(func_get_args());
			return ob_get_clean();
		}
	}
}
