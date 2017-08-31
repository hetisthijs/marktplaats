<?php
error_reporting(E_ALL ^ E_NOTICE);
define("LIMIT", $_GET['limit'] || 99999);

function getApiUrl() {
    return 'https://api.marktplaats.nl/';
}

function getAds() {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => getApiUrl(),
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko'
    ));
    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;
}

function categoryName($categoryId, $categories) {
    foreach($categories as $number => $category) {
        if($category['id'] == $categoryId)
            return $category['name'];
    }
    return "";
}

function filterTitle($title) {
    $title = str_ireplace('gratis af te halen', '', $title);
    return $title;
}

function filterJson($json) {
    $array = json_decode($json, true);
    $count = 0;
    $categories = $array['search_histograms']['categories'];
    $return = [];
    $return[] = ['categories' => $categories];
    foreach($array['listings'] as $number => $ad) {
        if ($count > LIMIT) break;
        if ($ad['ad_core']['price']["price_type"] == "FREE") {
            $object = [
                'urn' => $ad['ad_core']['urn'],
                'title' => filterTitle($ad['ad_core']['title']),
                'category' => categoryName($ad['ad_core']['category_id'], $categories),
                'categoryId' => $ad['ad_core']['category_id'],
                'picture' => $ad['ad_core']['picture']['large'],
                'city' => $ad['ad_core']['ad_address']['city'],
                'zip_code' => $ad['ad_core']['ad_address']['zip_code']
            ];
            $return[] = $object;
            $count++;
        }
    }
    return json_encode($return, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES);
}

function main() {
    $json = getAds();
    return filterJson($json);
}

print main();