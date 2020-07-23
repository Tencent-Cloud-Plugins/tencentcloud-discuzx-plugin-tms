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
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
//不是ajax请求直接退出
if( !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    exit('Access Denied');
}
use TencentDiscuzTMS\TMSActions;
use TencentDiscuzTMS\TMSOptions;
try {
    global $_G;
    $options = unserialize($_G['setting'][TENCENT_DISCUZX_TMS_PLUGIN_NAME]);
    $dzxTMS = new TMSActions();
    if ($options['examinePost'] === TMSOptions::EXAMINE_POST) {
        $dzxTMS->examineContent($dzxTMS->filterPostParam('subject'));
        $dzxTMS->examineContent($dzxTMS->filterPostParam('message'));
    }
    echo json_encode(array('code'=>$dzxTMS::CODE_SUCCESS,'msg'=>''));
    exit();
} catch (\Exception $exception) {
    echo json_encode(array('code'=>$dzxTMS::CODE_EXCEPTION,'msg'=>$exception->getMessage()));
    exit();
}
