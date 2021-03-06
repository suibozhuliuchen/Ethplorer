<?php
/*!
 * Copyright 2016 Everex https://everex.io
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require dirname(__FILE__) . '/lib/ethplorer.php';

$es = Ethplorer::db(require_once dirname(__FILE__) . '/config.php');

$data = isset($_GET["data"]) ? $_GET["data"] : false;
$page = isset($_GET["page"]) ? $_GET["page"] : false;
$refresh = isset($_GET["refresh"]) ? $_GET["refresh"] : false;

$search = isset($_GET["search"]) ? $_GET["search"] : false;

// Allow cross-domain ajax requests
header('Access-Control-Allow-Origin: *');

$pageSize = 10;

// Parse page data
if($page && (FALSE !== strpos($page, '='))){
    $aPageData = explode('&', $page);
    foreach($aPageData as $pageDataString){
        $aPageParams = explode('=', $pageDataString);
        if(2 === count($aPageParams)){
            switch($aPageParams[0]){
                case 'pageSize':
                    $pageSize = intval($aPageParams[1]);
                    break;
                case 'transfers':
                case 'issuances':
                case 'holders':
                case 'chainy':
                    $es->setPager($aPageParams[0], intval($aPageParams[1]));
                    break;
                case 'filter':
                    $es->setFilter($aPageParams[1]);
                    break;
            }
        }
    }
    if($refresh){
        $es->setPager('refresh', $refresh);
    }
}

$result = array();

if(strlen($search)){
    $result = $es->searchToken($search);
}else if(false !== $data){
    $es->setPageSize($pageSize);
    if($es->isValidAddress($data)){
        $result = $es->getAddressDetails($data);
    }elseif($es->isValidTransactionHash($data)){
        $result = $es->getTransactionDetails($data);
    }
    if(!isset($result['pager'])){
        $result['pager'] = array(
            'pageSize' => $pageSize
        );
    }
}

echo json_encode($result);
