<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! class_exists('Query')) require_once(PATH_MOD.'query/mod.query'.EXT);

class Activerecord extends Query
{
	function Activerecord()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->helper('security');
		
		foreach ($this->EE->TMPL->tagparams as $method => $value)
		{
			switch($method)
			{
				case 'from':
				case 'order_by':
				case 'group_by':
					
					call_user_func(array($this->EE->db, $method), xss_clean($value));
					
					break;
				
				case ($method === 'join' && $this->EE->TMPL->fetch_param('on')):
					
					$this->EE->db->join(
						xss_clean($value),
						xss_clean($this->EE->TMPL->fetch_param('on')),
						($this->EE->TMPL->fetch_param('join_type')) ? xss_clean($this->EE->TMPL->fetch_param('join_type')) : ''
					);
					
					break;
				
				case 'distinct':
				
					if (preg_match('/^(yes|y|on|1|true)$/i', $value))//if ($this->_bool_string($value))
					{
						call_user_func(array($this->EE->db, $method));
					}
					
					break;
				
				case (preg_match('/^(where|or_where):(.+)/', $method, $match) != 0):
					
					$method = $match[1];
					
					$key = $match[2];
					
					call_user_func(array($this->EE->db, $method), $key, xss_clean($value));
					
					break;
				case (preg_match('/^(where_in|or_where_in|where_not_in|or_where_not_in):(.+)/', $method, $match) != 0):
				
					$method = $match[1];
					
					$key = $match[2];
					
					$value = explode('|', xss_clean($value));
					
					call_user_func(array($this->EE->db, $method), $key, $value);
					
					break;
				
				
				case (preg_match('/^(where|or_where).*?/', $method, $match) != 0):
					
					$method = $match[1];
					
					call_user_func(array($this->EE->db, $method), xss_clean($value), NULL, FALSE);
					
					break;
				
				case (preg_match('/^(like|not_like|or_like|or_not_like):([^:]+):?(.*)/', $method, $match) != 0):
				
					$method = $match[1];
					
					$key = $match[2];
					
					$wildcard_location = ( ! empty($match[3]) && in_array($match[3], array('both','before','after'))) ? $match[3] : 'both';
					
					call_user_func(array($this->EE->db, $method), $key, $value, $wildcard_location);
					
					break;
			}
		}
		
		if ($this->EE->TMPL->fetch_param('select'))
		{
			$this->EE->db->select(
				xss_clean($this->EE->TMPL->fetch_param('select')),
				(preg_match('/^(no|n|off|0)$/i', $this->EE->TMPL->fetch_param('protect_select')) == 0)//$this->EE->TMPL->fetch_param('protect_select'), TRUE)
			);
		}
		
		$this->EE->TMPL->tagparams['sql'] = $this->EE->db->_compile_select();
		
		$this->EE->db->_reset_select();
		
		$this->basic_select();
		
		$this->return_data = $this->EE->TMPL->swap_var_single('absolute_total_results', $this->total_rows, $this->return_data);
		
		$this->return_data = $this->EE->TMPL->swap_var_single('query', $this->EE->TMPL->tagparams['sql'], $this->return_data);
	}
	
	/* no longer in use */
	/*
	function _parse()
	{
		$this->EE->db->select('COUNT(*) AS count', FALSE);
		
		$aboslute_total_results_sql = $this->EE->db->_compile_select(); //gangsta
		
		$this->EE->db->_reset_run(array('ar_select' => array()));
		
		if ($limit = xss_clean($this->EE->TMPL->fetch_param('limit')))
		{
			$this->EE->db->limit($limit);
		}
		
		if ($this->EE->TMPL->fetch_param('offset'))
		{
			$this->EE->db->offset(xss_clean($this->EE->TMPL->fetch_param('offset')));
		}
		
		if ($this->EE->TMPL->fetch_param('select'))
		{
			$this->EE->db->select(
				xss_clean($this->EE->TMPL->fetch_param('select')),
				(preg_match('/^(no|n|off|0)$/i', $this->EE->TMPL->fetch_param('protect_select')) != 0)//$this->EE->TMPL->fetch_param('protect_select'), TRUE)
			);
		}
		
		$get = $this->EE->db->get();
		
		$total_results = $absolute_total_results = $query->num_rows();
		
		if ( ! $total_results)
		{
			return $this->EE->TMPL->no_results();
		}
		
		if ($total_results == $limit)
		{
			//lets get the absolute total results
			$query = $this->EE->db->query($aboslute_total_results_sql);
			
			$aboslute_total_results = $query->row('count');
		}
		
		$vars = array();
		
		$count = 1;
		
		foreach ($get->result_array() as $row)
		{
			$row['count']  = $count++;
			
			$row['total_results'] = $total_results;
			
			$row['absolute_total_results'] = $aboslute_total_results;
			
			$vars[] = $row;
		}
		
		//@TODO pagination
		
		$this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars);
	}
	*/
	
	function usage()
	{
		ob_start(); 
?>
	{exp:activerecord
		select="member_id, username"
		from="members"
		where:group_id="1"
		order_by="screen_name"
		limit="10"
		paginate="top"
	}
		{!-- this parses exactly like a query module tag --}
		{member_id} - {username}<br />
		{paginate}<p>Page {current_page} of {total_pages} pages {pagination_links}</p>{/paginate}
	{/exp:activerecord}

## Variables
	{your_field_name}
	{switch="option_one|option_two|option_three"}
	{count}
	{total_results}
	{absolute_total_results}
	{paginate}<p>Page {current_page} of {total_pages} pages {pagination_links}</p>{/paginate}

## Conditionals
	{if no_results}

## Parameters

# select
	select="member_id, username"

protect your select statement
	select="COUNT(*) AS count"
	protect_select="yes"

# from (required)
	from="members"

# where
a where key/value pair
	where:group_id="1"

a where statement (not key/value pair)
	where="MATCH (field) AGAINST ('value')"
	
multiple where statements
	where[a]="MATCH (field) AGAINST ('value')"
	where[b]="MATCH (field2) AGAINST ('value2')"

# like
# not_like
# or_like
# or_not_like
use :before or :after to modify the location of the wildcard in the like statement
	like:screen_name="Joe"
	or_like:screen_name:before="oe"
	
# distinct
	distinct="yes"
	
# order_by
	order_by="screen_name"
	
# group_by
	group_by="group_id"

#join
on is required with a join, join_type is optional
	join="channel_data"
	on="channel_data.entry_id = channel_titles.entry_id"
	join_type="left"
	
# where_in
# or_where_in
# where_not_in
# or_where_not_in
separate multiple values with a pipe character
	where_in:entry_id="1|2|3|4"
<?php
		$buffer = ob_get_contents();
		      
		ob_end_clean(); 
	      
		return $buffer;
	}
}

$plugin_info = array(
	'pi_name' => 'Active Record',
	'pi_version' => '1.0.1',
	'pi_author' => 'Rob Sanchez',
	'pi_author_url' => 'http://github.com/rsanchez/activerecord',
	'pi_description' => 'Use the CodeIgniter Active Record pattern in an EE plugin.',
	'pi_usage' => Activerecord::usage()
);

/* End of file pi.activerecord.php */ 
/* Location: ./system/expressionengine/third_party/activerecord/pi.activerecord.php */ 