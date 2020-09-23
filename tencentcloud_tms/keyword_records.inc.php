<?php
/*
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
defined('TENCENT_DISCUZX_TMS_DIR')||define( 'TENCENT_DISCUZX_TMS_DIR', __DIR__.DIRECTORY_SEPARATOR);
defined('TENCENT_DISCUZX_TMS_KEYWORD_RECORDS_TABLE')||define( 'TENCENT_DISCUZX_TMS_KEYWORD_RECORDS_TABLE', 'tencent_discuzx_tms_keyword_records');
if (!is_file(TENCENT_DISCUZX_TMS_DIR.'vendor/autoload.php')) {
    exit(lang('plugin/tencentcloud_tms','require_sdk'));
}
require_once 'vendor/autoload.php';
use TencentDiscuzTMS\TMSActions;

try {
    //后台的展示时间不正确，取消下方代码注释
//    date_default_timezone_set('Asia/Shanghai');
    global $_G;
    $dzxTMS = new TMSActions();
    $status = intval($dzxTMS->filterGetParam('status',0));
    $page = $dzxTMS->filterGetParam('page',1);
    $pageSize = $dzxTMS->filterGetParam('pageSize',15);
    $evilLabel = $dzxTMS->filterGetParam('evilLabel','all');
    $dateStart = $dzxTMS->filterGetParam('dateStart',date('Y-m-d',time() - 86400));
    $dateEnd = $dzxTMS->filterGetParam('dateEnd',date('Y-m-d'));

    $commonUrl = "plugins&page={$page}&pageSize={$pageSize}&evilLabel={$evilLabel}&status={$status}&dateStart={$dateStart}&dateEnd={$dateEnd}&operation=config&identifier=tencentcloud_tms&pmod=keyword_records&do={$pluginid}";
    if ( $page < 1 || $page > 99999 || !is_numeric($page)) {
        $page = 1;
    }
    //页大小选项数组
    $pageSizeValues = array(15,45,100);
    if (!in_array($pageSize,$pageSizeValues)) {
        $page = $pageSizeValues[0];
    }
    $lang = lang('plugin/tencentcloud_tms');
    //类型数组
    $evilLabels = $lang['evil_label_desc'];
    $typeMaps = $lang['trigger_type_maps'];
    $interceptStatMaps = $lang['intercept_status_maps'];
    if (!in_array($evilLabel,array_keys($evilLabels))) {
        $evilLabel = 'all';
    }
    $pageSize = intval($pageSize);
    $page = intval($page);
    $skip = ($page - 1) * $pageSize;
    $pageSizeOptions = '';
    foreach ($pageSizeValues as $value) {
        $selected = '';
        if ($value === $pageSize) {
            $selected = "selected='selected'";
        }
        $pageSizeOptions .= "<option value='{$value}' {$selected} >$value</option>";
    }

    $typeOptions = '';
    foreach ($evilLabels as $key => $value) {
        $selected = '';
        if ($key === $evilLabel) {
            $selected = "selected='selected'";
        }
        $typeOptions .= "<option value='{$key}' {$selected} >$value</option>";
    }
    $statusOptions = '';
    foreach ($interceptStatMaps as $key => $value) {
        $selected = '';
        if ($key === $status) {
            $selected = "selected='selected'";
        }
        $statusOptions .= "<option value='{$key}' {$selected} >$value</option>";
    }

    showformheader($commonUrl);
    showtableheader($lang['examine_record']);
    $html = <<<HTML
<td>{$lang['start_date']}：<input value="$dateStart" name="dateStart" onclick="showcalendar(event, this)"></td>
<td>{$lang['end_date']}：<input value="$dateEnd" name="dateEnd" onclick="showcalendar(event, this)"></td>
<td>{$lang['evil_label']}：<select name="evilLabel">$typeOptions</select></td>
<td>{$lang['intercept_status']}：<select name="status">$statusOptions</select></td>
<td>{$lang['page_size']}：<select name="pageSize">$pageSizeOptions</select></td>
<td><button style="margin-left: 1rem;" class="btn">{$lang['search']}</button></td>
HTML;
    echo $html;
    showtablefooter();
    showformfooter();
    $where = '`examine_date` BETWEEN %d AND %d';
    $dateStart = strtotime($dateStart.' 00:00:00');
    $dateEnd = strtotime($dateEnd.' 23:59:59');
    $params = array(TENCENT_DISCUZX_TMS_KEYWORD_RECORDS_TABLE,$dateStart,$dateEnd);

    if (in_array($status,array($dzxTMS::STATUS_INTERCEPT,$dzxTMS::STATUS_WHITELIST))) {
        $where .= ' AND `status`= %d';
        $params[] = $status;
    }

    if ($evilLabel !== 'all') {
        $where .= ' AND `evil_label`= %s';
        $params[] = $evilLabel;
    }

    showtableheader();
    showsubtitle($lang['table_head']);

    $sql = "SELECT COUNT(*) FROM %t WHERE {$where}";
    $count = DB::result_first($sql,$params);

    $sql = "SELECT * FROM %t  WHERE {$where} ORDER BY `id` DESC LIMIT {$skip},{$pageSize}";
    $records = DB::fetch_all($sql,$params);
    foreach ($records as $record) {
        showtablerow('', array(), array(
            $record['uid'],
            $record['username'],
            $record['keyword'],
            $evilLabels[$record['evil_label']],
            $typeMaps[$record['type']],
            $interceptStatMaps[$record['status']],
            date('Y-m-d H:i:s',$record['examine_date'])
        ));
    }
    $queryString = ADMINSCRIPT."?action={$commonUrl}";
    $pagination = multi($count, $pageSize, $page, $queryString, 99999);
    echo '<tr>
            <td colspan="6">
                <div class="cuspages" style="float: right;">'.$pagination.'</div>
            </td>
        </tr>
        <script src="static/js/calendar.js" type="text/javascript"></script>';
    showtablefooter();
    echo '<div style="text-align: center;flex: 0 0 auto;margin-top: 3rem;">
            <a href="https://openapp.qq.com/docs/DiscuzX/sms.html" target="_blank">'.$lang['docs_center'].'</a> | 
            <a href="https://github.com/Tencent-Cloud-Plugins/tencentcloud-discuzx-plugin-sms" 
            target="_blank">GitHub</a> | 
            <a href="https://support.qq.com/product/164613" target="_blank">'.$lang['support'].'</a>
        </div>';
}catch (\Exception $exception) {
    cpmsg($exception->getMessage(), '', 'error');
    return;
}
