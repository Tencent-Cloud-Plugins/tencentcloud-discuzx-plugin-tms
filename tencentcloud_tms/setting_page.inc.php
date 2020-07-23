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
    exit('缺少依赖文件，请确保安装了腾讯云sdk');
}
require_once 'vendor/autoload.php';
use TencentDiscuzTMS\TMSActions;
use TencentDiscuzTMS\TMSOptions;

try {
    //不是ajax请求直接返回html页面
    if( $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $options = TMSActions::getTMSOptionsObject();
        $secretId = $options->getSecretID();
        $secretKey = $options->getSecretKey();
        $customKey = $options->getCustomKey();
        $examinePost = $options->getExaminePost();
        $examineReply = $options->getExamineReply();
        include template('tencentcloud_tms:setting_page');
        exit;
    }
    $dzxTMS = new TMSActions();
    $options = TMSActions::getTMSOptionsObject();
    $options->setCustomKey(intval($dzxTMS->filterPostParam('customKey',TMSOptions::GLOBAL_KEY)));
    $options->setSecretID($dzxTMS->filterPostParam('secretId'));
    $options->setSecretKey($dzxTMS->filterPostParam('secretKey'));
    $options->setExaminePost($dzxTMS->filterPostParam('examinePost'));
    $options->setExamineReply($dzxTMS->filterPostParam('examineReply'));

    C::t('common_setting')->update_batch(array("tencentcloud_tms" => $options->toArray()));
    updatecache('setting');
    TMSActions::uploadDzxStatisticsData('save_config');

    $url = 'action=plugins&operation=config&do='.$pluginid.'&identifier=tencentcloud_tms&pmod=setting_page';
    cpmsg('plugins_edit_succeed', $url, 'succeed');
}catch (\Exception $exception) {
    cpmsg($exception->getMessage(), '', 'error');
}
