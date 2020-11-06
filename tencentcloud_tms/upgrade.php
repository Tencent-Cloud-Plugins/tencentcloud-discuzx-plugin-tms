<?php
/**
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
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')){
    exit('Access Denied');
}
if ($toversion === '1.0.1') {
    try {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `cdb_tencent_discuzx_tms_keyword_whitelist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(128) NOT NULL DEFAULT '' ,
  `valid` tinyint(1) unsigned NOT NULL  DEFAULT 1 ,
  `add_date` bigint NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`),
  KEY uid_idx(`keyword`,`valid`)
) ENGINE=InnoDB;
SQL;
        runquery($sql);
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `cdb_tencent_discuzx_tms_keyword_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT 0,
  `username` varchar(255) NOT NULL DEFAULT '' ,
  `keyword` varchar(255) NOT NULL DEFAULT '' ,
  `evil_label` varchar(16)  NOT NULL DEFAULT '' ,
  `type` tinyint(2) unsigned NOT NULL  DEFAULT 1 ,
  `examine_text` text NOT NULL ,
  `status` tinyint(2) unsigned  NOT NULL DEFAULT 1 ,
  `examine_date` bigint unsigned NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`),
  KEY date_label_idx(`examine_date`,`evil_label`)
) ENGINE=InnoDB;
SQL;
        runquery($sql);
    } catch (\Exception $exception) {
        $finish = true;
    }
}
$finish = true;