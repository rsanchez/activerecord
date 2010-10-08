# Active Record #

Use the CodeIgniter Active Record pattern in an ExpressionEngine plugin.

## Installation

* Copy the /system/expressionengine/third_party/activerecord/ folder to your /system/expressionengine/third_party/ folder

## Usage

	{exp:activerecord select="member_id, username" from="members" where:group_id="1" order_by="screen_name" limit="10" paginate="top"}
		{!-- this parses exactly like a query module tag --}
		{member_id} - {username}<br />
		{paginate}<p>Page {current_page} of {total_pages} pages {pagination_links}</p>{/paginate}
	{/exp:activerecord}

You also get these vars:
	{switch="option_one|option_two|option_three"}
	{count}
	{total_results}
	{absolute_total_results}

## Other Param Examples

a where statement (not key/value pair)
	where="MATCH (field) AGAINST ('value')"
	
multiple where statements
	where[a]="MATCH (field) AGAINST ('value')"
	where[b]="MATCH (field2) AGAINST ('value2')"
	
like, not_like, or_like, or_not_like
	like:screen_name="Joe"
	or_like:screen_name="oe:before"

protect your select
	select="COUNT(*) AS count"
	protect_select="yes"
	
distinct
	distinct="yes"
	
joins (on is required with a join, join_type is optional)
	join="channel_data"
	on="channel_data.entry_id = channel_titles.entry_id"
	join_type="left"
	
where_in, or_where_in, where_not_in, or_where_not_in
	where_in:entry_id="1|2|3|4"