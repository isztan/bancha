<?php
/**
 * Feed Class
 *
 * This library generates the feeds
 *
 * @package		Bancha
 * @author		Nicholas Valbusa - info@squallstar.it - @squallstar
 * @copyright	Copyright (c) 2011, Squallstar
 * @license		GNU/GPL (General Public License)
 * @link		http://squallstar.it
 *
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

Class Feed
{
	/**
	 * @var mixed CodeIgniter instance
	 */
	private $CI;

	/**
	 * @var string Feed type (xml, json)
	 */
	private $_type;

	/**
	 * @var SimpleXMLElement Will contains the feed
	 */
	public $xml;

	/**
	 * @var array Feed headers
	 */
	public $feed_head;

	/**
	 * @var array Will contains the JSON data
	 */
	public $json;

	public function __construct()
	{
		$this->CI = & get_instance();
		$this->CI->load->library('parser');

		$this->feed_head = array(
			'title'			=> CMS,
			'description'	=> 'A standard feed generated by ' . CMS,
			'link'			=> current_url(),
			'last_update'	=> date(DATE_RFC822),
			'lang'			=> $this->CI->lang->current_language,
			'generator'		=> CMS . ' ' . BANCHA_VERSION
		);
	}

	/**
	 * Create a new feed
	 * @param array $data Feed header
	 * @param string $type Feed type
	 */
	public function create_new($data, $type='xml')
	{
		foreach ($data as $key => $val)
		{
			if ($val)
			{
				$this->feed_head[$key] = $val;
			}
		}

		if ($type == 'xml')
		{
			$xmlstring = read_file($this->CI->config->item('templates_folder').'RSSFeed.xml');
			$xmlstring = $this->CI->parser->parse_string($xmlstring, $this->feed_head, TRUE);
     		$this->xml = new SimpleXMLElement($xmlstring);
		} else if ($type == 'json')
		{
			foreach ($this->feed_head as $key => $val)
			{
				$this->json[$key] = $val;
			}
			$this->json['data'] = array();
		} else {
			show_error('Unknown feed type: '.$type);
		}
		$this->_type = $type;
     	return $this;
	}

	/**
	 * Adds an element to the feed
	 * @param array $item
	 */
	public function add_item($item)
	{
		if ($this->_type == 'xml')
		{
			$child = $this->xml->channel->addChild('item');
			foreach ($item as $key => $val)
			{
				$child->addChild($key, $val);
			}
		} else if ($this->_type == 'json')
		{
			$this->json['data'][] = $item;
		}

		return $this;
	}

	/**
	 * Renders the feed
	 * @param string $template
	 */
	public function render($template='feed')
	{
		$this->CI->output->enable_profiler(FALSE);
		$this->CI->view->set('type', $this->_type);

		if ($this->_type == 'xml')
		{
			$this->CI->view->set('feed', $this->xml->asXML());
			$this->CI->output->set_content_type('application/rss+xml');
		} else if ($this->_type == 'json') {
			$this->CI->view->set('feed', json_encode($this->json));
			$this->CI->output->set_content_type('application/json');
		}

		$this->CI->view->render_template($template, FALSE);
		return;
	}

}