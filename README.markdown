# Active Record #

Use the CodeIgniter Active Record pattern in an ExpressionEngine plugin. See <http://codeigniter.com/user_guide/database/active_record.html#select>

The tag parsing is the equivalent of the Query module tag parsing: <http://expressionengine.com/user_guide/modules/query/index.html>

## Installation

* Copy the /system/expressionengine/third\_party/activerecord/ folder to your /system/expressionengine/third\_party/ folder

## System Requirements

Requires ExpressionEngine 2.8+. If you need to support an older version of EE, use [version 1.0.4](https://github.com/rsanchez/activerecord/tree/v1.0.4).

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
		{paginate}
		<p>Page {current_page} of {total_pages} pages {pagination_links}</p>
		{/paginate}
	{/exp:activerecord}

## Variables

	{your_field_name}
	{switch="option_one|option_two|option_three"}
	{count}
	{total_results}
	{absolute_total_results}
	{paginate}
	<p>Page {current_page} of {total_pages} pages {pagination_links}</p>
	{/paginate}

## Conditionals
	{if no_results}

## Parameters

**select**

```
select="member_id, username"
```

Protect your select statement.

```
select="COUNT(*) AS count"
protect_select="yes"
```

**from (required)**

```
from="members"
```

**where**

A `where` key/value pair.

```
where:group_id="1"
```

A `where` key/value pair with custom comparison operator.

```
where:group_id[>=]="6"
where:member_id[!=]="1"
```

A `where` statement (not key/value pair).

```
where="MATCH (field) AGAINST ('value')"
```

Multiple `where` statements.

```
where[]="MATCH (field) AGAINST ('value')"
where[]="MATCH (field2) AGAINST ('value2')"
```

**like**

Use `:before` or `:after` to modify the location of the wildcard in the like statement. Use the same syntax for `not_like`, `or_like` and `or_not_like`.

```
like:screen_name="Joe"
or_like:screen_name:before="oe"
```

**distinct**

```
distinct="yes"
```

**order_by**

```
order_by="screen_name asc, group_id desc"
```

**group_by**

```
group_by="group_id"
```

**join**

```
join:channel_data="channel_data.entry_id = channel_titles.entry_id"
```

Advanced joins:

```
left_join:channel_data="channel_data.entry_id = channel_titles.entry_id"
right_join:channel_data="channel_data.entry_id = channel_titles.entry_id"
outer_join:channel_data="channel_data.entry_id = channel_titles.entry_id"
inner_join:channel_data="channel_data.entry_id = channel_titles.entry_id"
left_outer_join:channel_data="channel_data.entry_id = channel_titles.entry_id"
right_outer_join:channel_data="channel_data.entry_id = channel_titles.entry_id"
```

**where_in**

Separate multiple values with a pipe character. Use the same syntax for `or_where_in`, `where_not_in` and `or_where_not_in`

```
where_in:entry_id="1|2|3|4"
```