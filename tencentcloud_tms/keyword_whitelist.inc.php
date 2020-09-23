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
defined('TENCENT_DISCUZX_TMS_KEYWORD_WHITELIST_TABLE')||define( 'TENCENT_DISCUZX_TMS_KEYWORD_WHITELIST_TABLE', 'tencent_discuzx_tms_keyword_whitelist');
if (!is_file(TENCENT_DISCUZX_TMS_DIR.'vendor/autoload.php')) {
    exit(lang('plugin/tencentcloud_tms','require_sdk'));
}
require_once 'vendor/autoload.php';
use TencentDiscuzTMS\TMSActions;

try {
    if (submitcheck('add_keywords')) {
        $dzxTMS = new TMSActions();
        $keywords = $dzxTMS->filterPostParam('keywords');
        $deleteIds = $dzxTMS->filterPostParam('deleteIds');
        $result = $dzxTMS->addKeywordToWhitelist($keywords);
        if (!is_numeric($result)) {
            cpmsg('tencentcloud_tms:add_error', '', 'error');
        }
        $result = $dzxTMS->deleteKeywordsFromWhitelist($deleteIds);
        if (!$result) {
            cpmsg('tencentcloud_tms:delete_error', '', 'error');
        }
        cpmsg('tencentcloud_tms:success', "action=plugins&operation=config&do={$pluginid}&identifier=tencentcloud_tms&pmod=keyword_whitelist", 'succeed');
        return;
    }

    $sql = "SELECT * FROM %t  ORDER BY `id` DESC";
    $params = array(TENCENT_DISCUZX_TMS_KEYWORD_WHITELIST_TABLE);
    $records = DB::fetch_all($sql,$params);
    $lang = lang('plugin/tencentcloud_tms');
    showformheader("plugins&operation=config&identifier=tencentcloud_tms&pmod=keyword_whitelist&do={$pluginid}");
    showtableheader();
    showsubtitle(array($lang['del'],'id', $lang['keyword'], ''));
    foreach ($records as $record) {
        showtablerow('', array(), array(
            '<input class="checkbox" type="checkbox" name="deleteIds[]" value="'.$record['id'].'">',
            $record['id'],
            $record['keyword'],
        ));
    }
    echo '<tr><td colspan="1"></td><td colspan="7"><div><a href="###" onclick="addrow(this, 0, 0)" class="addtr">'.$lang['add'].'</a></div></td></tr>';
    showsubmit('add_keywords', $lang['submit'], 'select_all');
    showtablefooter();
    showformfooter();
    echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[[1, '', 'td25'], [1,'', 'td25'], [1, '<input name="keywords[]" value="" size="30" type="text" class="txt">']],
	];
</script>
EOT;
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