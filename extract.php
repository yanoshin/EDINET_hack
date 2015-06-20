<?php
/**
 * Created by IntelliJ IDEA.
 * User: yanoshin
 * Date: 6/20/15
 * Time: 13:30
 * これはEDINETから一括ダウンロードした大量保有報告書のXBRLデータ（zip）を走査して、
 * 発行者と提出者のリストCSVを作成するスクリプト
 */

require_once('./classes/XbrlSearchDlInfo.php');
require_once('./classes/Publisher.php');
require_once('./classes/Investor.php');
require_once('./vendor/simple_html_dom.php');

define('ZIP_DIR', './xbrl/');
define('EXTRACTED_DIR', './extracted/');
define('CSV_FILEPATH', './result_only_individual_since_2013.csv');

/**
 * zipファイルリストを生成
 */
$zip_files = array();

// ディレクトリハンドルの取得し、ファイル・ディレクトリの一覧を $file_list に。
$dir_h = opendir(ZIP_DIR);
while (false !== ($file_list[] = readdir($dir_h))) ;
closedir($dir_h);

//ディレクトリ内のファイル名を１つずつを取得
foreach ($file_list as $file_name) {
//ファイルのみを表示
    if (is_file(ZIP_DIR . $file_name)) {
        $p = pathinfo(ZIP_DIR . $file_name);
        if ($p["extension"] == "zip") {
            $zip_files[] = array(
                'file_path' => ZIP_DIR . $file_name,
                'dir_name' => $p["filename"],
                'file_name' => $file_name,
            );
        }
    }
}

/**
 * ひとつずつzipファイルを解凍
 */
$zip_count = count($zip_files);
$zip_i = 0;
$fp = fopen(CSV_FILEPATH, 'w');

foreach ($zip_files as $zip_file) {
    $zip_i++;
    $zip = new ZipArchive;
    //ひとつずつzipファイルを解凍していく
    if ($zip->open($zip_file['file_path']) === TRUE) {
        //zip解凍
        $extracte_to = EXTRACTED_DIR . $zip_file['dir_name'];
        $zip->extractTo($extracte_to);
        $zip->close();

        //インデックスCSVファイルを取得する
        $indexes = getListFromCSVIndex($extracte_to);

        //同梱の報告書XBRL(あるいはHTMLでもいいかも）を走査する
        $data = array();
        foreach ($indexes as $index) {
            $list = getDocumentInfo($index, $extracte_to);
            foreach ($list as $item) {
                $data[] = $item;
            }
        }

        //出力CSVに書き込み
        $count = count($data);
        $i = 0;
        $prev_name = null;
        $prev_address = null;
        foreach ($data as $info) {
            $i++;

            /* @var $info XbrlSearchDlInfo */

            //以前の名前・住所と一緒ならスキップ
            if($prev_name == $info->getInvestorName() && $prev_address == $info->getInvestorAddress()) continue;

            echo sprintf("(%03d / %03d)(%02d / %02d件) %s %s %s %s %s\n", $zip_i, $zip_count, $i, $count, $info->getPublisherName(), $info->getTitle(), $info->getInvestorGenre(), $info->getInvestorName(), $info->getInvestorAddress());

            //出力するデータ
            $csv_line = array(
                $info->getEdinetCode(), //EDINEWTコード
                $info->getTitle(), //大量報告書のタイトル
                $info->getPublisherName(), //発行者名
                $info->getPublisherOtc(),//発行者の上場・非上場
                $info->getPublisherTicker(),//証券コード
                $info->getPublisherStockListing(),//発行者の上場金融取引所
                $info->getInvestorGenre(),//提出者種別（個人に絞る）
                $info->getInvestorName(),//提出者の氏名又は名称
                $info->getInvestorAddress(),//提出者の住所又は本店所在地
            );

            fputcsv($fp, $csv_line, ',', '"');

            $prev_name = $info->getInvestorName();
            $prev_address = $info->getInvestorAddress();
        }


        //解凍したディレクトリは消しておく
        delTree($extracte_to);
    } else {
        echo '失敗:' . $zip_file['file_path'] . "\n";
    }
}

fclose($fp);

exit;


