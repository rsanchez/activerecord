<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name' => 'Active Record',
	'pi_version' => '1.0.4',
	'pi_author' => 'Rob Sanchez',
	'pi_author_url' => 'https://github.com/rsanchez/activerecord',
	'pi_description' => 'Use the CodeIgniter Active Record pattern in an EE plugin.',
	'pi_usage' => 'See https://github.com/rsanchez/activerecord'
);

require_once PATH_MOD.'query/mod.query.php';

class Activerecord extends Query
{
	public $return_data = '';
	
	/**
	 * exp:activerecord plugin tag / constructor
	 * 
	 * @return Type    Description
	 */
	public function Activerecord()
	{
		$this->EE =& get_instance();
		
		if ( ! is_array($this->EE->TMPL->tagparams))
		{
			$this->EE->TMPL->tagparams = array();
		}
		
		$this->EE->TMPL->tagparams['sql'] = $this->build_query($this->EE->TMPL->tagparams);
		
		$this->basic_select();
		
		$this->return_data = $this->EE->TMPL->swap_var_single('absolute_total_results', $this->total_rows, $this->return_data);
	}
	
	/**
	 * Builds a SQL query from tag params
	 * 
	 * @param array $params
	 * @param string|bool $tagdata
	 * 
	 * @return string the SQL query created by CI active record
	 */
	protected function build_query(array $params, $tagdata = FALSE)
	{
		if ($tagdata === FALSE)
		{
			$tagdata = (isset($this->EE->TMPL->tagdata)) ? $this->EE->TMPL->tagdata : '';
		}
		
		$this->EE->db->_reset_select();
		
		//this needs to be done before the rest
		if ( ! empty($params['select']))
		{
			$this->EE->db->select(
				$this->EE->security->xss_clean($params['select']),
				(isset($params['protect_select']) && preg_match('/^(no|n|off|0)$/i', $params['protect_select']) == 0)
			);
		}
		
		//query module ignores limit if there's no pagination, so we'll add it here
		if ( ! empty($params['limit']) && ! preg_match('/'.LD.'paginate'.RD.'(.+?)'.LD.'\/'.'paginate'.RD.'/s', $tagdata, $match))
		{
			$this->EE->db->limit($this->EE->security->xss_clean($params['limit']));
		}
		
		foreach ($params as $method => $value)
		{
			switch($value)
			{
				case '{member_id}':
				case '{logged_in_member_id}':
				case 'CURRENT_USER':
					$value = $this->EE->session->userdata('member_id');
					break;
				case '{group_id}':
				case '{logged_in_group_id}':
				case '{member_group}':
					$value = $this->EE->session->userdata('group_id');
					break;
			}
			
			switch($method)
			{
				case 'from':
				case 'order_by':
				case 'group_by':
					
					call_user_func(array($this->EE->db, $method), $this->EE->security->xss_clean($value));
					
					break;
				
				case ($method === 'join' && $this->EE->TMPL->fetch_param('on')):
					
					$this->EE->db->join(
						$this->EE->security->xss_clean($value),
						$this->EE->security->xss_clean($this->EE->TMPL->fetch_param('on')),
						($this->EE->TMPL->fetch_param('join_type')) ? $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('join_type')) : ''
					);
					
					break;
				
				case 'distinct':
				
					if (preg_match('/^(yes|y|on|1|true)$/i', $value))
					{
						call_user_func(array($this->EE->db, $method));
					}
					
					break;
				
				case (preg_match('/^(where|or_where):([^\[]+)(\[.*\])?/', $method, $match) != 0):
					
					$method = $match[1];
					
					$key = $match[2];
					
					if ( ! empty($match[3]))
					{
						$key .= ' '.substr($match[3], 1, -1);
					}
					
					call_user_func(array($this->EE->db, $method), $key, $value);
					
					break;
				
				case (preg_match('/^(where_in|or_where_in|where_not_in|or_where_not_in):(.+)/', $method, $match) != 0):
				
					$method = $match[1];
					
					$key = $match[2];
					
					$value = explode('|', $this->EE->security->xss_clean($value));
					
					call_user_func(array($this->EE->db, $method), $key, $value);
					
					break;
				
				case (preg_match('/^(where|or_where).*?/', $method, $match) != 0):
					
					$method = $match[1];
					
					call_user_func(array($this->EE->db, $method), $this->EE->security->xss_clean($value), NULL, FALSE);
					
					break;
				
				case (preg_match('/^(like|not_like|or_like|or_not_like):([^:]+):?(.*)/', $method, $match) != 0):
				
					$method = $match[1];
					
					$key = $match[2];
					
					$wildcard_location = ( ! empty($match[3]) && in_array($match[3], array('both','before','after'))) ? $match[3] : 'both';
					
					call_user_func(array($this->EE->db, $method), $key, $value, $wildcard_location);
					
					break;
			}
		}
		
		$query = $this->EE->db->_compile_select();
		
		$this->EE->db->_reset_select();
		
		return $query;
	}
}

/* End of file pi.activerecord.php */ 
/* Location: ./system/expressionengine/third_party/activerecord/pi.activerecord.php */ 