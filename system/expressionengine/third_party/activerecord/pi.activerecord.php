<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
    'pi_name' => 'Active Record',
    'pi_version' => '1.1.2',
    'pi_author' => 'Rob Sanchez',
    'pi_author_url' => 'https://github.com/rsanchez/activerecord',
    'pi_description' => 'Use the CodeIgniter Active Record pattern in an EE plugin.',
    'pi_usage' => 'See https://github.com/rsanchez/activerecord'
);

class Activerecord
{
    public $return_data = '';

    /**
     * exp:activerecord plugin tag / constructor
     *
     * @return void
     */
    public function __construct()
    {
        foreach (ee()->TMPL->tagparams as $method => $value)
        {
            switch($value)
            {
                case '{member_id}':
                case '{logged_in_member_id}':
                case 'CURRENT_USER':
                    $value = ee()->session->userdata('member_id');
                    break;
                case '{group_id}':
                case '{logged_in_group_id}':
                case '{member_group}':
                    $value = ee()->session->userdata('group_id');
                    break;
            }

            switch($method)
            {
                case 'from':
                case 'order_by':
                case 'group_by':

                    call_user_func(array(ee()->db, $method), ee()->security->xss_clean($value));

                    break;

                case 'where[]':
                	preg_match_all('/\swhere\[\]=([\042\047])(.*?)\\1/', ee()->TMPL->tagproper, $matches);

                	foreach ($matches[2] as $value)
                	{
                		ee()->db->where(ee()->security->xss_clean($value), NULL, FALSE);
                	}

                	break;

                case 'or_where[]':
                	preg_match_all('/or_where\[\]=([\042\047])(.*?)\\1/', ee()->TMPL->tagproper, $matches);

                	foreach ($matches[2] as $value)
                	{
                		ee()->db->or_where(ee()->security->xss_clean($value), NULL, FALSE);
                	}

                	break;

                case (preg_match('/^(left_outer_|right_outer|outer_|inner_|right_|left_)?join:(.*?)$/', $method, $match) != 0):

                    ee()->db->join(
                        ee()->security->xss_clean($match[2]),
                        ee()->security->xss_clean($value),
                        rtrim(str_replace('_', ' ', $match[1]), '_')
                    );

                	break;

                case ($method === 'join' && ee()->TMPL->fetch_param('on')):

                    ee()->db->join(
                        ee()->security->xss_clean($value),
                        ee()->security->xss_clean(ee()->TMPL->fetch_param('on')),
                        (ee()->TMPL->fetch_param('join_type')) ? ee()->security->xss_clean(ee()->TMPL->fetch_param('join_type')) : ''
                    );

                    break;

                case 'distinct':

                    if (preg_match('/^(yes|y|on|1|true)$/i', $value))
                    {
                        call_user_func(array(ee()->db, $method));
                    }

                    break;

                case (preg_match('/^(where|or_where):([^\[]+)(\[.*\])?/', $method, $match) != 0):

                    $method = $match[1];

                    $key = $match[2];

                    if ( ! empty($match[3]))
                    {
                        $key .= ' '.substr($match[3], 1, -1);
                    }

                    call_user_func(array(ee()->db, $method), $key, $value);

                    break;

                case (preg_match('/^(where_in|or_where_in|where_not_in|or_where_not_in):(.+)/', $method, $match) != 0):

                    $method = $match[1];

                    $key = $match[2];

                    $value = explode('|', ee()->security->xss_clean($value));

                    call_user_func(array(ee()->db, $method), $key, $value);

                    break;

                case (preg_match('/^(where|or_where).*?/', $method, $match) != 0):

                    $method = $match[1];

                    call_user_func(array(ee()->db, $method), ee()->security->xss_clean($value), NULL, FALSE);

                    break;

                case (preg_match('/^(like|not_like|or_like|or_not_like):([^:]+):?(.*)/', $method, $match) != 0):

                    $method = $match[1];

                    $key = $match[2];

                    $wildcard_location = ( ! empty($match[3]) && in_array($match[3], array('both','before','after'))) ? $match[3] : 'both';

                    call_user_func(array(ee()->db, $method), $key, $value, $wildcard_location);

                    break;
            }
        }

        ee()->db->select('COUNT(*) AS count');

        $count_sql = ee()->db->_compile_select();

        $count_query = ee()->db->query($count_sql);

        $absolute_total_results = $count_query->row('count');

        $count_query->free_result();

        if ($absolute_total_results <= 0)
        {
            ee()->db->_reset_select();

            return $this->return_data = ee()->TMPL->no_results();
        }

        ee()->db->ar_select = array();

        if ($select = ee()->TMPL->fetch_param('select'))
        {
            $protect_select = ee()->TMPL->fetch_param('protect_select');

            ee()->db->select(
                ee()->security->xss_clean($select),
                $protect_select && preg_match('/^(no|n|off|0)$/i', $protect_select) === 0
            );
        }

        ee()->load->library('pagination');

        $pagination = ee()->pagination->create();

        ee()->TMPL->tagdata = $pagination->prepare(ee()->TMPL->tagdata);

        $limit = ee()->TMPL->fetch_param('limit');

        if ( ! $limit)
        {
            $pagination->paginate = FALSE;
        }
        else
        {
            ee()->db->limit(ee()->security->xss_clean($limit));
        }

        if ($pagination->paginate)
        {
            if (preg_match('#P(\d+)/?$#', ee()->uri->uri_string(), $match))
            {
                ee()->db->offset($match[1]);
            }

            $pagination->build($absolute_total_results, $limit);
        }

        $query = ee()->db->get();

        if ($query->num_rows() === 0)
        {
            return $this->return_data = ee()->TMPL->no_results();
        }

        $results = $query->result_array();

        $this->return_data = ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $results);

        if ($pagination->paginate === TRUE)
        {
            $this->return_data = $pagination->render($this->return_data);
        }

        $this->return_data = ee()->TMPL->swap_var_single('absolute_total_results', $absolute_total_results, $this->return_data);
    }
}

/* End of file pi.activerecord.php */
/* Location: ./system/expressionengine/third_party/activerecord/pi.activerecord.php */