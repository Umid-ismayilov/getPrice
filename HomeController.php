<?php

namespace App\Http\Controllers\Menu;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;



class HomeController extends Controller
{
    public function bot(Request $request){

        if($request->method()=='POST') {
            try {
                $url = $request->url;
                $datas = [];
                if (strpos($url, 'trendyol')) {
                    $datas = $this->trendYol($url);
                } elseif (strpos($url, 'defacto')) {
                    $datas = $this->defacto($url);
                } elseif (strpos($url, 'hepsiburada')) {
                    $datas = $this->hepsiburada($url);
                }
                return response()->json($datas, 200);
            }catch (\Exception $exception){
//                $exception->getMessage()
                return response()->json([], 401);
            }
        }
        else{
            return view('menu.bot',[]);
        }

    }
    private function trendYol($url){

        $html = self::curl($url);
        $dom = new \DomDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $datas['title']   =  utf8_decode($xpath->query('/html/head/meta[@name="description"]/@content')[0]->value);
        $datas['fiyat1']  = $xpath->query('/html/head/meta[@name="twitter:data1"]/@content')[0]->value;
        $datas['fiyat2']  = $xpath->query('/html/head/meta[@name="twitter:data2"]/@content')[0]->value;
        return $datas;

    }
    private function defacto($url){
        $html = self::curl($url);
        $dom = new \DomDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $datas['title']    =  $xpath->query('/html/head/meta[@name="description"]/@content')[0]->value;
//        $datas['fiyat1']   = $xpath->query('//*[@id="product-main-container"]/div[1]/div/div[1]/div/div/div[3]/div/div[1]/div/div[1]/div/div[1]')[0]->childNodes[0]->data;
        $datas['fiyat']    = $xpath->query('//*[@id="product-main-container"]/div[1]/div/div[1]/div/div/div[3]/div/div[1]/div/div[1]/div/div[1]')[0]->childNodes[0]->data;
        return $datas;

    }
    private function hepsiburada($url){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'Authority: user-content-gw-hermes.hepsiburada.com';
        $headers[] = 'Accept: application/json, text/plain, */*';
        $headers[] = 'Accept-Language: en-US,en;q=0.9';
        $headers[] = 'Authorization: Bearer undefined';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Dnt: 1';
        $headers[] = 'Origin: https://www.hepsiburada.com';
        $headers[] = "Referer: $url";
        $headers[] = 'Sec-Ch-Ua: ^^';
        $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
        $headers[] = 'Sec-Ch-Ua-Platform: ^^Windows^^\"\"';
        $headers[] = 'Sec-Fetch-Dest: empty';
        $headers[] = 'Sec-Fetch-Mode: cors';
        $headers[] = 'Sec-Fetch-Site: same-site';
        $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36 Edg/101.0.1210.53';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $html = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $dom = new \DomDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $data = [];
        $data['title']    =  trim(str_replace("\r\n","",$xpath->query('//*[@id="product-name"]')[0]->childNodes[0]->data));
        $data['fiyat1']   =  $xpath->query('//*[@id="offering-price"]')[0]->attributes[4]->value;
        return response()->json($data,200);
    }
    static function curl($url){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $html = curl_exec($curl);
        return $html;
    }
    
}