/////
function delTree($dir)
{
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

/**
 * @param $extracte_to
 * @return XbrlSearchDlInfo[]
 */
function getListFromCSVIndex($extracte_to)
{
    $ret_array = array();

    $csv_lines = explode("\n", mb_convert_encoding(file_get_contents(($extracte_to . '/XbrlSearchDlInfo.csv')), 'UTF-8', 'SJIS'));
    foreach ($csv_lines as $csv_line) {
        if (trim($csv_line) == "") continue;

        $csv = explode(",", $csv_line);

        //最初の情報行は無視
        if ($csv[0] == 'ダウンロードデータ作成日') continue;
        if ($csv[0] == '書類管理番号') continue;

        $XbrlSearchDlInfo = new XbrlSearchDlInfo();
        $XbrlSearchDlInfo->setDocumentCode($csv[0]);
        $XbrlSearchDlInfo->setTitle($csv[1]);
        $XbrlSearchDlInfo->setEdinetCode($csv[2]);
        $XbrlSearchDlInfo->setPublisher($csv[3]);
        $ret_array[] = $XbrlSearchDlInfo;
        $XbrlSearchDlInfo = null;
    }
    return $ret_array;
}

/**
 * @param XbrlSearchDlInfo $XbrlSearchDlInfo
 */
/**
 * @param $XbrlSearchDlInfo
 * @param $extracte_to
 * @return XbrlSearchDlInfo[]
 */
function getDocumentInfo($XbrlSearchDlInfo, $extracte_to)
{
    //本文ファイルを探す
    $honbun_file = getHonbunFilename($extracte_to . '/' . $XbrlSearchDlInfo->getDocumentCode() . '/XBRL/PublicDoc/');

    //エラー処理
    if ($honbun_file == null) {
        //書類情報を表示して、終了
        var_dump($extracte_to);
        var_dump($XbrlSearchDlInfo->getDocumentCode());
        var_dump($honbun_file);
        die();
    }

    $data = parse_doc_html(file_get_contents($honbun_file));

    $ret_array = array();
    for ($i = 0; $i < count($data['investors']); $i++) { //提出者が複数人いる場合があるので
        $info = new XbrlSearchDlInfo();
        $info = $XbrlSearchDlInfo; //初期値は元ネタ借りる

        /* @var $publisher Publisher */
        $publisher = $data['publisher'];
        $info->setPublisherName($publisher->getName());
        $info->setPublisherTicker($publisher->getTicker());
        $info->setPublisherOtc($publisher->getOtc());
        $info->setPublisherStockListing($publisher->getStockListing());

        $investor = $data['investors'][$i];
        /* @var $investor Investor */

        //注意：個人の場合にだけに絞る
        if (!preg_match('/個人/', $investor->getGenre())) continue;

        $info->setInvestorGenre($investor->getGenre());
        $info->setInvestorName($investor->getName());
        $info->setInvestorAddress($investor->getAddress());

        $ret_array[] = $info;
    }

    return $ret_array;
}

function getHonbunFilename($dir)
{
    $dir_h = opendir($dir);
    $file_list = array();
    while (false !== ($file_list[] = readdir($dir_h))) ;
    closedir($dir_h);

//ディレクトリ内のファイル名を１つずつを取得
    foreach ($file_list as $file_name) {
        //ファイルのみを表示
        if (is_file($dir . $file_name)) {
            $p = pathinfo($dir . $file_name);
            if ($p["extension"] == 'htm' &&
                (preg_match('_honbun_', $p["filename"]) || preg_match('_holder_', $p["filename"]))
            ) {
                return $dir . $p["basename"];
            }
        }
    }

    return null;
}

function parse_doc_html($html)
{
    $ret_array = array();
    $dom = str_get_html($html);

    if($dom==null) return array();

    //発行者に関する情報
    $publisher_name = $dom->find('ix:nonnumeric[name="jplvh_cor:NameOfIssuer"]'); //発行者の名称
    $publisher_ticker = $dom->find('ix:nonnumeric[name="jplvh_cor:SecurityCodeOfIssuer"]'); //証券コード
    $publisher_OTC = $dom->find('ix:nonnumeric[name="jplvh_cor:ListedOrOTC"]'); //上場・店頭の別
    $publisher_StockListing = $dom->find('ix:nonnumeric[name="jplvh_cor:StockListing"]'); //上場金融商品取引所

    $publisher = new Publisher();
    if(isset($publisher_name[0])) {
        $publisher->setName(get_string_from_html($publisher_name[0]));
        $publisher->setTicker(get_string_from_html($publisher_ticker[0]));
        $publisher->setOtc(get_string_from_html($publisher_OTC[0]));
        $publisher->setStockListing(get_string_from_html($publisher_StockListing[0]));
    }


    //提出者に関する情報
    $genre = $dom->find('ix:nonnumeric[name="jplvh_cor:IndividualOrCorporation"]'); //個人・法人の別
    $name = $dom->find('ix:nonnumeric[name="jplvh_cor:Name"]'); //氏名又は名称
    $address = $dom->find('ix:nonnumeric[name="jplvh_cor:ResidentialAddressOrAddressOfRegisteredHeadquarter"]'); //住所又は本店所在地

    //該当する件数を取得
    $count = count($genre); //「個人・法人の別」の件数を基準。氏名又は名称の出現回数がなぜが多い、けど整数倍だった。

    $investors = array();
    for ($i = 0; $i < $count; $i++) {
        $investor = new Investor();
        $investor->setGenre(get_string_from_html($genre[$i]));
        $investor->setName(get_string_from_html($name[$i]));
        $investor->setAddress(get_string_from_html($address[$i]));
        $investors[] = $investor;
    }

    //返却値を作る
    $ret_array = array(
        'publisher' => $publisher,
        'investors' => $investors
    );

    return $ret_array;
}

function get_string_from_html($html)
{
    $str = htmlspecialchars_decode(strip_tags((string)$html));
    $str = preg_replace('/&#160;/', ' ', $str); //&#160; 半角空白
    return $str;
}
