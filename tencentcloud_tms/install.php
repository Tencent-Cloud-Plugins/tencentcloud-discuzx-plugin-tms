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
if (!is_file(TENCENT_DISCUZX_TMS_DIR.'vendor/autoload.php')) {
    exit(lang('plugin/tencentcloud_tms','require_sdk'));
}
require_once 'vendor/autoload.php';
use TencentDiscuzTMS\TMSActions;
global $_G;
$careatesql = "CREATE TABLE IF NOT EXISTS cdb_tencentcloud_pluginInfo (
       `plugin_name` varchar(150) NOT NULL DEFAULT '',
       `version` varchar(32) NOT NULL DEFAULT '',
       `href` varchar(255) NOT NULL  DEFAULT '',
       `plugin_id` varchar(255) NOT NULL DEFAULT '',
       `activation` varchar(32) NOT NULL DEFAULT '',
       `status` varchar(32) NOT NULL DEFAULT '',
       `install_datetime` bigint NOT NULL DEFAULT 0,
       `last_modify_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP(),
       PRIMARY KEY (`plugin_name`)
) ENGINE=InnoDB;
";
runquery($careatesql);
$href = ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid;
$time = time();
$inserSQL=<<<EOF
REPLACE INTO pre_tencentcloud_pluginInfo (`plugin_name`, `version`, `href`, `plugin_id`, `activation`, `status`, `install_datetime`)
 VALUES ( 'tencentcloud_tms', '1.0.0', '$href', '$pluginid', 'true', 'false', '$time');
EOF;
runquery($inserSQL);
TMSActions::uploadDzxStatisticsData('activate');
$finish = true;
