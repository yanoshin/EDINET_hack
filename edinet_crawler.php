<?php
/**
 * Created by IntelliJ IDEA.
 * User: yanoshin
 * Date: 6/20/15
 * Time: 13:30

 * これはEDINETから大量保有報告書のXBRLデータ（zip）を一括ダウンロードして保存するスクリプト
 * 検索条件を「全期間」にしてmita(=$url1)けど、XBRL形式での対象保有報告書の提出は2014年以降（先行試行の２０１３年も含む）
 * なので、報告書約２万件くらい？
 */

$url1 = "https://disclosure.edinet-fsa.go.jp/E01EW/BLMainController.jsp?uji.verb=W1E63010CXW1E6A010DSPSch&uji.bean=ee.bean.parent.EECommonSearchBean&TID=W1E63011&PID=W1E63010&SESSIONKEY=1434732394999&lgKbn=2&pkbn=0&skbn=0&dskb=&dflg=0&iflg=0&preId=1&row=100&idx=%d&syoruiKanriNo=&mul=&lpr=on&cal=1&era=H&yer=&mon=&pfs=5";
$url2 = "https://disclosure.edinet-fsa.go.jp/E01EW/download?uji.verb=W1E63011CXW1E6A011DSPXbrl&uji.bean=ee.bean.parent.EECommonSearchBean&TID=W1E63011&PID=W1E63010&SESSIONKEY=1434732394999&lgKbn=2&pkbn=0&skbn=1&dskb=&askb=&dflg=0&iflg=0&preId=1&mul=&lpr=on&cal=1&era=H&yer=&mon=&pfs=5&row=100&idx=%d&str=&kbn=1&flg=&syoruiKanriNo=";


$num_index = 57437/100 + 1;

for($i=0;$i<$num_index;$i++){
    $url = sprintf($url1, $i*100);
    $curl = curl_init($url);
    $headers = array(
        'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36',
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($curl);
    // Then, after your curl_exec call:
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $response_headers = explode("\n", substr($response, 0, $header_size));
    $extra_headers = array();
    foreach($response_headers as $response_header) {
        if(strpos($response_header, 'Set-Cookie:')===0){
            $extra_headers[] = substr(trim($response_header),4);
        }
    }
    curl_close($curl);

    echo sprintf('./xbrl/%03d.zip',$i)."\n";

    $url = sprintf($url2, $i*100);
    //echo $url."\n";
    $curl = curl_init($url);

    foreach($extra_headers as $extra_header) {
        $headers[] = $extra_header;
    }
    //var_dump($headers);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($curl);

    file_put_contents(sprintf('./xbrl/%03d.zip',$i),$result);

    curl_close($curl);
}

