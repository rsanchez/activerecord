# Active Record #

Use the CodeIgniter Active Record pattern in an ExpressionEngine plugin. See <http://codeigniter.com/user_guide/database/active_record.html#select>

The tag parsing is the equivalent of the Query module tag parsing: <http://expressionengine.com/user_guide/modules/query/index.html>

## Installation

* Copy the /system/expressionengine/third_party/activerecord/ folder to your /system/expressionengine/third_party/ folder

## Usage
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

**select**  
	select="member_id, username"

protect your select statement
	select="COUNT(*) AS count"
	protect_select="yes"

**from (required)**  
	from="members"

**where**  
a where key/value pair
	where:group_id="1"

a where statement (not key/value pair)
	where="MATCH (field) AGAINST ('value')"
	
multiple where statements
	where[a]="MATCH (field) AGAINST ('value')"
	where[b]="MATCH (field2) AGAINST ('value2')"

**like**  
**not_like**  
**or_like**  
**or_not_like**  
use :before or :after to modify the location of the wildcard in the like statement
	like:screen_name="Joe"
	or_like:screen_name:before="oe"
	
**distinct**  
	distinct="yes"
	
**order_by**  
	order_by="screen_name"
	
**group_by**  
	group_by="group_id"

**join**  
on is required with a join, join_type is optional
	join="channel_data"
	on="channel_data.entry_id = channel_titles.entry_id"
	join_type="left"
	
**where_in**  
**or_where_in**  
**where_not_in**  
**or_where_not_in**  
separate multiple values with a pipe character
	where_in:entry_id="1|2|3|4"